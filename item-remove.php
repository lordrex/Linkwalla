<?php 
// check login status before displaying this page
require_once 'lwFunctions.php';
lw_auth();

$pagetitle='remove link';

//You want lightbox or regular view?
$lightbox = intval($_GET["lightbox"]);
if($lightbox != 1) {
    include'head.php';
}
//which link?
$itemID =$_GET["itemID"];
$archive =$_GET["archive"];
$xmlFileName = 'archive/' . $archive . 'archive.xml';
$xml = simplexml_load_file($xmlFileName);
$link=$xml->xpath('//link[sortdate="' . $itemID . '"]');
$link=$link[0];
?>

<h1>Remove Link</h1>

<p>Are you sure you want to remove the link "<a href="<?php echo $link->url ?>"><?php echo $link->linktitle ?></a>"?</p>
<form name="editform" action="item-act.php" method="post">
<input type="hidden" name="itemID" value="<?php echo $itemID; ?>">
<input type="hidden" name="actiontype" value="remove">
<input type="hidden" name="archive" value="<?php echo $archive; ?>">
<div id="submitdiv">
<?php if(!is_writable($xmlFileName)) { ?>
<p><strong>The linkwalla XML files don't seem to be writable. Editing won't work until you fix that problem.</strong></p>
<input class="button" type="submit" id="submit" value="Remove" disabled="disabled">
<?php
}
else {
?>
<input class="button submit" type="submit" id="submit" value="Remove">
<?php 
}
if($lightbox) { ?>
<a href="#" class="lbAction" rel="deactivate"><button>Cancel</button></a>
<?php } ?>
</div>
</form>

<?php if($lightbox != 1) {
     include'foot.php';
} ?>