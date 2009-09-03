<?php

// Exit, if script is called directly (must be included via eID in index_ts.php)
if (!defined ('PATH_typo3conf'))     die ('Could not access this script directly!');

// Initialize FE user object:
$feUserObj = tslib_eidtools::initFeUser();

// Connect to database:
tslib_eidtools::connectDB();
$data = $TYPO3_DB->exec_SELECTgetRows('*','tt_content','uid='.$_GET["uid"]);


require_once(t3lib_extMgm::extPath('iocean_downloadarea').'pi1/class.tx_ioceandownloadarea_pi1.php');
$file = t3lib_extMgm::extPath('iocean_downloadarea').'res/downloadarea.tmpl';
$template = file_get_contents($file);
$ajax = new tx_ioceandownloadarea_pi1();
$conf['userFunc'] = $_GET['eID'].'->main';
echo $ajax->buildSingleView($conf,$data,$template);

?>

