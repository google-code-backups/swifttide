<?php
//v1.5 12-4-05 removed echo string
//v1.52 01-05-06 sort by student last name
session_start();
if(!session_is_registered('UserId') || $_SESSION['UserType'] != "A")
{
	header ("Location: index.php?action=notauth");
	exit;
}

//set up database connection
include_once"ez_sql.php";
include_once"common.php";
// Include configuration
include_once "configuration.php";

$sorted_1 = get_param("sorted_1");
$sorted_2 = get_param("sorted_2");
$start_db_date = get_param("start_date");
$end_db_date = get_param("end_date");

//make sure we have all the post variables no matter what settings apache has
// if(!$sorted_1) $sorted_1 = $GET['sorted_1'];
// if(!$sorted_2) $sorted_2 = $GET['sorted_2'];
// if(!$start_date) $start_date = $GET['start_date'];
// if(!$end_date) $end_date = $GET['end_date'];

//fix the dates
if($start_db_date) $start_db_date = fix_date($start_db_date);
if($end_db_date) $end_db_date = fix_date($end_db_date);

$q = "SELECT * FROM discipline_history, infraction_codes, studentbio, 
	school_names, ethnicity, school_years, grades, student_grade_year, school_rooms 
	WHERE (discipline_history.discipline_history_student = studentbio.studentbio_id) 
	AND (discipline_history.discipline_history_code = infraction_codes.infraction_codes_id) 
	AND (studentbio.studentbio_id = student_grade_year.student_grade_year_student) 
	AND (discipline_history.discipline_history_school = school_names.school_names_id) 
	AND (studentbio.studentbio_ethnicity = ethnicity.ethnicity_id) 
	AND (grades.grades_id = student_grade_year.student_grade_year_grade) 
	AND (school_years.school_years_id = discipline_history.discipline_history_year) 
	AND (studentbio.studentbio_homeroom = school_rooms.school_rooms_id) ";

if ($start_db_date != "") {
  if ($end_db_date != "") {
	$q .= "AND (discipline_history.discipline_history_date BETWEEN '$start_db_date' AND '$end_db_date')"; }
  else
	$q .= "AND (discipline_history.discipline_history_date > '$start_db_date')"; }

$q .= " ORDER BY discipline_history_date, $sorted_1";
if($sorted_2 == $sorted_1) $sorted_2 = 'none';
if($sorted_2 != 'none') $q .= ", $sorted_2";

if($sorted_1 == "grades_id") $display_1 = 'grades_desc';
else if($sorted_1 == "studentbio_ethnicity") $display_1 = 'ethnicity_desc';
else if($sorted_1 == "studentbio_homeroom") $display_1 = 'school_rooms_desc';
else $display_1 = $sorted_1;

if($sorted_2 == "grades_id") $display_2 = 'grades_desc';
else if($sorted_2 == "studentbio_ethnicity") $display_2 = 'ethnicity_desc';
else if($sorted_1 == "studentbio_homeroom") $display_2 = 'school_rooms_desc';
else $display_2 = $sorted_2;

$q .= " , studentbio_lname ASC";
$r = $db->get_results($q);

?>
<html><title><?php echo _REPORT_DISCIPLINE_BROWSER_TITLE?></title>
<head>
<style type="text/css" media="all">@import "student-admin.css";</style>
</head>
<body>
<?php
if(is_array($r)) {
	echo"<table align='center' width='80%' cellspacing=5>
	     <th align='center'><h1>" . _REPORT_DISCIPLINE_HEADER;
	if ($start_db_date && $end_db_date)
	{ echo _REPORT_DISCIPLINE_FROM . $start_db_date . _REPORT_DISCIPLINE_TO . $end_db_date; }

	echo "</h1></th>";
	$ps1 = "";
	$ps2 = "";
	foreach($r as $s) {
	
		$cs1 = $s->{$sorted_1};
		if($cs1 != $ps1) {
			$cd1 = $s->{$display_1};
			$heading_text_1 = "";
			if($display_1 == 'studentbio_bus') $heading_text_1 = _REPORT_DISCIPLINE_ROUTE;
			else if($display_1 == 'studentbio_homeroom') $heading_text_1 = _REPORT_DISCIPLINE_HOME;

			echo"<tr><td align='left'><h1>$heading_text_1 $cd1</h1></td></tr>";
		}
	
		if($sorted_2 != 'none') {
			$cs2 = $s->{$sorted_2};
			if($cs2 != $ps2) {
				$cd2 = $s->{$display_2};
				$heading_text_2 = "";
				if($display_2 == 'studentbio_bus') $heading_text_2 = _REPORT_DISCIPLINE_ROUTE;
				else if($display_2 == 'studentbio_homeroom') $heading_text_2 = _REPORT_DISCIPLINE_HOME;
				echo"<tr><td align='right'><h2>$heading_text_2 $cd2</h2></td></tr>";	
			}
		}

		//display the students name
		echo"<tr><td align='center'>
			<table border=1 width='100%'>
			<tr><td><table width='100%'><tr><td>
			<table width='100%'><tr><td class='record_heading'>
			$s->infraction_codes_desc " . _REPORT_DISCIPLINE_BY . " $s->studentbio_fname $s->studentbio_lname 
			on $s->discipline_history_date</td>";

		//display the students gender
		if($sorted_1 != 'studentbio_gender' && $sorted_2 != 'studentbio_gender') 
			echo"<td class='gender' align='right'>($s->studentbio_gender)</td>";
		echo"</tr></table></td></tr>";

		if($s->discipline_history_notes && $s->discipline_history_notes != '')
			echo"<tr><td><table width='100%'><tr><td class='tblhead' width='10%'>" . _REPORT_DISCIPLINE_NOTES . ":</td>
			<td class='tblcont' width='90%' align='left'>$s->discipline_history_notes</td></tr></table></td></tr>";
		if($s->discipline_history_action && $s->discipline_history_action != '')
			echo"<tr><td><table width='100%'><tr><td class='tblhead' with='10%'>" . _REPORT_DISCIPLINE_ACTION . ":</td>
			<td class='tblcont' width='90%' align='left'>$s->discipline_history_action</td></tr></table></td></tr>";
		
		//the static column headers
		echo"<tr><td><table border=1 width='100%' align='center'>
			<tr class='tblhead'><td>" . _REPORT_DISCIPLINE_INTERNAL . "</td><td>" . _REPORT_DISCIPLINE_DOB . "</td><td>" . _REPORT_DISCIPLINE_SCHOOL . "</td>";
		//the dynamic column headers
		if($sorted_1 != 'grades_id' && $sorted_2 != 'grades_id') 
			echo"<td>" . _REPORT_DISCIPLINE_GRADE . "</td>";
		if($sorted_1 != 'studentbio_ethnicity' && $sorted_2 != 'studentbio_ethnicity')
			echo"<td>" . _REPORT_DISCIPLINE_ETHNICITY . "</td>";
		if($sorted_1 != 'studentbio_homeroom' && $sorted_2 != 'studentbio_homeroom')
			echo"<td>" . _REPORT_DISCIPLINE_HOME . "</td>";
		if($sorted_1 != 'studentbio_bus' && $sorted_2 != 'studentbio_bus')
			echo"<td>" . _REPORT_DISCIPLINE_ROUTE . "</td>";
		
		echo"</td></tr><tr class='tblcont'><td>$s->studentbio_internalid</td><td>$s->studentbio_dob</td>
			<td>$s->school_names_desc</td>";
		if($sorted_1 != 'grades_id' && $sorted_2 != 'grades_id') 
			echo"<td>$s->grades_desc</td>";
		if($sorted_1 != 'studentbio_ethnicity' && $sorted_2 != 'studentbio_ethnicity')
			echo"<td>$s->ethnicity_desc</td>";
		if($sorted_1 != 'studentbio_homeroom' && $sorted_2 != 'studentbio_homeroom')
			echo"<td>$s->school_rooms_desc</td>";
		if($sorted_1 != 'studentbio_bus' && $sorted_2 != 'studentbio_bus')
			echo"<td>$s->studentbio_bus</td>";

		echo"</tr></table></td></tr></table></td></tr></table></td></tr>";
		if ($sorted_2 != "none") { $ps2 = $s->{$sorted_2}; }
		$ps1 = $s->{$sorted_1};
	}	

	?></table></body></html><?php
} else {
	echo"<center><h1>" . _REPORT_DISCIPLINE_NONE . "</h2></center>";
}
