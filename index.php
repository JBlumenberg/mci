<?php
/* 
Manual Coding Interface v. 1.0

Dr. Johannes N. Blumenberg
www.Manual-Coding.com
Johannes.Blumenberg@gesis.org

If you use this software, please cite me:
Blumenberg, Johannes N. (2018). Manual Coding Interface – A web interface to facilitate the 
coding of open-ended (survey) questions. v. 1.0. Available at www.Manual-Coding.com.

***********************************************************************************

MIT License

Copyright (c) 2018 Johannes N. Blumenberg

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

***********************************************************************************

This is the main file, which pulls everything together.

*/


session_start();
include ("inc/mysql.php");


$conn_id = mysql_connect($host,$user,$pw);
mysql_select_db($database,$conn_id);

$db = $_SESSION['userdatabase'];


// In this section the codes become saved.
if ($_GET['action'] == "save") {


		// Read what has been sent in the formular via the header
		$idoeq = $_POST['idoeqpost'];	
		$entry_start = $_POST['entry_start'];
		$entry_end = time();
		$code1 = $_POST['code1'];
		$code2 = $_POST['code2'];
		$code3 = $_POST['code3'];
		$code4 = $_POST['code4'];
		$fulltext = $_POST['fulltext'];

		// Strip everything to prevent SQL injection (well, at least something); feel free to rewrite all queries to MySQLi or PDO
		$idoeq_strip = mysql_real_escape_string($idoeq);	
		$entry_start_strip = mysql_real_escape_string($entry_start);
		$entry_end_strip = mysql_real_escape_string($entry_end);
		$entry_difference = $entry_end_strip - $entry_start_strip;
		$code1_strip = mysql_real_escape_string($code1);
		$code2_strip = mysql_real_escape_string($code2);
		$code3_strip = mysql_real_escape_string($code3);			
		$code4_strip = mysql_real_escape_string($code4);
		$fulltext_strip = mysql_real_escape_string($fulltext);	
		

		// Insert the contents of the formular into the userdatabase
		  mysql_query("update $db set 
				code1 = '$code1_strip',
				code2 = '$code2_strip',
				code3 = '$code3_strip',
				code4 = '$code4_strip',
				comment = '$fulltext_strip',
				entry_start = '$entry_start_strip',
				entry_end = '$entry_end_strip',
				entry_difference = '$entry_difference'
		  where idoeq = '$idoeq_strip'") or die(mysql_error());
  

		// Request the coder ID from the session and update the coders table
		$userid = $_SESSION['user'];
		mysql_query("update $tablecoders set idoeq = '$idoeq_strip' where id = '$userid'");
  
		$next = $idoeq_strip+1;
  
		$_SESSION['marker'] = "1";  
  
  
// The following HTML page just signals the coder that everything was fine and redirects to the next code
// The JavaScript was taken from this tutorial: https://www.apphp.com/index.php?snippet=javascript-redirect-with-timer
?>
	<html>
	<head>
	<title>SUCCESS!</title>
	<link href="css/mci.css" rel="stylesheet" type="text/css" media="screen" />
	
		<script type="text/javascript">
		var count = 2;

		<?php
		echo 'var redirect = "index.php?action=mkentry&next='.$next.'";';
		?>
		
		function countDown(){
			var timer = document.getElementById("timer");
			if(count > 0){
				count--;
				timer.innerHTML = "SUCCESS! You will be redirected in "+count+" seconds.";
				setTimeout("countDown()", 1000);
			}else{
				window.location.href = redirect;
			}
		}
		</script>	
	
	</head>
	<center>
	<div style="font-family: verdana; font-size: 12pt; color: #657593; font-weight: bold;">
	<span id="timer">
	<script type="text/javascript">countDown();</script>
	</span></div></center>
	</body>
	</html>



<?php
// This is the password check
	} elseif ($_GET['action'] == "mkentry") {
		
	if ($_SESSION['marker'] == "1") {
		
	} elseif ($_SESSION['marker'] != "1") {


	$_SESSION['user'] = $_POST['userid'];
	$_SESSION['pw'] = $_POST['password'];

	}

	$userid = $_SESSION['user'];
	$password = $_SESSION['pw'];

	$result = mysql_query("select * from $tablecoders where id = '$userid'");
			
	$databasecoder = mysql_result($result,0,"databasecoder");	
	$passworddb = mysql_result($result,0,"password");	
	$idoeq = mysql_result($result,0,"idoeq");

	$_SESSION['userdatabase'] = $databasecoder;

// If the password is incorrect the coder becomes redirected to the landing page. 
if ($password != $passworddb) {
		?>	
	<html>
	<head>
	<title>Password incorrect</title>
	<link href="css/mci.css" rel="stylesheet" type="text/css" media="screen" />
	
		<script type="text/javascript">
		var count = 6;
		var redirect = "index.php";
		
		function countDown(){
			var timer = document.getElementById("timer");
			if(count > 0){
				count--;
				timer.innerHTML = "Your password was incorrect. You will be redirected in "+count+" seconds.";
				setTimeout("countDown()", 1000);
			}else{
				window.location.href = redirect;
			}
		}
		</script>	
	
	</head>
	<center>
	<div style="font-family: verdana; font-size: 12pt; color: #657593; font-weight: bold;">
	<span id="timer">
	<script type="text/javascript">countDown();</script>
	</span></div></center>
	</body>
	</html>



<?php
// If the password is correct, the entry mask appears


		} elseif ($password == $passworddb) {

		// Next marks the next code in line
		$next = $idoeq+1;

		?>

		<html>
		<head>
		<title>Manual Coding Interface</title>
		</head>
		<link href="css/mci.css" rel="stylesheet" type="text/css" media="screen" />

		<?php

		// Read the result
		$result = mysql_query("select * from $databasecoder where idoeq = '".$next."'");
				
		$oldidoeq = mysql_result($result,0,"idoeq");
		$text = mysql_result($result,0,"text");
		$entry_start = time(); // This is needed for the timestamps

		echo '<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">';

		echo '<div id="fixed">';
		echo '<center><h1><i>&raquo;'.$text.'&laquo;</i></h1></center>';
		echo '<p align="right"><small>(ID: '.$next.')</small><br><a href="logout.php">Logout</a></p>';
		echo '</div>';

		echo '<div id="wrapper">';
		echo '<center><h1><i>&raquo;'.$text.'&laquo;</i></h1></center>';
		echo '<form action="index.php?action=save" method=post name="codierung" >';
		echo '<p align="right"><small>(ID: '.$next.')</small><br><a href="logout.php">Logout</a></p><br>';
		echo '<input type=hidden name=action value="save">';
		echo '<input type=hidden name=idoeqpost VALUE="'.$oldidoeq.'">';
		echo '<input type=hidden name=entry_start VALUE="'.$entry_start.'">';
		
		// It is simple! Just a HTML formula...
		?>
		 <h2>Codes<h2>
		 <table style="text-align: left; width: 100%; width: 800px" cellpadding="0" cellspacing="0">
				<tr>
					<td colspan="2" class="blue">
		<strong>Code 1:</strong></td><td> <select id="code1" name="code1">
									<option value="3">This is</option>
									<option value="1">an</option>
									<option value="2">example</option>
								</select>			
					</td>
					</tr>	  
				<tr>
					<td colspan="2" class="grey">
		<strong>Code 2:</strong></td><td> <select id="code2" name="code2">
									<option value="1">an</option>
									<option value="3">This is</option>
									<option value="2">example</option>
								</select>			
					</td>
					</tr>	
				<tr>
					<td colspan="2" class="blue">
		<strong>Code 3:</strong></td><td> <select id="code3" name="code3">
									<option value="2">example</option>
									<option value="3">This is</option>
									<option value="1">an</option>
									<option value="4">with</option>
									<option value="5">many</option>
									<option value="6">codes</option>							
								</select>			
					</td>
					</tr>	
		</table>    

		
		 <h2>Meta Information<h2>
			<table style="text-align: left; width: 100%; width: 800px" cellpadding="0" cellspacing="0">
			<tr>
			<td colspan="1" class="blue">	
				<fieldset name="typ" class="field">
					<legend>This is a checkbox</legend>
					<input name="code4" type="checkbox" id="code4"  value="1"/><label for="code4">Checkbox applies</label><br />
					</fieldset>
				<br />		
			Anything else to add?</br>	
			<textarea name="fulltext" class"field" cols="80" rows="5"></textarea>							
			</td>
			</tr>	  
			</table> 
		
		 <center><input type=submit value="Send" class="button" style="margin-top: 20px;"></form></center>		 
		 
		 </div>
		</body>
		</html>
<?php
}



// If no option is transported through the header, the landing page appears
} else {



?>

	<html>
	<head>
	<title>Manual Coding Interface</title>
	</head>
	<link href="css/mci.css" rel="stylesheet" type="text/css" media="screen" />
	<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
	<div id="wrapper">
	<center><h2>Manual Coding Interface</h2>
	<h3>A web interface to facilitate the coding of open-ended (survey) questions</h3></center>
	<?php
	echo '<center>';
	echo '<form action="index.php?action=mkentry" method=post name="mkentry" style="padding-top: 20px">';
	echo '<strong>User: </strong><select id="userid" name="userid">';
	echo '<option value=" ">Please select:</option>';

	// All usernames are listed to make the selection easier - however, less secure as well.
	// Alas, passwords are not encrypted anyway...
	$result = mysql_query("select * from $tablecoders") or die(mysql_error());
		
	  if ($num = mysql_num_rows($result)) {
		
		for($i=0;$i < $num; $i++) {
		$name = mysql_result($result,$i,"name");
		$id = mysql_result($result,$i,"id");

		
		echo '<option value="'.$id.'">'.$name.'</option>';
		}
	  }
		
	echo '</select><br /><strong>Password: </strong><input name="password" type=password><br /><br />';	
	echo '<input type=submit value="Start" class="button"></form></center>';
	echo '</br></br></p>';
	echo '</body>';
	echo '</html>';
}
?>
