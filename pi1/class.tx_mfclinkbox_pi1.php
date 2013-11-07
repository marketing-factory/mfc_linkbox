<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Yves Becker <yb@marketing-factory.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib . 'class.tslib_pibase.php');

/**
 * Plugin 'Linkbox' for the 'mfc_linkbox' extension.
 *
 * @author	Yves Becker <yb@marketing-factory.de>
 * @since 	27.01.2012
 * @package	TYPO3
 * @subpackage	mfc_tagcloud
 */
class tx_mfclinkbox_pi1 extends tslib_pibase {
	public $prefixId      = 'tx_mfclinkbox_pi1';		// Same as class name
	public $scriptRelPath = 'pi1/class.tx_mfclinkbox_pi1.php';
	public $extKey        = 'mfc_linkbox';	// The extension key.

	/**
	 * @var array
	 */
	public $conf = array();

	/**
	 * flexform with optional content for the linkbox
	 *
	 * @var array
	 */
	private $flexform = array();

	/**
	 * template of the linkbox
	 *
	 * @var string
	 */
	private $template = '';

	/**
	 * contains marker to replace in template
	 *
	 * @var array
	 */
	private $markerArray = array();

	/**
	 * page id of the parent page
	 *
	 * @var integer
	 */
	private $parentPid = 0;

	/**
	 * list of pids of the current page's treelevel
	 *
	 * @var array
	 */
	private $uidList = array();

	/**
	 * key of the current page in $uidList, needed to find next and previous page IDs
	 *
	 * @var integer
	 */
	private $currentPageKey = 0;


	/**
	 * constructor sets pid of current page
	 *
	 * @return tx_mfclinkbox_pi1
	 */
	public function __construct() {
		$this->setParentPid($GLOBALS['TSFE']->id);
	}

	/**
	 * main function of the plugin
	 *
	 * @param 	string 	$content
	 * @param 	array 	$conf
	 * @return 	string	$content
	 */
	public function main($content, $conf) {
			// load basic config
		$this->conf = $conf;
		$this->pi_loadLL();

			// build linkbox
		$this->prepareValuesForLinkbox();
		$this->setDynamicLinks();
		$content = $this->getLinkboxContent();

		return $content;
	}

	/**
	 * get id of parent page
	 *
	 * @param int $id
	 */
	protected function setParentPid($id)   {
		$current_page = $GLOBALS['TSFE']->sys_page->getPage($id);
		if (is_array($current_page)) {
			$parentPid = $current_page['pid'];
		} else {
			$parentPid = 0;
		}
		$this->parentPid = $parentPid;
	}

	/**
	 * contains initializing methods and sets values we need for linkbuilding later
	 *
	 * @return	void
	 */
	protected function prepareValuesForLinkbox() {
		$this->initializeFlexform();
		$this->initializeTemplate();
		$this->setDefaultMarkerArray();

		$this->uidList = $this->getTreeLevelUids();
		$this->currentPageKey = array_search((string) $GLOBALS['TSFE']->id, $this->uidList, $strict = TRUE);
	}

	/**
	 * initializes configuration and flexform
	 *
	 * @return 	void
	 */
	protected function initializeFlexform() {
		$this->pi_initPIflexForm();
		$this->flexform = $this->cObj->data['pi_flexform'];
	}

	/**
	 * load template file
	 *
	 * @return	void
	 */
	protected function initializeTemplate() {
		$templateFile = $this->cObj->fileResource($this->conf['templateFile']);
		$this->template = $this->cObj->getSubpart($templateFile, '###LINKBOX###');
	}

	/**
	 * set default values for markerArray
	 *
	 * @return	void
	 */
	protected function setDefaultMarkerArray() {
		$this->markerArray = array(
			'LABEL_HEADLINE' 		=> '',
			'LABEL_ABOVE_PAGE' 		=> $this->pi_getLL('label.abovePage'),
			'LABEL_PREVIOUS_PAGE' 	=> '',
			'LABEL_NEXT_PAGE'		=> '',
			'LINK_ABOVE_PAGE' 		=> '',
			'LINK_PREVIOUS_PAGE'	=> '',
			'LINK_NEXT_PAGE'		=> '',
			'OPTIONAL_CONTENT'		=> ''
		);
	}

	/**
	 * build the linkbox depending on available pages and additional content
	 *
	 * @return	void
	 */
	protected function setDynamicLinks() {
		$this->setHeadline();
		$this->setNextPageLink();
		$this->setPreviousPageLink();
		$this->setAbovePageLink();
		$this->addOptionalContent();
	}

	protected function setHeadline() {
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'headline') != '') {
			$this->markerArray['LABEL_HEADLINE'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'headline');
		} else {
			$this->markerArray['LABEL_HEADLINE'] = $this->conf['headline'];
		}
	}

	/**
	 * implement link for next page if its available
	 *
	 * @return	void
	 */
	protected function setNextPageLink() {
		$wrap = '<span class="icon-circle-arrow-right"></span> |';
		$this->markerArray['DISPLAY_NEXT_PAGE'] = 'none';
		if ($this->currentPageKey + 1 < count($this->uidList)) {
			$nextPagePid = $this->currentPageKey + 1;
			$this->markerArray['LABEL_NEXT_PAGE'] = $this->pi_getLL('label.nextPage');

			$nextPageTitle = $this->getTitleByPid($this->uidList[$nextPagePid]);
			#$nextPageTitle = '<span class="icon-circle-arrow-right" />' . $nextPageTitle;
			$nextPageLink = $this->getLinkByPid($this->uidList[$nextPagePid], $nextPageTitle, $wrap);
			$this->markerArray['LINK_NEXT_PAGE'] = $nextPageLink;
			$this->markerArray['DISPLAY_NEXT_PAGE'] = 'block';
		}
	}

	/**
	 * implement link for previous page if its available
	 *
	 * @return	void
	 */
	protected function setPreviousPageLink() {
		$wrap = '<span class="icon-circle-arrow-left"></span> |';
		$this->markerArray['DISPLAY_PREVIOUS_PAGE'] = 'none';
		if ($this->currentPageKey != 0) {
			$previousPagePid = $this->currentPageKey - 1;
			$this->markerArray['LABEL_PREVIOUS_PAGE'] = $this->pi_getLL('label.previousPage');

			$previousPageTitle = $this->getTitleByPid($this->uidList[$previousPagePid]);
			$previousPageLink = $this->getLinkByPid($this->uidList[$previousPagePid], $previousPageTitle, $wrap);
			$this->markerArray['LINK_PREVIOUS_PAGE'] = $previousPageLink;
			$this->markerArray['DISPLAY_PREVIOUS_PAGE'] = 'block';
		}
	}

	/**
	 * implement link for next not hidden above page
	 *
	 * @return	void
	 */
	protected function setAbovePageLink() {
		$wrap = '<span class="icon-circle-arrow-up"></span> |';
		$this->markerArray['DISPLAY_ABOVE_PAGE'] = 'none';
		$pageExists = $this->getLinkByPid($this->parentPid);
		if (!$pageExists) {
			foreach($GLOBALS['TSFE']->rootLine as $page) {
				if ($page['hidden'] == '0' && $page['pid'] != $this->parentPid) {
					$nextPid = $page['uid'];
					$abovePageTitle = $this->getTitleByPid($nextPid);
					$this->markerArray['LINK_ABOVE_PAGE'] = $this->getLinkByPid($nextPid, $abovePageTitle, $wrap);
					$this->markerArray['DISPLAY_ABOVE_PAGE'] = 'block';
					break;
				}
			}
		} else {
			$abovePageTitle = $this->getTitleByPid($this->parentPid);
			$this->markerArray['LINK_ABOVE_PAGE'] = $this->getLinkByPid($this->parentPid, $abovePageTitle, $wrap);
			$this->markerArray['DISPLAY_ABOVE_PAGE'] = 'block';
		}
	}

	/**
	 * add optional content to the linkbox if RTE in Backend was used
	 *
	 * @return	void
	 */
	protected function addOptionalContent() {
		$this->markerArray['OPTIONAL_CONTENT_ABOVE'] = $this->pi_RTEcssText(
			$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'optional_content_above')
		);

		$this->markerArray['OPTIONAL_CONTENT_BELOW'] = $this->pi_RTEcssText(
			$this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display')
		);
	}

	/**
	 * parse marker array in template and return finished content
	 *
	 * @return	string	$content
	 */
	protected function getLinkboxContent() {
		$this->template = $this->cObj->substituteMarkerArray($this->template, $this->markerArray, '###|###', true);
		return $this->cObj->substituteSubpart($this->template, '###LINKBOX###', $this->template);
	}

	/**
	 * get List of Uids of the current Treelevel
	 *
	 * @return	array	$uidList
	 */
	protected function getTreeLevelUids() {
		$tree = t3lib_div::makeInstance('t3lib_queryGenerator');
		$uidListString = $tree->getTreeList($this->parentPid, 1, 0, 'hidden = 0 AND nav_hide = 0');
		$uidListArray = t3lib_div::trimExplode(',', $uidListString);

			// cut off parent pid
		$uidList = array_diff($uidListArray, array(0, $this->parentPid));
		$uidList = array_values($uidList);
		return $uidList;
	}

	/**
	 * get title of a page by given pid
	 *
	 * @param 	int	 	$pid
	 * @return	string	$title
	 */
	protected function getTitleByPid($pid) {
		$page = t3lib_div::makeInstance('t3lib_pageSelect');
		$pageContent = $page->getPage($pid); //get page record by pageId
		$title = $pageContent['title']; //get title
		return $title;
	}

	/**
	 * build link to a page by given pid and title
	 *
	 * @param int $pid
	 * @param string $title
	 * @param string $wrap
	 * @return string $link
	 */
	protected function getLinkByPid($pid, $title = '', $wrap = '') {
		$linkConf = array(
			'parameter' => $pid,
			'useCacheHash' => '1',
			'ATagBeforeWrap' => '1',
			'title' =>  $title,
			'wrap' => $wrap
		);
		return $this->cObj->typoLink($title, $linkConf, 1);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mfc_linkbox/pi1/class.tx_mfclinkbox_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mfc_linkbox/pi1/class.tx_mfclinkbox_pi1.php']);
}