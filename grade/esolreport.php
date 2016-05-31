<?php
require_once("../config.php");
require_once("../lib/gradelib.php");

require_once("$CFG->dirroot/mod/assignment/lib.php");

$currentuser = required_param('user', PARAM_INT);  // user id
$viewtype = required_param('view', PARAM_TEXT); // view type
$reporttype = required_param('type', PARAM_TEXT); // report type - eReport, National standards etc.
$courseid = optional_param('course', SITEID, PARAM_INT); // added to allow teachers etc viewing

$idesol = 9; // national standards scale id

if (empty($currentuser)) {            // See your own profile by default
    require_login();
    //$currentuser = $USER->id;
}

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$usercontext = get_context_instance(CONTEXT_USER, $currentuser, MUST_EXIST);

if (has_capability('moodle/grade:view', $usercontext) || $currentuser == $USER->id || has_capability('moodle/grade:view', $coursecontext)) {
    $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_assignment_submissions.assignment, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.id, mdl_assignment.name, mdl_assignment.grade AS scalegrade, mdl_assignment_submissions.grade, mdl_assignment_submissions.submissioncomment, mdl_assignment_submissions.teacher, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid, mdl_grade_outcomes.description
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id
GROUP BY mdl_assignment_submissions.assignment, mdl_user.username, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_course.fullname HAVING mdl_user.id = '$currentuser' AND scalegrade = '-" . $idesol . "'ORDER BY mdl_course.fullname, mdl_assignment.name;";
    $scalesql = "SELECT mdl_scale.id, mdl_scale.scale FROM mdl_scale WHERE mdl_scale.id=" . $idesol;
    // need some sort of ESOL summary introduction thing below
    $hovertext = "Your child's progress and achievement in relation to standards based assessment is reported using our Mission Heights Junior College 'WAME' <b><i>(Working Towards, Achieved, Merit, </i></b>and<b><i> Excellence)</i></b>.<br /><br /><b><u>Please note:</u></b> the <b><i>Achieved</i></b> level is the standard we expect students to reach by the end of the year. Please feel free to contact your child's Learning Advisor if you have any questions or concerns about this eReport.";
} else {
    print_error('cannotviewprofile');
}

$PAGE->set_context($coursecontext);

$stuinfo = $DB->get_records_sql($sql);

$scalesrec = $DB->get_record_sql($scalesql);

$scaleitem = explode(", ", $scalesrec->scale);

$studentdetails = $DB->get_record('user', array('id' => $currentuser));
$studentname = fullname($studentdetails);

// these arrays are to clean the html comments and get rid of the funny characters

$find[] = 'â€œ';  // left side double smart quote
$find[] = 'â€';  // right side double smart quote
$find[] = 'â€˜';  // left side single smart quote
$find[] = 'â€™';  // right side single smart quote
$find[] = 'â€¦';  // elipsis
$find[] = 'â€”';  // em dash
$find[] = 'â€“';  // en dash
$find[] = '“';
$find[] = '”';
$find[] = 'é';
$find[] = 'É';
$find[] = '•';
$find[] = '–';
$find[] = 'Â·';
$find[] = 'Â';

$replace[] = '"';
$replace[] = '"';
$replace[] = "'";
$replace[] = "'";
$replace[] = "...";
$replace[] = "-";
$replace[] = "-";
$replace[] = '"';
$replace[] = '"';
$replace[] = 'e';
$replace[] = 'E';
$replace[] = '-';
$replace[] = '-';
$replace[] = ''; //'&#8226;';
$replace[] = '';
?>
<html>
    <head>
        <?php
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <title>Live eReport for <?php print $studentname; ?></title>
        <style type="text/css">
            <!--
            body {
                font-family: "Century Gothic", Helvetica, "Helvetica Neue", Arial, sans-serif; }

            div#links a {
                color: #00626B;
                font-weight: bold;
                text-decoration: none; }

            div#links a span {
                text-decoration: none;
                display: none; }

            div#links a:hover span {
                display: block;
                padding: 5px;
                margin: 10px;
                z-index: 100;
                text-decoration: none;
                border: thin solid black;
                color: black;
                font-size: 1em;
                font-weight: normal; }

            div#datetext {
                color: #00626B;
                font-style: italic; }

            div#helptext a {
                color: #00626B;
                text-decoration: none; }

            div#helptext a span {
                text-decoration: none;
                display: none; }

            div#helptext a:hover span {
                display: block;
                padding: 5px;
                margin: 10px;
                z-index: 100;
                text-decoration: none;
                border: thin solid black;
                background-color: #EEEEEE;
                color: black;
                font-size: 1em;
                font-weight: normal; }

            div#arrow {
                background: -webkit-gradient(linear, left top, right top, from(rgba(0, 0, 0, 0)), to(rgba(0, 98, 107, 1)));
                background: -webkit-linear-gradient(left, rgba(0, 0, 0, 0), rgba(0, 98, 107, 1));
                background: -moz-linear-gradient(left, rgba(0, 0, 0, 0), rgba(0, 98, 107, 1));
                position: relative;
                width: 96%;
                height: 20px;
            }

            div#arrowhead {
                content: ' ';
                position: relative;
                height: 0;
                width: 0;
                border: 20px solid transparent;
                border-left-color: rgba(0, 98, 107, 1);
                left: 100%;
                top: -10px;
            }


            table#bdr {
                border: thin solid black;
                border-spacing: 0px;
                width: 100%; }

            table#bdr td {
                border: thin solid black;
                vertical-align: top;
                padding: 5px; }

            td.highlight {
                text-align: center;
                background-color: #00626B;
                font-weight: bold;
                color: white; }

            td.nohighlight {
                text-align: center;
                background-color: #DDDDDD;
                color: #999999; }
            -->
        </style>
    </head>
    <body>
        <?php
        /* BEGIN MAIN LAYOUT TABLE */
        print "<table border='0' cellpadding='5' cellspacing='0' width='1000px' align='center'>";
        print "<tr>";
        print "<td><h3>Live eReport for</h3><h2>" . $studentname . ", " . $studentdetails->department . "</h2></td>";
        print "<td>&nbsp;</td>";
        print "<td align='right'><img src='http://online.mhjc.school.nz/file.php/1/logo/MIS_Junior_College_Logo.png' width='337' height='132' /></td>";
        print "</tr>";

        print "<tr>";
        print "<td><div id='helptext'><a href='#'>Click here to learn how to interpret your child's report.<span>" . $hovertext . "</span></a></div></td>";
        print "<td>&nbsp;</td>";
        print "<td align='right'><i>" . date("jS F, Y") . "</i></td>";
        print "</tr>";

        /* print "<tr>";
          print "<td><a href='".$CFG->wwwroot."/grade/pdfereport.php?user=".$currentuser."&view=".$viewtype."'>Download PDF version</a></td>";
          print "<td>&nbsp;</td>";
          print "<td>&nbsp;</td>";
          print "</tr>"; */

        /* MAIN LAYOUT TABLE REPORT CELL */
        print "<tr><td colspan='3'>";

        /* BEGIN REPORT LAYOUT TABLE */
        print "<table border='0' cellpadding='5' cellspacing='0' width='100%'>";

//blank temp class name
        $tempclassname = '';

        foreach ($stuinfo as $row) {
            $grading_info = grade_get_grades($row->course, 'mod', 'assignment', $row->assignment, $currentuser);

            // added this if statement to stop undefined index errors occuring when 1000 was not found
            if (array_key_exists('1000', $grading_info->outcomes)) {
                $item = $grading_info->outcomes['1000'];
                $grade = $item->grades[$currentuser];
                $studentmark_task = $grade->str_grade;
            } else {
                $studentmark_task = 'dummy';
            }

            $studentmark_final = $grading_info->items[0]->grades[$currentuser]->str_grade;

            $dategraded = date("d-m-Y", $grading_info->items[0]->grades[$currentuser]->dategraded);
            $yeargraded = date("Y", $grading_info->items[0]->grades[$currentuser]->dategraded);
            $currentyear = date("Y");

            if (!$assignment = $DB->get_record("assignment", array('id' => $row->assignment))) {
                print_error("Course module is incorrect");
            }
            if (!$course = $DB->get_record("course", array('id' => $assignment->course))) {
                print_error("Course is misconfigured");
            }
            if (!$cm = get_coursemodule_from_instance("assignment", $assignment->id, $course->id)) {
                print_error("Course Module ID was incorrect");
            }

            require_once ("$CFG->dirroot/mod/assignment/type/$assignment->assignmenttype/assignment.class.php");

            $assignmentclass = "assignment_$assignment->assignmenttype";
            $assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);

            $assignmentdesc = $assignmentinstance->assignment->intro; //used to be called description, now 'intro'

            if ($studentmark_final == $studentmark_task) {
                if ($row->fullname != $tempclassname) {
                    $teacherdetails = $DB->get_record('user', array('id' => $row->teacher));

                    $teachername = explode(" ", fullname($teacherdetails));
                    $teachfn = substr($teachername[0], 0, 1);
                    $teachsn = $teachername[1];
                    print "<tr bgcolor=#999999>";
                    print "<td colspan=2><b><a name='$row->fullname'>$row->fullname</a>, <i>Teacher: " . $teachfn . " " . $teachsn . "</i></b></td>";
                    print "</tr>";
                }
                print "<tr>";
                print "<td colspan='2'><div id='links'><a href='#" . $row->asid . "'>" . $row->name . "</a></div><div id='datetext'>($dategraded)</div></td>";
                print "</tr>";
                print "<tr>";
                print "<td>&nbsp;</td>";
                print "<td><b>Current Level of Achievement:</b><br />";

                print "<table id='bdr'><tr>";

                $assignmentdesc = preg_replace('/<style(.*)>(.*)<\/style>/', '', $assignmentdesc); // remove style tags and stuff in between them
                $assignmentdesc = str_replace($find, $replace, $assignmentdesc);
                $assignmentdesc = strip_tags($assignmentdesc, '<p><br><table><tr><td><ul><li>');
                $assignmentdesc = preg_replace('/<\s*([a-z]+)([^>]*)>/', '<\1>', $assignmentdesc); // get rid of special colouring etc
                $assignmentdesc = str_replace("<table>", "<table id='bdr'>", $assignmentdesc);

                $cellwidth = (1 / count($scaleitem)) * 100;
                foreach ($scaleitem as $tabletext) {
                    if ($studentmark_task == $tabletext) {
                        $assignmentdesc = str_replace("<td>" . $tabletext, "<td class='highlight' width='$cellwidth%'>" . $tabletext, $assignmentdesc);
                        print "<td class='highlight' width='$cellwidth%'>$tabletext</td>";
                    } else {
                        $assignmentdesc = str_replace("<td>" . $tabletext, "<td class='nohighlight' width='$cellwidth%'>" . $tabletext, $assignmentdesc);
                        print "<td class='nohighlight' width='$cellwidth%'>$tabletext</td>";
                    }
                }

                $cleancomment = preg_replace('/<style(.*)>(.*)<\/style>/', '', $row->submissioncomment); // remove style tags and stuff in between them
                $cleancomment = str_replace($find, $replace, $cleancomment);
                $cleancomment = strip_tags($cleancomment, '<p><br><table><tr><td><ul><li>');
                $cleancomment = preg_replace('/<\s*([a-z]+)([^>]*)>/', '<\1>', $cleancomment);
                $cleancomment = str_replace("<table>", "<table id='bdr'>", $cleancomment);

                print "</tr></table><br /><div id='arrow'><div id='arrowhead'></div></div></td>";
                print "</tr>";
                print "<tr>";
                print "<td>&nbsp;</td>";
                print "<td><b>Teacher Feedback / Feedforward:</b><br />" . $cleancomment . "</td>";
                print "</tr>";
                print "<tr>";
                print "<td colspan='2'><hr /></td>";
                print "</tr>";

                $tempclassname = $row->fullname;
            }
        }
        print "</table>";

        print "</td></tr>";
        print "</table>";
        ?> 
    </body>
</html>