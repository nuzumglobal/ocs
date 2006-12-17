<?php

/**
 * PublishedPaper.inc.php
 *
 * Copyright (c) 2003-2007 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package paper
 *
 * Published paper class.
 *
 * $Id$
 */

import('paper.Paper');

class PublishedPaper extends Paper {

	/**
	 * Constructor.
	 */
	function PublishedPaper() {
		parent::Paper();
	}
	
	/**
	 * Get ID of published paper.
	 * @return int
	 */
	function getPubId() {
		return $this->getData('pubId');
	}
	
	/**
	 * Set ID of published paper.
	 * @param $pubId int
	 */
	function setPubId($pubId) {
		return $this->setData('pubId', $pubId);
	}

	/**
	 * Get ID of associated paper.
	 * @return int
	 */
	function getPaperId() {
		return $this->getData('paperId');
	}
	
	/**
	 * Set ID of associated paper.
	 * @param $paperId int
	 */
	function setPaperId($paperId) {
		return $this->setData('paperId', $paperId);
	}
	
	/**
	 * Get ID of the event this paper is in.
	 * @return int
	 */
	function getEventId() {
		return $this->getData('eventId');
	}
	
	/**
	 * Set ID of the event this paper is in.
	 * @param $eventId int
	 */
	function setEventId($eventId) {
		return $this->setData('eventId', $eventId);
	}

	/**
	 * Get track ID of the event this paper is in.
	 * @return int
	 */
	function getTrackId() {
		return $this->getData('trackId');
	}
	
	/**
	 * Set track ID of the event this paper is in.
	 * @param $trackId int
	 */
	function setTrackId($trackId) {
		return $this->setData('trackId', $trackId);
	}

	/**
	 * Get date published.
	 * @return date
	 */
	
	function getDatePublished() {
		return $this->getData('datePublished');	
	}
	

	/**
	 * Set date published.
	 * @param $datePublished date
	 */
	 
	function setDatePublished($datePublished) {
		return $this->SetData('datePublished', $datePublished);
	}
	
	/**
	 * Get sequence of paper in table of contents.
	 * @return float
	 */
	function getSeq() {
		return $this->getData('seq');
	}
	
	/**
	 * Set sequence of paper in table of contents.
	 * @param $sequence float
	 */
	function setSeq($seq) {
		return $this->setData('seq', $seq);
	}

	/**
	 * Get views of the published paper.
	 * @return int
	 */
	function getViews() {
		return $this->getData('views');
	}
	
	/**
	 * Set views of the published paper.
	 * @param $views int
	 */
	function setViews($views) {
		return $this->setData('views', $views);
	}

	/**
	 * get access status
	 * @return int
	 */
	function getAccessStatus() {
		return $this->getData('accessStatus');
	}
	 
	/**
	 * set access status
	 * @param $accessStatus int
	 */
	function setAccessStatus($accessStatus) {
		return $this->setData('accessStatus',$accessStatus);
	}

	/**
	 * Get the galleys for an paper.
	 * @return array PaperGalley
	 */
	function &getGalleys() {
		$galleys = &$this->getData('galleys');
		return $galleys;
	}
	
	/**
	 * Set the galleys for an paper.
	 * @param $galleys array PaperGalley
	 */
	function setGalleys(&$galleys) {
		return $this->setData('galleys', $galleys);
	}
		
	/**
	 * Get supplementary files for this paper.
	 * @return array SuppFiles
	 */
	function &getSuppFiles() {
		$returner =& $this->getData('suppFiles');
		return $returner;
	}
	
	/**
	 * Set supplementary file for this paper.
	 * @param $suppFiles array SuppFiles
	 */
	function setSuppFiles($suppFiles) {
		return $this->setData('suppFiles', $suppFiles);
	}
	
	/**
	 * Get public paper id
	 * @return string
	 */
	function getPublicPaperId() {
		return $this->getData('publicPaperId');
	}

	/**
	 * Set public paper id
	 * @param $publicPaperId string
	 */
	function setPublicPaperId($publicPaperId) {
		return $this->setData('publicPaperId', $publicPaperId);
	}

	/**
	 * Return the "best" paper ID -- If a public paper ID is set,
	 * use it; otherwise use the internal paper Id. (Checks the conference
	 * settings to ensure that the public ID feature is enabled.)
	 * @param $conference Object the conference this paper is in
	 * @return string
	 */
	function getBestPaperId($conference = null) {
		// Retrieve the conference, if necessary.
		if (!isset($event)) {
			$eventDao = &DAORegistry::getDAO('EventDAO');
			$event = $eventDao->getEvent($this->getEventId());
		}

		if ($event->getSetting('enablePublicPaperId', true)) {
			$publicPaperId = $this->getPublicPaperId();
			if (!empty($publicPaperId)) return $publicPaperId;
		}
		return $this->getPaperId();
	}
}

?>
