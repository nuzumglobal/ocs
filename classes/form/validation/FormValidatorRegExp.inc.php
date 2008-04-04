<?php

/**
 * @file FormValidatorRegExp.inc.php
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package form.validation
 * @class FormValidatorRegExp
 *
 * Form validation check using a regular expression.
 *
 * $Id$
 */

import ('form.validation.FormValidator');

class FormValidatorRegExp extends FormValidator {

	/** The regular expression to match against the field value */
	var $regExp;

	/**
	 * Constructor.
	 * @see FormValidator::FormValidator()
	 * @param $regExp string the regular expression (PCRE form)
	 */
	function FormValidatorRegExp(&$form, $field, $type, $message, $regExp) {
		parent::FormValidator($form, $field, $type, $message);
		$this->regExp = $regExp;
	}

	/**
	 * Check if field value is valid.
	 * Value is valid if it is empty and optional or matches regular expression.
	 * @return boolean
	 */
	function isValid() {
		return $this->isEmptyAndOptional() || String::regexp_match($this->regExp, $this->form->getData($this->field));
	}

}

?>
