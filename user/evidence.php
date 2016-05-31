<?php

require_once("../config.php");
require_once("../lib/gradelib.php");
require_once($CFG->libdir . '/filelib.php');
require_once("$CFG->dirroot/mod/assignment/lib.php");
require_once($CFG->libdir . '/plagiarismlib.php');

$currentuser = required_param('id', PARAM_INT);  // user id
$courseid = optional_param('course', SITEID, PARAM_INT); // for teacher viewing

if (empty($currentuser)) {            // See your own profile by default
    require_login();
}

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$usercontext = context_user::instance($currentuser, MUST_EXIST);
$coursecontext = context_course::instance($course->id);

if (has_capability('moodle/grade:view', $usercontext) || $currentuser == $USER->id || has_capability('moodle/grade:view', $coursecontext)) {
    $repoinfo = $DB->get_records_sql("SELECT mdl_assign_grades.id, mdl_assign_grades.assignment AS asid, mdl_assign_grades.timemodified, mdl_assign.name, mdl_assign.course, mdl_course.fullname, mdl_assignfeedback_comments.repository FROM mdl_assign_grades INNER JOIN mdl_assign ON mdl_assign_grades.assignment = mdl_assign.id INNER JOIN mdl_course ON mdl_assign.course = mdl_course.id INNER JOIN mdl_assignfeedback_comments ON mdl_assign_grades.id = mdl_assignfeedback_comments.grade WHERE mdl_assign_grades.userid = ? AND mdl_assignfeedback_comments.repository = 1 ORDER BY mdl_course.fullname, mdl_assign_grades.timemodified", array($currentuser));
} else {
    print_error('cannotviewprofile');
}

$studentdetails = $DB->get_record('user', array('id' => $currentuser));
$studentname = fullname($studentdetails);

$PAGE->set_context($usercontext);
$PAGE->set_url($CFG->wwwroot . "/user/evidence.php");
$PAGE->set_title("National Standards Evidence");
$PAGE->set_heading("National Standards Evidence");
$PAGE->navbar->add($studentname);

echo $OUTPUT->header();
echo $OUTPUT->heading("National Standards Evidence for " . $studentname);

print "<table>\n";

$prevcourse = NULL;
if (count($repoinfo) > 0) {
    foreach ($repoinfo as $repoitem) {
        $coursemodule = get_coursemodule_from_instance('assign', $repoitem->asid);
        $context = context_module::instance($coursemodule->id);
        $assigngrade = $DB->get_record('assign_submission', array('assignment' => $repoitem->asid, 'userid' => $currentuser, 'latest' => 1));
        $fileinfo = $DB->get_record('files', array('contextid' => $context->id, 'itemid' => $assigngrade->id, 'license' => 'allrightsreserved'));
        if (isset($fileinfo->id)) {
            $filepath = "/" . $context->id . "/assignsubmission_file/submission_files/" . $assigngrade->id . "/" . $fileinfo->filename;
            if ($prevcourse != $repoitem->fullname) {
                echo "<tr><td colspan='3'><b>" . $repoitem->fullname . "</b></td></tr>\n";
            }
            echo "<tr><td>" . date("d-m-Y", $repoitem->timemodified) . "</td>";
            echo "<td>" . $repoitem->name . "</td><td>";
            echo "<a href='/grade/asttleimage.php?image=" . $filepath . "'>" . $fileinfo->filename . "</a>";
            echo "</td></tr>\n";
            $prevcourse = $repoitem->fullname;
        }
    }
} else {
    print "<tr><td><p>There is currently no evidence in the Evidence Bank.</p></td></tr>";
}
print "</table>\n";
echo $OUTPUT->footer();
?>