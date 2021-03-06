<?php

/**
 * @file pages/announcement/AnnouncementHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AnnouncementHandler
 * @ingroup pages_announcement
 *
 * @brief Handle requests for public announcement functions.
 */


import('lib.pkp.pages.announcement.PKPAnnouncementHandler');

class AnnouncementHandler extends PKPAnnouncementHandler {
	/**
	 * Constructor
	 */
	function AnnouncementHandler() {
		parent::PKPAnnouncementHandler();
	}
}

?>
