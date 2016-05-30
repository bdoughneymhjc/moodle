<?php

require_once('../config.php');
require_once('../course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/pear/HTML/QuickForm/input.php');

$PAGE->set_context(context_course::instance(1));
$PAGE->set_url($CFG->wwwroot . "/DEEP/index.php");
$PAGE->set_title("DEEP Selections");
$PAGE->set_heading("DEEP Selections");

$userid = optional_param('userid', 0, PARAM_INT);
$errors = optional_param('errors', NULL, PARAM_TEXT);

// might need to revisit this to not allow guest to select DEEPs
if ($userid === 0) {
    $userid = $USER->id;
}
// get all of the DEEP config info from the database
$year = $DB->get_field('config', 'value', array('name' => "deep_year")); // current year for deep
$term = $DB->get_field('config', 'value', array('name' => "deep_current_term")); // current term for deep
$maxday = $DB->get_field('config', 'value', array('name' => "deep_maxday")); // current options per day
$availtimes = explode(",", $DB->get_field('config', 'value', array('name' => "deep_times_avail"))); // available deep selections times

$studentdetails = $DB->get_record('user', array('id' => $userid));
$deepdays = $DB->get_records_sql("SELECT * FROM mdl_deep_days;");

$totalsels = count($deepdays) * $maxday;

$currenttime = time();

foreach ($availtimes as $times) {
    $startstop = explode("-", $times);
    if ($currenttime > $startstop[0] && $currenttime < $startstop[1]) {
        // the DEEP selections are available
        $available = TRUE;
        break;
    } elseif ($userid != $USER->id) {
        $available = TRUE;
        break;
    } else {
        $available = FALSE;
    }
}

// Print MOL header
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    require_login();
    echo $OUTPUT->header();

    $rolesql = "SELECT mdl_role_assignments.id, mdl_role_assignments.roleid, mdl_role_assignments.userid from mdl_role_assignments WHERE mdl_role_assignments.userid=$USER->id AND mdl_role_assignments.roleid=12;";
    $userroles = $DB->get_records_sql($rolesql);
    if ($userroles) {
        // user can be shown extra links etc. also can
        // update other other users DEEP records

        echo "<p><a href='manage.php'>Manage DEEP Courses</a> | <a href='selections.php'>Manage DEEP Selections</a> | <a href='export.php'>Export DEEP Selections</a></p>";
    }

    //first check if a student has entered their options - if so grey the submit button out
    //list all options available for the student
    //if pre-selected options, only list those on the appropriate day
    // sql to get all subjects on current year / term
    $sql = "SELECT mdl_deep_class.id, mdl_deep_class.name, mdl_deep_class.descr, mdl_deep_class.day, mdl_deep_class.category, mdl_deep_class.cost, mdl_deep_class.classsize, mdl_deep_class.term, mdl_deep_days.id as dayid, mdl_deep_days.dayname, mdl_deep_category.colour from mdl_deep_class INNER JOIN mdl_deep_days ON mdl_deep_class.day = mdl_deep_days.id INNER JOIN mdl_deep_category ON mdl_deep_class.category = mdl_deep_category.id WHERE mdl_deep_class.term = ? AND mdl_deep_class.year = ? ORDER BY mdl_deep_class.day, mdl_deep_class.name;";
    $deepsubjects = $DB->get_records_sql($sql, array($term, $year));

    // sql to get any student pre-selections
    $ssql = "SELECT mdl_deep_selections.id, mdl_deep_selections.classid from mdl_deep_selections WHERE mdl_deep_selections.userid=" . $userid . " AND mdl_deep_selections.term=" . $term . " AND mdl_deep_selections.year=" . $year . ";";
    $preselections = $DB->get_records_sql($ssql);

    $blocksql = "SELECT mdl_deep_days.dayname FROM mdl_deep_selections INNER JOIN mdl_deep_class ON mdl_deep_selections.classid = mdl_deep_class.id INNER JOIN mdl_deep_category ON mdl_deep_class.category = mdl_deep_category.id INNER JOIN mdl_deep_days on mdl_deep_class.day = mdl_deep_days.id WHERE mdl_deep_selections.userid = ? AND mdl_deep_selections.term = ? AND mdl_deep_selections.year = ? AND mdl_deep_category.blockday = 1;";
    $blockdays = $DB->get_records_sql($blocksql, array($userid, $term, $year));

    $blockdaysout = array();
    $blockdaysout[] = "";

    foreach ($blockdays as $bkey => $bday) {
        $blockdaysout[] = $bday->dayname;
    }

    $preselout = array();

    //convert preselections to a sensible array so we can search it
    foreach ($preselections as $key => $preselection) {
        //if we have 8 options here, then grey out the submit button because they are done!
        $preselout[$key] = $preselection->classid;
    }

    if (!$available) {
        echo "<div style='background: red; width: 100% padding: 10px; text-align: center; font-size: 20px; font-weight: bold'>DEEP selections are not available at the moment, please come back later.<br />\n";
        echo "DEEP selections are available at these times;<br />";
        foreach ($availtimes as $times) {
            $startstop = explode("-", $times);
            echo date("D j F H:i", $startstop[0]);
            echo " to ";
            echo date("H:i", $startstop[1]);
            echo "<br />";
        }
        echo "</div>\n";
    }

    //count($stuclasses) - $blockcount->blockcount + ($blockcount->blockcount * $maxday) != $totaloptions
    $blockcount = count($blockdaysout) - 1;
    if (count($preselout) - $blockcount + ($blockcount * $maxday) == $totalsels) {
        $seldone = TRUE;
        echo "<div style='background: lightgreen; width: 100%; padding: 10px; text-align: center; font-size: 20px; font-weight: bold;'>You have finished selecting your DEEP options for this term.</div>";
    } else {
        $seldone = FALSE;
    }

    $deepdays = $DB->get_records_sql("SELECT mdl_deep_days.id, mdl_deep_days.dayname from mdl_deep_days;");
    $daycount = array();

    // this convoluted mess is to figure out the number of rows needed in the table
    foreach ($deepdays as $dayno) {
        $temp = $DB->get_record_sql("SELECT COUNT(mdl_deep_class.day) AS daycount FROM mdl_deep_class WHERE mdl_deep_class.day = ? AND mdl_deep_class.term = ? AND mdl_deep_class.year = ?;", array($dayno->id, $term, $year));
        $daycount[] = $temp->daycount;
    }
    rsort($daycount);
    $rowcount = $daycount[0];

    echo "<SCRIPT LANGUAGE='javascript' type='text/javascript'>\n";
    echo "function checkedCount(nme) {\n";
    echo "var checkbox = document.getElementsByName(nme);";
    echo "var NewCount = checkbox.length;\n";
    echo "var total = 0;";
    echo "for(var i=0; i<NewCount; i++) {\n";
    echo "if(checkbox[i].checked){\n";
    echo "total = total +1; }\n";
    echo "}";
    echo "if (total > " . $maxday . ")\n";
    echo "{\n";
    echo "alert('Please select only two options on each day.')\n";
    echo "document.deep; return false;\n";
    echo "}\n";
    //add a else turn background green here, so student knows they have finished that day?
    echo "}\n";
    echo "</SCRIPT>\n";

    echo "<h3>DEEP Selections for <b>" . $studentdetails->firstname . " " . $studentdetails->lastname . "</b></h3>";

    if (isset($errors)) {
        if ($errors != '') {
            echo "<p style='color: red; font-weight: bold;'>Sorry, but the following errors have occurred; <br />";
            echo $errors;
            echo "</p>";
        }
    }

    $categories = $DB->get_records_sql("SELECT * from mdl_deep_category;");
    $headspan = count($categories);

    echo "<p>Click and hold down on a course name to see description.</p>";
    echo "<table>";
    echo "<tr><td colspan='" . $headspan . "'><b>Key</b></td></tr>";
    echo "<tr>";
    foreach ($categories as $category) {
        echo "<td style='background-color: " . $category->colour . "'>" . $category->catname . "</td>";
    }
    echo "</tr>";
    echo "</table>";

    //output header line of table
    echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "' autocomplete='off' name='deep'>";
    echo "<table border='1' width='100%'>";
    echo "<tr>";

    $colwidth = 100 / count($deepdays);
    foreach ($deepdays as $day) {
        echo "<td colspan='2' style='background-color: lightgray;' width='" . $colwidth . "%'>" . $day->dayname . "</td>";
    }
    echo "</tr>\n";

    $deeptable = array();

    foreach ($deepsubjects as $subject) {
        $background = $subject->colour;

        $classsizes = $DB->get_record_sql("SELECT COUNT(mdl_deep_selections.classid) AS classcount FROM mdl_deep_selections WHERE mdl_deep_selections.classid=" . $subject->id . " AND mdl_deep_selections.term=" . $term . " AND mdl_deep_selections.year=" . $year . ";");

        if ($classsizes->classcount >= $subject->classsize || array_search($subject->dayname, $blockdaysout)) {
            $disabled = " disabled='disabled' ";
            $background = "dimgray";
        } else {
            $disabled = " ";
        }

        if (isset($subject->cost) && $subject->cost != 0) {
            $cost = "<p style='font-size: x-small; color: red;'>Cost: $" . $subject->cost . "</p>";
        } else {
            $cost = "<p style='font-size: x-small; color: red;'></p>";
        }

        if (!array_search($subject->id, $preselout)) {
            $deeptable[$subject->dayname][] = "<td style='background-color: " . $background . ";' class='" . $subject->dayname . "'><input type='checkbox' name='" . $subject->dayname . "[]' value='" . $subject->id . "' onClick='return checkedCount(\"" . $subject->dayname . "[]\")'" . $disabled . "/></td><td style='background-color: " . $background . ";'><a href='#' class='deeplinks'>" . $subject->name . $cost . "<span>" . $subject->descr . "</span></a></td>";
        } else {
            $disabled = " disabled='disabled' ";
            $deeptable[$subject->dayname][] = "<td style='background-color: " . $background . ";' class='" . $subject->dayname . "'><input type='checkbox' checked='checked' name='" . $subject->dayname . "[]' value='" . $subject->id . "' onClick='return checkedCount(\"" . $subject->dayname . "[]\")'" . $disabled . "/></td><td style='background-color: " . $background . ";'><a href='#' class='deeplinks'>" . $subject->name . $cost . "<span>" . $subject->descr . "</span></a><input type='hidden' value='TRUE' name='" . $subject->dayname . "sel' /></td>";
        }
    }

    for ($i = 0; $i <= $rowcount; $i++) {
        echo "<tr>";
        foreach ($deepdays as $day) {
            if (isset($deeptable[$day->dayname][$i])) {
                echo $deeptable[$day->dayname][$i];
            } else {
                echo "<td>&nbsp;</td><td>&nbsp;</td>";
            }
        }
        echo "</tr>\n";
    }
    echo "</table>";
    echo "<p style='text-align: right;'>";
    echo "<input type='hidden' name='userid' value='" . $userid . "' />";
    if (!$seldone && $available) {
        echo "<input type='submit' value='Save' />";
    } else {
        echo "<input type='submit' value='Save' disabled='disabled' />";
    }

    echo "</p>\n";
    echo "</form>";

    // Print MOL footer
    echo $OUTPUT->footer();
} else { //user has pressed save
    $datenow = time();

    $deepdays = $DB->get_records_sql("SELECT mdl_deep_days.id, mdl_deep_days.dayname from mdl_deep_days;");

    $userid = $_POST['userid'];

    foreach ($deepdays as $day) {
        if (isset($_POST[$day->dayname])) {

            foreach ($_POST[$day->dayname] as $dayrec) {
                //some sort of checking needs to go here to make sure student
                //has not selected too many of anything etc

                $coursecat = $DB->get_record_sql("SELECT mdl_deep_class.category, mdl_deep_class.classsize, mdl_deep_class.name, mdl_deep_category.maxsel, mdl_deep_category.catname FROM mdl_deep_class INNER JOIN mdl_deep_category ON mdl_deep_class.category = mdl_deep_category.id WHERE mdl_deep_class.id = " . $dayrec . ";");
                // get every selection that the student has selected in this category
                $checksels = $DB->get_record_sql("SELECT mdl_deep_selections.id, mdl_deep_selections.userid, COUNT(mdl_deep_class.category) AS catcount FROM mdl_deep_selections INNER JOIN mdl_deep_class ON mdl_deep_selections.classid = mdl_deep_class.id WHERE mdl_deep_selections.userid=" . $userid . " AND mdl_deep_class.category = " . $coursecat->category . " AND mdl_deep_selections.term = " . $term . " AND mdl_deep_selections.year = " . $year . ";");

                // this is just in case the class has filled up in between selecting it and saving it
                $classsizes = $DB->get_record_sql("SELECT COUNT(mdl_deep_selections.classid) AS classcount FROM mdl_deep_selections WHERE mdl_deep_selections.classid=" . $dayrec . ";");

                $selection->classid = $dayrec;
                $selection->userid = $userid;
                $selection->term = $term;
                $selection->year = $year;
                $selection->status = "SE";

                // count them and compare to category max
                if ($checksels->catcount >= $coursecat->maxsel) {
                    $redirect .= " You have selected too many " . $coursecat->catname . " options.";
                } else {
                    if ($classsizes->classcount >= $coursecat->classsize) {
                        $redirect .= " Sorry, " . $coursecat->name . " is full already. ";
                    } else {
                        $DB->insert_record('deep_selections', $selection);
                    }
                }
            }
        } else {
            if (isset($_POST[$day->dayname . 'sel'])) {
                // the user has pre selected courses, no need to give an error
            } else {
                $redirect .= " Please select a class on " . $day->dayname . ".";
            }
        }
    }
    $PAGE->navigation->clear_cache();
    // tell the user that their options were saved and go to some other page
    redirect($CFG->wwwroot . '/DEEP/index.php?errors=' . $redirect . '&userid=' . $userid);
}
?>