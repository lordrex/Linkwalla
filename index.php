<?php 
$pagetitle='Link list';
$pagetype='form';
// Redirect to the installer if the seting page doesn't exist
if(!(file_exists('lwSettings.xml'))) {
	if(!(file_exists('lwInstaller.php'))){
}
	else{
    header("Location: lwInstaller.php");
}
}
include 'head.php'; 
require_once 'lwFunctions.php';
// $archive determines whether to show an archived month or the current month
$archive =$_GET["archive"];
$loginstatus = lw_logincheck();
$action = $_GET["action"];
?>
    
<h1>Link list</h1>
<?php
//Detects if lwInstaller.php still exists after installation
if($loginstatus == 1 && file_exists('lwSettings.xml') && file_exists('lwInstaller.php') && $action != "lw_deleteInstaller") {
	echo '<p class="lwFailed">Linkwalla has been installed correctly but the installer file, lwInstaller.php, is still on the server. Keeping this file in place is a security risk. <a href="index.php?action=lw_deleteInstaller">Click here to delete the installer.</a></p>';
}
if($action == "lw_deleteInstaller"){ lw_deleteInstaller(); }
?>
<p><a href="linkrss.xml">Syndicate (RSS)</a></p>

<!-- Login -->
<p><?php if($loginstatus == 1) { ?>
<a href="item-edit.php?itemID=<?php echo $linkcount;?>&actiontype=add" class="editlink lbOn">Add Link</a> | 
<a href="lwSettings-edit.php" class="editlink">Settings</a> | 
<a href="login.php?action=logout" class="editlink">Log Out</a>
<?php } else {?>
<a href="login.php" class="editlink">Log In</a>
<?php } ?>
</p>

<!-- Archive links -->
<div class="archives">
<?php linkwalla_archiveList($archive); ?>
</div>

<!-- The list of links -->
<?php 
/* The link list HTML is in the lwGetArchive() function for the enclosing list HTML and the linkwalla_linkList() function (for the individual links) in lwFunctions.php. */ 
?>
<?php lwGetArchive($archive);?>

<!-- Archive links (again) -->
<div class="archives">
<?php linkwalla_archiveList($archive); ?>
</div>
<?php include'foot.php'; ?>