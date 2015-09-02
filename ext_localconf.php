<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_econda_pi1.php','_pi1','econda',0);
$cN = t3lib_extMgm::getCN($_EXTKEY);
t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
# Setting '.$_EXTKEY.' plugin TypoScript
'.'page.8888 = < plugin.'.$cN.'_pi1'.'
',43);
?>