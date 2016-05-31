<?php

require_once("../config.php");
require_once('wkhtmltopdf.php');

$currentuser = required_param('user', PARAM_INT);
$viewtype = optional_param('view', 'current', PARAM_TEXT);
$reporttype = optional_param('type', 'ereport', PARAM_TEXT); // report type - eReport, National standards etc.
$courseid = optional_param('course', SITEID, PARAM_INT);
$referrer = required_param('referrer', PARAM_TEXT);

if (empty($currentuser)) {            // See your own profile by default
    require_login();
}

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = context_course::instance($course->id);
$usercontext = context_user::instance($currentuser, MUST_EXIST);

//need to check here that user is allowed to get the pdf file
if (has_capability('moodle/grade:view', $usercontext) || $currentuser == $USER->id || has_capability('moodle/grade:view', $coursecontext) || has_capability('moodle/grade:viewall', $coursecontext) || has_capability('moodle/grade:viewall', $usercontext)) {
    $content = file_get_contents('http://online.mhjc.school.nz/grade/' . $referrer . '.php?user=' . $currentuser . '&view=' . $viewtype . '&type=' . $reporttype . '&course=' . $courseid . '&pdf=TRUE');
} else {
    print_error('cannotviewprofile');
}

//get rid of stupid A characters
$content = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $content);

$pdf = new WkHtmlToPdf;
$pdf->addPage($content);
$pdf->send('eReport.pdf');
?>
