<?php

/**
 * Track.inc.php
 *
 * Copyright (c) 2003-2007 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package conference
 *
 * Track class.
 * Describes basic track properties.
 *
 * $Id$
 */

class Track extends DataObject {

	/**
	 * Constructor.
	 */
	function Track() {
		parent::DataObject();
	}

	/**
	 * Get localized title of conference track.
	 */
	function getTrackTitle() {
		$eventId = &$this->getEventId();
		$eventDao = &DAORegistry::getDao('EventDAO');
		$event = &$eventDao->getEvent($eventId);
		$conference = &$event->getConference();
		$alternateLocaleNum = Locale::isAlternateConferenceLocale($conference->getConferenceId());
		
		$title = null;
		switch ($alternateLocaleNum) {
			case 1: $title = $this->getTitleAlt1(); break;
			case 2: $title = $this->getTitleAlt2(); break;
		}
		// Fall back on the primary locale title.
		if (empty($title)) $title = $this->getTitle();

		return $title;
	}

	/**
	 * Get localized abbreviation of conference track.
	 */
	function getTrackAbbrev() {
		$eventId = &$this->getEventId();
		$eventDao = &DAORegistry::getDao('EventDAO');
		$event = &$eventDao->getEvent($eventId);
		$conference = &$event->getConference();
		$alternateLocaleNum = Locale::isAlternateConferenceLocale($conference->getConferenceId());
		
		$abbrev = null;
		switch ($alternateLocaleNum) {
			case 1: $abbrev = $this->getAbbrevAlt1(); break;
			case 2: $abbrev = $this->getAbbrevAlt2(); break;
		}
		// Fall back on the primary locale title.
		if (empty($abbrev)) $abbrev = $this->getAbbrev();

		return $abbrev;
	}

	//
	// Get/set methods
	//
	
	/**
	 * Get ID of track.
	 * @return int
	 */
	function getTrackId() {
		return $this->getData('trackId');
	}
	
	/**
	 * Set ID of track.
	 * @param $trackId int
	 */
	function setTrackId($trackId) {
		return $this->setData('trackId', $trackId);
	}
	
	/**
	 * Get ID of event.
	 * @return int
	 */
	function getEventId() {
		return $this->getData('eventId');
	}
	
	/**
	 * Set ID of event.
	 * @param $eventId int
	 */
	function setEventId($eventId) {
		return $this->setData('eventId', $eventId);
	}
	
	/**
	 * Get title of track.
	 * @return string
	 */
	function getTitle() {
		return $this->getData('title');
	}
	
	/**
	 * Set title of track.
	 * @param $title string
	 */
	function setTitle($title) {
		return $this->setData('title', $title);
	}
	
	/**
	 * Get title of track (alternate locale 1).
	 * @return string
	 */
	function getTitleAlt1() {
		return $this->getData('titleAlt1');
	}
	
	/**
	 * Set title of track (alternate locale 1).
	 * @param $titleAlt1 string
	 */
	function setTitleAlt1($titleAlt1) {
		return $this->setData('titleAlt1', $titleAlt1);
	}
	
	/**
	 * Get title of track (alternate locale 2).
	 * @return string
	 */
	function getTitleAlt2() {
		return $this->getData('titleAlt2');
	}
	
	/**
	 * Set title of track (alternate locale 2).
	 * @param $titleAlt2 string
	 */
	function setTitleAlt2($titleAlt2) {
		return $this->setData('titleAlt2', $titleAlt2);
	}
	
	/**
	 * Get track title abbreviation.
	 * @return string
	 */
	function getAbbrev() {
		return $this->getData('abbrev');
	}
	
	/**
	 * Set track title abbreviation.
	 * @param $abbrev string
	 */
	function setAbbrev($abbrev) {
		return $this->setData('abbrev', $abbrev);
	}
	
	/**
	 * Get track title abbreviation (alternate locale 1).
	 * @return string
	 */
	function getAbbrevAlt1() {
		return $this->getData('abbrevAlt1');
	}
	
	/**
	 * Set track title abbreviation (alternate locale 1).
	 * @param $abbrevAlt1 string
	 */
	function setAbbrevAlt1($abbrevAlt1) {
		return $this->setData('abbrevAlt1', $abbrevAlt1);
	}
	
	/**
	 * Get track title abbreviation (alternate locale 2).
	 * @return string
	 */
	function getAbbrevAlt2() {
		return $this->getData('abbrevAlt2');
	}
	
	/**
	 * Set track title abbreviation (alternate locale 2).
	 * @param $abbrevAlt2 string
	 */
	function setAbbrevAlt2($abbrevAlt2) {
		return $this->setData('abbrevAlt2', $abbrevAlt2);
	}
	
	/**
	 * Get sequence of track.
	 * @return float
	 */
	function getSequence() {
		return $this->getData('sequence');
	}
	
	/**
	 * Set sequence of track.
	 * @param $sequence float
	 */
	function setSequence($sequence) {
		return $this->setData('sequence', $sequence);
	}
	
	/**
	 * Get open archive setting of track.
	 * @return boolean
	 */
	function getMetaIndexed() {
		return $this->getData('metaIndexed');
	}
	
	/**
	 * Set open archive setting of track.
	 * @param $metaIndexed boolean
	 */
	function setMetaIndexed($metaIndexed) {
		return $this->setData('metaIndexed', $metaIndexed);
	}
	
	/**
	 * Get string identifying type of items in this track.
	 * @return string
	 */
	function getIdentifyType() {
		return $this->getData('identifyType');
	}
	
	/**
	 * Set string identifying type of items in this track.
	 * @param $identifyType string
	 */
	function setIdentifyType($identifyType) {
		return $this->setData('identifyType', $identifyType);
	}
	
	/**
	 * Return boolean indicating whether or not submissions are restricted to [track]Editors.
	 * @return boolean
	 */
	function getEditorRestricted() {
		return $this->getData('editorRestricted');
	}
	
	/**
	 * Set whether or not submissions are restricted to [track]Editors.
	 * @param $editorRestricted boolean
	 */
	function setEditorRestricted($editorRestricted) {
		return $this->setData('editorRestricted', $editorRestricted);
	}
	
	/**
	 * Return boolean indicating if title should be hidden in issue ToC.
	 * @return boolean
	 */
	function getHideTitle() {
		return $this->getData('hideTitle');
	}
	
	/**
	 * Set if title should be hidden in issue ToC.
	 * @param $hideTitle boolean
	 */
	function setHideTitle($hideTitle) {
		return $this->setData('hideTitle', $hideTitle);
	}
	
	/**
	 * Get policy.
	 * @return string
	 */
	function getPolicy() {
		return $this->getData('policy');
	}
	
	/**
	 * Set policy.
	 * @param $policy string
	 */
	function setPolicy($policy) {
		return $this->setData('policy', $policy);
	}
	
}

?>
