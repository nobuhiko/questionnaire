<?php
$basename = basename(__FILE__, '.php');
preg_match('/([0-9]*)+$/', $basename, $matches);
$_GET['questionnaire_id'] = $matches[0];
require_once 'bloc.php';
