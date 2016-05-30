<?php

require_once('../config.php');
require_once('../course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/pear/HTML/QuickForm/input.php');

$PAGE->set_url($CFG->wwwroot . "/DEEP/export.php");

$userid = optional_param('userid', 0, PARAM_INT);
$errors = optional_param('errors', NULL, PARAM_TEXT);

require_login();

$rolesql = "SELECT mdl_role_assignments.id, mdl_role_assignments.roleid, mdl_role_assignments.userid from mdl_role_assignments WHERE mdl_role_assignments.userid=$USER->id AND mdl_role_assignments.roleid=12;";
$userroles = $DB->get_records_sql($rolesql);
if ($userroles) {
    //echo "<p><a href='manage.php'>Manage DEEP Courses</a> | <a href='selections.php'>Manage DEEP Selections</a> | <a href='export.php'>Export DEEP Selections</a></p>";
} else {
    redirect($CFG->wwwroot . '/DEEP/index.php');
}

// get all of the DEEP config info from the database
$years = explode(",", $DB->get_field('config', 'value', array('name' => "deep_manage_years")));
$terms = explode(",", $DB->get_field('config', 'value', array('name' => "deep_manage_terms")));
$year = $DB->get_field('config', 'value', array('name' => "deep_year")); // current year for deep
$term = $DB->get_field('config', 'value', array('name' => "deep_current_term")); // current term for deep
$maxday = $DB->get_field('config', 'value', array('name' => "deep_maxday")); // current options per day
$availtimes = explode(",", $DB->get_field('config', 'value', array('name' => "deep_times_avail"))); // available deep selections times
// get all student selections for this term year from database
// group by student id to squash selections so that we know which
// student ids to look up

$ssql = "SELECT mdl_deep_selections.id, mdl_deep_selections.userid from mdl_deep_selections WHERE mdl_deep_selections.term=" . $term . " AND mdl_deep_selections.year=" . $year . " GROUP BY mdl_deep_selections.userid;";
$selections = $DB->get_records_sql($ssql);

$deepdays = $DB->get_records_sql("SELECT * from mdl_deep_days;");
$daycount = array();

$totaloptions = count($deepdays) * $maxday;

header("Expires: 0");
header("Cache-Control: private");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=deepexport_" . $year . "_" . $term . ".csv");

echo "\"TTGrid_IDNumber\", \"FirstName\", \"LastName\", \"Class\", ";

$daycountera = array();

// set up the header rows in the table
foreach ($deepdays as $day => $dayno) {
    $daycounter = 0;
    while ($daycounter < $maxday) {
        echo "\"" . $dayno->dayname . $daycounter . "\", ";
        $daycountera[] = $day;
        $daycounter++;
    }
}

echo "\n";

foreach ($selections as $student) {
    $stuinfo = $DB->get_record_sql("SELECT mdl_user.id, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_user.idnumber FROM mdl_user WHERE mdl_user.id = " . $student->userid . ";");
    $stuclasses = $DB->get_records_sql("SELECT mdl_deep_selections.id, mdl_deep_selections.classid, mdl_deep_class.code, mdl_deep_class.day FROM mdl_deep_selections INNER JOIN mdl_deep_class ON mdl_deep_class.id = mdl_deep_selections.classid WHERE mdl_deep_selections.userid=" . $student->userid . " AND mdl_deep_selections.term=" . $term . " AND mdl_deep_selections.year=" . $year . " ORDER BY mdl_deep_class.day;");

    if (count($stuclasses) != $totaloptions) {
        // perhaps we can use this to skip options export if not complete?
        $background = " style='background: red;'";
    } else {
        $background = "";
    }

    echo "\"" . $year . "TT_" . $stuinfo->idnumber . "\", \"" . $stuinfo->firstname . "\", \"" . $stuinfo->lastname . "\", \"" . $stuinfo->department . "\", ";

    $counter = 0;
    foreach ($stuclasses as $id => $class) {
        if ($counter < $totaloptions) {
            if ($class->day < $daycountera[$counter]) {
                // there are too many options on a day
                // skip it
            }
            if ($class->day == $daycountera[$counter]) {
                echo "\"" . $class->code . $term;
                echo "\", ";

                $counter++;
            }
            if (($class->day > $daycountera[$counter]) && isset($daycountera[$counter])) {
                while ($class->day > $daycountera[$counter]) {
                    echo "\"\", "; // print a blank space
                    $counter++;
                    if (!isset($daycountera[$counter])) {
                        break; // if it is null get out of this loop!
                    }
                }
                // now we have caught up with what column we should be in 
                echo "\"" . $class->code . $term;
                echo "\", ";
                $counter++;
            }
        }
    }

    if ($counter < $totaloptions) {
        while ($counter < $totaloptions) {
            echo "\"\", ";
            $counter++;
        }
    }
    echo "\n";
}
?>