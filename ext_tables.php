<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

if (!defined ('TYPO3_MODE'))    die ('Access denied.');
$tempColumns = Array (
    'tx_econda_static_country_isocode' => Array (
        'exclude' => 0,
        'label' => 'LLL:EXT:econda/locallang_db.xml:sys_language.tx_econda_static_country_isocode',
        'config' => Array (
            'type' => 'input',
            'size' => '5',
            'max' => '2',
            'eval' => 'required,trim'
        )
    ),
    'tx_econda_static_currency_factor' => Array (
        'exclude' => 0,
        'label' => 'LLL:EXT:econda/locallang_db.xml:sys_language.tx_econda_static_currency_factor',
        'config' => Array (
            'type' => 'input',
            'size' => '5',
            'max' => '12',
            'eval' => 'required,trim',
            'default' => '1'
        )
    )
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    $_EXTKEY,
    'Configuration/TypoScript',
    'Econda'
);
t3lib_div::loadTCA('sys_language');
t3lib_extMgm::addTCAcolumns('sys_language',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("sys_language","tx_econda_static_country_isocode;;;;1-1-1, tx_econda_static_currency_factor");
