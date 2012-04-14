<?php
// check login status before running this page
require_once 'lwFunctions.php';
lw_auth();

/* 1 GET VALUES FROM THE FORM */
$lwData = array(
	'itemID' => $_POST["itemID"],
	'actiontype' => $_POST["actiontype"],
	'redirect' => $_POST["redirect"],
	'linktitle' => htmlspecialchars(stripslashes($_POST["linktitle"])),
	'url' => htmlspecialchars($_POST["url"]),
	'adddate' => date("n/j/y"),
	'rssdate' => date("r"),
	'sortdate' => date("YmdHis"),
	'description' => htmlspecialchars(stripslashes(strval($_POST["description"])), ENT_QUOTES)
);
if($del_username!='') {
	$lwData['del_post'] = $_POST["del_post"];
	$lwData['del_tags'] = $_POST["del_tags"];
}
if($mag_username!='') {
	$lwData['mag_post'] = $_POST["mag_post"];
	$lwData['mag_tags'] = $_POST["mag_tags"];
}

/* 2. SELECT THE XML FILE */
$archive =$_POST["archive"];
$xmlFileName = 'archive/' . $archive . 'archive.xml';

/* 3a. ADD, EDIT OR REMOVE */
if($lwData['actiontype']=="add") { lwAdd($xmlFileName,$lwData); }
if($lwData['actiontype']=="edit") { lwEdit($xmlFileName,$lwData); }
if($lwData['actiontype']=="remove") { lwRemove($xmlFileName,$lwData); }

// 4. UPDATE THE RSS FEED
linkwalla_rss();

/* 5. RETURN HOME OR TO REFERRER*/
if( $lwData['redirect']=="1") { header('Location: ' . $_POST["url"]); }
else { 
    $headerLocation= "Location: index.php";
    header($headerLocation); 
}
?>