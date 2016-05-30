<?php

require_once('../config.php');
require_once('../course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/pear/HTML/QuickForm/input.php');

$PAGE->set_url($CFG->wwwroot . "/DEEP/index.php");
$PAGE->set_title("DEEP Course Management");
$PAGE->set_heading("DEEP Course Management");

$errors = optional_param('errors', NULL, PARAM_TEXT);
$classid = optional_param('classid', NULL, PARAM_INT);
$sortby = optional_param('sortby', "name", PARAM_TEXT);
$copyto = optional_param('copy', FALSE, PARAM_BOOL);

// get all of the DEEP config info from the database
$years = explode(",", $DB->get_field('config', 'value', array('name' => "deep_manage_years")));
$terms = explode(",", $DB->get_field('config', 'value', array('name' => "deep_manage_terms")));
$year = $DB->get_field('config', 'value', array('name' => "deep_year")); // current year for deep
$term = $DB->get_field('config', 'value', array('name' => "deep_current_term")); // current term for deep
$maxday = $DB->get_field('config', 'value', array('name' => "deep_maxday")); // current options per day
$availtimes = explode(",", $DB->get_field('config', 'value', array('name' => "deep_times_avail"))); // available deep selections times

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    require_login();

    $rolesql = "SELECT mdl_role_assignments.id, mdl_role_assignments.roleid, mdl_role_assignments.userid from mdl_role_assignments WHERE mdl_role_assignments.userid=$USER->id AND mdl_role_assignments.roleid=12;";
    $userroles = $DB->get_records_sql($rolesql);
    if ($userroles) {
        echo $OUTPUT->header();
        echo "<p><a href='manage.php'>Manage DEEP Courses</a> | <a href='selections.php'>Manage DEEP Selections</a> | <a href='export.php'>Export DEEP Selections</a></p>";
    } else {
        // the user should not be here, send them away
        redirect($CFG->wwwroot . '/DEEP/index.php');
    }

    if (isset($errors)) {
        echo $errors;
        echo "<br />";
    }

    // output a form for creating and editing subjects here
    echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "' autocomplete='off' name='deepclasses'>";

    // see if we are editing a subject- if so, look it up
    if (isset($classid)) {
        $subrec = $DB->get_record_sql("SELECT mdl_deep_class.code, mdl_deep_class.name, mdl_deep_class.descr, mdl_deep_class.day, mdl_deep_class.category, mdl_deep_class.cost, mdl_deep_class.year, mdl_deep_class.term, mdl_deep_class.teacher, mdl_deep_class.classsize, mdl_deep_days.id as dayid, mdl_deep_days.dayname from mdl_deep_class INNER JOIN mdl_deep_days on mdl_deep_class.day = mdl_deep_days.id WHERE mdl_deep_class.id = " . $classid . ";");

        if ($copyto) {
            // copy this record into the edit box ready for the next term
            // add one to the term and one to the year if it is term 4
            if ($subrec->term != 4) {
                $subrec->term++;
            } else {
                $subrec->term = 1;
                $subrec->year++;
            }
        } else {
            echo "<input type='hidden' name='classid' value='" . $classid . "' />";
        }
    } else {
        $subrec->year = $year;
        $subrec->classsize = 999;
    }

    $deepcategories = $DB->get_records_sql("SELECT mdl_deep_category.id, mdl_deep_category.catname, mdl_deep_category.colour FROM mdl_deep_category");
    $deepdays = $DB->get_records_sql("SELECT mdl_deep_days.id, mdl_deep_days.dayname FROM mdl_deep_days;");

    echo "<table style='border: 1px solid black;'>";
    echo "<tr><th><b>Category</b></th>";
    foreach ($deepdays as $deepday) {
        echo "<th><b>" . $deepday->dayname . "</b></th>";
    }
    echo "</tr>\n";
    foreach ($deepcategories as $deepcategory) {
        echo "<tr>";
        echo "<td style='background: " . $deepcategory->colour . ";'>" . $deepcategory->catname . "</td>";
        foreach ($deepdays as $deepday) {
            $catcount = $DB->get_record_sql("SELECT COUNT(mdl_deep_class.id) as classcount FROM mdl_deep_class WHERE mdl_deep_class.term = ? AND mdl_deep_class.year = ? AND mdl_deep_class.day = ? AND mdl_deep_class.category = ?;", array($term, $year, $deepday->id, $deepcategory->id));
            echo "<td style='background: " . $deepcategory->colour . ";'>" . $catcount->classcount . "</td>";
        }
        echo "</tr>\n";
    }

    echo "<tr><td><b>Classes / Day</b></td>";

    foreach ($deepdays as $deepday) {
        $daycount = $DB->get_record_sql("SELECT COUNT(mdl_deep_class.id) as classcount FROM mdl_deep_class WHERE mdl_deep_class.term = ? AND mdl_deep_class.year = ? AND mdl_deep_class.day = ?;", array($term, $year, $deepday->id));
        echo "<td><b>" . $daycount->classcount . "</b></td>";
    }

    echo "</tr>";

    echo "</table>";

    echo "<table>";
    echo "<tr><td>Code:</td><td><input type='text' name='code' value='" . $subrec->code . "' /> <i>4 letter code - Term number is automatically appended</i></td></tr>";
    echo "<tr><td>Name:</td><td><input type='text' name='name' value='" . $subrec->name . "' size='80' /></td></tr>";
    echo "<tr><td>Description:</td><td><textarea name='descr' id='descr' rows='5' cols='80'>" . $subrec->descr . "</textarea></td></tr>";
    echo "<tr><td>Cost:</td><td><input type='text' name='cost' value='" . $subrec->cost . "' /></td></tr>";
    echo "<tr><td>Day:</td><td><select name='day'>";

    $dayrec = $DB->get_records_sql("SELECT * from mdl_deep_days;");
    foreach ($dayrec as $day) {
        echo "<option value='" . $day->id . "'";
        if ($day->id == $subrec->dayid) {
            echo " selected='selected'";
        }
        echo ">" . $day->dayname . "</option>";
    }

    echo "</select></td></tr>";
    echo "<tr><td>Term:</td><td><select name='term'>";

    $tterm = 1;
    while ($tterm < 5) {
        echo "<option value='" . $tterm . "'";
        if ($tterm == $subrec->term || $tterm == $term) {
            echo " selected='selected'";
        }
        echo ">" . $tterm . "</option>";
        $tterm++;
    }

    echo "</select></td></tr>";
    echo "<tr><td>Year:</td><td><input type='text' name='year' value='" . $subrec->year . "' /></td></tr>";

    echo "<tr><td>Category:</td><td><select name='category'>";

    $catrec = $DB->get_records_sql("SELECT * from mdl_deep_category;");
    foreach ($catrec as $cat) {
        echo "<option value='" . $cat->id . "'";
        if ($cat->id == $subrec->category) {
            echo " selected='selected'";
        }
        echo ">" . $cat->catname . "</option>";
    }

    echo "</select></td></tr>";

    echo "<tr><td>Teacher:</td><td><input type='text' name='teacher' value='" . $subrec->teacher . "' /> <i>3 letter teaching initials</i></td></tr>";
    echo "<tr><td>Class Size:</td><td><input type='text' name='classsize' value='" . $subrec->classsize . "' /> <i>999 = no limit</i></td></tr>";
    echo "</table>";

    echo "<p style='text-align: right;'>";
    echo "<input type='submit' value='Save'/>";
    echo "</p>\n";

    // sql to get all subjects on current year / term
    $sql = "SELECT mdl_deep_class.id, mdl_deep_class.code, mdl_deep_class.teacher, mdl_deep_class.term, mdl_deep_class.name, mdl_deep_class.descr, mdl_deep_class.day, mdl_deep_class.category, mdl_deep_class.cost, mdl_deep_class.classsize, mdl_deep_class.year, mdl_deep_days.id as dayid, mdl_deep_days.dayname, mdl_deep_category.colour from mdl_deep_class INNER JOIN mdl_deep_days ON mdl_deep_class.day = mdl_deep_days.id INNER JOIN mdl_deep_category ON mdl_deep_class.category = mdl_deep_category.id WHERE mdl_deep_class.year IN ('" . join("','", $years) . "') AND mdl_deep_class.term IN ('" . join("','", $terms) . "') ORDER BY mdl_deep_class.year, mdl_deep_class." . $sortby . ";";
    $deepsubjects = $DB->get_records_sql($sql);

    //output header line of table
    echo "<table border='1' width='100%'>";
    echo "<tr>";
    echo "<td><b><a href='manage.php?sortby=name'>Code</a></b></td>";
    echo "<td><b><a href='manage.php?sortby=teacher'>Teach</a></b></td>";
    echo "<td><b><a href='manage.php?sortby=name'>Name</a></b></td>";
    echo "<td><b><a href='manage.php?sortby=descr'>Description<a/></b></td>";
    echo "<td><b>Cost</b></td>";
    echo "<td><b><a href='manage.php?sortby=year'>Year</a></b></td>";
    echo "<td><b><a href='manage.php?sortby=day'>Day</a></b></td>";
    echo "<td><b><a href='manage.php?sortby=classsize'>Size</a></b></td>";
    echo "</tr>";

    $deeptable = array();

    foreach ($deepsubjects as $subject) {
        $background = $subject->colour;

        echo "<tr>";
        echo "<td style='background-color: " . $background . ";'><a href='manage.php?classid=" . $subject->id . "'>" . $subject->code . $subject->term . "</a></td>";
        echo "<td style='background-color: " . $background . ";'>" . $subject->teacher . "</td>";
        echo "<td style='background-color: " . $background . ";'>" . $subject->name . "</td>";
        echo "<td style='background-color: " . $background . ";'>" . $subject->descr . "</td>";
        echo "<td style='background-color: " . $background . ";'>" . $subject->cost . "</td>";
        echo "<td style='background-color: " . $background . ";'>" . $subject->year . "</td>";
        echo "<td style='background-color: " . $background . ";'>" . $subject->dayname . "</td>";
        echo "<td style='background-color: " . $background . ";'>" . $subject->classsize . "</td>";
        echo "<td style='background-color: " . $background . ";'><a href='manage.php?classid=" . $subject->id . "&copy=true'>Copy to...</a></td>";
        echo "</tr>";
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
    echo "</form>";

    // Print MOL footer
    echo $OUTPUT->footer();
} else { //user has pressed save
    $datenow = time();

    // this line below is just to get the database going for the escape string below
    $subrec = $DB->get_record_sql("SELECT mdl_deep_class.code from mdl_deep_class WHERE mdl_deep_class.id = '';");

    //$class = array();

    $class['code'] = $_POST['code'];
    $class['name'] = $_POST['name'];
    $class['descr'] = $_POST['descr'];
    $class['cost'] = $_POST['cost'];
    $class['day'] = $_POST['day'];
    $class['term'] = $_POST['term'];
    $class['year'] = $_POST['year'];
    $class['category'] = $_POST['category'];
    $class['teacher'] = $_POST['teacher'];
    $class['classsize'] = $_POST['classsize'];

    if (isset($_POST['classid'])) {
        // if this is set, update a record
        $class['id'] = $_POST['classid'];
        $DB->update_record('deep_class', $class);
    } else {
        $DB->insert_record('deep_class', $class);
    }

    $PAGE->navigation->clear_cache();

    // tell the user that their options were saved and go to some other page
    redirect($CFG->wwwroot . '/DEEP/manage.php');
}
?>