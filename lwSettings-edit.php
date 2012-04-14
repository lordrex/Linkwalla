<?php 
require_once 'lwFunctions.php';
// check login status before displaying this page
lw_auth();
$pagetype='form';
$pagetitle='Linkwalla settings';
include 'head.php'; 

/* 1 GET VALUES FROM OF THE CURRENT SETINGS */
	$settingsFile = 'lwSettings.xml';
	$settingsXml = simplexml_load_file($settingsFile);
                $username = $settingsXml->lwUsername;
		$secretpassword = $settingsXml->lwPassword;
		$lwLoginKey = $settingsXml->lwLoginKey;
		$rssTitle = $settingsXml->rssTitle;
		$rssLink = $settingsXml->rssLink;
		$rssDescription = $settingsXml->rssDescription;
		$rssWebMaster = $settingsXml->rssWebMaster;
		$rssLanguage = $settingsXml->rssLanguage;
		$linkListCount = $settingsXml->linkListCount;
	$xmldec= '<?xml version="1.0" encoding="UTF-8"?>';

/* 2 GET VALUES FROM THE FORM */
if($_POST["actiontype"]) {
	$lwSettings = array( 
		'actiontype' => $_POST["actiontype"],
		'lwPassword' => $_POST["lwPassword"],
		'lwUsername' => $_POST["lwUsername"],
		'lwPasswordCheck' => $_POST["lwPasswordCheck"],
		'del_password' => linkwalla_encode($_POST["del_password"]),
		'del_username' => linkwalla_encode($_POST["del_username"]),
		'mag_password' => linkwalla_encode($_POST["mag_password"]),
		'mag_username' => linkwalla_encode($_POST["mag_username"]),
		'rssTitle' => htmlspecialchars($_POST["rssTitle"]),
		'rssLink' => htmlspecialchars($_POST["rssLink"]),
		'rssDescription' => htmlspecialchars($_POST["rssDescription"]),
		'rssWebMaster' => htmlspecialchars($_POST["rssWebMaster"]),
		'rssLanguage' => htmlspecialchars($_POST["rssLanguage"]),
		'linkListCount' => $_POST["linkListCount"]
	);
	$lwMessages = array();
	if($lwSettings['lwPassword'] != $lwSettings['lwPasswordCheck']){
				$lwMessages[] = '<p class="lwFailed">Your password and password verification did not match</p>';
	}
	else {
		ob_start(); ?>
<?php echo $xmldec; ?>

<lwSettings>
<?php //change password if there's a new value
	if($lwSettings['lwPassword']=='' && $lwSettings['lwPasswordCheck']=='') {$lwNewPassword = $secretpassword;} else { $lwNewPassword = crypt($lwSettings['lwPassword']); }
?>
  <lwUsername><?php echo $lwSettings['lwUsername'];?></lwUsername>
	<lwPassword><?php echo $lwNewPassword;?></lwPassword>
	<lwLoginKey><?php echo $lwLoginKey;?></lwLoginKey>
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
			$lwMessages[] = '<p class="lwSuccess">Wrote new settings to the settings file. <a href="index.php">Return to the linklist</a></p>';
			fclose($handle);
		}
		else {
			$lwMessages[] = '<p class="lwFailed">Uh oh! Couldn\'t write the settings into the file</p>';
		}
	}
} // end of the setting update action
$lwHome = 'http://' . $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, -19);
?>

<h1>Linkwalla settings</h1>

<?php if($_POST["actiontype"]) {
	/* DISPLAY SUCCESS AND FAILURE MESSAGES*/
	foreach($lwMessages as $lwMessage) {
		echo $lwMessage;
	}
}
?>
<?php
	$settingsFile = 'lwSettings.xml';
	$settingsXml = simplexml_load_file($settingsFile);
                $username = $settingsXml->lwUsername;
		$secretpassword = $settingsXml->lwPassword;
		$rssTitle = $settingsXml->rssTitle;
		$rssLink = $settingsXml->rssLink;
		$rssDescription = $settingsXml->rssDescription;
		$rssWebMaster = $settingsXml->rssWebMaster;
		$rssLanguage = $settingsXml->rssLanguage;
		$linkListCount = $settingsXml->linkListCount;
	$xmldec= '<?xml version="1.0" encoding="UTF-8"?>';
?>

<p class="instruction">Don't forget your password. There's no reminder.</p>
<p id="formerror">Please fill in the missing fields.</p>
<form class="checkSubmit" action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
<fieldset>
	<legend>Change Password or Username (leave blank to keep your password the same)</legend>
	<div class="textinput">
		<label for="lwUsername">Username *</label>
		<input type="text" name="lwUsername" id="lwUsername" size="20" maxlength="20" value="<?php echo $username; ?>"/>
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
		<input type="text" class="required" name="rssTitle" id="rssTitle" size="40" maxlength="120" value="<?php echo $rssTitle; ?>">
	</div>
	<div class="textinput">
		<label for="rssLink">Site URL *</label>
		<input type="text" class="required" name="rssLink" id="rssLink" size="40" maxlength="120" value="<?php echo $rssLink; ?>">
	</div>
	<div class="textinput">
		<label for="rssDescription">rssDescription *</label>
		<input type="text" class="required" name="rssDescription" id="rssDescription" size="40" maxlength="80" value="<?php echo $rssDescription; ?>">
	</div>
	<div class="textinput">
		<label for="rssWebMaster">rssWebMaster *</label>
		<input type="text" class="required" name="rssWebMaster" id="rssWebMaster" size="40" maxlength="40" value="<?php echo $rssWebMaster; ?>">
	</div>
	<div class="textinput">
		<label for="rssLanguage">rssLanguage *</label>
		<input type="text" class="required" name="rssLanguage" id="rssLanguage" size="8" maxlength="6" value="<?php echo $rssLanguage; ?>">
	</div>
</fieldset>

<input type="hidden" name="actiontype" value="install">

<div id="submitdiv">
    <input type="submit" id="submit" value="Save">
</div>
</form>

<h2>Now what?</h2>
<h3>How about a handy bookmarklet?</h3>
<p>Just drag this link to the bookmark bar on your browser. Click it to add a link to the page you are viewing. The bookmarklet: <a href="javascript:location.href='<?php echo($lwHome); ?>item-edit.php?actiontype=add&amp;url='+encodeURIComponent(location.href)+'&amp;title='+encodeURIComponent(document.title)">+linkwalla</a></p>
<h3><a href="index.php">Return to you link list</a></h3>

<?php include'foot.php'; ?>
