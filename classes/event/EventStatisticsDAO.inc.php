<?php

/**
 * EventStatisticsDAO.inc.php
 *
 * Copyright (c) 2003-2006 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package event
 *
 * Class for Event Statistics DAO.
 * Operations for retrieving event statistics.
 *
 * $Id$
 */

define('REPORT_TYPE_CONFERENCE',	0x00001);
define('REPORT_TYPE_EVENT',	0x00002);
define('REPORT_TYPE_EDITOR',	0x00003);
define('REPORT_TYPE_REVIEWER',	0x00004);
define('REPORT_TYPE_TRACK',	0x00005);

class EventStatisticsDAO extends DAO {
	/**
	 * Constructor.
	 */
	function EventDAO() {
		parent::DAO();
	}

	/**
	 * Get statistics about papers in the system.
	 * Returns a map of name => value pairs.
	 * @param $eventId int The event to fetch statistics for
	 * @param $trackId int The track to query stats for (optional)
	 * @param $dateStart date The submit date to search from; optional
	 * @param $dateEnd date The submit date to search to; optional
	 * @return array
	 */
	function getPaperStatistics($eventId, $trackIds = null, $dateStart = null, $dateEnd = null) {
		$params = array($eventId);
		if (!empty($trackIds)) {
			$trackSql = ' AND (a.track_id = ?';
			$params[] = array_shift($trackIds);
			foreach ($trackIds as $trackId) {
				$trackSql .= ' OR a.track_id = ?';
				$params[] = $trackId;
			}
			$trackSql .= ')';
		} else $trackSql = '';

		$sql =	'SELECT a.paper_id AS paper_id, a.date_submitted AS date_submitted, pa.date_published AS date_published, pa.pub_id AS pub_id, d.decision FROM papers a LEFT JOIN published_papers pa ON (a.paper_id = pa.paper_id) LEFT JOIN edit_decisions d ON (d.paper_id = a.paper_id) WHERE a.event_id = ?' .
			($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
			$trackSql .
			' ORDER BY a.paper_id, d.date_decided DESC';

		$result = &$this->retrieve($sql, $params);

		$returner = array(
			'numSubmissions' => 0,
			'numPublishedSubmissions' => 0,
			'submissionsAccept' => 0,
			'submissionsDecline' => 0,
			'submissionsRevise' => 0,
			'submissionsUndecided' => 0,
			'submissionsAcceptPercent' => 0,
			'submissionsDeclinePercent' => 0,
			'submissionsRevisePercent' => 0,
			'submissionsUndecidedPercent' => 0,
			'daysToPublication' => 0
		);

		// Track which papers we're including
		$paperIds = array();

		$totalTimeToPublication = 0;
		$timeToPublicationCount = 0;

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);

			// For each paper, pick the most recent editor
			// decision only and ignore the rest. Depends on sort
			// order. FIXME -- there must be a better way of doing
			// this that's database independent.
			if (!in_array($row['paper_id'], $paperIds)) {
				$paperIds[] = $row['paper_id'];
				$returner['numSubmissions']++;

				if (!empty($row['pub_id'])) {
					$returner['numPublishedSubmissions']++;
				}

				if (!empty($row['date_submitted']) && !empty($row['date_published'])) {
					$timeSubmitted = strtotime($this->datetimeFromDB($row['date_submitted']));
					$timePublished = strtotime($this->datetimeFromDB($row['date_published']));
					if ($timePublished > $timeSubmitted) {
						$totalTimeToPublication += ($timePublished - $timeSubmitted);
						$timeToPublicationCount++;
					}
				}

				import('submission.common.Action');
				switch ($row['decision']) {
					case SUBMISSION_EDITOR_DECISION_ACCEPT:
						$returner['submissionsAccept']++;
						break;
					case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
					case SUBMISSION_EDITOR_DECISION_RESUBMIT:
						$returner['submissionsRevise']++;
						break;
					case SUBMISSION_EDITOR_DECISION_DECLINE:
						$returner['submissionsDecline']++;
						break;
					default:
						// If an paper is published
						// but no decision recorded,
						// assume it was accepted
						if (isset($row['pub_id'])) {
							$returner['submissionsAccept']++;
						} else {
							$returner['submissionsUndecided']++;
						}
						break;
				}
			}

			$result->moveNext();
		}

		$result->Close();
		unset($result);

		// Calculate percentages where necessary
		if ($returner['numSubmissions'] != 0) {
			$returner['submissionsAcceptPercent'] = round($returner['submissionsAccept'] * 100 / $returner['numSubmissions']);
			$returner['submissionsDeclinePercent'] = round($returner['submissionsDecline'] * 100 / $returner['numSubmissions']);
			$returner['submissionsRevisePercent'] = round($returner['submissionsRevise'] * 100 / $returner['numSubmissions']);
			$returner['submissionsUndecidedPercent'] = round($returner['submissionsUndecided'] * 100 / $returner['numSubmissions']);
		}

		if ($timeToPublicationCount != 0) {
			// Keep one sig fig
			$returner['daysToPublication'] = round($totalTimeToPublication / $timeToPublicationCount / 60 / 60 / 24);
		}

		return $returner;
	}
	
	/**
	 * Get statistics about users in the system.
	 * Returns a map of name => value pairs.
	 * @param $eventId int The event to fetch statistics for
	 * @param $dateStart date optional
	 * @param $dateEnd date optional
	 * @return array
	 */
	function getUserStatistics($eventId, $dateStart = null, $dateEnd = null) {
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		// Get count of total users for this event
		$result = &$this->retrieve(
			'SELECT COUNT(DISTINCT r.user_id) FROM roles r, users u WHERE r.user_id = u.user_id AND r.event_id = ?' .
			($dateStart !== null ? ' AND u.date_registered >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND u.date_registered <= ' . $this->datetimeToDB($dateEnd) : ''),
			$eventId
		);

		$returner = array(
			'totalUsersCount' => $result->fields[0]
		);

		$result->Close();
		unset($result);

		// Get user counts for each role.
		$result = &$this->retrieve(
			'SELECT r.role_id, COUNT(r.user_id) AS role_count FROM roles r LEFT JOIN users u ON (r.user_id = u.user_id) WHERE r.event_id = ?' .
			($dateStart !== null ? ' AND u.date_registered >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND u.date_registered <= ' . $this->datetimeToDB($dateEnd) : '') .
			'GROUP BY r.role_id',
			$eventId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[$roleDao->getRolePath($row['role_id'])] = $row['role_count'];
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get statistics about registrations.
	 * @param $eventId int The event to fetch statistics for
	 * @param $dateStart date optional
	 * @param $dateEnd date optional
	 * @return array
	 */
	function getRegistrationStatistics($eventId, $dateStart = null, $dateEnd = null) {
		$result = &$this->retrieve(
			'SELECT st.type_id,
				st.type_name,
				count(s.registration_id) AS type_count
			FROM registration_types st,
				registrations s
			WHERE st.event_id = ?
				AND s.type_id = st.type_id' .
				($dateStart !== null ? ' AND st.opening_date >= ' . $this->datetimeToDB($dateStart) : '') .
				($dateEnd !== null ? ' AND st.closing_date <= ' . $this->datetimeToDB($dateEnd) : '') .
			' GROUP BY st.type_id, st.type_name',
			$eventId
		);

		$returner = array();

		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$returner[$row['type_id']] = array(
				'name' => $row['type_name'],
				'count' => $row['type_count']
			);
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get statistics about issues in the system.
	 * Returns a map of name => value pairs.
	 * @param $eventId int The event to fetch statistics for
	 * @param $dateStart date The publish date to search from; optional
	 * @param $dateEnd date The publish date to search to; optional
	 * @return array
	 */
	function getIssueStatistics($eventId, $dateStart = null, $dateEnd = null) {
		$result = &$this->retrieve(
			'SELECT COUNT(*) AS count, published FROM issues  WHERE event_id = ?' .
			($dateStart !== null ? ' AND date_published >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND date_published <= ' . $this->datetimeToDB($dateEnd) : '') .
			' GROUP BY published',
			$eventId
		);

		$returner = array(
			'numPublishedIssues' => 0,
			'numUnpublishedIssues' => 0
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);

			if ($row['published']) {
				$returner['numPublishedIssues'] = $row['count'];
			} else {
				$returner['numUnpublishedIssues'] = $row['count'];
			}
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		$returner['numIssues'] = $returner['numPublishedIssues'] + $returner['numUnpublishedIssues'];

		return $returner;
	}

	/**
	 * Get statistics about reviewers in the system.
	 * Returns a map of name => value pairs.
	 * @param $eventId int The event to fetch statistics for
	 * @param $dateStart date The publish date to search from; optional
	 * @param $dateEnd date The publish date to search to; optional
	 * @return array
	 */
	function getReviewerStatistics($eventId, $trackIds, $dateStart = null, $dateEnd = null) {
		$params = array($eventId);
		if (!empty($trackIds)) {
			$trackSql = ' AND (a.track_id = ?';
			$params[] = array_shift($trackIds);
			foreach ($trackIds as $trackId) {
				$trackSql .= ' OR a.track_id = ?';
				$params[] = $trackId;
			}
			$trackSql .= ')';
		} else $trackSql = '';

		$sql =	'SELECT a.paper_id, af.date_uploaded AS date_rv_uploaded, r.review_id AS review_id, u.date_registered AS date_registered, r.reviewer_id AS reviewer_id, r.quality AS quality, r.date_assigned AS date_assigned, r.date_completed AS date_completed FROM papers a, paper_files af, review_assignments r LEFT JOIN users u ON (u.user_id = r.reviewer_id) WHERE a.event_id = ? AND r.paper_id = a.paper_id AND af.paper_id = a.paper_id AND af.file_id = a.review_file_id AND af.revision = 1' .
			($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
			$trackSql;
		$result = &$this->retrieve($sql, $params);

		$returner = array(
			'reviewsCount' => 0,
			'reviewerScore' => 0,
			'daysPerReview' => 0,
			'reviewerAddedCount' => 0,
			'reviewerCount' => 0,
			'reviewedSubmissionsCount' => 0
		);

		$scoredReviewsCount = 0;
		$totalScore = 0;
		$completedReviewsCount = 0;
		$totalElapsedTime = 0;
		$reviewerList = array();
		$paperIds = array();

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner['reviewsCount']++;
			if (!empty($row['quality'])) {
				$scoredReviewsCount++;
				$totalScore += $row['quality'];
			}

			$paperIds[] = $row['paper_id'];

			if (!empty($row['reviewer_id']) && !in_array($row['reviewer_id'], $reviewerList)) {
				$returner['reviewerCount']++;
				$dateRegistered = strtotime($this->datetimeFromDB($row['date_registered']));
				if (($dateRegistered >= $dateStart || $dateStart === null) && ($dateRegistered <= $dateEnd || $dateEnd == null)) {
					$returner['reviewerAddedCount']++;
				}
				array_push($reviewerList, $row['reviewer_id']);
			}

			if (!empty($row['date_assigned']) && !empty($row['date_completed'])) {
				$timeReviewVersionUploaded = strtotime($this->datetimeFromDB($row['date_rv_uploaded']));
				$timeCompleted = strtotime($this->datetimeFromDB($row['date_completed']));
				if ($timeCompleted > $timeReviewVersionUploaded) {
					$completedReviewsCount++;
					$totalElapsedTime += ($timeCompleted - $timeReviewVersionUploaded);
				}
			}
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		if ($scoredReviewsCount > 0) {
			// To one decimal place
			$returner['reviewerScore'] = round($totalScore * 10 / $scoredReviewsCount) / 10;
		}
		if ($completedReviewsCount > 0) {
			$seconds = $totalElapsedTime / $completedReviewsCount;
			$returner['daysPerReview'] = $seconds / 60 / 60 / 24;
		}

		$paperIds = array_unique($paperIds);
		$returner['reviewedSubmissionsCount'] = count($paperIds);

		return $returner;
	}

	function &getCountryDistribution($eventId, $locale = null) {
		if ($locale == null) $locale = Locale::getLocale();
		$result = &$this->retrieve(
			'SELECT DISTINCT u.country AS country FROM users u, roles r WHERE r.event_id = ? AND r.user_id = u.user_id',
			$eventId
		);

		$countries = array();
		$countryDao =& DAORegistry::getDAO('CountryDAO');
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			array_push($countries, $countryDao->getCountry($row['country'], $locale));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $countries;
	}

	/**
	 * Generate a Event Report between the given dates (optional)
	 * @param $eventId int The event to report on
	 * @param $dateStart string The start date
	 * @param $dateEnd string The end date
	 */
	function &getEventReport($eventId, $dateStart = null, $dateEnd = null) {
		$result = &$this->retrieve(
			'SELECT a.paper_id AS paper_id, a.title AS paper_title, ' .
			'pa.pub_id AS pub_id, pa.date_published AS date_published, ' .
			's.title AS track_title, s.title_alt1 AS track_title_alt1, s.title_alt2 AS track_title_alt2, ' .
			'a.date_submitted AS date_submitted, a.status AS status ' .
			'FROM ' .
			'papers a ' .
			'LEFT JOIN tracks s ON (s.track_id = a.track_id) ' .
			'LEFT JOIN published_papers pa ON (a.paper_id = pa.paper_id) ' .
			'WHERE a.event_id = ? ' .
			($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
			' ORDER BY a.date_submitted',
			$eventId
		);
		import('event.EventReportIterator');
		$report =& new EventReportIterator($eventId, $result, $dateStart, $dateEnd, REPORT_TYPE_EVENT);
		return $report;
	}

	/**
	 * Generate a Track Report between the given dates (optional)
	 * @param $eventId int The event to report on
	 * @param $dateStart string The start date
	 * @param $dateEnd string The end date
	 */
	function &getTrackReport($eventId, $dateStart = null, $dateEnd = null) {
		$result = &$this->retrieve(
			'SELECT a.paper_id AS paper_id, a.title AS paper_title, ' .
			'pa.pub_id AS pub_id, pa.date_published AS date_published, ' .
			's.title AS track_title, s.title_alt1 AS track_title_alt1, s.title_alt2 AS track_title_alt2, ' .
			'a.date_submitted AS date_submitted, a.status AS status ' .
			'FROM ' .
			'papers a ' .
			'LEFT JOIN tracks s ON (s.track_id = a.track_id) ' .
			'LEFT JOIN published_papers pa ON (a.paper_id = pa.paper_id) ' .
			'WHERE a.event_id = ? ' .
			($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
			' ORDER BY s.title, a.date_submitted',
			$eventId
		);
		import('event.EventReportIterator');
		$report =& new EventReportIterator($eventId, $result, $dateStart, $dateEnd, REPORT_TYPE_SECTION);
		return $report;
	}

	/**
	 * Generate a Reviewer Report between the given dates (optional)
	 * @param $eventId int The event to report on
	 * @param $dateStart string The start date
	 * @param $dateEnd string The end date
	 */
	function &getReviewerReport($eventId, $dateStart = null, $dateEnd = null) {
		$result = &$this->retrieve(
			'SELECT ra.reviewer_id, ra.quality, a.paper_id AS paper_id, a.title AS paper_title, ' .
			'pa.pub_id AS pub_id, pa.date_published AS date_published, ' .
			's.title AS track_title, s.title_alt1 AS track_title_alt1, s.title_alt2 AS track_title_alt2, ' .
			'a.date_submitted AS date_submitted, a.status AS status ' .
			'FROM ' .
			'review_assignments ra, ' .
			'papers a ' .
			'LEFT JOIN tracks s ON (s.track_id = a.track_id) ' .
			'LEFT JOIN published_papers pa ON (a.paper_id = pa.paper_id) ' .
			'WHERE a.event_id = ? AND ra.paper_id = a.paper_id ' .
			($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
			' ORDER BY ra.reviewer_id, a.date_submitted',
			$eventId
		);
		import('event.EventReportIterator');
		$report =& new EventReportIterator($eventId, $result, $dateStart, $dateEnd, REPORT_TYPE_REVIEWER);
		return $report;
	}

	/**
	 * Generate an Editor Report between the given dates (optional)
	 * @param $eventId int The event to report on
	 * @param $dateStart string The start date
	 * @param $dateEnd string The end date
	 */
	function &getEditorReport($eventId, $dateStart = null, $dateEnd = null) {
		$result = &$this->retrieve(
			'SELECT ee.editor_id, a.paper_id AS paper_id, a.title AS paper_title, ' .
			'pa.pub_id AS pub_id, pa.date_published AS date_published, ' .
			's.title AS track_title, s.title_alt1 AS track_title_alt1, s.title_alt2 AS track_title_alt2, ' .
			'a.date_submitted AS date_submitted, a.status AS status ' .
			'FROM ' .
			'papers a ' .
			'LEFT JOIN edit_assignments ee ON (ee.paper_id = a.paper_id) ' .
			'LEFT JOIN tracks s ON (s.track_id = a.track_id) ' .
			'LEFT JOIN published_papers pa ON (a.paper_id = pa.paper_id) ' .
			'WHERE a.event_id = ? ' .
			($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
			' ORDER BY ee.editor_id, a.date_submitted',
			$eventId
		);
		import('event.EventReportIterator');
		$report =& new EventReportIterator($eventId, $result, $dateStart, $dateEnd, REPORT_TYPE_EDITOR);
		return $report;
	}

	/**
	 * Determine, within a given event and date range (optional),
	 * the maximum number of authors that a single submission has.
	 * @param $eventId int
	 * @param $dateStart string
	 * @param $dateEnd string
	 */
	function getMaxAuthorCount($eventId, $dateStart, $dateEnd) {
		$result = &$this->retrieve(
			'SELECT COUNT(aa.author_id) ' .
			'FROM ' .
			'papers a, ' .
			'paper_authors aa ' .
			'WHERE a.event_id = ? ' .
			'AND aa.paper_id = a.paper_id ' .
			($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
			' GROUP BY a.paper_id',
			$eventId
		);

		$max = null;
		while (!$result->EOF) {
			if ($max === null || $max < $result->fields[0]) {
				$max = $result->fields[0];
			}
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $max;
	}

	/**
	 * Determine, within a given event and date range (optional),
	 * the maximum number of reviewers that a single submission has.
	 * @param $eventId int
	 * @param $dateStart string
	 * @param $dateEnd string
	 */
	function getMaxReviewerCount($eventId, $dateStart, $dateEnd) {
		$result = &$this->retrieve(
			'SELECT COUNT(r.review_id) ' .
			'FROM ' .
			'papers a, ' .
			'review_assignments r ' .
			'WHERE a.event_id = ? ' .
			'AND r.paper_id = a.paper_id ' .
			($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
			' GROUP BY r.paper_id',
			$eventId
		);

		$max = null;
		while (!$result->EOF) {
			if ($max === null || $max < $result->fields[0]) {
				$max = $result->fields[0];
			}
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $max;
	}

	/**
	 * Determine, within a given event and date range (optional),
	 * the maximum number of editors that a single submission has.
	 * @param $eventId int
	 * @param $dateStart string
	 * @param $dateEnd string
	 */
	function getMaxEditorCount($eventId, $dateStart, $dateEnd) {
		$result = &$this->retrieve(
			'SELECT COUNT(e.editor_id) ' .
			'FROM ' .
			'papers a, ' .
			'edit_assignments e ' .
			'WHERE a.event_id = ? ' .
			'AND e.paper_id = a.paper_id ' .
			($dateStart !== null ? ' AND a.date_submitted >= ' . $this->datetimeToDB($dateStart) : '') .
			($dateEnd !== null ? ' AND a.date_submitted <= ' . $this->datetimeToDB($dateEnd) : '') .
			' GROUP BY e.paper_id',
			$eventId
		);

		$max = null;
		while (!$result->EOF) {
			if ($max === null || $max < $result->fields[0]) {
				$max = $result->fields[0];
			}
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $max;
	}
}

?>
