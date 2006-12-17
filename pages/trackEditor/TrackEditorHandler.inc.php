<?php

/**
 * TrackEditorHandler.inc.php
 *
 * Copyright (c) 2003-2007 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.trackEditor
 *
 * Handle requests for track editor functions. 
 *
 * $Id$
 */

import('submission.trackEditor.TrackEditorAction');

class TrackEditorHandler extends Handler {

	/**
	 * Display track editor index page.
	 */
	function index($args) {
		TrackEditorHandler::validate();
		TrackEditorHandler::setupTemplate();

		$event = &Request::getEvent();
		$user = &Request::getUser();

		$rangeInfo = Handler::getRangeInfo('submissions');

		// Get the user's search conditions, if any
		$searchField = Request::getUserVar('searchField');
		$dateSearchField = Request::getUserVar('dateSearchField');
		$searchMatch = Request::getUserVar('searchMatch');
		$search = Request::getUserVar('search');

		$fromDate = Request::getUserDateVar('dateFrom', 1, 1);
		if ($fromDate !== null) $fromDate = date('Y-m-d H:i:s', $fromDate);
		$toDate = Request::getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
		if ($toDate !== null) $toDate = date('Y-m-d H:i:s', $toDate);

		$trackDao = &DAORegistry::getDAO('TrackDAO');
		$trackEditorSubmissionDao = &DAORegistry::getDAO('TrackEditorSubmissionDAO');

		$page = isset($args[0]) ? $args[0] : '';
		$tracks = &$trackDao->getTrackTitles($event->getEventId());

		switch($page) {
			case 'submissionsInEditing':
				$functionName = 'getTrackEditorSubmissionsInEditing';
				$helpTopicId = 'editorial.trackEditorsRole.submissions.inEditing';
				break;
			case 'submissionsArchives':
				$functionName = 'getTrackEditorSubmissionsArchives';
				$helpTopicId = 'editorial.trackEditorsRole.submissions.archives';
				break;
			default:
				$page = 'submissionsInReview';
				$functionName = 'getTrackEditorSubmissionsInReview';
				$helpTopicId = 'editorial.trackEditorsRole.submissions.inReview';
		}

		$submissions = &$trackEditorSubmissionDao->$functionName(
			$user->getUserId(),
			$event->getEventId(),
			Request::getUserVar('track'),
			$searchField,
			$searchMatch,
			$search,
			$dateSearchField,
			$fromDate,
			$toDate,
			$rangeInfo
		);

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', $helpTopicId);
		$templateMgr->assign('trackOptions', array(0 => Locale::Translate('editor.allTracks')) + $tracks);
		$templateMgr->assign_by_ref('submissions', $submissions);
		$templateMgr->assign('track', Request::getUserVar('track'));
		$templateMgr->assign('pageToDisplay', $page);
		$templateMgr->assign('trackEditor', $user->getFullName());

		// Set search parameters
		$duplicateParameters = array(
			'searchField', 'searchMatch', 'search',
			'dateFromMonth', 'dateFromDay', 'dateFromYear',
			'dateToMonth', 'dateToDay', 'dateToYear',
			'dateSearchField'
		);
		foreach ($duplicateParameters as $param)
			$templateMgr->assign($param, Request::getUserVar($param));

		$templateMgr->assign('reviewType', Array(
			REVIEW_PROGRESS_ABSTRACT => Locale::translate('submission.abstract'),
			REVIEW_PROGRESS_PAPER => Locale::translate('submission.paper')
		));
		
		$templateMgr->assign('dateFrom', $fromDate);
		$templateMgr->assign('dateTo', $toDate);
		$templateMgr->assign('fieldOptions', Array(
			SUBMISSION_FIELD_TITLE => 'paper.title',
			SUBMISSION_FIELD_AUTHOR => 'user.role.author',
			SUBMISSION_FIELD_EDITOR => 'user.role.editor'
		));
		$templateMgr->assign('dateFieldOptions', Array(
			SUBMISSION_FIELD_DATE_SUBMITTED => 'submissions.submitted'
		));

		$templateMgr->display('trackEditor/index.tpl');
	}

	/**
	 * Validate that user is a track editor in the selected conference.
	 * Redirects to user index page if not properly authenticated.
	 */
	function validate() {
		parent::validate();
		$conference = &Request::getConference();
		$event = &Request::getEvent();

		$page = Request::getRequestedPage();

		if (!isset($conference) || !isset($event)) {
			Validation::redirectLogin();
		}
		
		if($page == ROLE_PATH_TRACK_EDITOR && !Validation::isTrackEditor($conference->getConferenceId(), $event->getEventId())) {
			Validation::redirectLogin();
		}
		
		if($page == ROLE_PATH_EDITOR && !Validation::isEditor($conference->getConferenceId(), $event->getEventId())) {
			Validation::redirectLogin();
		}
	}
	
	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false, $paperId = 0, $parentPage = null, $showSidebar = true) {
		$templateMgr = &TemplateManager::getManager();
		$isEditor = Validation::isEditor();

		if (Request::getRequestedPage() == 'editor') {
			EditorHandler::setupTemplate(EDITOR_TRACK_SUBMISSIONS, $showSidebar, $paperId, $parentPage);
			$templateMgr->assign('helpTopicId', 'editorial.editorsRole');
			
		} else {
			$templateMgr->assign('helpTopicId', 'editorial.trackEditorsRole');

			$pageHierarchy = $subclass ? array(array(Request::url(null, null, 'user'), 'navigation.user'), array(Request::url(null, null, $isEditor?'editor':'trackEditor'), $isEditor?'user.role.editor':'user.role.trackEditor'), array(Request::url(null, null, $isEditor?'editor':'trackEditor'), 'paper.submissions'))
				: array(array(Request::url(null, null, 'user'), 'navigation.user'), array(Request::url(null, null, $isEditor?'editor':'trackEditor'), $isEditor?'user.role.editor':'user.role.trackEditor'));

			import('submission.trackEditor.TrackEditorAction');
			$submissionCrumb = TrackEditorAction::submissionBreadcrumb($paperId, $parentPage, 'trackEditor');
			if (isset($submissionCrumb)) {
				$pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
			}
			$templateMgr->assign('pageHierarchy', $pageHierarchy);

			if ($showSidebar) {
				$templateMgr->assign('sidebarTemplate', 'trackEditor/navsidebar.tpl');
				$event = &Request::getEvent();
				$user = &Request::getUser();

				$trackEditorSubmissionDao = &DAORegistry::getDAO('TrackEditorSubmissionDAO');
				$submissionsCount = &$trackEditorSubmissionDao->getTrackEditorSubmissionsCount($user->getUserId(), $event->getEventId());
				$templateMgr->assign('submissionsCount', $submissionsCount);
			}
		}
	}
	
	/**
	 * Display submission management instructions.
	 * @param $args (type)
	 */
	function instructions($args) {
		import('submission.trackEditor.TrackEditorAction');
		if (!isset($args[0]) || !TrackEditorAction::instructions($args[0])) {
			Request::redirect(null, null, Request::getRequestedPage());
		}
	}

	//
	// Submission Tracking
	//

	function enrollSearch($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::enrollSearch($args);
	}

	function createReviewer($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::createReviewer($args);
	}

	function enroll($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::enroll($args);
	}

	function submission($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::submission($args);
	}

	function submissionRegrets($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionRegrets($args);
	}
	
	function submissionReview($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionReview($args);
	}
	
	function submissionEditing($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionEditing($args);
	}
	
	function submissionHistory($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionHistory($args);
	}
	
	function changeTrack() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::changeTrack();
	}
	
	function recordDecision() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::recordDecision();
	}
	
	function selectReviewer($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectReviewer($args);
	}
	
	function notifyReviewer($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyReviewer($args);
	}
	
	function notifyAllReviewers($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyAllReviewers($args);
	}
	
	function userProfile($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::userProfile($args);
	}
	
	function clearReview($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::clearReview($args);
	}
	
	function cancelReview($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::cancelReview($args);
	}
	
	function remindReviewer($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::remindReviewer($args);
	}

	function acknowledgeReviewerUnderway($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::acknowledgeReviewerUnderway($args);
	}

	function thankReviewer($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankReviewer($args);
	}
	
	function rateReviewer() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::rateReviewer();
	}
	
	function acceptReviewForReviewer($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::acceptReviewForReviewer($args);
	}
	
	function uploadReviewForReviewer($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadReviewForReviewer($args);
	}
	
	function enterReviewerRecommendation($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::enterReviewerRecommendation($args);
	}
	
	function makeReviewerFileViewable() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::makeReviewerFileViewable();
	}
	
	function setDueDate($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::setDueDate($args);
	}
	
	function viewMetadata($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::viewMetadata($args);
	}
	
	function saveMetadata() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::saveMetadata();
	}

	function editorReview() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::editorReview();
	}

	function notifyAuthor($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyAuthor($args);
	}

	function uploadReviewVersion() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadReviewVersion();
	}
	
	function addSuppFile($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::addSuppFile($args);
	}

	function setSuppFileVisibility($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::setSuppFileVisibility($args);
	}

	function editSuppFile($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::editSuppFile($args);
	}
	
	function saveSuppFile($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::saveSuppFile($args);
	}

	function deleteSuppFile($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::deleteSuppFile($args);
	}
	
	function deletePaperFile($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::deletePaperFile($args);
	}
	
	function archiveSubmission($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::archiveSubmission($args);
	}

	function unsuitableSubmission($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::unsuitableSubmission($args);
	}

	function restoreToQueue($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::restoreToQueue($args);
	}
	
	function updateTrack($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::updateTrack($args);
	}

	function updateSecondaryTrack($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::updateSecondaryTrack($args);
	}
	
	
	//
	// Layout Editing
	//
	
	function uploadGalley() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadGalley();
	}
	
	function editGalley($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::editGalley($args);
	}
	
	function saveGalley($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::saveGalley($args);
	}
	
	function orderGalley() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::orderGalley();
	}

	function deleteGalley($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::deleteGalley($args);
	}
	
	function uploadSuppFile() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::uploadSuppFile();
	}
	
	function orderSuppFile() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::orderSuppFile();
	}
	
	
	//
	// Submission History
	//

	function submissionEventLog($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionEventLog($args);
	}		

	function submissionEventLogType($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionEventLogType($args);
	}
	
	function clearSubmissionEventLog($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::clearSubmissionEventLog($args);
	}
	
	function submissionEmailLog($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionEmailLog($args);
	}
	
	function submissionEmailLogType($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionEmailLogType($args);
	}
	
	function clearSubmissionEmailLog($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::clearSubmissionEmailLog($args);
	}

	function addSubmissionNote() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::addSubmissionNote();
	}

	function removeSubmissionNote() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::removeSubmissionNote();
	}		

	function updateSubmissionNote() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::updateSubmissionNote();
	}

	function clearAllSubmissionNotes() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::clearAllSubmissionNotes();
	}

	function submissionNotes($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::submissionNotes($args);
	}
	
	
	//
	// Misc.
	//

	function downloadFile($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::downloadFile($args);
	}
	
	function viewFile($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::viewFile($args);
	}
	
	//
	// Submission Comments
	//
	
	function viewPeerReviewComments($args) {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::viewPeerReviewComments($args);
	}
	
	function postPeerReviewComment() {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::postPeerReviewComment();
	}
	
	function viewEditorDecisionComments($args) {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::viewEditorDecisionComments($args);
	}
	
	function blindCcReviewsToReviewers($args) {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::blindCcReviewsToReviewers($args);
	}
	
	function postEditorDecisionComment() {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::postEditorDecisionComment();
	}
	
	function emailEditorDecisionComment() {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::emailEditorDecisionComment();
	}
	
	function viewLayoutComments($args) {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::viewLayoutComments($args);
	}
	
	function postLayoutComment() {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::postLayoutComment();
	}
	
	function viewProofreadComments($args) {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::viewProofreadComments($args);
	}
	
	function postProofreadComment() {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::postProofreadComment();
	}
		
	function editComment($args) {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::editComment($args);
	}
	
	function saveComment() {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::saveComment();
	}
	
	function deleteComment($args) {
		import('pages.trackEditor.SubmissionCommentsHandler');
		SubmissionCommentsHandler::deleteComment($args);
	}
	
	/** Proof Assignment Functions */
	function selectProofreader($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::selectProofreader($args);
	}

	function queueForScheduling($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::queueForScheduling($args);
	}

	function notifyAuthorProofreader($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyAuthorProofreader($args);
	}

	function thankAuthorProofreader($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankAuthorProofreader($args);	
	}

	function editorInitiateProofreader() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::editorInitiateProofreader();
	}

	function editorCompleteProofreader() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::editorCompleteProofreader();
	}

	function notifyProofreader($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyProofreader($args);
	}

	function thankProofreader($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankProofreader($args);
	}

	function editorInitiateLayoutEditor() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::editorInitiateLayoutEditor();
	}

	function editorCompleteLayoutEditor() {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::editorCompleteLayoutEditor();
	}

	function notifyLayoutEditorProofreader($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::notifyLayoutEditorProofreader($args);
	}

	function thankLayoutEditorProofreader($args) {
		import('pages.trackEditor.SubmissionEditHandler');
		SubmissionEditHandler::thankLayoutEditorProofreader($args);
	}

}

?>
