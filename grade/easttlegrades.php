<?php

require_once("../config.php");
require_once("../lib/gradelib.php");

require_once("$CFG->dirroot/mod/assignment/lib.php");
require_once("$CFG->dirroot/mod/assign/lib.php");
require_once("$CFG->dirroot/mod/assign/locallib.php");
require_once("$CFG->dirroot/mod/assign/feedback/file/lib.php");
require_once("$CFG->dirroot/mod/assign/feedback/file/locallib.php");

if (empty($currentuser)) {            // See your own profile by default
    require_login();
}

$currentuser = required_param('user', PARAM_INT);     // user id
$courseid = optional_param('course', SITEID, PARAM_INT); // added to allow teachers etc viewing

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = context_course::instance($course->id);
$usercontext = context_user::instance($currentuser, MUST_EXIST);

$PAGE->set_context($coursecontext);
$PAGE->set_url($CFG->wwwroot . "/grade/easttlegrades.php");

$selectscale = '5';

if (has_capability('moodle/grade:view', $usercontext) || has_capability('moodle/user:viewhiddendetails', $coursecontext) || $currentuser == $USER->id || has_coursecontact_role($userid)) {
    $stuinfo = $DB->get_records_sql("SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.timemodified, mdl_grade_grades.feedback, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) AS roundedgrade, mdl_grade_grades.usermodified, mdl_grade_grades.rawscaleid, mdl_grade_items.itemname, mdl_grade_items.itemmodule, mdl_grade_items.iteminstance, mdl_course.fullname, mdl_course.id AS courseid FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id where mdl_grade_grades.userid = ? AND mdl_grade_grades.rawscaleid = ? AND mdl_grade_grades.finalgrade IS NOT NULL ORDER BY mdl_grade_grades.timemodified DESC, mdl_course.fullname", array($currentuser, $selectscale));
} else {
    print_error('cannotviewprofile');
}

$scalesrec = $DB->get_record_sql("SELECT mdl_scale.id, mdl_scale.scale FROM mdl_scale WHERE mdl_scale.id = ?", array($selectscale));

$scaleitem = explode(",", $scalesrec->scale);

$studentdetails = $DB->get_record('user', array('id' => $currentuser));
$studentname = fullname($studentdetails);
echo "<!DOCTYPE html>\n";
echo "<html>\n";
echo "<head>\n";
header('Content-Type: text/html; charset=utf-8');
echo "<title>Live eReport for " . $studentname . "</title>\n";
echo "<style type = 'text/css'>\n";
echo "<!--\n";
echo "body {\n";
echo "\tfont-family: 'Century Gothic', Helvetica, 'Helvetica Neue', Arial, sans-serif;\n";
echo "}\n";
echo "div#links a {\n";
echo "\tcolor: #00626B;\n";
echo "\tfont-weight: bold;\n";
echo "\ttext-decoration: none;\n";
echo "}\n";

echo "div#links a span {\n";
echo "text-decoration: none;\n";
echo "display: none;\n";
echo "}\n";

echo "div#links a:hover span {\n";
echo "\tdisplay: block;\n";
echo "\tpadding: 5px;\n";
echo "\tmargin: 10px;\n";
echo "\tz-index: 100;\n";
echo "\ttext-decoration: none;\n";
echo "\tborder: thin solid black;\n";
echo "\tcolor: black;\n";
echo "\tfont-size: 1em;\n";
echo "\tfont-weight: normal;\n";
echo "}\n";

echo "div#datetext {\n";
echo "\tcolor: #00626B;\n";
echo "\tfont-style: italic;\n";
echo "}\n";

echo "div#helptext a {\n";
echo "\tcolor: #00626B;\n";
echo "\ttext-decoration: none;\n";
echo "}\n";

echo "div#helptext a span {\n";
echo "\ttext-decoration: none;\n";
echo "\tdisplay: none;\n";
echo "}\n";

echo "div#helptext a:hover span {\n";
echo "\tdisplay: block;\n";
echo "\tpadding: 5px;\n";
echo "\tmargin: 10px;\n";
echo "\tz-index: 100;\n";
echo "\ttext-decoration: none;\n";
echo "\tborder: thin solid black;\n";
echo "\tbgcolor: #EEEEEE;\n";
echo "\tcolor: black;\n";
echo "\tfont-size: 1em;\n";
echo "\tfont-weight: normal;\n";
echo "}\n";

echo "a:hover span tr#headerrow td {\n";
echo "\tbackground-color: #00626B;\n";
echo "\tcolor: #FFFFFF;\n";
echo "\ttext-align: center;\n";
echo "\tfont-weight: bold;\n";
echo "}\n";

echo "a:hover span tr td {\n";
echo "\tfont-size: 0.7em;\n";
echo "}\n";

echo "a:hover span tr td#side {\n";
echo "\tbackground-color: #CCCCCC;\n";
echo "\ttext-align: center;\n";
echo "}\n";

echo "table#bdr {\n";
echo "\tborder: thin solid black;\n";
echo "\tborder-spacing: 0px;\n";
echo "\twidth: 100%;\n";
echo "}\n";

echo "table#bdr td {\n";
echo "\tborder: thin solid black;\n";
echo "\tvertical-align: top;\n";
echo "\tpadding: 5px;\n";
echo "}\n";

echo "td.highlight {\n";
echo "\ttext-align: center;\n";
echo "\tbackground-color: #00626B;\n";
echo "\tfont-weight: bold;\n";
echo "\tcolor: white;\n";
echo "}\n";

echo "td.nohighlight {\n";
echo "\ttext-align: center;\n";
echo "\tbackground-color: #DDDDDD;\n";
echo "\tcolor: #999999;\n";
echo "}\n";
echo "-->\n";
echo "</style>\n\n";
echo "<script type = 'text/JavaScript'>\n";
echo "\tfunction GetWidth()\n";
echo "\t{\n";
echo "\t\tvar x = 0;\n";
echo "\t\tif (self.innerHeight)\n";
echo "\t\t{\n";
echo "\t\t\tx = self.innerWidth;\n";
echo "\t\t}\n";
echo "\t\telse if (document.documentElement && document.documentElement.clientHeight)\n";
echo "\t\t{\n";
echo "\t\t\tx = document.documentElement.clientWidth;\n";
echo "\t\t}\n";
echo "\t\telse if (document.body)\n";
echo "\t\t{\n";
echo "\t\t\tx = document.body.clientWidth;\n";
echo "\t\t}\n";
echo "\t\treturn x;\n";
echo "\t}\n";
echo "</script>\n";
echo "</head>\n";
echo "<body>\n";
/* BEGIN MAIN LAYOUT TABLE */
print "<table border='0' cellpadding='5' cellspacing='0' width='100%' align='center'>\n";
print "<tr>";
print "<td><h3>Live eAsTTle Results for</h3><h2>" . $studentname . ", " . $studentdetails->department . "</h2></td>";
print "<td align='right'><img src='$CFG->wwwroot/file.php/1/logo/MIS_Junior_College_Logo.png' width='337' height='132' /></td>";
print "</tr>\n";
print "<tr>";
print "<td><div id='helptext'><a href='#'>Click here to learn how to interpret your child's report.<span>e-asTTle is a web-based assessment tool that MHJC teachers use to electronically set reading, writing and mathematics tests that are aligned to the New Zealand Curriculum.<br /><br />At MHJC we use e-asTTle to identify exactly what a student can and can't do, so that we can focus teaching and identify the 'where to next?' for learning. Information from e-asTTle is used to allow students to set learning goals and targets, and to help parents understand what they can do to support their child's learning. Please don't hesitate to contact your child's Learning Advisor if you have any questions or concerns about any of the e-asTTle reports.<br /><br />The e-asTTle table (below), can help students ascertain their achievement level(s) against our MHJC standards.<br /><br /><center><table width='80%' border='1' cellspacing='0' cellpadding='5'><tr id='headerrow'><td width='20%'>Year Group</td><td width='20%'>Working Towards</td><td width='20%'>Achieved</td><td width='20%'>Merit</td><td width='20%'>Excellence</td></tr><tr><td id='side'>Year 7</td><td>3A and Below</td><td>4B</td><td>4P</td><td>4A and Above</td></tr><tr><td id='side'>Year 8</td><td>4B and Below</td><td>4P and 4A</td><td>5B</td><td>5P and Above</td></tr><tr><td id='side'>Year 9</td><td>4A and Below</td><td>5B</td><td>5P</td><td>5A and Above</td></tr><tr><td id='side'>Year 10</td><td>5B and Below</td><td>5P and 5A</td><td>6B</td><td>6P and Above</td></tr></table></center></span></a></div></td>";
print "<td align='right'><i>" . date("jS F, Y") . "</i></td>";
print "</tr>\n";

/* MAIN LAYOUT TABLE REPORT CELL */
//blank temp class name
$tempclassname = '';

foreach ($stuinfo as $row) {
    $roundedgrade = round($row->finalgrade) - 1;
    $studentmark_task = $scaleitem[$roundedgrade];

    $dategraded = date("d-m-Y", $row->timemodified);

    if ($row->fullname != $tempclassname) {
        $teacherdetails = $DB->get_record('user', array('id' => $row->usermodified));
        $teachername = explode(" ", fullname($teacherdetails));
        $teachfn = substr($teachername[0], 0, 1);
        $teachsn = $teachername[1];
        print "<tr bgcolor=#999999><td colspan='2'><b>" . $row->fullname . "</b></td></tr>\n";
    }
    print "<tr colspan='2'><td><div id='datetext'>($dategraded)</div></td></tr>\n";
    print "<tr colspan='2'><td><p style='font-weight: bold;'>Current Level of Achievement:</p>";
    print "<table id='bdr'>\n<tr>\n";
    $cellwidth = (1 / count($scaleitem)) * 100;
    foreach ($scaleitem as $tabletext) {
        if ($studentmark_task == $tabletext) {
            print "<td width='$cellwidth%' class='highlight'>$tabletext</td>";
        } else {
            print "<td width='$cellwidth%' class='nohighlight'>$tabletext</td>";
        }
    }

    print "</tr>\n</table>\n</td></tr>\n";
    print "<tr colspan='2'><td>";

    $coursemodule = get_coursemodule_from_instance('assign', $row->iteminstance);
    $context = context_module::instance($coursemodule->id);

    $assigngrade = $DB->get_record('assign_grades', array('assignment' => $row->iteminstance, 'userid' => $currentuser));
    $fileinfo = $DB->get_record('files', array('contextid' => $context->id, 'itemid' => $assigngrade->id, 'license' => 'allrightsreserved'));
    $filepath = "/" . $context->id . "/assignfeedback_file/feedback_files/" . $assigngrade->id . "/" . $fileinfo->filename;

    print "<img src='asttleimage.php?image=" . $filepath . "' width='500px'/>";
    print "</td></tr>\n";

    print "<tr><td><hr /></td></tr>\n";

    $tempclassname = $row->fullname;
}
print "</table>\n";
print "</body>\n";
print "</html>\n";
?>