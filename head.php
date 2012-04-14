<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="en" />

<!-- The stuff below is important, be careful editing -->
<?php 
if ($pagetitle) { 
    echo'<title>' . $pagetitle . '</title>';
}
else {
    echo'<title>Linkwalla</title>';
}
?>
<script src="prototype.js" type="text/javascript"></script>
<script src="linkwalla.js" type="text/javascript"></script>
<style type="text/css" title="text/css">
	@import "linkwalla.css";
</style>
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="linkrss.xml" />
<!-- End of important stuff -->

</head>
<body>
<div id="page">