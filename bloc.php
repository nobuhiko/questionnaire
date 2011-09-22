<?php
require_once realpath(dirname(__FILE__)) . '/../../../require.php';
$arrPluginInfo = SC_Utils_Ex::sfLoadPluginInfo(dirname(__FILE__) . '/plugin_info.php');
require_once $arrPluginInfo['fullpath'] . 'classes/pages/LC_Page_FrontParts_Bloc_Questionnaire.php';

$objPage = new LC_Page_FrontParts_Bloc_Questionnaire();
$objPage->arrPluginInfo = $arrPluginInfo;
$objPage->blocItems     = $params['items'];
register_shutdown_function(array($objPage, 'destroy'));
$objPage->init();
$objPage->process();
