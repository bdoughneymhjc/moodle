<?php
require_once("../config.php");
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->dirroot . '/mod/assign/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/grade/grading/form/rubric/lib.php');
require_once($CFG->dirroot . '/grade/grading/form/rubric/renderer.php');

function strip_selected_tags_by_id_or_class($array_of_id_or_class, $text) {
    $array_quoted = array_map('preg_quote', $array_of_id_or_class);
    $name = implode('|', $array_quoted);
    $regex = '#<(\w+)\s[^>]*(class|id)\s*=\s*[\'"](' . $name .
            ')[\'"][^>]*>.*</\\1>#isU';
    return(preg_replace($regex, '', $text));
}

$currentuser = required_param('user', PARAM_INT);  // user id
$viewtype = required_param('view', PARAM_TEXT); // view type
$reporttype = required_param('type', PARAM_TEXT); // report type - eReport, National standards etc.
$courseid = optional_param('course', SITEID, PARAM_INT); // added to allow teachers etc viewing
$pdf = optional_param('pdf', 0, PARAM_BOOL);

if (empty($currentuser)) {            // See your own profile by default
    require_login();
}

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = context_course::instance($course->id);
$usercontext = context_user::instance($currentuser, MUST_EXIST);

if ($viewtype == "current") {
    $currentyear = strtotime("January 1 " . date("Y"));
} else {
    $currentyear = 0;
}

$css = "\tbody { font-family: 'Century Gothic', Helvetica, 'Helvetica Neue', Arial, sans-serif; }\n";
$css .= "\ttable#bdrsml { border: thin solid black; border-spacing: 0px; width: 100%; }\n";
$css .= "\ttable#bdrsml td { border: thin solid black; vertical-align: top; padding: 5px; font-size: 0.6em; }\n";

if (has_capability('moodle/grade:view', $usercontext) || $currentuser == $USER->id || has_capability('moodle/grade:view', $coursecontext) || $pdf == TRUE) {
    switch ($reporttype) {
        case "esol":
            $title = "ESOL";
            $scaleid = 9;
            $scalesrec = $DB->get_record_sql("SELECT mdl_scale.id, mdl_scale.scale FROM mdl_scale WHERE mdl_scale.id = ?", array($scaleid));
            $scaleitem = explode(", ", $scalesrec->scale);

            $cellwidth = (1 / count($scaleitem)) * 100;
            $hovertext = "Your child's progress and achievement in relation to standards based assessment is reported using our Mission Heights Junior College 'WAME' <b><i>(Working Towards, Achieved, Merit, </i></b>and<b><i> Excellence)</i></b>.<br /><br /><b><u>Please note:</u></b> the <b><i>Achieved</i></b> level is the standard we expect students to reach by the end of the year. Please feel free to contact your child's Learning Advisor if you have any questions or concerns about this eReport.";
            $css .= "\ttable.main { width: 1000px; padding: 5px; margin-left: auto; margin-right: auto; border-spacing: 0; }\n";
            $css .= "\ttable.main td { padding: 5px; }\n";
            $css .= "\ttr.greenbox { display: none; }\n";
            $css .= "\ttr.classname { background-color: darkgray; }\n";
            $css .= "\t.logo { float: right; width: 337px; height: 132px; }\n";
            $css .= "\tdiv#links a { color: #00626B; font-weight: bold; text-decoration: none; float: left; }\n";
            $css .= "\tdiv#datetext { color: #00626B; font-style: italic; float: right; }\n";
            $css .= "\ttable#bdr { border: thin solid black; border-spacing: 0px; width: 100%; }\n";
            $css .= "\ttable#bdr td { border: thin solid black; vertical-align: top; padding: 5px; }\n";
            $css .= "\tdiv.achievement { font-weight: bold; clear: both; padding-top: 0.6em; }\n";
            $css .= '\ttd.maininfo { vertical-align: middle; }\n';
            $css .= "\ttd.highlight { text-align: center; background-color: #00626B; font-weight: bold; color: white; width: " . $cellwidth . "%; }\n";
            $css .= "\ttd.nohighlight { text-align: center; background-color: #DDDDDD; color: #999999; width: " . $cellwidth . "%; }\n";
            $css .= "\tdiv#helptext a { color: #00626B; text-decoration: none; }\n";
            $css .= "\tdiv#helptext a span { text-decoration: none; display: none; }\n";
            $css .= "\tdiv#helptext a:hover span { display: block; padding: 5px; margin: 10px; z-index: 100; text-decoration: none; border: thin solid black; background-color: #EEEEEE; color: black; font-size: 1em; font-weight: normal; }";
            break;
        case "summary":
            $title = "Summary eReport";
            $scaleid = 2;
            $scalesrec = $DB->get_record_sql("SELECT mdl_scale.id, mdl_scale.scale FROM mdl_scale WHERE mdl_scale.id = ?", array($scaleid));
            $scaleitem = explode(", ", $scalesrec->scale);

            $hovertext = "<b>Working Towards</b> indicates that students have not met the expected standard<br \><b>Achieved</b> indicates that students have met the expected standard<br /><b>Merit</b> indicates students have exceeded the expected standard<br /><b>Excellence</b> indicates students have achieved well above the expected standard";
            $css .= "\ttable.main { width: 1000px; padding: 5px;  margin-left: auto; margin-right: auto; border-spacing: 0; }\n";
            $css .= "\ttable.main td { padding: 5px; }\n";
            $css .= "\ttr.greenbox { background-color: #00626B; }\n";
            $css .= "\ttr.classname { background-color: darkgray; }\n";
            $css .= "\t.logo { float: right; width: 169px; height: 66px; }\n";
            $css .= "\tdiv#links a { color: black; text-decoration: none; float: left; vertical-align: middle; }\n";
            $css .= "\tdiv#datetext { color: black; float: left; padding-left: 0.2em; vertical-align: middle; }\n";
            $css .= "\ttable#bdr { border: none; float: right; vertical-align: middle; border-spacing: 0; }\n";
            $css .= "\ttable#bdr tr { background: none; }\n";
            $css .= "\ttable#bdr td { vertical-align: middle; padding: 0; }\n";
            $css .= "\tdiv.achievement { display: none; }\n";
            $css .= "\tdiv.titleleft { font-weight: bold; color: white; float: left; }\n";
            $css .= "\tdiv.titleright { font-weight: bold; color: white; float: right; }\n";
            $css .= "\ttd.jumpto { display: none; }\n";
            $css .= "\ttd.taskdesc { display: none; }\n";
            $css .= "\ttd.taskfeedback { display: none; }\n";
            $css .= "\ttable td.highlight { border: none; }\n";
            $css .= "\ttd.highlight { text-align: right; vertical-align: middle; background: none; border-spacing: 0;}\n";
            $css .= "\ttd.nohighlight { display: none; }\n";
            $css .= "\ttd.rule { display: none; }\n";
            $css .= "\tdiv#helptext a { color: white; }\n";
            $css .= "\tdiv#helptext a span { color: #00626B; display: block; text-decoration: none; font-size: 0.8em; width: 100%;  }";
            break;
        default:
            $title = "eReport";
            $scaleid = 2;
            $scalesrec = $DB->get_record_sql("SELECT mdl_scale.id, mdl_scale.scale FROM mdl_scale WHERE mdl_scale.id = ?", array($scaleid));
            $scaleitem = explode(", ", $scalesrec->scale);

            $cellwidth = (1 / (count($scaleitem) + 1)) * 100;
            $hovertext = "Your child's progress and achievement in relation to standards based assessment is reported using our Mission Heights Junior College 'WAME' <b><i>(Working Towards, Achieved, Merit, </i></b>and<b><i> Excellence)</i></b>.<br /><br /><b><u>Please note:</u></b> the <b><i>Achieved</i></b> level is the standard we expect students to reach by the end of the year. Please feel free to contact your child's Learning Advisor if you have any questions or concerns about this eReport.";
            $css .= "\ttable.main { width: 1000px; padding: 5px; align: center; margin-left: auto; margin-right: auto; border-spacing: 0; }\n";
            $css .= "\ttable.main td { padding: 5px; }\n";
            $css .= "\ttr.greenbox { display: none; }\n";
            $css .= "\ttr.classname { background-color: darkgray; }\n";
            $css .= "\t.logo { float: right; width: 337px; height: 132px; }\n";
            $css .= "\tdiv#links a { color: #00626B; font-weight: bold; text-decoration: none; float: left; }\n";
            $css .= "\tdiv#datetext { color: #00626B; font-style: italic; float: right; }\n";
            $css .= "\ttable#bdr, table.criteria { border: thin solid black; border-spacing: 0px; width: 100%; }\n";
            $css .= "\ttable#bdr td, table.criteria td { border: thin solid black; vertical-align: top; padding: 5px; }\n";
            $css .= "\ttable.criteria td div { padding: 0; spacing: 0; }\n";
            $css .= "\tdiv.achievement { font-weight: bold; clear: both; padding-top: 0.6em; }\n";
            $css .= "\ttd.highlight { text-align: center; background-color: #00626B; font-weight: bold; color: white; width: " . $cellwidth . "%; }\n";
            $css .= "\ttd.nohighlight { text-align: center; background-color: #DDDDDD; color: #999999; width: " . $cellwidth . "%; }\n";
            $css .= "\tdiv#helptext a { color: #00626B; text-decoration: none; }\n";
            $css .= "\tdiv#helptext a span { text-decoration: none; display: none; }\n";
            $css .= "\tdiv#helptext a:hover span { display: block; padding: 5px; margin: 10px; z-index: 100; text-decoration: none; border: thin solid black; background-color: #EEEEEE; color: black; font-size: 1em; font-weight: normal; }";
            $css .= "\ttd.checked { background-color: #00626B; font-weight: bold; color: white; }\n";
            $css .= "\ttable { border-collapse: collapse; }\n";
            $css .= "\ttd.levels table { width: 100%; }\n";
            $css .= "\ttd.description { width: 20%; }\n";
    }
} else {
    print_error('cannotviewprofile');
}

$stuinfo = $DB->get_records_sql("SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.timemodified, mdl_grade_grades.feedback, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) AS roundedgrade, mdl_grade_grades.usermodified, mdl_grade_grades.rawscaleid, mdl_grade_items.itemname, mdl_grade_items.itemmodule, mdl_grade_items.iteminstance, mdl_grade_items.courseid, mdl_course.fullname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id where mdl_grade_grades.userid = ? AND mdl_grade_grades.rawscaleid = ? AND mdl_grade_grades.feedback IS NOT NULL AND mdl_grade_grades.timemodified > ? ORDER BY mdl_course.fullname", array($currentuser, $scaleid, $currentyear));

$studentdetails = $DB->get_record('user', array('id' => $currentuser));
$studentname = fullname($studentdetails);

$PAGE->set_url(new moodle_url('/grade/allmygrades.php', array('user' => $currentuser, 'view' => $viewtype, 'type' => $reporttype)));
$PAGE->set_context($coursecontext);
$PAGE->set_title("Live " . $title . " for " . $studentname);
$PAGE->set_heading("Live " . $title . " for " . $studentname);

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

print "<!DOCTYPE html>\n";
print "<html>\n";
print "<head>\n";
header('Content-Type: text/html; charset=utf-8');
print "<title>Live " . $title . " for " . $studentname . "</title>\n";
print "<style type='text/css'>\n";
print "<!--\n";
print $css;
print "\n-->\n";
print "</style>\n";
print "</head>\n";
print "<body>\n";
/* BEGIN MAIN LAYOUT TABLE */
print "<table class='main'>";
print "<tr>";
print "<td><h3>Live " . $title . " for</h3><h2>" . $studentname . ", " . $studentdetails->department . "<img src='http://online.mhjc.school.nz/file.php/1/logo/MIS_Junior_College_Logo.png' class='logo' /></h2></td>";
print "</tr>";

print "<tr>";
print "<td><div id='helptext'><a href='#'>Click here to learn how to interpret your child's report.<span>" . $hovertext . "</span></a></div></td>";
print "</tr>";

if ($pdf === 0) {
    print "<tr>";
    print "<td><a href='" . $CFG->wwwroot . "/grade/exporttopdf.php?user=" . $currentuser . "&view=" . $viewtype . "&type=" . $reporttype . "&course=" . $courseid . "&referrer=allmygrades'>Download PDF version</a>";
    print "<div style='align: right; float: right;'><i>" . date("jS F, Y") . "</i></div></td>";
    print "</tr>";
}

/* MAIN LAYOUT TABLE REPORT CELL */
print "<tr><td class='jumpto'>Jump to: ";

$classinfo = array();
$i = 0;

foreach ($stuinfo as $row) {
    $classinfo[$i] = $row->fullname;
    $i++;
}

$classunique = array_unique($classinfo);

foreach ($classunique as $classn) {
    print "<a href='#$classn'>$classn</a> ";
}

print "</td></tr>";
//blank temp class name
$tempclassname = '';

print "<tr class='greenbox'><td><div class='titleleft'>Assessment Name (Date)</div><div class='titleright'>Final Grade</div></td></tr>\n";

foreach ($stuinfo as $row) {
    $dategraded = date("d-m-Y", $row->timemodified);
    $yeargraded = date("Y", $row->timemodified);
    if ($row->fullname != $tempclassname) {
        $teacherdetails = $DB->get_record('user', array('id' => $row->usermodified));
        $teachername = explode(" ", fullname($teacherdetails));
        $teachfn = substr($teachername[0], 0, 1);
        $teachsn = $teachername[1];
        print "<tr class='classname'>";
        print "<td><b><a name='$row->fullname'>$row->fullname</a>, <i>Teacher: " . $teachfn . " " . $teachsn . "</i></b></td>";
        print "</tr>\n";
    }
    // find a matching outcome in the database
    if ($reporttype == "esol") {
        $osql = "SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) as roundedgrade, mdl_grade_grades.usermodified, mdl_grade_items.itemmodule, mdl_grade_items.iteminstance, mdl_course.fullname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id where mdl_grade_grades.userid = ? AND ROUND(mdl_grade_grades.finalgrade) = ? AND mdl_grade_items.itemmodule = ? AND mdl_grade_items.iteminstance = ? ORDER BY mdl_course.fullname;";
    } else {
        $osql = "SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) as roundedgrade, mdl_grade_grades.usermodified, mdl_grade_items.itemmodule, mdl_grade_items.iteminstance, mdl_course.fullname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id where mdl_grade_grades.userid = ? AND ROUND(mdl_grade_grades.finalgrade) = ? AND mdl_grade_items.itemmodule = ? AND mdl_grade_items.outcomeid is NOT NULL AND mdl_grade_items.iteminstance = ? AND mdl_grade_grades.rawgrade IS NULL ORDER BY mdl_course.fullname;";
    }

    //print "<!-- Row Info: ";
    //print_r($row);
    //print " -->\n";
    //print "<!-- RoundedGrade: " . $row->roundedgrade . " -->\n";
    //print "<!-- ItemModule: " . $row->itemmodule . " -->\n";
    //print "<!-- ItemInstance: " . $row->iteminstance . " -->\n";
    $outcomes = $DB->get_record_sql($osql, array($currentuser, $row->roundedgrade, $row->itemmodule, $row->iteminstance));

    if ($outcomes) {

        print "<tr>";
        print "<td class='maininfo'><div id='links'><a href='#" . $row->id . "'>" . $row->itemname . "</a></div><div id='datetext'>($dategraded)</div>";
        print "<div class='achievement'>Current Level of Achievement:</div>\n";
        $assignmentinfo = $DB->get_record($row->itemmodule, array('id' => $row->iteminstance));
        /* lines to get rubric from database */
        $cm = get_coursemodule_from_instance('assign', $assignmentinfo->id);
        $asctx = context_module::instance($cm->id);
        $courseobj = get_course($row->courseid);
        $grad_area = $DB->get_record('grading_areas', array('contextid' => $asctx->id, 'component' => 'mod_assign', 'areaname' => 'submissions'), '*', MUST_EXIST);
        $aobj = new assign($asctx, $cm, $courseobj);
        $submission = $aobj->get_user_submission($studentdetails->id, true);
        $gradform = new gradingform_rubric_controller($asctx, 'mod_assign', 'submissions', $grad_area->id);
        $gradinginfo = grade_get_grades($row->courseid, 'mod', 'assign', $assignmentinfo->id, $studentdetails->id);
        $grade = $aobj->get_user_grade($studentdetails->id, FALSE);
        $rubric = $gradform->render_grade($PAGE, $grade->id, $gradinginfo, '', FALSE);
        $cleanrubric = strip_selected_tags_by_id_or_class(array('remark', 'score'), $rubric);
        $striprubric = strip_tags($cleanrubric, '<table><tr><td>');

        print "<table id='bdr'><tr>";
        if (!empty($striprubric)) {
            print "<td class='description'>&nbsp;</td>";
        }

        $assignmentdesc = preg_replace('/<style(.*)>(.*)<\/style>/', '', $assignmentinfo->intro); // remove style tags and stuff in between them
        $assignmentdesc = str_replace($find, $replace, $assignmentdesc);
        $assignmentdesc = strip_tags($assignmentdesc, '<p><br><table><tr><td><ul><li>');
        $assignmentdesc = preg_replace('/<\s*([a-z]+)([^>]*)>/', '<\1>', $assignmentdesc); // get rid of special colouring etc
        $assignmentdesc = str_replace("<table>", "<table id='bdrsml'>", $assignmentdesc);

        $markno = 1;
        foreach ($scaleitem as $tabletext) {
            if ($row->roundedgrade == $markno) {
                $assignmentdesc = str_replace("<td>" . $tabletext, "<td class='highlight'>" . $tabletext, $assignmentdesc);
                print "<td class='highlight'>$tabletext</td>";
            } else {
                $assignmentdesc = str_replace("<td>" . $tabletext, "<td class='nohighlight'>" . $tabletext, $assignmentdesc);
                print "<td class='nohighlight'>$tabletext</td>";
            }
            $markno++;
        }

        $cleancomment = preg_replace('/<style(.*)>(.*)<\/style>/', '', $row->feedback); // remove style tags and stuff in between them
        $cleancomment = str_replace($find, $replace, $cleancomment);
        $cleancomment = strip_tags($cleancomment, '<p><br><table><tr><td><ul><li>');
        $cleancomment = preg_replace('/<\s*([a-z]+)([^>]*)>/', '<\1>', $cleancomment);
        $cleancomment = str_replace("<table>", "<table id='bdrsml'>", $cleancomment);

        print "</tr>";
        print "</table>";
        print $striprubric;
        print "</td>";
        print "</tr>\n";

        print "<tr>";
        print "<td class='taskdesc'><b>Task Description:</b><br />" . $assignmentdesc . "</td>";
        print "</tr>\n";
        print "<tr>";
        print "<td class='taskfeedback'><b>Teacher Feedback / Feedforward:</b><br />" . $cleancomment . "</td>";
        print "</tr>\n";
        print "<tr>";
        print "<td class='rule'><hr /></td>";
        print "</tr>\n";
        print "<tr><td>";
        print "</td></tr>";
    }
    unset($tempclassname);
    $tempclassname = $row->fullname;
}
print "<tr class='greenbox'><td>&nbsp;</td></tr>\n";
print "</table>\n";
?> 
</body>
</html>