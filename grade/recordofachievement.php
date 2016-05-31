<?php
require_once("../config.php");
require_once("../lib/gradelib.php");

require_once("$CFG->dirroot/mod/assignment/lib.php");

$currentuser = required_param('user', PARAM_INT);  // user id
$viewtype = required_param('view', PARAM_TEXT); // view type
$reporttype = required_param('type', PARAM_TEXT); // report type - eReport, National standards etc.
$courseid = optional_param('course', SITEID, PARAM_INT); // added to allow teachers etc viewing
$pdf = optional_param('pdf', 0, PARAM_BOOL);

$idncea = 11; // wame scale id

if (empty($currentuser)) {            // See your own profile by default
    require_login();
}

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$usercontext = get_context_instance(CONTEXT_USER, $currentuser, MUST_EXIST);

if (has_capability('moodle/grade:view', $usercontext) || $currentuser == $USER->id || has_capability('moodle/grade:view', $coursecontext) || $pdf == TRUE) {
    //$sql = "SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.timemodified, mdl_grade_grades.feedback, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) AS roundedgrade, mdl_grade_grades.usermodified, mdl_grade_grades.rawscaleid, mdl_grade_items.itemname, mdl_grade_items.itemmodule, mdl_grade_items.iteminstance, mdl_course.fullname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id where mdl_grade_grades.userid = '" . $currentuser . "' AND mdl_grade_grades.rawscaleid = '" . $idncea . "' AND mdl_grade_grades.finalgrade IS NOT NULL ORDER BY mdl_course.fullname;";
    $stuinfo = $DB->get_records_sql("SELECT q.id, q.userid, q.finalgrade, q.roundedgrade, q.timemodified, q.feedback, q.shortname, q.department, q.itemmodule, q.iteminstance, q.scaleid, q.itemid, q.itemname, q.outcomeid, q.firstname, q.lastname FROM (SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) AS roundedgrade, mdl_grade_grades.timemodified, mdl_grade_grades.feedback, mdl_course.shortname, mdl_user.department, mdl_grade_items.itemmodule, mdl_grade_items.iteminstance, mdl_grade_items.scaleid, mdl_grade_grades.itemid, mdl_grade_items.itemname, mdl_grade_items.outcomeid, mdl_user.firstname, mdl_user.lastname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id INNER JOIN mdl_user on mdl_grade_grades.userid = mdl_user.id WHERE mdl_user.id = ? AND mdl_grade_grades.finalgrade > 0 AND mdl_grade_items.scaleid = ? ) AS q WHERE q.feedback IS NULL AND q.outcomeid IS NULL", array($currentuser, $idncea));
    $scalesql = "SELECT mdl_scale.id, mdl_scale.scale FROM mdl_scale WHERE mdl_scale.id=" . $idncea;
} else {
    print_error('cannotviewprofile');
}

$PAGE->set_context($coursecontext);

//$stuinfo = $DB->get_records_sql($sql);
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
<!DOCTYPE html>
<html>
    <head>
        <?php
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <title>Live Record of Achievement for <?php print $studentname; ?></title>
        <style type="text/css">
            <!--
            body {
                font-family: "Century Gothic", Helvetica, "Helvetica Neue", Arial, sans-serif; }

            table tr td {
                vertical-align: text-top;
            }

            td.oneline {
                white-space: nowrap;
            }

            #watermark {
                color: rgba(0, 0, 0, 0.1);
                font-size: 72pt;
                -webkit-transform: rotate(-45deg);
                -moz-transform: rotate(-45deg);
                position: relative;
                /*width: 100%;
                height: 100%;
                margin: 0;*/
                z-index: -1;
                top:-400px;
            }

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

            div#taskdesc {
                font-size: 0.6em;
            }

            table#bdr {
                border: thin solid black;
                border-spacing: 0px;
                width: 100%; }

            table#bdr td {
                border: thin solid black;
                vertical-align: top;
                padding: 5px; }

            table#bdrsml {
                border: thin solid black;
                border-spacing: 0px;
                width: 100%; }

            table#bdrsml td {
                border: thin solid black;
                vertical-align: top;
                padding: 5px;
                font-size: 0.6em; }

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

        print "<table border='0' cellpadding='5' cellspacing='0' width='1000px' align='center'>\n";
        print "<tr>\n";
        print "<td><h3>Live Record of NCEA Achievement for</h3><h2>" . $studentname . ", " . $studentdetails->department . "</h2></td>";
        print "<td>&nbsp;</td>";
        print "<td align='right'><img src='http://online.mhjc.school.nz/file.php/1/logo/MIS_Junior_College_Logo.png' width='337' height='132' /></td>";
        print "</tr>\n";

        print "<tr>\n";
        print "<td>&nbsp;</td>";
        print "<td>&nbsp;</td>";
        print "<td align='right'><i>" . date("jS F, Y") . "</i></td>";
        print "</tr>\n";

        print "<tr>\n";
        print "<td>&nbsp;</td>";
        print "<td>&nbsp;</td>";
        print "<td>&nbsp;</td>";
        print "</tr>\n";

        /* MAIN LAYOUT TABLE REPORT CELL */
        print "<tr>\n<td colspan='3'>";

        /* BEGIN REPORT LAYOUT TABLE */
        print "<table border='0' cellpadding='5' cellspacing='0' width='100%'>\n";
        print "<tr>\n<td><b>Level</b></td><td colspan='2'><b>Achievement Standard</b></td><td><b>Credits</b></td><td><b>Result</b></td><td><b>Date</b></td></tr>\n";
        print "<tr>\n<td colspan='6'><hr /></td></tr>\n";

//blank temp class name
        $tempclassname = '';
        $totalcredits = 0;

        foreach ($stuinfo as $row) {
            $dategraded = date("d-m-Y", $row->timemodified);
            $yeargraded = date("Y", $row->timemodified);
            $currentyear = date("Y");

            $assignmentinfo = $DB->get_record($row->itemmodule, array('id' => $row->iteminstance));
            //$osql = "SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) as roundedgrade, mdl_grade_grades.usermodified, mdl_grade_items.itemmodule, mdl_grade_items.iteminstance, mdl_course.fullname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id where mdl_grade_grades.userid = '" . $currentuser . "' AND mdl_grade_grades.usermodified = '" . $row->usermodified . "' AND ROUND(mdl_grade_grades.finalgrade) = '" . $row->roundedgrade . "' AND mdl_grade_items.itemmodule = '" . $row->itemmodule . "' AND mdl_grade_items.outcomeid is NOT NULL AND mdl_grade_items.iteminstance = '" . $row->iteminstance . "' AND mdl_grade_grades.rawgrade IS NULL ORDER BY mdl_course.fullname;";
            //print "<!-- Outcome: " . $osql . " -->\n";
            //$outcomes = $DB->get_record_sql($osql);
            //if ($outcomes) {
            $title = explode('#', $row->itemname);
            if ($title[0] != $tempclassname) { // this might be useful to divide up english maths etc
                // but we won't use it at the moment
                print "<tr>\n";
                print "<td colspan='5'><b>" . $title[0] . "</b></td>";
                print "</tr>\n";
            }

            $assignmentdesc = preg_replace('/<style(.*)>(.*)<\/style>/', '', $assignmentinfo->intro); // remove style tags and stuff in between them
            $assignmentdesc = str_replace($find, $replace, $assignmentdesc);
            $assignmentdesc = strip_tags($assignmentdesc, '');
            $assignmentdesc = preg_replace('/<\s*([a-z]+)([^>]*)>/', '<\1>', $assignmentdesc); // get rid of special colouring etc
            $credits = intval($assignmentdesc);

            $studentmark_task = $scaleitem[$row->roundedgrade - 1];

            print "<tr>\n";
            print "<td class='oneline'>" . $title[1] . "</td><td>" . $title[2] . "</td><td>" . $title[3] . "</td><td>" . $credits . "</td><td>" . $studentmark_task . "</td><td class='oneline'>" . $dategraded . "</td>";
            print "</tr>\n";

            if ($row->roundedgrade > 1) {
                $totalcredits = $totalcredits + $credits;
            }
            $tempclassname = $title[0];

            //}
        }
        print "<tr>\n<td colspan='6'><hr /></td></tr>\n";
        print "<tr>\n<td>&nbsp;</td><td>&nbsp;</td><td><b>Total Credits:</b></td><td>" . $totalcredits . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
        print "<tr>\n<td colspan='6'>Course endorsement provides recognition for students who perform exceptionally well in individual courses. Students will gain an endorsement for a course if, in a single school year, they achieve:<br />";
        print "14 or more credits at Merit or Excellence, where at least 3 of these credits are from externally assessed standards and 3 credits from internally assessed standards. Therefore these credits can not be used for Course ";
        print "Endorsement. They can however, be used for Overall Certificate Endorsement.</td></tr>\n";
        print "</table>";

        print "<div id='watermark'><p>UNREGISTERED</p></div></td></tr>\n";
        print "</table>";
        ?> 
    </body>
</html>