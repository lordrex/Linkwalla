<?php 
require_once 'lwFunctions.php';
// check login status before displaying this page
lw_auth();
$pagetype='form';
$pagetitle='Edit Link';
$lightbox = intval($_GET["lightbox"]); //You want lightbox or regular view?
if($lightbox != 1) { include'head.php'; } // If regular view, load the template head

//which link?
$itemID =$_GET["itemID"];
$actiontype=$_GET["actiontype"];
$url=$_GET["url"];
$title=urldecode($_GET["title"]);
// redirect back to referring page
if($url != "") { $redirect="1"; }

// This part decides whether show an archived month or the current month
$archive = $_GET["archive"]; // Pull the archive date from the URL
if($archive==FALSE) { $archive = date("ym"); } // If it's not in the URL, set to this month

/* CREATE AN OBJECT OF OF THE RIGHT XML NODE */
else { 
    $xmlFileName = 'archive/' . $archive . 'archive.xml';
    $xml = simplexml_load_file($xmlFileName);
    $link=$xml->xpath('//link[sortdate="' . $itemID . '"]');
    $link=$link[0];
}
?>

<?php if($actiontype=='edit') { ?>
<h1>Edit Link</h1>
<?php } 
if($actiontype=='add') { ?>
<h1>Add Link</h1>
<?php } ?>

<p><?php echo $title; ?></p>

<p class="instruction">'Title' is required.</p>
<p id="formerror">Please fill in the missing fields.</p>

<form class="checkSubmit" name="editform" action="item-act.php" method="post">
<div class="textinput">
<label for="linktitle">Title *</label>
<?php if($actiontype=='edit') { ?>
<input type="text" class="required" name="linktitle" id="linktitle" size="60" maxlength="60" value="<?php echo $link->linktitle; ?>">
<?php } 
if($actiontype=='add') { ?>
<input type="text" class="required" name="linktitle" id="linktitle" size="60" maxlength="60" value="<?php echo $title; ?>">
<?php } ?>
</div>

<div class="textinput">
<label for="url">URL</label>
<?php if($actiontype=='edit') { ?>
<input type="text" name="url" id="url" size="60" maxlength="200" value="<?php echo $link->url; ?>">
<?php } 
if($actiontype=='add') { ?>
<input type="text" name="url" id="url" size="60" maxlength="200" value="<?php echo $url; ?>">
<?php } ?>
</div>
  
<div class="textinput">
<label for="description">Description:</label>
<textarea name="description" id="description" class="lwResize" rows="6" cols="40"><?php if($actiontype=='edit') { 
    echo $link->description;
} ?></textarea>
</div>

<!-- del.icio.us posting -->
<?php if($actiontype=='add' && $del_username!='') { ?>
<div class="checkbox">
    <input name="del_post" id="del_post" type="checkbox" value="si" onclick="revealOptions('delOptions')" />
    <label for="del_post">
    Add this post to my <a href="http://del.icio.us/" target="_blank">del.icio.us</a> account
    </label>
</div>
<div id="delOptions" style="display:none;margin:0;">
    <div class="textinput">
        <label for="del_tags">del.icio.us tags</label>
        <input type="text" name="del_tags" id="del_tags" size="60" maxlength="200" value="">
    </div>
</div>
<?php } ?>

<!-- ma.gnolia posting -->
<?php if($actiontype=='add' && $mag_username!='') { ?>
<div class="checkbox">
    <input name="mag_post" id="mag_post" type="checkbox" value="si" onclick="revealOptions('magOptions')" />
    <label for="mag_post">
    Add this post to my <a href="http://ma.gnolia.com/" target="_blank">ma.gnolia</a> account
    </label>
</div>
<div id="magOptions" style="display:none;margin:0;">
    <div class="textinput">
        <label for="mag_tags">ma.gnolia tags</label>
        <input type="text" name="mag_tags" id="mag_tags" size="60" maxlength="200" value="">
    </div>
</div>
<?php } ?>

<input type="hidden" name="itemID" value="<?php echo $itemID; ?>">
<input type="hidden" name="actiontype" value="<?php echo $actiontype; ?>">
<input type="hidden" name="archive" value="<?php echo $archive; ?>">
<input type="hidden" name="redirect" value="<?php echo $redirect; ?>">

<div id="submitdiv">
<?php if(!is_writable('archive')) { ?>
    <p><strong>The linkwalla XML archive directory doesn't seem to be writable. Editing won't work until you fix that problem.</strong></p>
    <input class="button" type="submit" id="submit" value="Save" disabled="disabled">
<?php
}
elseif(file_exists($xmlFileName) && !is_writable($xmlFileName)) { ?>
    <p><strong>The linkwalla XML files don't seem to be writable. Editing won't work until you fix that problem.</strong></p>
    <input class="button" type="submit" id="submit" value="Save" disabled="disabled">
<?php
}
else {
?>
    <input class="button lwSubmit" type="submit" id="submit" value="Save">
<?php 
}
if($lightbox) { ?>
    <a href="#" class="lbAction" rel="deactivate"><button>Cancel</button></a>
<?php } ?>
</div>
</form>

<?php if($lightbox != 1) { include'foot.php'; }  // If regular view, load the template foot ?>