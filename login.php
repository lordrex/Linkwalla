<?php 
$pagetitle='Linkwalla Login';
$pagetype='form';
require_once 'lwFunctions.php';
if($_GET["action"] == "logout") {
    lw_logout(); // Kills the login cooie
    header("Location: index.php"); // retrns to the home page
}
if($_POST["action"] == "login") {
    $password = $_POST["password"];
    $l_username = $_POST["lwusername"];
    lw_login($password, $l_username);
}
include 'head.php'; 
?>

<h2>Log in</h2>
<?php
  if($_GET["error"] == "yes") {
    echo '<p id="phpformerror"><b>Error</b>: That was the wrong username/password.</p>';
  }
?>
<p id="formerror">Please fill in the missing fields.</p>
<form class="checkSubmit" action="login.php" method="post">
	<div class="textinput">
	<label for="username">Username</label>
	<input type="text" name="lwusername" id="lwusername" class="required" size="20" maxlength="20" />
	</div>
	<div class="textinput">
	<label for="password">Password</label>
	<input type="password" name="password" id="password" class="required" size="20" maxlength="20" />
	</div>
	<input name="action" type="hidden" value="login" />
	<div id="submitdiv">
	<input type="submit" value="submit" />
	</div>
</form>
<?php include 'foot.php'; ?>