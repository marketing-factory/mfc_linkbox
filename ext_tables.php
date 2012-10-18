<?php 
/******************************************************************************
 * Linkbox Plugin
 ******************************************************************************/
t3lib_extMgm::addPlugin(
	array(
		'LLL:EXT:mfc_linkbox/resources/private/language/locallang_db.xml:tt_content.list_type_pi1',
		$_EXTKEY . '_pi1'
	),
	'list_type'
);

t3lib_extMgm::addStaticFile($_EXTKEY, 'configuration/typoscript/', 'Linkbox');


$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';

t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY . '/flexform.xml');

	// remove some fields from the tt_content content element
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages,recursive';
?>