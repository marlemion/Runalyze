<?php
/**
 * File for matching activities from Garmin Communicator
 * Call:   ajax.activityMatcher.php
 */
require_once '../inc/class.Frontend.php';

$Frontend = new Frontend(true);
use Runalyze\Activity\DuplicateFinder;
header('Content-type: application/json');

$IDs     = array();
$Matches = array();
$Array   = explode('&', urldecode(file_get_contents('php://input')));
foreach ($Array as $String) {
	if (substr($String,0,12) == 'externalIds=')
		$IDs[] = substr($String,12);
}

$IgnoreIDs = \Runalyze\Configuration::ActivityForm()->ignoredActivityIDs();
$DuplicateFinder = new DuplicateFinder(DB::getInstance(), SessionAccountHandler::getId());

foreach ($IDs as $ID) {
	$dup = $DuplicateFinder->checkForDuplicate(strtotime($ID));
	$found = $dup || in_array($ID, $IgnoreIDs);
	$Matches[$ID] = array('match' => $found);
}

$Response = array('matches' => $Matches);

echo json_encode((object)$Response);