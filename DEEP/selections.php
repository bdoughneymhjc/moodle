<?php

require_once('../config.php');
require_once('../course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/pear/HTML/QuickForm/input.php');

$PAGE->set_url($CFG->wwwroot . "/DEEP/selections.php");
$PAGE->set_title("DEEP Selections");
$PAGE->set_heading("DEEP Selections");

$userid = optional_param('userid', 0, PARAM_INT);
$errors = optional_param('errors', NULL, PARAM_TEXT);

require_login();

$rolesql = "SELECT mdl_role_assignments.id, mdl_role_assignments.roleid, mdl_role_assignments.userid from mdl_role_assignments WHERE mdl_role_assignments.userid=$USER->id AND mdl_role_assignments.roleid=12;";
$userroles = $DB->get_records_sql($rolesql);
if ($userroles) {
    echo $OUTPUT->header();
    echo "<p><a href='manage.php'>Manage DEEP Courses</a> | <a href='selections.php'>Manage DEEP Selections</a> | <a href='export.php'>Export DEEP Selections</a></p>";
} else {
    redirect($CFG->wwwroot . '/DEEP/index.php');
}

$action = optional_param('action', NULL, PARAM_TEXT);
if ($action == 'delete') {
    $delid = required_param('delid', PARAM_INT);
    $DB->delete_records('deep_selections', array('id' => $delid));
}

// get all of the DEEP config info from the database
$years = explode(",", $DB->get_field('config', 'value', array('name' => "deep_manage_years")));
$terms = explode(",", $DB->get_field('config', 'value', array('name' => "deep_manage_terms")));
$year = $DB->get_field('config', 'value', array('name' => "deep_year")); // current year for deep
$term = $DB->get_field('config', 'value', array('name' => "deep_current_term")); // current term for deep
$maxday = $DB->get_field('config', 'value', array('name' => "deep_maxday")); // current options per day
$availtimes = explode(",", $DB->get_field('config', 'value', array('name' => "deep_times_avail"))); // available deep selections times

$studentselected = array();
$studentselected[7] = "";
$studentselected[8] = "";
$studentselected[9] = "";
$studentselected[10] = "";

// get all student selections for this term year from database
// group by student id to squash selections so that we know which
// student ids to look up

$ssql = "SELECT mdl_deep_selections.id, mdl_deep_selections.userid from mdl_deep_selections WHERE mdl_deep_selections.term=" . $term . " AND mdl_deep_selections.year=" . $year . " GROUP BY mdl_deep_selections.userid;";
$selections = $DB->get_records_sql($ssql);

$deepdays = $DB->get_records_sql("SELECT * from mdl_deep_days;");
$daycount = array();

$totaloptions = count($deepdays) * $maxday;

echo "<p><b>" . count($selections) . " students have selected their options.</b></p>";

echo "<table width='100%'>";
echo "<tr>";
echo "<td style='font-size: xx-small;'>Name</td><td style='font-size: xx-small;'>Class</td>";

$daycountera = array();

// set up the header rows in the table
foreach ($deepdays as $day => $dayno) {
    $daycounter = 0;
    while ($daycounter < $maxday) {
        echo "<td style='font-size: xx-small;'>" . $dayno->dayname . $daycounter . "</td>";
        $daycountera[] = $day;
        $daycounter++;
    }
}

echo "</tr>";

foreach ($selections as $student) {
    $stuinfo = $DB->get_record_sql("SELECT mdl_user.id, mdl_user.firstname, mdl_user.lastname, mdl_user.department FROM mdl_user WHERE mdl_user.id = " . $student->userid . ";");
    $stuclasses = $DB->get_records_sql("SELECT mdl_deep_selections.id, mdl_deep_selections.classid, mdl_deep_class.code, mdl_deep_class.day, mdl_deep_category.blockday FROM mdl_deep_selections INNER JOIN mdl_deep_class ON mdl_deep_class.id = mdl_deep_selections.classid INNER JOIN mdl_deep_category ON mdl_deep_class.category = mdl_deep_category.id WHERE mdl_deep_selections.userid=" . $student->userid . " AND mdl_deep_selections.term=" . $term . " AND mdl_deep_selections.year=" . $year . " ORDER BY mdl_deep_class.day;");

    $blockcount = $DB->get_record_sql("SELECT count(mdl_deep_selections.id) AS blockcount FROM mdl_deep_selections INNER JOIN mdl_deep_class ON mdl_deep_class.id = mdl_deep_selections.classid INNER JOIN mdl_deep_category ON mdl_deep_class.category = mdl_deep_category.id WHERE mdl_deep_selections.userid = ? AND mdl_deep_selections.term = ? AND mdl_deep_selections.year = ? AND mdl_deep_category.blockday = ?;", array($student->userid, $term, $year, 1));

    $yearlevel = substr($stuinfo->department, 0, 1);
    if ($yearlevel == 1) {
        $yearlevel = 10;
    }

    if (count($stuclasses) - $blockcount->blockcount + ($blockcount->blockcount * $maxday) != $totaloptions) {
        if (count($stuclasses) - $blockcount->blockcount + ($blockcount->blockcount * $maxday) < $totaloptions) {
            $background = " style='background: red;'";
        } else {
            $background = " style='background: orange;'";
        }
    } else {
        $background = "";
        $studentselected[$yearlevel] ++;
    }

    echo "<tr><td" . $background . ">" . $stuinfo->firstname . " " . $stuinfo->lastname . "</td><td>" . $stuinfo->department . "</td>";

    $counter = 0;
    foreach ($stuclasses as $id => $class) {
        if ($counter < $totaloptions) {
            if ($class->day < $daycountera[$counter]) {
                // there are too many options on a day
                // skip it
            }
            if ($class->day == $daycountera[$counter]) {
                echo "<td><a href='selections.php?action=delete&delid=" . $id . "#" . $stuinfo->id . "'>";
                echo $class->code . $term;
                echo "(<i style='font-size: xx-small;'>" . $id . "</i>)";
                echo "</td>";

                $counter++;
            }
            if (($class->day > $daycountera[$counter]) && isset($daycountera[$counter])) {
                while ($class->day > $daycountera[$counter]) {
                    echo "<td>&nbsp;</td>";
                    $counter++;
                    if (!isset($daycountera[$counter])) {
                        break; // if it is null get out of this loop!
                    }
                }
                // now we have caught up with what column we should be in 
                echo "<td><a href='selections.php?action=delete&delid=" . $id . "#" . $stuinfo->id . "'>";
                echo $class->code . $term;
                echo "(<i style='font-size: xx-small;'>" . $id . "</i>)";
                echo "</td>";
                $counter++;
            }
        }
    }

    echo "</tr>";
}

echo "</table>";

echo "<p style='font-weight: bold;'>";

$totalproper = 0;

foreach ($studentselected as $yearno => $yearlevels) {
    echo "Year " . $yearno . ": " . $yearlevels . ", ";
    $totalproper = $totalproper + $yearlevels;
}

echo "Total: " . $totalproper;
echo "</p>";

// Print MOL footer
echo $OUTPUT->footer();
?>