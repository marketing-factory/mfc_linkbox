<?php

$_EXTKEY = 'mfc_linkbox';

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
    $_EXTKEY,
    'pi1/class.tx_mfclinkbox_pi1.php',
    '_pi1',
    'list_type',
    0
);
