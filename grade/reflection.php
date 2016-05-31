<?php
require_once("../config.php");
require_once("../lib/gradelib.php");

require_once("$CFG->dirroot/mod/assignment/lib.php");

require_login();

$currentuser = required_param('user', PARAM_INT);     // user id
$viewtype = required_param('view', PARAM_TEXT);    // view type
$courseid = optional_param('course', SITEID, PARAM_INT); // added to allow teachers etc viewing

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = context_course::instance($course->id);
$usercontext = context_user::instance($currentuser, MUST_EXIST);

$PAGE->set_context($coursecontext);

if (has_capability('moodle/grade:view', $usercontext) || $currentuser == $USER->id || has_capability('moodle/grade:view', $coursecontext)) {
    $sql = "SELECT mdl_assignment_submissions.assignment, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.id, mdl_assignment.name, mdl_assignment.grade AS scalegrade, mdl_assignment_submissions.grade, mdl_assignment_submissions.submissioncomment, mdl_assignment_submissions.teacher, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_assignment_submissions.data1
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id
GROUP BY mdl_user.username, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_course.fullname
HAVING mdl_user.id = '$currentuser' AND scalegrade = '-6'
ORDER BY mdl_course.fullname , mdl_assignment.name;";
} else {
    print_error('cannotviewprofile');
}

$stuinfo = $DB->get_records_sql($sql);

$sql = "SELECT mdl_scale.id, mdl_scale.scale FROM mdl_scale WHERE mdl_scale.id=6";
$scalesrec = $DB->get_record_sql($sql);
$scaleitem = explode(",", $scalesrec->scale);

$studentdetails = $DB->get_record('user', array('id' => $currentuser));
$studentname = fullname($studentdetails);
?>
<html>
    <head>
        <title>Live eReport for <?php print $studentname; ?></title>
        <style type="text/css">
            <!--
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
                bgcolor: #EEEEEE;
                color: black;
                font-size: 1em;
                font-weight: normal; }
            -->
        </style>
    </head>
    <body>
        <?php
        /* BEGIN MAIN LAYOUT TABLE */
        print "<table border='0' cellpadding='5' cellspacing='0' width='1000px' align='center'>";
        print "<tr>";
        print "<td><h3>Student-Led Conference Self-Reflection for</h3><h2>" . $studentname . ", " . $studentdetails->department . "</h2></td>";
        print "<td>&nbsp;</td>";
        print "<td align='right'><img src='http://online.mhjc.school.nz/file.php/1/logo/MIS_Junior_College_Logo.png' width='337' height='132' /></td>";
        print "</tr>";

        print "<tr>";
        print "<td><div id='helptext'><a href='#'>Click here to learn how to interpret your child's Self-Reflection.<span>As a part of assessing and reporting your child's progress and achievement in relation to the National Standards, students are required to complete a <b>Student-Led Conference Reflection</b> to... <ul><li>record their current learning goals;</li><li>identify what help they need from teachers;</li><li>communicate what help they need from the school;</li><li>describe how their parents/whanau can support their learning;</li><li>make decisions about the <i>'where to next...'</i> for their learning.</li></ul>Mission Heights Junior College staff hope that the <b>Student-Led Conferences</b> and the <b>Self-Reflection</b> processes consolidate the strong partnerships between the teacher, student, school and family, so that all partners actively promote student learning.<br /><br />Shared expectations of achievement against the Standards in reading, writing and mathematics are essential and will enable students to successfully learn within the New Zealand Curriculum.</span></a></div></td>";
        print "<td>&nbsp;</td>";
        print "<td align='right'><i>" . date("jS F, Y") . "</i></td>";
        print "</tr>";

        print "<tr>";
        print "<td><a href='" . $CFG->wwwroot . "/grade/pdfereport.php?user=" . $currentuser . "'>Download PDF version</a></td>";
        print "<td>&nbsp;</td>";
        print "<td>&nbsp;</td>";
        print "</tr>";

        /* MAIN LAYOUT TABLE REPORT CELL */
        print "<tr><td colspan='3'>";

        /* BEGIN REPORT LAYOUT TABLE */
        print "<table border='0' cellpadding='5' cellspacing='0' width='100%'>\n";
//blank temp class name
        $tempclassname = '';

        foreach ($stuinfo as $row) {
            $grading_info = grade_get_grades($row->course, 'mod', 'assignment', $row->assignment, $currentuser);

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

            $assignmentdesc = $assignmentinstance->assignment->intro;

            if (($yeargraded != $currentyear) AND ( $viewtype == "current")) {
                $dummy = 1;
            } else {
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
                print "<td colspan='2'><div id='links'><a href='#'>" . $row->name . "</a></div><div id='datetext'>($dategraded)</div></td>";
                print "</tr>";
                print "<tr>";
                print "<td>&nbsp;</td>";
                print "<td>&nbsp;</td>";
                print "</tr>";
                print "<tr>";
                print "<td width='15%'>&nbsp;</td>";
                print "<td><b>Student Reflection:</b><br />" . $row->data1 . "<br /></td>";
                print "</tr>";
                print "<tr>";
                print "<td>&nbsp;</td>";
                print "<td>&nbsp;</td>";
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