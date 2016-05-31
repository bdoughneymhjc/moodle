<?php

require_once('../config.php');
require_once("../course/lib.php");
require_once("$CFG->libdir/blocklib.php");
require_once($CFG->libdir . '/formslib.php');

$reporttype = optional_param('reporttype', NULL, PARAM_TEXT);  // report type

$stuid = $_GET['stuid'];
$courseid = $_GET['courseid'];

if ($_GET['reporttype'] != "") {
    $reporttype = $_GET['reporttype'];
}

class emailreport_form extends moodleform {

    function definition() {
        global $CFG, $DB, $reporttype;

        $mform = $this->_form;
        $formstuid = $this->_customdata['stuid'];
        $formcourseid = $this->_customdata['courseid'];

        $mform->addElement('html', '<h3>Email eReport</h3>');

        $mform->addElement('text', 'recipients', 'To', array('size' => '50'));
        $mform->setType('recipients', PARAM_TEXT);

        $mform->addElement('textarea', 'body', 'Email text', 'wrap="virtual" rows="30" cols="50"');
        $mform->setType('body', PARAM_TEXT);

        $mform->addElement('checkbox', 'ereportattach', 'Attach eReport');
        $mform->addElement('checkbox', 'natstandattach', 'Attach National Standards');

        $mform->addElement('hidden', 'stuid', $formstuid);
        $mform->addElement('hidden', 'courseid', $formcourseid);
        $mform->addElement('hidden', 'reporttype', $reporttype);

        $this->add_action_buttons(true, 'Send Email');
    }

}

$coursecontext = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($coursecontext);
$PAGE->set_url($CFG->wwwroot . "/user/email_report.php");
$PAGE->set_title("Email Report");
$PAGE->set_heading("Email Report");

function errormsg($message, $link = '') {
    global $CFG, $SESSION;

    print_header(get_string('error'));
    echo '<br />';

    $message = clean_text($message);

    print_simple_box('<span style="font-family:monospace;color:#000000;">' . $message . '</span>', 'center', '', '#FFBBBB', 5, 'errorbox');

    if (!$link) {
        if (!empty($SESSION->fromurl)) {
            $link = $SESSION->fromurl;
            unset($SESSION->fromurl);
        } else {
            $link = $CFG->wwwroot . '/';
        }
    }
    print_continue($link);
    print_footer();
    die;
}

require_login();
$currentuser = $USER->id;

$me = $_SERVER['PHP_SELF'];

// Print MOL header
echo $OUTPUT->header();

$editoroptions = array('context' => $coursecontext, 'maxfiles' => '0', 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);

$userdetails = $DB->get_record('user', array('id' => $currentuser));

$studentdetails = $DB->get_record('user', array('id' => $stuid));
$parentdetails = $DB->get_record('user', array('idnumber' => "p" . $studentdetails->idnumber));

$editdata->recipients = $parentdetails->email;

$mform = new emailreport_form(null, array('stuid' => $stuid, 'courseid' => $courseid, 'editoroptions' => $editoroptions));

if ($reporttype != "ncea") {
    $editdata->body = "Dear Parents and Caregivers,\n\n";
    if (in_array(date('n'), range(5, 7))) {
        $editdata->body .= "Re: " . $studentdetails->firstname . "'s Mid Year National Standards " . date("Y") . "\n\n";
        $editdata->body .= "Please find attached " . $studentdetails->firstname . "'s mid-year National Standards eReport. ";
        $editdata->body .= "Nationally, all students are assessed against the National Standards at the end of each year up to Year 8. ";
        $editdata->body .= "Mid year results should be considered an indicator of where the student is currently achieving, in relation ";
        $editdata->body .= "to the standard that should be met by the end of the year.\n\n";
        $editdata->body .= "To be able to view more information about the standards, please log on to Mission ";
        $editdata->body .= "Heights Online and view the National Standards eReport.\n\n";
        $editdata->body .= "I will be in contact early next term to arrange a time for a ";
        $editdata->body .= "student led conference.\n\n";
        $editdata->body .= "Wishing you and your family a safe and happy holiday.\n\n";
    } elseif (in_array(date('n'), range(8, 9))) {
        $editdata->body .= "Re: " . $studentdetails->firstname . "'s Student Led Conference\n\n";
        $editdata->body .= "At Mission Heights Junior College we have a commitment to personalised ";
        $editdata->body .= "learning and to meeting the needs of each of our students to the best ";
        $editdata->body .= "of our ability.\n\n";
        $editdata->body .= "As part of this commitment we have reviewed the format of how parent ";
        $editdata->body .= "interviews are traditionally held to ensure we can provide you with a ";
        $editdata->body .= "full picture of your child's areas of strength and also identify any ";
        $editdata->body .= "areas which require development.\n\n";
        $editdata->body .= "As a result we will not be holding a parent interview where parents ";
        $editdata->body .= "can meet with teachers for only approximately 5 minutes, but would like ";
        $editdata->body .= "to ask that, instead, you attend, with your child, an individual ";
        $editdata->body .= "interview which we anticipate will be for approximately 30 minutes. ";
        $editdata->body .= "This interview will be with me as I am your child's learning advisor.\n\n";
        $editdata->body .= "This will primarily be a student led conference where " . $studentdetails->firstname . " ";
        $editdata->body .= "will provide evidence of progress to date, including the current ";
        $editdata->body .= "eReport attached, also with input from me. I will also be happy to ";
        $editdata->body .= "answer any other questions you may have at that meeting and refer any ";
        $editdata->body .= "questions or comments you may have to " . $studentdetails->firstname . "'s ";
        $editdata->body .= "teachers.\n\n";
        $editdata->body .= "This round of student led conferences will be held during an afternoon ";
        $editdata->body .= "or evening that suits you;\n\n";
        $editdata->body .= "Monday the 1st September: 9:00am - 4:30pm\n\n";
        $editdata->body .= "Tuesday the 2nd September: 9:00am - 4:30pm\n\n";
        $editdata->body .= "Wednesday the 3rd September: 9:00am - 2:30pm\n\n";
        $editdata->body .= "Please notify me by return email as to which of the dates and times suit ";
        $editdata->body .= "you. AsI will be scheduling a number of interviews, I would appreciate ";
        $editdata->body .= "a prompt response so I can then confirm a definite time with you.\n\n";
        $editdata->body .= "We see this meeting as very important for " . $studentdetails->firstname . "'s ";
        $editdata->body .= "learning and hope that you will make every effort to attend with ";
        $editdata->body .= $studentdetails->firstname . ". I look forward to your response and to ";
        $editdata->body .= "meeting with you in the near future.\n\n";
    } else {
        $editdata->body .= "Re: " . $studentdetails->firstname . "'s Completed eReport " . date("Y") . "\n\n";
        $editdata->body .= "Please find attached " . $studentdetails->firstname . "'s completed Mission ";
        $editdata->body .= "Heights Junior College eReport. The eReport contains all ";
        $editdata->body .= "academic assessment information for " . $studentdetails->firstname . ". If ";
        $editdata->body .= "you have any questions or concerns please contact " . $userdetails->firstname;
        $editdata->body .= " " . $userdetails->lastname . " at " . $userdetails->email . " (" . $studentdetails->firstname;
        $editdata->body .= "'s Learning Advisor).\n\n";
        $editdata->body .= "Please note that the reporting system used at Mission Heights Junior College ";
        $editdata->body .= "is one of 'live reporting'. This means that " . $studentdetails->firstname . "'s ";
        $editdata->body .= "eReport is continually updated throughout  the year and is available for you ";
        $editdata->body .= "to view at anytime. This 'live report' can be viewed on Mission Heights Online ";
        $editdata->body .= "using your parent/caregiver login and password.\n\n";
        $editdata->body .= "Wishing you and your family a happy and safe summer break.\n\n";
    }
    $editdata->body .= "Yours faithfully,\n";
    $editdata->body .= $userdetails->firstname . " " . $userdetails->lastname . "\n";
    $editdata->body .= $studentdetails->firstname . "'s Learning Advisor";
} else {
    $editdata->body = "Dear Parents and Caregivers,\n\n";
    $editdata->body .= "Re: " . $studentdetails->firstname . "'s Unregistered NCEA Achievement " . date("Y") . "\n\n";
    $editdata->body .= "This year " . $studentdetails->firstname . " attained achievement in some NCEA Achievement Standards ";
    $editdata->body .= "(as detailed below) however these Achievement Standards are unregistered at this point. ";
    $editdata->body .= "Please note that this data will be transferred to the school " . $studentdetails->firstname . " ";
    $editdata->body .= "is enrolled in for " . (date("Y") + 1) . ". This transfer will occur in February " . (date("Y") + 1) . ". ";
    $editdata->body .= "This data will appear on " . $studentdetails->firstname . "'s record of learning in March " . (date("Y") + 1);
    $editdata->body .= " (approximately).\n\n";
    $editdata->body .= $studentdetails->firstname . " has had two opportunities to verify these grades. If however, ";
    $editdata->body .= $studentdetails->firstname . " believes there is an error with this data, please contact ";
    $editdata->body .= "Kate Lambert, Principal's Nominee for NZQA at klambert@mhjc.school.nz.\n\n";
    $editdata->body .= "Kind Regards,\n";
    $editdata->body .= "PP. Kate Lambert\n\n";
    $editdata->body .= "Principal's Nominee\n";
    $editdata->body .= "Senior Leader Curriculum\n";
    $editdata->body .= "Forest Whanau";
}

$mform->set_data($editdata);
$mform->display();

/// If data submitted, then process and store.
if ($mform->is_cancelled()) {
    $data = $mform->get_data();
    redirect($CFG->wwwroot . '/user/view.php?id=' . $data->stuid . '&course=' . $data->courseid); // change this to redirect to user profile page.
} else if ($data = $mform->get_data()) {

    $email->recipients = $data->recipients;
    $email->body = $data->body;
    $email->stuid = $data->stuid;
    $email->courseid = $data->courseid;
    $email->ereportattach = $data->ereportattach;
    $email->natstandattach = $data->natstandattach;

    if ($reporttype == "ncea") {
        redirect($CFG->wwwroot . '/grade/emailereport.php?user=' . $email->stuid . '&view=all&referrer=allmygrades&type=ereport&reporttype=ncea&course=' . $email->courseid . '&recipients=' . $email->recipients . '&body=' . urlencode($email->body));
    } else {
        redirect($CFG->wwwroot . '/grade/emailereport.php?user=' . $email->stuid . '&view=current&referrer=allmygrades&type=ereport&course=' . $email->courseid . '&recipients=' . $email->recipients . '&ereportattach=' . $email->ereportattach . '&natstandattach=' . $email->natstandattach . '&body=' . urlencode($email->body));
    }
}

/// Print MOL footer
echo $OUTPUT->footer();
?>