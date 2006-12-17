<?php

/**
 * EmailTemplateForm.inc.php
 *
 * Copyright (c) 2003-2007 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package eventDirector.form
 *
 * Form for creating and modifying conference tracks.
 *
 * $Id$
 */

import('form.Form');

class EmailTemplateForm extends Form {

	/** The key of the email template being edited */
	var $emailKey;

	/** The conference of the email template being edited */
	var $conference;

	/** The event of the email template being edited */
	var $event;
	
	/**
	 * Constructor.
	 * @param $emailKey string
	 */
	function EmailTemplateForm($emailKey, $conference, $event) {
		parent::Form('eventDirector/emails/emailTemplateForm.tpl');
		
		$this->conference = $conference;
		$this->event = $event;
		$this->emailKey = $emailKey;
		
		// Validation checks for this form
		$this->addCheck(new FormValidatorArray($this, 'subject', 'required', 'eventDirector.emails.form.subjectRequired'));
		$this->addCheck(new FormValidatorArray($this, 'body', 'required', 'eventDirector.emails.form.bodyRequired'));
	}
	
	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr = &TemplateManager::getManager();
		
		$conferenceId = $this->conference->getConferenceId();
		$eventId = ($this->event ? $this->event->getEventId() : 0);
		
		$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplate = &$emailTemplateDao->getBaseEmailTemplate($this->emailKey, $conferenceId, $eventId);
		$templateMgr->assign('canDisable', $emailTemplate?$emailTemplate->getCanDisable():false);
		$templateMgr->assign('supportedLocales', $this->conference->getSupportedLocaleNames());
		$templateMgr->assign('helpTopicId','conference.managementPages.emails');
		parent::display();
	}
	
	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		$eventId = ($this->event ? $this->event->getEventId() : 0);
		$conferenceId = $this->conference->getConferenceId();
		
		$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');

		// If there's already an event-level template, grab it.
		$emailTemplate = &$emailTemplateDao->getLocaleEmailTemplate($this->emailKey, $conferenceId, $eventId, false);

		// If not, initialize with the conference template (if one exists). Note
		// it's necessary to blank the ID field.
		if(!$emailTemplate->getEmailId()) {
			$emailTemplate = &$emailTemplateDao->getLocaleEmailTemplate($this->emailKey, $conferenceId, $eventId, true);
			$emailTemplate->setEmailId(null);
		}
		$thisLocale = Locale::getLocale();

		if ($emailTemplate) {
			$subject = array();
			$body = array();
			$description = array();
			foreach ($emailTemplate->getLocales() as $locale) {
				$subject[$locale] = $emailTemplate->getSubject($locale);
				$body[$locale] = $emailTemplate->getBody($locale);
				$description[$locale] = $emailTemplate->getDescription($locale);
			}
			
			if ($emailTemplate != null) {
				$this->_data = array(
					'emailId' => $emailTemplate->getEmailId(),
					'emailKey' => $emailTemplate->getEmailKey(),
					'subject' => $subject,
					'body' => $body,
					'description' => isset($description[$thisLocale])?$description[$thisLocale]:null,
					'enabled' => $emailTemplate->getEnabled()
				);
			}
		} else {
			$this->_data = array('isNewTemplate' => true);
		}
	}
	
	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('emailId', 'subject', 'body', 'enabled', 'conferenceId', 'eventId', 'emailKey'));
	}
	
	/**
	 * Save email template.
	 */
	function execute() {
		$eventId = ($this->event ? $this->event->getEventId() : 0);
		$conferenceId = $this->conference->getConferenceId();

		$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplate = &$emailTemplateDao->getLocaleEmailTemplate($this->emailKey, $conferenceId, $eventId, false);

		if (!$emailTemplate) {
			$emailTemplate = &new LocaleEmailTemplate();
			$emailTemplate->setCustomTemplate(true);
			$emailTemplate->setCanDisable(false);
			$emailTemplate->setEnabled(true);
			$emailTemplate->setEmailKey($this->getData('emailKey'));
		} else {
			$emailTemplate->setEmailId($this->getData('emailId'));
			if ($emailTemplate->getCanDisable()) {
				$emailTemplate->setEnabled($this->getData('enabled'));
			}
			$foo = $emailTemplate->getEmailId();
		}

		$emailTemplate->setConferenceId($conferenceId);
		$emailTemplate->setEventId($eventId);
		
		$supportedLocales = $this->conference->getSupportedLocaleNames();
		if (!empty($supportedLocales)) {
			foreach ($conference->getSupportedLocaleNames() as $localeKey => $localeName) {
				$emailTemplate->setSubject($localeKey, $this->_data['subject'][$localeKey]);
				$emailTemplate->setBody($localeKey, $this->_data['body'][$localeKey]);
			}
		} else {
			$localeKey = Locale::getLocale();
			$emailTemplate->setSubject($localeKey, $this->_data['subject'][$localeKey]);
			$emailTemplate->setBody($localeKey, $this->_data['body'][$localeKey]);
		}

		if ($emailTemplate->getEmailId() != null) {
			$emailTemplateDao->updateLocaleEmailTemplate($emailTemplate);
		} else {
			$emailTemplateDao->insertLocaleEmailTemplate($emailTemplate);
		}
	}
}

?>
