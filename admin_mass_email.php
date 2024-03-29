<?php
//*
// admin_mass_email.php
// Admin Section
// Send email message to selected recipients or all
//*

//Check if admin is logged in
session_start();
if(!session_is_registered('UserId') || $_SESSION['UserType'] != "A")
  {
    header ("Location: index.php?action=notauth");
	exit;
}

// config
include_once "configuration.php";
//Include global functions
include_once "common.php";
//Initiate database functions
include_once "ez_sql.php";

//Get list of rooms
$sSQL="SELECT * FROM school_rooms ORDER BY school_rooms_id";
$rooms=$db->get_results($sSQL);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title><?php echo _BROWSER_TITLE?></title>
<style type="text/css" media="all">@import "student-admin.css";</style>
<SCRIPT language="JavaScript">
/* Javascript function to submit form and check if field is empty */
function submitform(fldName)
{
  var f = document.forms[0];
  var t = f.elements[fldName]; 
  if (t.value!="") 
    f.submit();
  else
    alert("<?php echo _ENTER_VALUE?>");
}
</SCRIPT>
<link rel="icon" href="favicon.ico" type="image/x-icon"><link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

<script type="text/javascript" language="JavaScript" src="sms.js"></script>
</head>

<body><img src="images/<?php echo _LOGO?>" border="0">

<div id="Header">
<table width="100%">
  <tr>
    <td width="50%" align="left"><font size="2">&nbsp;&nbsp;<?php echo date(_DATE_FORMAT); ?></font></td>
    <td width="50%"><?php echo _ADMIN_MASS_EMAIL_UPPER?></td>
  </tr>
</table>
</div>

<div id="Content">
	<h1><?php echo _ADMIN_MASS_EMAIL_TITLE?></h1>
	<table width="85%">
	    <tr> 
	      <td>
			  <p class="ltext"><?php echo _ADMIN_MASS_EMAIL_SUBTITLE?></p>
		  </td>
		</tr>
		  <form name="massmail" method="post" action="admin_process_mass_mail.php">
		<tr>
		  <td class="tdinput"><?php echo _ADMIN_MASS_EMAIL_SEND?> : <br>
		    <input type="radio" value="studentcontact" checked name="mailto"> <?php echo _ADMIN_MASS_EMAIL_CONTACTS?>
		    <?php echo _ADMIN_MASS_EMAIL_ROOM?>
		    <select name="room">
		    <?php
		    echo "<option value=all selected='selected'>"._ADMIN_MASS_EMAIL_ALL."</option>";
		    foreach($rooms as $room){
		      echo "<option value=".$room->school_rooms_id.">".$room->school_rooms_desc."</option>";
		    }
		    ?>
		    </select><br>

		    <input type="radio" value="teachers" name="mailto"> <?php echo _ADMIN_MASS_EMAIL_TEACHERS?><br>
		    <input type="radio" value="both" name="mailto"> <?php echo _ADMIN_MASS_EMAIL_BOTH?><br>
		    <?php echo _ADMIN_MASS_EMAIL_SUBJECT?> :<br>
		    <input type="text" name="subject" size="50"><br>
		 </td>
	  </tr>
	  <tr> 
	      <td class="tdinput">
		  <?php echo _ADMIN_MASS_EMAIL_MESSAGE?> : <br>
	        <textarea name="message" cols="65" rows="6"></textarea>
	      </td>
     </tr>
	 <tr> 
		<td><b><A href="javascript: submitform('message')" class="aform"><?php echo _ADMIN_MASS_EMAIL_NOW?></a></b></td>
  </tr>					  
  </form>
 </table>
</div>
<?php include "admin_menu.inc.php"; ?>
</body>

</html>
