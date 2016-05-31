<?php

require_once("../config.php");
require_once("../lib/gradelib.php");
require_once("$CFG->dirroot/mod/assignment/lib.php");
require_once('../lib/phpmailer/class.phpmailer.php');
require_once('wkhtmltopdf.php');

$currentuser = required_param('user', PARAM_INT);  // user id
$viewtype = required_param('view', PARAM_TEXT); // view type
$referrer = required_param('referrer', PARAM_TEXT);
$courseid = optional_param('course', SITEID, PARAM_INT); // added to allow teachers etc viewing
$reporttype = optional_param('reporttype', NULL, PARAM_TEXT);  // report type
$ereportattach = optional_param('ereportattach', 0, PARAM_INT);
$natstandattach = optional_param('natstandattach', 0, PARAM_INT);

$ebody = required_param('body', PARAM_TEXT);
$ebody = urldecode($ebody);
$erecipients = required_param('recipients', PARAM_TEXT);
$erecipients = explode(";", $erecipients);

if (empty($currentuser)) {            // See your own profile by default
    require_login();
}

$studentdetails = $DB->get_record('user', array('id' => $currentuser));
$studentname = fullname($studentdetails);

$output = "<html>\n";
$output .= "<head></head>\n";
$output .= "<body style='width: 21cm; margin-left: 1cm; margin-right: 1cm; margin-top: 1cm; font-family: \"Century Gothic\", Helvetica, \"Helvetica Neue\", Arial, sans-serif;'>\n";

$output .= str_replace("\n", "<br />", $ebody);

$output .= "<p>&nbsp;</p>";

$output .= "</body>\n";
$output .= "</html>";

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$usercontext = get_context_instance(CONTEXT_USER, $currentuser, MUST_EXIST);

//need to check here that user is allowed to get the pdf file
if (has_capability('moodle/grade:view', $usercontext) || $currentuser === $USER->id || has_capability('moodle/grade:view', $coursecontext) || has_capability('moodle/grade:viewall', $coursecontext) || has_capability('moodle/grade:viewall', $usercontext)) {
    if ($reporttype != "ncea") {
        if ($ereportattach == 1) {
            $content = file_get_contents('http://online.mhjc.school.nz/grade/' . $referrer . '.php?user=' . $currentuser . '&view=' . $viewtype . '&type=ereport&course=' . $courseid . '&pdf=TRUE');
        }
        if ((substr($studentdetails->department, 0, 1) == '7' || substr($studentdetails->department, 0, 1) == '8') AND ( $natstandattach == 1)) {
            $ncontent = file_get_contents('http://online.mhjc.school.nz/grade/natstandards.php?user=' . $currentuser . '&view=all&type=natstandards&course=' . $courseid . '&pdf=TRUE');
        }
    } else {
        $content = file_get_contents('http://online.mhjc.school.nz/grade/recordofachievement.php?user=' . $currentuser . '&view=' . $viewtype . '&type=ereport&course=' . $courseid . '&pdf=TRUE');
    }
} else {
    print_error('cannotviewprofile');
}

if ($ereportattach == 1) {
//get rid of stupid A characters
    $content = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $content);
    $ereportpdf = tempnam("/tmp", "ereport" . $currentuser);

    $pdf = new WkHtmlToPdf;
    $pdf->addPage($content);
    $pdf->saveAs($ereportpdf);
}

if (($natstandattach == 1) AND ( $reporttype != "ncea")) {
    $ncontent = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $ncontent);
    $nreportpdf = tempnam("/tmp", "nreport" . $currentuser);

    $npdf = new WkHtmlToPdf;
    $npdf->addPage($ncontent);
    $npdf->saveAs($nreportpdf);
}

if ($reporttype == "ncea") {
    $content = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $content);
    $ereportpdf = tempnam("/tmp", "nceareport" . $currentuser);

    $pdf = new WkHTMLToPdf;
    $pdf->addpage($content);
    $pdf->saveAs($ereportpdf);
}

$userdetails = $DB->get_record('user', array('id' => $USER->id));

$etext = "Your child's eReport";

$mail = new PHPMailer;

$mail->isHTML(true);
$mail->From = $userdetails->email;
$mail->FromName = $userdetails->firstname . " " . $userdetails->lastname;

$erecipients[] = $userdetails->email;

foreach ($erecipients as $recipient) {
    $mail->AddAddress($recipient);
}

$mail->Subject = "Live eReport for " . $studentname;
$mail->Body = $output;
$mail->AltBody = $etext;
$mail->WordWrap = 50;

if ($reporttype != "ncea") {
    if ($ereportattach == 1) {
        $mail->AddAttachment($ereportpdf, "Live eReport for " . $studentname . ".pdf");
    }
    if ($natstandattach == 1) {
        $mail->AddAttachment($nreportpdf, "Live National Standards report for " . $studentname . ".pdf");
    }
} else {
    $mail->AddAttachment($ereportpdf, "Unregistered Record of Achievement for " . $studentname . ".pdf");
}

if (!$mail->Send()) {
    echo "email was not sent";
    echo "Error: " . $mail->ErrorInfo;
} else {
    if ($ereportattach == 1) {
        unlink($ereportpdf);
    }
    if ($natstandattach == 1) {
        unlink($nreportpdf);
    }
    redirect($CFG->wwwroot . '/user/view.php?id=' . $currentuser . '&course=' . $courseid);
}
?>