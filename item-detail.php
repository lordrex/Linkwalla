<?php 
// check login status before displaying this page
require_once 'lwFunctions.php';

$loginstatus = lw_logincheck();
//which link?
$node =intval($_GET["node"]);
$itemID =$_GET["id"];

// This part decides whether show an archived month or the current month
$archive =$_GET["archive"];
if($archive==FALSE) {
    $archive = substr($itemID,2,4);
}

/* CREATE AN OBJECT OUT OF THE RIGHT XML NODE */
$xmlFile = 'archive/' . $archive . 'archive.xml';
$xml = simplexml_load_file($xmlFile);
$link=$xml->link[$node];

if($itemID =="") {
    $itemID =$link->sortdate;
}

// This link, the next link and the previous link
$link = $xml->xpath('//link[sortdate="' . $itemID . '"]');
$link = $link[0];
$nextLink = $link->xpath('following-sibling::*');
$prevLink = $link->xpath('preceding-sibling::*');

$pagetitle= $link->linktitle;


//You want lightbox or regular view?
$lightbox = intval($_GET["lightbox"]);
if($lightbox != 1) {
    include'head.php';
}


/* USED TO FIGURE OUT WHAT THE LAST LINK IS */
$currentFileCount = linkwalla_linkCount($xmlFile);
?>

<h1>Link List</h1>

<p>
<?php if($prevLink[0]->sortdate != '') { ?>
    <a href="item-detail.php?id=<?php echo end($prevLink)->sortdate; ?>">
    &lt; <?php echo end($prevLink)->linktitle; ?> </a>| 
<?php } ?>

<?php
$year = substr($xmlFile,-15,2);
$month = substr($xmlFile,-13,2);
echo "<a href=\"index.php?archive=$year$month\">$month/$year</a> | ";
?>

<?php if($nextLink[0]->sortdate != '') { ?>
    <a href="item-detail.php?id=<?php echo $nextLink[0]->sortdate; ?>">
    <?php echo $nextLink[0]->linktitle; ?> &gt;</a>
<?php } ?>

<div id="links">

<?php linkwalla_linkList($linkCount,$linkTotal,$xmlFile,$loginstatus); ?>


<div class="xfolkentry">
	<h3>
		<?php if($link->url != ''){ ?><a class="taggedlink" href="<?php echo htmlspecialchars($link->url);?>"><?php } ?>
			<?php echo $link->linktitle;?>
		<?php if($link->url != ''){ ?></a><?php } ?>
	</h3>
	<p class="detail">Posted <?php echo $link->adddate;?>&nbsp;
	<a href="item-detail.php?id=<?php echo $link->sortdate;?>" title="permanent link" class="lwPermanentLink"><img src="lwbookmark.gif" alt="permanent link" title="permanent link" width="10" height="10"></a>
	
	<?php if($loginstatus == 1) {?>
	| <a href="item-edit.php?itemID=<?php echo $link->sortdate; ?>&actiontype=edit&archive=<?php echo substr($xmlFile,-15,4); ?>" class="editlink lbOn">Edit</a>
	<a href="item-remove.php?itemID=<?php echo $node;?>&archive=<?php echo substr($xmlFile,-15,4); ?>" class="editlink lbOn">Remove</a>
	<?php }?>
	</p>
	
	<?php if($link->description != ''){ ?>
	<div class="description"><?php echo Markdown($link->description);?></div>
	<?php }?>
</div>
</div>

<?php if($lightbox != 1) {
     include'foot.php';
} ?>