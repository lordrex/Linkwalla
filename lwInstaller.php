<?php 
$pagetitle='Linkwalla Installer';
$pagetype='form';
include 'head.php'; 
require_once 'lwFunctions.php';

$success = "true";

/* 1 GET VALUES FROM THE FORM */
if($_POST["actiontype"] && $_POST["lwPassword"]!='' && $_POST["lwPassword"]==$_POST["lwPasswordCheck"]) {
	$lwSettings = array( 
		'actiontype' => $_POST["actiontype"],
		'lwUsername' => $_POST["lwUsername"],
		'lwPassword' => $_POST["lwPassword"],
		'lwPasswordCheck' => $_POST["lwPasswordCheck"],
		'rssTitle' => htmlspecialchars($_POST["rssTitle"]),
		'rssLink' => htmlspecialchars($_POST["rssLink"]),
		'rssDescription' => htmlspecialchars($_POST["rssDescription"]),
		'rssWebMaster' => htmlspecialchars($_POST["rssWebMaster"]),
		'rssLanguage' => htmlspecialchars($_POST["rssLanguage"]),
		'linkListCount' => $_POST["linkListCount"]
	);
	$lwData = array( 
		'linktitle' => "Linkwalla",
		'url' => htmlspecialchars("http://linkwalla.benbrophy.com/"),
		'adddate' => date("n/j/y"),
		'rssdate' => date("r"),
		'sortdate' => date("YmdHis"),
		'description' => htmlspecialchars(strval("I used Linkwalla to create this new link blog.")),
		'del_post' => 'no',
		'mag_post' => 'no'
	);
	
	$lwMessages = array();
	if( mkdir('archive')) {
		$lwMessages[] = '<p class="lwSuccess">Archive directory created</p>';
	}
	else {
		$lwMessages[] = '<p class="lwFailed">Uh oh. Archive directory  not created. Maybe it was already there. </p>';
		$success = "false";
	}
	if( touch('lwSettings.xml')) {
		chmod("lwSettings.xml", 0600); //make sure the settings file is hidden from browsers
		$lwMessages[] = '<p class="lwSuccess">Settings file created</p>';
	}
	else {
		$lwMessages[] = '<p class="lwFailed">Uh oh! Settings file not created</p>';
		$success = "false";
	}
		
	ob_start(); ?>
<?php echo $xmldec; ?>
<lwSettings>
	<lwUsername><?php echo $lwSettings['lwUsername'];?></lwUsername>
	<lwPassword><?php echo crypt($lwSettings['lwPassword']);?></lwPassword>
	<lwLoginKey></lwLoginKey>
	<rssTitle><?php echo $lwSettings['rssTitle'];?></rssTitle>
	<rssLink><?php echo $lwSettings['rssLink'];?></rssLink>
	<rssDescription><?php echo $lwSettings['rssDescription'];?></rssDescription>
	<rssWebMaster><?php echo $lwSettings['rssWebMaster'];?></rssWebMaster>
	<rssLanguage><?php echo $lwSettings['rssLanguage'];?></rssLanguage>
	<linkListCount><?php echo $lwSettings['linkListCount'];?></linkListCount>
</lwSettings>
<?php
	/* WRITE THE OUTPUT BUFFER TO THE XML FILE */
	$somecontent = ob_get_clean();
	$filename = 'lwSettings.xml';
	$handle = fopen($filename, "w");
	if(fwrite($handle, $somecontent)) {
		$lwMessages[] = '<p class="lwSuccess">Wrote new settings to the settings file</p>';
		fclose($handle);
	}
	else {
		$lwMessages[] = '<p class="lwFailed">Uh oh! Couldn\'t write the settings into the file</p>';
		$success = "false";
	}
	$xmlFileName = 'archive/' . date("ym") . 'archive.xml';
	if(lwAdd($xmlFileName,$lwData)) {
		$lwMessages[] = '<p class="lwSuccess">Wrote a sample link to get you started</p>';
	}
	else {
		$lwMessages[] = '<p class="lwFailed"> Couldn\'t write the first link</p>';
		$success = false;
	}
	if(linkwalla_rss()) {
		$lwMessages[] = '<p class="lwSuccess">Created an RSS feed for your links</p>';
	}
	else {
		$lwMessages[] = '<p class="lwFailed"> Couldn\'t create an RSS feed for your links</p>';
		$success = false;
	}

	if($success == "false")
	{
		$lwMessages[] = '<p class="lwFailed">There was an error in Installing Linkwalla, please review the information above to fix the problem</p>';
	}
	else
	{
		$lwMessages[] = '<p class="lwSuccess">Linkwalla installation success, please read below to find out how to finish the installation</p>';
	}
	
} // end of the installation action
$lwHome = 'http://' . $_SERVER['SERVER_NAME'] . substr($_SERVER['PHP_SELF'], 0, -15);
?>

<h1>Linkwalla Installer</h1>

<p>You are using PHP Version <strong><?php echo PHP_VERSION; ?></strong>. You need at least version 5.0, 
<?php 
if(floatval(PHP_VERSION) > floatval('5.0')) { echo "so this should work"; }
else { echo "so we may have a problem"; }
?>.</p>

<?php if($_POST["actiontype"]==null || $_POST["actiontype"] && $_POST["lwPassword"]!=$_POST["lwPasswordCheck"]){

if($_POST["actiontype"] && $_POST["lwPassword"]!=$_POST["lwPasswordCheck"]) {
	/* DISPLAY NON-MATCHING PASSWORD MESSAGE*/
	echo '<p class="lwFailed">Your passwords did not match. Please try again. </p>';
}
?>

<p class="instruction">Fill out the form below. When you submit the form, Linkwalla will generate the files you need to get started with your new link blog. Fields marked with a '*' are required.</p>
<p id="formerror">Please fill in the missing fields.</p>

<form class="checkSubmit" action="lwInstaller.php" method="post">
<fieldset>
	<legend>Login Information</legend>
	<div class="textinput">
		<label for="lwUsername">Username *</label>

		<input type="text" class="required" name="lwUsername" id="lwUsername" size="20" maxlength="20" value="">

	</div>
	<div class="textinput">
		<label for="lwPassword">Password *</label>
		<input type="password" name="lwPassword" id="lwPassword" size="20" maxlength="20" />
	</div>
	<div class="textinput">
		<label for="lwPasswordCheck">Verify password *</label>
		<input type="password" name="lwPasswordCheck" id="lwPasswordCheck" size="20" maxlength="20" />
	</div>
</fieldset>

<fieldset>
	<legend>Link List</legend>
	<div class="textinput">
		<label for="linkListCount">Number of links on the main page</label>
		<select id="linkListCount" name="linkListCount">
		<option label="1" value="1">1</option>
		<option label="5" value="5">5</option>
		<option label="10" value="10">10</option>
		<option label="15" value="15" selected>15</option>
		<option label="20" value="20">20</option>
		<option label="50" value="50">50</option>
		</select>
	</div>
</fieldset>

<fieldset>
	<legend>Metadata for the RSS feed</legend>
	<div class="textinput">
		<label for="rssTitle">Site title *</label>
		<input type="text" class="required" name="rssTitle" id="rssTitle" size="40" maxlength="120" value="My links">
	</div>
	<div class="textinput">
		<label for="rssLink">Site URL *</label>
		<input type="text" class="required" name="rssLink" id="rssLink" size="40" maxlength="120" value="<?php echo($lwHome); ?>">
	</div>
	<div class="textinput">
		<label for="rssDescription">Description *</label>
		<input type="text" class="required" name="rssDescription" id="rssDescription" size="40" maxlength="80" value="My links">
	</div>
	<div class="textinput">
		<label for="rssWebMaster">Web Master *</label>
		<input type="text" class="required" name="rssWebMaster" id="rssWebMaster" size="40" maxlength="40" value="me@test.com">
	</div>
	<div class="textinput">
		<label for="rssLanguage">Language *</label>
		<input type="text" class="required" name="rssLanguage" id="rssLanguage" size="8" maxlength="6" value="en-us">
	</div>
</fieldset>

<input type="hidden" name="actiontype" value="install">

<div id="submitdiv">
    <input type="submit" id="submit" value="Save">
</div>
</form>
<?php }?>
<?php if($_POST["actiontype"] && $_POST["lwPassword"]!='') {
	/* DISPLAY SUCCESS AND FAILURE MESSAGES*/
	foreach($lwMessages as $lwMessage) {
		echo $lwMessage;
	}
?>
<h2>Now what?</h2>
<ol>
	<li>Here's a handy bookmarklet. Just drag it to the bookmark bar on your browser. Click it to add a link to the page you are viewing. The bookmarklet: <a href="javascript:location.href='<?php echo($lwHome); ?>item-edit.php?actiontype=add&amp;url='+encodeURIComponent(location.href)+'&amp;title='+encodeURIComponent(document.title)
	">+linkwalla</a></li>
	
	<li><strong>Don't forget!</strong> Once everything is working delete lwInstaller.php from your server. If you don't someone else could use it to take over your site.</li>
	
	<li>Go to <a href="index.php">your new Linkwalla blog</a> to try it out.Have fun! <a href="http://benbrophy.com/contact/">Let me know how it goes.</a></li>
</ol>
<h3>Del.icio.us and Magnolia Intergration</h3>
<p>You can turn on Linkwalla's Del.icio.us or Magnolia Intergration by entering your account information on the <a href="lwSettings-edit.php">settings page</a>.</p>

<?php
}
if($_POST["actiontype"] && $_POST["lwPassword"]=='') {
	/* DISPLAY BLANK PASSWORD FAILURE MESSAGE*/
	echo '<p class="lwFailed">You need to enter a password. Click the back button to return to the install form.</p>';
} ?>


<?php include'foot.php'; ?>