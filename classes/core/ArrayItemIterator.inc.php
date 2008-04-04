<?php

/**
 * @file ArrayItemIterator.inc.php
 *
 * Copyright (c) 2000-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package db
 * @class ArrayItemIterator
 *
 * Provides paging and iteration for arrays.
 *
 * $Id$
 */

import('core.ItemIterator');

class ArrayItemIterator extends ItemIterator {
	/** The array of contents of this iterator. */
	var $theArray;

	/** Number of items to iterate through on this page */
	var $itemsPerPage;

	/** The current page. */
	var $page;

	/** The total number of items. */
	var $count;

	/** Whether or not the iterator was empty from the start */
	var $wasEmpty;

	/**
	 * Constructor.
	 * @param $theArray array The array of items to iterate through
	 * @param $page int the current page number
	 * @param $itemsPerPage int Number of items to display per page
	 */
	function ArrayItemIterator(&$theArray, $page=-1, $itemsPerPage=-1) {
		if ($page>=1 && $itemsPerPage>=1) {
			$this->theArray = $this->array_slice_key($theArray, ($page-1) * $itemsPerPage, $itemsPerPage);
			$this->page = $page;
		} else {
			$this->theArray = &$theArray;
			$this->page = 1;
			$this->itemsPerPage = max(count($this->theArray),1);
		}
		$this->count = count($theArray);
		$this->itemsPerPage = $itemsPerPage;
		$this->wasEmpty = count($this->theArray)==0;
		reset($this->theArray);
	}

	/**
	 * Return the next item in the iterator.
	 * @return object
	 */
	function &next() {
		if (!is_array($this->theArray)) {
			$value = null;
			return $value;
		}
		$value = current($this->theArray);
		if (next($this->theArray)==null) {
			$this->theArray = null;
		}
		return $value;
	}

	/**
	 * Return the next item in the iterator, with key.
	 * @return array (key, value)
	 */
	function &nextWithKey() {
		$key = key($this->theArray);
		$value = $this->next();
		return array($key, $value);
	}

	function atFirstPage() {
		return $this->page==1;
	}

	function atLastPage() {
		return ($this->page * $this->itemsPerPage) + 1 > $this->count;
	}

	function getPage() {
		return $this->page;
	}

	function getCount() {
		return $this->count;
	}

	function getPageCount() {
		return max(1, ceil($this->count / $this->itemsPerPage));
	}

	/**
	 * Return a boolean indicating whether or not we've reached the end of results
	 * @return boolean
	 */
	function eof() {
		return (($this->theArray == null) || (count($this->theArray)==0));
	}

	/**
	 * Return a boolean indicating whether or not this iterator was empty from the beginning
	 * @return boolean
	 */
	function wasEmpty() {
		return $this->wasEmpty;
	}

	function &toArray() {
		return $this->theArray;
	}

	function array_slice_key($array, $offset, $len=-1) {
		// A version of array_slice that takes keys into account.
		// Thanks to pies at sputnik dot pl. (Retrieved from
		// http://ca3.php.net/manual/en/function.array-slice.php)

		// This is made redundant by PHP 5.0.2's updated
		// array_slice, but we can't assume everyone has that.

		if (!is_array($array)) return false;

		$return = array();
		$length = $len >= 0? $len: count($array);
		$keys = array_slice(array_keys($array), $offset, $length);
		foreach($keys as $key) {
			$return[$key] = $array[$key];
		}

		return $return;
	}

	function isInBounds() {
		return ($this->getPageCount() >= $this->page);
	}

	function &getLastPageRangeInfo() {
		import('db.DBResultRange');
		$returner =& new DBResultRange(
			$this->itemsPerPage,
			$this->getPageCount()
		);
		return $returner;
	}
}

?>
