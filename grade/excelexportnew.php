<?php

require_once('../config.php');
require_once('../course/lib.php');
require_once('../lib/gradelib.php');
require_once("$CFG->libdir/blocklib.php");
require_once("$CFG->dirroot/mod/assignment/lib.php");

$courseid = required_param('course', PARAM_INT);
$viewtype = optional_param('viewtype', 'current', PARAM_TEXT);

$currentyear = date("Y");
$currentshortyear = date("y");

if ($viewtype == "current") {
    $currentuyear = strtotime("1 January " . $currentyear);
} else {
    $currentuyear = 0;
}

$course = $DB->get_record('course', array('id' => $courseid));
$classname = explode(" ", $course->fullname);
$class = $classname[0];

$scale = 2;

if (strlen($class) < 3) { // check if it is a core class or a option class
// if it is an option class, find users based on userid
    $studentlist = $DB->get_records_sql("SELECT mdl_user.id FROM mdl_role_assignments, mdl_user, mdl_course, mdl_context WHERE mdl_role_assignments.userid = mdl_user.id AND mdl_role_assignments.contextid = mdl_context.id AND mdl_context.instanceid = mdl_course.id AND mdl_course.id = ? AND roleid = ? AND mdl_user.auth <> ? ORDER BY mdl_user.lastname, mdl_user.firstname", array($courseid, 5, 'nologin'));
    $stuinfo = $DB->get_records_sql("SELECT q.id, q.userid, q.finalgrade, q.roundedgrade, q.timemodified, q.feedback, q.shortname, q.department, q.iteminstance, q.scaleid, q.itemid, q.itemname, q.outcomeid, q.firstname, q.lastname FROM (SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) AS roundedgrade, mdl_grade_grades.timemodified, mdl_grade_grades.feedback, mdl_course.shortname, mdl_user.department, mdl_grade_items.iteminstance, mdl_grade_items.scaleid, mdl_grade_grades.itemid, mdl_grade_items.itemname, mdl_grade_items.outcomeid, mdl_user.firstname, mdl_user.lastname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id INNER JOIN mdl_user on mdl_grade_grades.userid = mdl_user.id WHERE (mdl_user.id IN ('" . join("','", array_keys($studentlist)) . "') AND mdl_grade_grades.finalgrade > 0 AND mdl_grade_items.scaleid = ? AND mdl_grade_grades.timemodified > ? )) AS q WHERE q.feedback IS NULL AND q.outcomeid IS NOT NULL", array($scale, $currentuyear));
    $class = $course->fullname;
} else {
    $stuinfo = $DB->get_records_sql("SELECT q.id, q.userid, q.finalgrade, q.roundedgrade, q.timemodified, q.feedback, q.shortname, q.department, q.iteminstance, q.scaleid, q.itemid, q.itemname, q.outcomeid, q.firstname, q.lastname FROM (SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) AS roundedgrade, mdl_grade_grades.timemodified, mdl_grade_grades.feedback, mdl_course.shortname, mdl_user.department, mdl_grade_items.iteminstance, mdl_grade_items.scaleid, mdl_grade_grades.itemid, mdl_grade_items.itemname, mdl_grade_items.outcomeid, mdl_user.firstname, mdl_user.lastname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id INNER JOIN mdl_user on mdl_grade_grades.userid = mdl_user.id WHERE (mdl_user.department LIKE ? AND mdl_grade_grades.finalgrade > 0 AND mdl_grade_items.scaleid = ? AND mdl_grade_grades.timemodified > ? AND mdl_user.auth <> ?)) AS q WHERE q.feedback IS NULL AND q.outcomeid IS NOT NULL ORDER BY CASE WHEN q.shortname LIKE '$class%$currentshortyear' THEN '1' ELSE q.shortname END ASC;", array($class, $scale, $currentuyear, 'nologin'));
}

$coursecontext = context_course::instance($courseid);
$PAGE->set_context($coursecontext);

foreach ($stuinfo as $row) {
    if (stripos($row->shortname, $class) !== FALSE) {
        $asids[$row->itemid] = " " . $row->shortname . ": " . $row->itemname;
    } else {
        $asids[$row->itemid] = $row->shortname . ": " . $row->itemname;
    }
    $uids[$row->userid] = $row->firstname . " " . $row->lastname;
    $resultstable[$row->userid][$row->itemid] = $row->roundedgrade;
}

$find[] = '1';
$find[] = '2';
$find[] = '3';
$find[] = '4';

$replace[] = "<td class='working'>W</td>";
$replace[] = "<td class='achieved'>A</td>";
$replace[] = "<td class='merit'>M</td>";
$replace[] = "<td class='excellence'>E</td>";

asort($asids);

$tabwidth = (count($asids) + 1) / 100;

echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
echo "<meta charset='UTF-8'>\n";
echo "<title>WAME Grades Report for " . $class . "</title>\n";
echo "<style type='text/css'>\n";
echo "<!--\n";
echo "\tbody { font-family: 'Century Gothic', Helvetica, 'Helvetica Neue', Arial, sans-serif;}\n";
echo "\t#name { float: left; width: 5%; }\n";
echo "\t#name td { white-space: nowrap; }\n";
echo "\t#marks { width: 85%; overflow: auto; }\n";
echo "\ttable.bdr { border-spacing: 0; width: 100%; }\n";
echo "\ttable.bdr td { border: thin solid black; vertical-align: top; padding: 3px; font-size: 0.8em; width: " . $tabwidth . "%; }\n";
echo "\ttable.bdr td.working { background-color: #fe2e2e; }\n";
echo "\ttable.bdr td.achieved { background-color: #ffff00; }\n";
echo "\ttable.bdr td.merit { background-color: #00ff00; }\n";
echo "\ttable.bdr td.excellence { background-color: #00bfff; }\n";
echo "\ttable.bdr th { background-color: black; color: white; font-weight: normal; height: 200px; width: 80px;  padding: 0; margin: 0; }\n";
echo "\ttable.bdr th div { font-size: 0.6em; text-align: left; width: 100px; margin: 0; padding: 0; -webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); overflow: hidden; }\n";
echo "-->\n";
echo "</style>\n";

echo "</head>\n";
echo "<body>\n";

echo "<h3>WAME Grades Report for " . $class . "</h3>\n";
echo "<div style='width: 100%;'>";
echo "<table class='bdr' id='name'>\n";
echo "<thead>";
echo "<tr><th>Name</th><th>#</th></tr>";
echo "</thead>\n";
echo "<tbody>";

foreach ($uids as $uid => $name) {
    echo "<tr><td class='name'>" . $name . "</td><td>" . count($resultstable[$uid]) . "</td></tr>";
}

echo "</tbody>";
echo "</table>\n";

echo "<div id='marks'>";
echo "<table class='bdr'>";
echo "<thead><tr>";
foreach ($asids as $asid => $asname) {
    echo "<th><div>" . $asname . "</div></th>";
}
echo "</tr></thead>";
echo "<tbody>";

foreach ($uids as $uid => $name) {
    echo "<tr>\n";
    foreach ($asids as $asid => $asname) {
        if (isset($resultstable[$uid][$asid])) {
            if (is_null($resultstable[$uid][$asid])) {
                echo "<td>&nbsp;</td>";
            } else {
                echo str_replace($find, $replace, $resultstable[$uid][$asid]);
            }
        } else {
            echo "<td>&nbsp;</td>";
        }
    }
    echo "</tr>\n";
}

echo "</tbody>";
echo "</table>\n";
echo "</div>";
echo "</div>";
echo "</body>\n";
echo "</html>";
?>