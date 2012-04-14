<?php
require_once "markdown.php";

/* CONFIGURATION */

$settingsFile = 'lwSettings.xml';
if(file_exists('lwSettings.xml')) {
	$settingsXml = simplexml_load_file($settingsFile);
	$username = $settingsXml->lwUsername;
	$secretpassword = $settingsXml->lwPassword;
	$del_username = linkwalla_decode($settingsXml->del_username);
	$del_password = linkwalla_decode($settingsXml->del_password);
	$mag_username = linkwalla_decode($settingsXml->mag_username);
	$mag_password = linkwalla_decode($settingsXml->mag_password);
	$rssTitle = $settingsXml->rssTitle;
	$rssLink = $settingsXml->rssLink;
	$rssDescription = $settingsXml->rssDescription;
	$rssWebMaster = $settingsXml->rssWebMaster;
	$rssLanguage = $settingsXml->rssLanguage;
	$linkListCount = $settingsXml->linkListCount;
}
$xmldec= '<?xml version="1.0" encoding="UTF-8"?>';

/* FUNCTIONS */

///////////////////////////
//  SIMPLE AUTHORIZATION //
///////////////////////////

// Checks login, and returns user to the loginpage if it's not successful
function lw_auth() {
    global $settingsXml;
    if (crypt($_COOKIE['login'],$settingsXml->lwLoginKey) != $settingsXml->lwLoginKey) {
        header("Location: login.php");
    }
}

// Create the login cookie if password is correct
function lw_login($password, $user) {    
	global $secretpassword;
	global $username;
	global $settingsXml;
	if(crypt($password, $secretpassword) == $secretpassword ) // is the entered pw correct?
	{
		if($username == $user) // is the entered username correct?
		{
  		setcookie('login','',time()-3600,'/'); //destroys old login cookie
			$lwLoginKey = ""; //start a blank key
			$possible_characters = "abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
			for ($i = 1; $i <= 10; $i++) {
				$lwLoginKey .= substr($possible_characters, mt_rand(0,strlen($possible_characters)),1);
			} //create a random key
			setcookie('login',$lwLoginKey,time()+60*60*24*30*12,'/'); //create cookie with key
			$settingsXml->lwLoginKey = crypt($lwLoginKey); // add key to site settings
			$settingsXml->asXML('lwSettings.xml'); // re-write the settings file
			header("Location: index.php"); // return to the home page
		}
		else // if your username was wrong....
		{
			header("Location: login.php?error=yes"); // Return with an error
		}
	}
	else // if your password was wrong ...
	{
			header("Location: login.php?error=yes"); // Return with an error
	}
}


// Checks login, and returns variable, 1=logged in, 0=not
function lw_logincheck() {
	global $settingsXml;
	if (crypt($_COOKIE['login'],$settingsXml->lwLoginKey) != $settingsXml->lwLoginKey) {
			$x = 0;
	}
	else {
			$x = 1;
	}
	return $x;
}

// clears out the login cookie
function lw_logout() {
  setcookie('login','',time()-3600,'/'); //destroys the login cookie
}

// deletes the installer so no can use it to hijack the linklog
function lw_deleteInstaller() {
	if (unlink("lwInstaller.php")) { echo '<p class="lwSuccess">File Removed Successfully</p>'; }
	else { echo '<p class="lwFailed">An unspecified error occurred when trying to delete lwInstaller.php. Please check if the file exists and try again.</p>'; }
}

///////////////////////////////
// ADD, EDIT OR REMOVE LINKS //
///////////////////////////////

/* ADD: WRITES THE NEW LINK XML - THEN REWRITES ALL OF THE OLD ENTRIES */
function lwAdd($xmlFileName,$lwData) {
	global $xmldec;
	global $del_username;
	global $del_password;
	global $mag_username;
	global $mag_password;
	if(file_exists($xmlFileName)) {
		$xml = simplexml_load_file($xmlFileName);
	}
    ob_start(); ?>
<?php echo $xmldec; ?>
<linklist>
<link>
    <linktitle><?php echo $lwData['linktitle'];?></linktitle>
    <url><?php echo $lwData['url'];?></url>
    <adddate><?php echo $lwData['adddate'];?></adddate>
    <rssdate><?php echo $lwData['rssdate'];?></rssdate>
    <sortdate><?php echo $lwData['sortdate'];?></sortdate>
    <description><?php echo $lwData['description'];
    				   linkwalla_flickr($lwData['url']);?></description>
</link>
<?php foreach ($xml->link as $link) { ?>
<link>
    <linktitle><?php echo htmlspecialchars($link->linktitle);?></linktitle>
    <url><?php echo htmlspecialchars($link->url);?></url>
    <adddate><?php echo $link->adddate;?></adddate>
    <rssdate><?php echo $link->rssdate;?></rssdate>
    <sortdate><?php echo $link->sortdate;?></sortdate>
    <description><?php echo htmlspecialchars($link->description, ENT_QUOTES);?>
    </description>
</link>
<?php } ?>
</linklist>
<?php
    /* WRITE THE OUTPUT BUFFER TO THE XML FILE */
    $somecontent = ob_get_clean();
    $filename = $xmlFileName;
    $handle = fopen($filename, "w");
    fwrite($handle, $somecontent);
    fclose($handle);
// Parameters for del.icio.us update
if($lwData['del_post']=="si") {
    $params = array( 
        'url'  => $lwData['url'], // The URL
        'description'  => $lwData['linktitle'], // The linkwalla title
        'extended'  => $lwData['description'], // The linkwalla description
        'tags'     => $lwData['del_tags'], // tags (not saved in XML)
        'dt'     => date("c") // datestamp ISO8601
    );
    linkwalla_delicious($del_username, $del_password, $params);
}
// Parameters for ma.gnolia update
if($lwData['mag_post']=="si") {
    $params = array( 
        'title'  => $lwData['linktitle'], // The linkwalla title
        'description'  => $lwData['description'], // The linkwalla description
        'url'  => $lwData['url'], // The URL
        'private'  => "false", // The privacy setting
        'rating'  => 5, // The Rating
        'tags'     => $lwData['mag_tags'] // tags (not saved in XML)
    );
    linkwalla_magnolia($mag_username, $mag_password, $params);
}
    return true;
}

/* EDIT: PUSH NEW FORM DATA INTO THE XML*/
function lwEdit($xmlFileName,$lwData) {
	$xml = simplexml_load_file($xmlFileName);
	$link=$xml->xpath('//link[sortdate="' . $lwData['itemID'] . '"]');
	$link=$link[0];
    $link->linktitle = $lwData['linktitle'];
    $link->url = $lwData['url'];
    $link->description = $lwData['description'];
    $xml->asXML($xmlFileName);
}

/* REMOVE: REBUILDS ALL NODES EXCEPT THE 1 BEING DELETED */ 
function lwRemove($xmlFileName,$lwData) {
	global $xmldec;
	$xml = simplexml_load_file($xmlFileName);
    $i=0;
    ob_start(); ?>
<?php echo $xmldec; ?>
<linklist>
<?php foreach ($xml->link as $link) { 
if($link->sortdate != $lwData['itemID']){?>
<link>
    <linktitle><?php echo htmlspecialchars($link->linktitle);?></linktitle>
    <url><?php echo htmlspecialchars($link->url);?></url>
    <adddate><?php echo $link->adddate;?></adddate>
    <rssdate><?php echo $link->rssdate;?></rssdate>
    <sortdate><?php echo $link->sortdate;?></sortdate>
    <description><?php echo htmlspecialchars($link->description);?></description>
</link>
<?php $i++;}
} 
?>
</linklist>
<?php
if ($i > 0) {
    /* WRITE THE OUTPUT BUFFER TO THE XML FILE */
    $somecontent = ob_get_clean();
    $filename = $xmlFileName;
    $handle = fopen($filename, "w");
    fwrite($handle, $somecontent);
    fclose($handle);
    }
else {
    unlink($xmlFileName);
    }
}


//////////////////////
// REWRITE RSS FEED //
//////////////////////
function linkwalla_rss() {
global $xmldec;
global $rssTitle;
global $rssLink;
global $rssDescription;
global $rssWebMaster;
global $rssLanguage;

ob_start(); 
?>
<?php echo $xmldec; ?>
<rss version="2.0">
<channel>
<title><?php echo $rssTitle; ?></title>
<link><?php echo $rssLink; ?></link>
<description><?php echo $rssDescription; ?></description>
<language><?php echo $rssLanguage; ?></language>
<webMaster><?php echo $rssWebMaster; ?></webMaster>
<generator>Linkwalla</generator>
<?php linkwalla_iterateFiles('linkwalla_rssItems',15,1); ?>
</channel>
</rss>
<?php
    /* WRITE THE OUTPUT BUFFER TO THE XML FILE */
    $rsscontent = ob_get_clean();
    $rssname = 'linkrss.xml';
    $handle = fopen($rssname, "w");
    fwrite($handle, $rsscontent);
    fclose($handle);
    return true;
}


function linkwalla_rssItems($linkCount,$linkTotal,$xmlFile) {
    $xml = simplexml_load_file($xmlFile);
    $link=$xml->link;
    $i=0;
    
    while($linkCount < $linkTotal) {
        if(!$link[$i]) { return($linkCount); }
    ?>
    <item>
        <title><?php echo htmlspecialchars($link[$i]->linktitle);?></title>
<?php if($link[$i]->url != '') { ?>        <link><?php echo htmlspecialchars($link[$i]->url);?></link><?php } else { ?>
        <link><?php echo htmlspecialchars('http://' . $_SERVER['SERVER_NAME'] . substr($_SERVER['REQUEST_URI'],0,-8) . '-detail.php?id=' . $link[$i]->sortdate); ?></link>
        <?php } ?>
        <pubDate><?php echo $link[$i]->rssdate;?></pubDate>
        <description><?php echo htmlspecialchars(Markdown($link[$i]->description));?></description>
    </item>
    <?php 
        $i++;
        $linkCount++; 
    }
    return($linkCount);
}

///////////////////////////////
// LIST OF LINKS ON HOMEPAGE //
///////////////////////////////

function linkwalla_linkList($linkCount,$linkTotal,$xmlFile,$loginstatus) {
$xml = simplexml_load_file($xmlFile);
$link=$xml->link;
$i=0;
while  ($linkCount < $linkTotal) {
    if(!$link[$i]) { return($linkCount); }
    ?>
    <div class="xfolkentry">
    <h3>
        <?php if($link[$i]->url != ''){ ?><a class="taggedlink" href="<?php echo htmlspecialchars($link[$i]->url);?>"><?php } ?>
            <?php echo $link[$i]->linktitle;?>
        <?php if($link[$i]->url != ''){ ?></a><?php } ?>
    </h3>
    <p class="detail">Posted <?php echo $link[$i]->adddate;?>&nbsp; 
    <a href="item-detail.php?id=<?php echo $link[$i]->sortdate;?>" title="permanent link" class="lwPermanentLink"><img src="lwbookmark.gif" alt="permanent link" title="permanent link" width="10" height="10"></a>
<?php if($loginstatus == 1) {?>
    | <a href="item-edit.php?itemID=<?php echo $link[$i]->sortdate; ?>&amp;actiontype=edit&amp;archive=<?php echo substr($xmlFile,-15,4); ?>" class="editlink lbOn">Edit</a>
    <a href="item-remove.php?itemID=<?php echo $link[$i]->sortdate; ?>&amp;archive=<?php echo substr($xmlFile,-15,4); ?>" class="editlink lbOn">Remove</a>
<?php }?>
    </p>

<?php if($link[$i]->description != ''){ ?>
    <div class="description">
    <?php echo Markdown($link[$i]->description);?>
    </div>
<?php }?>
    </div>
<?php
    $i++;
    $linkCount++; 
}
return($linkCount);
}


function linkwalla_iterateFiles($generatingFunction,$linkTotal,$loginstatus) {
   if(!($file = scandir('archive',1))) {
       return false;
   }
   $linkCount=0;
   $i=-1;
    while($linkCount < $linkTotal) {
        $i++;
        if(substr($file[$i],0)==".") {
            break;
        }
        if(substr($file[$i],-4,4)!=".xml") {
            continue;
        }
        if($generatingFunction=='linkwalla_linkList') {
        $linkCount = linkwalla_linkList($linkCount,$linkTotal,'archive/' . $file[$i],$loginstatus);
        }
        if($generatingFunction=='linkwalla_rssItems') {
        $linkCount = linkwalla_rssItems($linkCount,$linkTotal,'archive/' . $file[$i]);
        }
    }
    return true;
}

function linkwalla_linkCount($xmlFile) {
   $xml = simplexml_load_file($xmlFile);
   $link=$xml->link;
   $i=-1;
   foreach($link as $linkcount) {
        $i++;
    }
    return $i;
}

//This function builds the list on the home page, but not the individual links
function lwGetArchive($archive) {
	global $linkListCount;
	if($archive==FALSE) {
		$xmlFileName = 'archive/' . date("ym") . 'archive.xml';
	}
	else {
		$xmlFileName = 'archive/' . $archive . 'archive.xml';
	} 
	if(file_exists($xmlFileName)) {
	  $xml = simplexml_load_file($xmlFileName);
	}
	$linkCount=0; 
	$linkTotal=$linkListCount; 
	$loginstatus = lw_logincheck();
	echo '<div id="links">';
	if($archive==FALSE) {
		if(!(linkwalla_iterateFiles(linkwalla_linkList,$linkTotal,$loginstatus))) {
		  echo "<p>No links yet. Run the <a href=\"lwInstaller.php\">Linkwalla Installer</a></p>";
		}
	}
	else {
		$xmlFileName = 'archive/' . $archive . 'archive.xml';
		linkwalla_linkList(0,1000,$xmlFileName,$loginstatus);
	}
	echo '</div>';
}


//////////////////////////////////////////
// LIST OF MONTHLY ARCHIVES ON HOMEPAGE //
//////////////////////////////////////////

function linkwalla_archiveList($archive) {
	if ($handle = opendir('archive')) {
		if($archive == null) { echo '<a class="archiveLink currentArchive">newish</a> | '; }
		else { echo '<a href="index.php" class="archiveLink">newish</a> | '; }
		while (false !== ($eachfile = readdir($handle))) {
			$files [] = $eachfile;
		}
		rsort($files);
		$i=0;
		foreach($files as $file){
			if (substr($file,-4,4)==".xml") {
				$year = substr($file,0,2);
				$month = substr($file,2,2);
				if($archive == $year . $month) { echo "<a class=\"archiveLink $year$month currentArchive\">$month/$year</a> | ";}
				else { echo "<a href=\"index.php?archive=$year$month\" class=\"archiveLink $year$month\">$month/$year</a> | ";}
				$i++;
			}
			// Change the number below to decide how many months are initially revealed
			if ($i == 12) {
				echo "<span class=\"lwOldArchives\">";
			}
		}
		echo "</span>";
		closedir($handle);
	}
}



/* EDIT SETTINGS*/
function lwEditSettings($xmlFileName,$lwData) {
	$xml = simplexml_load_file($xmlFileName);
	$link=$xml->xpath('//link[sortdate="' . $lwData['itemID'] . '"]');
	$link=$link[0];
    $link->linktitle = $lwData['linktitle'];
    $link->url = $lwData['url'];
    $link->description = $lwData['description'];
    $xml->asXML($xmlFileName);
		chmod("lwSettings.xml", 0600); //make sure the settings file is hidden from browsers
}


////////////////////////
// FLICKR INTEGRATION //
////////////////////////

function linkwalla_flickr($flickrURL) {
// Checking if this is a Flickr URL
$regex = "[0-9]{5,}";
if (!(eregi("flickr.com", $flickrURL))) {
	return;
}
// Extracting the Photo ID from the URL
else {
	if (!(ereg($regex, $flickrURL, $numbers))) {
		return;
	}
	else {
		$photoID = $numbers[0];
	}
}
// Querying Flickr using the ID
    $base = "http://api.flickr.com/services/rest/";
    $query_string = "method=flickr.photos.getSizes&api_key=be1c7f35a8836e5d4df04f7e1d2463b5&photo_id=$photoID";
    $url = "$base?$query_string";
	$xml = simplexml_load_file($url);
	foreach ($xml->sizes->size as $size) {
		if((string) $size['label']=='Small') {
			$thumbnailSource= (string) $size['source'];
			$thumbnailURL= (string) $size['url'];
		}
	}
	echo "\n\n" . htmlspecialchars("<a href=\"$flickrURL\"><img src=\"$thumbnailSource\" alt=\"view image on Flickr\" title=\"view image on Flickr $photoID \" /></a>");
}


////////////////////////
// DEL.ICIO.US UPDATE //
////////////////////////

function linkwalla_delicious($del_username, $del_password, $params) {
    $base = "https://$del_username:$del_password@api.del.icio.us/v1/posts/add";
    $query_string = '';
    foreach ($params as $key => $value) { 
        $query_string .= "$key=" . urlencode($value) . "&";
    }
    $url = "$base?$query_string";
    $response = file_get_contents($url);
}

////////////////////////
// MA.GNOLIA UPDATE //
////////////////////////
function linkwalla_magnolia($mag_username, $mag_password, $params) {
    $mag_key_file = file_get_contents("https://ma.gnolia.com/api/rest/1/get_key?id=$mag_username&password=$mag_password");
    $mag_key_xml = simplexml_load_string($mag_key_file);
    $mag_key = $mag_key_xml->key;
    $base         = "http://ma.gnolia.com/api/rest/1/bookmarks_add?api_key=$mag_key";
    $query_string = '';
    foreach ($params as $key => $value) { 
        $query_string .= "$key=" . urlencode($value) . "&";
    }
    $url = "$base&$query_string";
    $response = file_get_contents($url);
}

// ENCRYPTION 

//function to encrypt the string
function linkwalla_encode($str)
{
  for($i=0; $i<5;$i++)
  {
    $str=strrev(base64_encode($str)); //apply base64 first and then reverse the string
  }
  return $str;
}

//function to decrypt the string
function linkwalla_decode($str)
{
  for($i=0; $i<5;$i++)
  {
    $str=base64_decode(strrev($str)); //apply base64 first and then reverse the string}
  }
  return $str;
}
?>