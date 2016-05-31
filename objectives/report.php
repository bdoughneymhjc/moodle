<?php

require_once('../config.php');
require_once('../course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/pear/HTML/QuickForm/input.php');

$courseid = required_param('courseid', PARAM_INT);  // course id
$currlevel = optional_param('level', 0, PARAM_INT);
$currsubject = optional_param('subject', 0, PARAM_TEXT);
$cao = optional_param('cao', 0, PARAM_INT);
$aao = optional_param('aao', 0, PARAM_INT);
$uid = optional_param('uid', 0, PARAM_INT);
$tid = optional_param('tid', 0, PARAM_INT);

if (isset($_POST['checkyear'])) {
    $year = FALSE;
} else {
    $year = TRUE;
}

if (isset($_POST['checkshow'])) {
    $listall = TRUE;
} else {
    $listall = FALSE;
}

$pageaction = optional_param('action', 0, PARAM_TEXT);
$actionid = optional_param('id', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = context_course::instance($courseid);
$PAGE->set_context($coursecontext);
$PAGE->set_url($CFG->wwwroot . "/objectives/report.php");

$coursename = explode(" ", $course->fullname, 2); //split up the course name to find the level and rest of course name

if (strlen($coursename[0]) < 3) {
    $searchon = $course->shortname;
} else {
    $searchon = $coursename[0];
}

if ($pageaction != NULL) {
    switch ($pageaction) {
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                $aoinfo = $DB->get_record_sql("SELECT mdl_achievement_objectives_class.id, mdl_achievement_objectives_class.aoid, mdl_achievement_objectives_class.date, mdl_achievement_objectives_class.context, mdl_achievement_objectives_class.term, mdl_achievement_objectives.id AS aoid, mdl_achievement_objectives.description, mdl_achievement_objectives.level FROM mdl_achievement_objectives_class INNER JOIN mdl_achievement_objectives ON mdl_achievement_objectives.id = mdl_achievement_objectives_class.aoid WHERE mdl_achievement_objectives_class.id = ?", array($actionid));
                echo $OUTPUT->header();
                echo "<link href=\"http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css\" rel=\"stylesheet\" type=\"text/css\"/>";
                echo "<script src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js\"></script>";
                echo "<script src=\"http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js\"></script>";
                echo "<script type=\"text/javascript\">\n";
                echo "$(function() {\n";
                echo "$( '#datepicker' ).datepicker({ dateFormat: 'dd-mm-yy' });\n";
                echo "});";
                echo "</script>";
                echo "<h3>Editing &quot;<i>L$aoinfo->level $aoinfo->description</i>&quot;</h3>";
                echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "?action=edit&courseid=$courseid'>";

                $formdate = date('d-m-Y', $aoinfo->date);

                //echo "Date: <input id='datepicker' type='text' name='date' value='$formdate'><br />";
                echo "<table>\n";
                echo "<tr><td>";
                echo "Term:</td><td><select name='term'>";

                $i = 1;

                while ($i < 5) {
                    echo "<option ";
                    if ($aoinfo->term == $i) {
                        echo " selected='selected' ";
                    }
                    echo "value='$i'>Term $i</option>";
                    $i++;
                }

                echo "</select></td></tr>";
                echo "<tr><td>Context:</td><td><input type='text' name='context' value='$aoinfo->context' size='50'></td></tr>\n";
                echo "<tr><td><input type='hidden' name='aoid' value='" . $actionid . "' /></td><td>";
                echo "<input type='submit' value='Save' /></td></tr>\n";
                echo "</table>\n";
                echo "</form>";
                echo $OUTPUT->footer();
            } else { // user has pushed the save button- update the record
                $aorec = new stdClass();
                $aorec->id = $_POST['aoid'];
                //$aorec->date = strtotime($_POST['date']);
                $aorec->term = $_POST['term'];
                $aorec->context = $_POST['context'];

                $DB->update_record('achievement_objectives_class', $aorec);

                // redirect to report page
                $PAGE->navigation->clear_cache();
                redirect($CFG->wwwroot . '/objectives/report.php?courseid=' . $courseid);
            }
            break;
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] != 'POST') {
                $aoinfo = $DB->get_record_sql("SELECT mdl_achievement_objectives_class.id, mdl_achievement_objectives_class.aoid, mdl_achievement_objectives_class.date, mdl_achievement_objectives_class.context, mdl_achievement_objectives.id AS aoid, mdl_achievement_objectives.description, mdl_achievement_objectives.level FROM mdl_achievement_objectives_class INNER JOIN mdl_achievement_objectives ON mdl_achievement_objectives.id = mdl_achievement_objectives_class.aoid WHERE mdl_achievement_objectives_class.id='$actionid';");
                echo $OUTPUT->header();
                echo "<h3>Deleting &quot;<i>L$aoinfo->level $aoinfo->description</i>&quot;</h3>";
                echo "<p>Delete this achievement objective from this class?</p>";
                echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "?action=delete&courseid=$courseid'>";
                echo "<input type='hidden' name='aoid' value='" . $actionid . "' />";
                echo "<br /><a href='" . $_SERVER['PHP_SELF'] . "?courseid=$courseid'><input type='button' name='cancel' value='Cancel' /></a><input type='submit' value='Delete' />";
                echo "</form>";
                echo $OUTPUT->footer();
            } else {
                $aoid = $_POST['aoid'];

                $DB->delete_records('achievement_objectives_class', array('id' => $aoid));

                $PAGE->navigation->clear_cache();
                redirect($CFG->wwwroot . '/objectives/report.php?courseid=' . $courseid);
            }
            break;
    }
} else {

    if ($uid === 0) {
        $participants = $DB->get_records_sql("SELECT mdl_user.id AS id, mdl_user.firstname, mdl_user.lastname FROM mdl_role_assignments, mdl_user, mdl_course, mdl_context WHERE mdl_role_assignments.userid = mdl_user.id AND mdl_role_assignments.contextid = mdl_context.id AND mdl_context.instanceid = mdl_course.id AND mdl_course.id = ? AND roleid = 5 AND mdl_user.auth <> 'nologin' ORDER BY mdl_user.lastname, mdl_user.firstname;", array($courseid));
    } else {
        $participants = $DB->get_records_sql("SELECT mdl_user.id AS id, mdl_user.firstname, mdl_user.lastname FROM mdl_role_assignments, mdl_user, mdl_course, mdl_context WHERE mdl_role_assignments.userid = mdl_user.id AND mdl_role_assignments.contextid = mdl_context.id AND mdl_context.instanceid = mdl_course.id AND mdl_course.id = ? AND roleid = 5 AND mdl_user.id = ? and mdl_user.auth <> 'nologin' ORDER BY mdl_user.lastname, mdl_user.firstname;", array($courseid, $uid));
    }

    $noparticipants = count($participants);

    if ($noparticipants > 0) {

        echo "<!DOCTYPE html>\n";
        echo "<html>\n";
        echo "<head>\n";
        header('Content-Type: text/html; charset=utf-8');
        echo "<title>AO Coverage Report</title>\n";
        echo "<style type='text/css'>\n";
        echo "<!--";
        echo "h3 { color: rgba( 0, 128, 128, 1); font-size: 1.4em; }\n";
        echo "body { font-family: 'Century Gothic', Helvetica, 'Helvetica Neue', Arial, sans-serif; }\n";
        echo "p.key { font-size: 0.6em; }\n";
        echo ".topform { font-size: 0.8em; }\n";
        echo "div#links a {color: #590009;font-weight: bold;text-decoration: none; }\n";
        echo "div#links a span {text-decoration: none;display: none; }\n";
        echo "div#links a:hover span {display: block;padding: 5px;margin: 10px;z-index: 100;text-decoration: none;border: thin solid black;color: black;font-size: 1em;font-weight: normal; }\n";
        echo ".aotable { clear: both; border: solid thin black; padding: 0px; border-collapse: collapse; margin-top: 10px; width: 100%; }\n";
        echo ".aotable td { border : solid thin black; padding: 5px; font-size: 0.8em;}\n";
        echo "span.notdone { color: rgba(30, 30, 30, 1); }\n";
        echo ".english { background-color: rgba(0, 0, 128, 1); color: rgba(255, 255, 255, 0.8); font-weight: bold; }\n";
        echo ".assenglish { background-color: rgba(0, 0, 128, 0.3); }\n";
        echo ".mathematicsandstatistics { background-color: rgba(128, 0, 64, 1); color: rgba(255, 255, 255, 0.8); font-weight: bold; }\n";
        echo ".assmathematicsandstatistics { background-color: rgba(128, 0, 64, 0.3); }\n";
        echo ".healthandphysicaleducation { background-color: rgba(144, 0, 40, 1); color: rgba(255, 255, 255, 0.8); font-weight: bold; }\n";
        echo ".asshealthandphysicaleducation { background-color: rgba(144, 0, 40, 0.3); }\n";
        echo ".learninglanguages { background-color: rgba(0, 126, 150, 1); color: rgba(255, 255, 255, 0.8); font-weight: bold; }\n";
        echo ".asslearninglanguages { background-color: rgba(0, 126, 150, 0.3); }\n";
        echo ".science { background-color: rgba(0, 50, 0, 1); color: rgba(255, 255, 255, 0.8); font-weight: bold; }\n";
        echo ".assscience { background-color: rgba(0, 50, 0, 0.3); }\n";
        echo ".thearts { background-color: rgba(255, 128, 0, 1); color: rgba(255, 255, 255, 0.8); font-weight: bold; }\n";
        echo ".assthearts { background-color: rgba(255, 128, 0, 0.3); }\n";
        echo ".socialsciences { background-color: rgba(70, 0, 150, 1); color: rgba(255, 255, 255, 0.8); font-weight: bold; }\n";
        echo ".asssocialsciences { background-color: rgba(70, 0, 150, 0.3); }\n";
        echo ".technology { background-color: rgba(98, 50, 0, 1); color: rgba(255, 255, 255, 0.8); font-weight: bold; }\n";
        echo ".asstechnology { background-color: rgba(98, 50, 0, 0.3); }\n";
        echo ".careerseducationandguidance { background-color: rgba(144, 108, 0, 1); color: rgba(255, 255, 255, 0.8; font-weight: bold; }\n";
        echo ".asscareerseducationandguidance { background-color: rgba(144, 108, 0, 0.3); }\n";
        echo "span.description { font-size: smaller; font-style: italic; }\n";
        echo "div.toggle_container { background-color: LightGrey; font-size: smaller; font-style: italic; padding: 5px; }\n";
        echo "-->";
        echo "</style>\n";
        echo "<script type=\"text/javascript\" src=\"http://code.jquery.com/jquery-latest.js\"></script>\n";
        echo "</head>\n";
        echo "<body>\n";

        $datenow = date("d M Y");

        echo "<img style='float: right; top: 5px;' src='http://online.mhjc.school.nz/file.php/1/logo/MIS_Junior_College_Logo.png' width='168' height='66' />";
        echo "<h3>$course->fullname AO Coverage, $datenow</h3>\n";
        echo "<p class='key'><b>KEY:</b> <i>Not Shaded = <b>Covered</b>, Shaded = <b>Covered and Assessed</b></i><form method='post' action='" . $_SERVER['PHP_SELF'] . "?courseid=$courseid' id='showall' class='topform'> Show All AO's: <input type='checkbox' name='checkshow' value='true' onclick='document.getElementById(this.form.id).submit();'";

        if ($listall) {
            echo " checked='checked' ";
        }

        echo "/></form>";

        echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "?courseid=$courseid' id='year' class='topform'> Show Only Current Year: <input type='checkbox' name='checkyear' value='true' onclick='document.getElementById(this.form.id).submit();'";

        if ($year) {
            echo " checked='checked' ";
        }
        echo "/></form>";
        echo "</p>\n";

        //check what ao's each member of this class has done and store it in aosdone array
        /* foreach ($participants as $participant) {
          $participantids[] = $participant->id;
          $aosql = "SELECT mdl_achievement_objectives_class.id, mdl_achievement_objectives_class.aoid, mdl_achievement_objectives_class.userid FROM mdl_achievement_objectives_class WHERE mdl_achievement_objectives_class.userid = ?";
          if ($tid != 0) {
          $aosql .= " AND mdl_achievement_objectives_class.teacherid = " . $tid;
          }
          if ($year) {
          $aosql .= " AND mdl_achievement_objectives_class.date > " . strtotime("January 1 " . date("Y"));
          }
          $aos = $DB->get_records_sql($aosql, array($participant->id));

          foreach ($aos as $ao) {
          $aosdone[] = $ao->aoid;
          }
          } */

        // get all AO's for class in a sensible list ordered by category
        //$aolist = $DB->get_records_sql("SELECT mdl_achievement_objectives_class.id, mdl_achievement_objectives.id, mdl_achievement_objectives.category, mdl_achievement_objectives.description, mdl_achievement_objectives.level, mdl_achievement_objectives_cat.id AS catid, mdl_achievement_objectives_cat.subjectarea, mdl_achievement_objectives_cat.subheading FROM mdl_achievement_objectives INNER JOIN mdl_achievement_objectives_cat ON mdl_achievement_objectives.category = mdl_achievement_objectives_cat.id WHERE mdl_achievement_objectives.id IN ('" . join("','", $aosdone) . "') ORDER BY mdl_achievement_objectives_cat.subjectarea, mdl_achievement_objectives.level;");
        $aolist = $DB->get_records_sql("SELECT mdl_achievement_objectives_class.id, mdl_achievement_objectives_class.date, mdl_achievement_objectives_class.teacherid, mdl_achievement_objectives_class.courseid, mdl_achievement_objectives_class.context, mdl_achievement_objectives_class.asscov, mdl_achievement_objectives_class.term, mdl_achievement_objectives.id AS obid, mdl_achievement_objectives.category, mdl_achievement_objectives.description, mdl_achievement_objectives.level, mdl_achievement_objectives_cat.id AS catid, mdl_achievement_objectives_cat.subjectarea, mdl_achievement_objectives_cat.subheading FROM mdl_achievement_objectives_class INNER JOIN mdl_achievement_objectives ON mdl_achievement_objectives_class.aoid = mdl_achievement_objectives.id INNER JOIN mdl_achievement_objectives_cat ON mdl_achievement_objectives.category = mdl_achievement_objectives_cat.id WHERE mdl_achievement_objectives_class.class='" . $searchon . "' ORDER BY mdl_achievement_objectives_cat.subjectarea, mdl_achievement_objectives.level;");

        echo "<table class='aotable'>\n";
        echo "<tr style='background-color: black; color: white; font-weight: bold;'><td width='50%'>AO Coverage</td><td>Date</td><td>Context</td><td>&nbsp;</td></tr>\n";

        $prevsubject = NULL;

        foreach ($aolist as $list) {
            if ($list->subjectarea != $prevsubject) {
                $subclass = strtolower(str_replace(" ", "", $list->subjectarea));
                echo "<tr><td colspan='4' class='" . $subclass . "'>" . $list->subjectarea . "</td></tr>\n";
            }

            echo "<tr><td valign='top'><b>L" . $list->level . " " . $list->subheading . "</b> <br /><span class='description'>" . $list->description . "</span></td>";

            $line = 0;

            $teacherdetails = $DB->get_record('user', array('id' => $list->teacherid));
            $teachername = fullname($teacherdetails);
            $coursedetails = $DB->get_record('course', array('id' => $list->courseid));

            if ($line != 0) {
                echo "<tr>";
            }

            echo "<td valign='top'";
            if ($list->asscov == "a") {
                echo " class='ass" . $subclass . "' ";
            }
            echo ">Term " . $list->term . " " . date('Y', $list->date) . ", " . $coursedetails->fullname . ": " . $teachername . "</td><td valign='top'";
            if ($list->asscov == "a") {
                echo " class='ass" . $subclass . "' ";
            }
            echo ">" . stripslashes($list->context) . "</td>";
            echo "<td valign='top'><a href='report.php?action=edit&id=$list->id&courseid=$courseid'><img src='../pix/t/edit.png' border='0' /></a> <a href='report.php?action=delete&id=$list->id&courseid=$courseid'><img src='../pix/t/delete.png' border='0' /></a></td>";
            echo "</tr>";

            if ($listall == TRUE) {
                $notaolist = $DB->get_records_sql("SELECT mdl_achievement_objectives.id, mdl_achievement_objectives.category, mdl_achievement_objectives.description, mdl_achievement_objectives.level, mdl_achievement_objectives_cat.id AS catid, mdl_achievement_objectives_cat.subjectarea, mdl_achievement_objectives_cat.subheading FROM mdl_achievement_objectives INNER JOIN mdl_achievement_objectives_cat ON mdl_achievement_objectives.category = mdl_achievement_objectives_cat.id WHERE NOT mdl_achievement_objectives.id IN ('" . join("','", $aosdone) . "') AND mdl_achievement_objectives_cat.subjectarea='$list->subjectarea' ORDER BY mdl_achievement_objectives_cat.subjectarea, mdl_achievement_objectives.level;");
                foreach ($notaolist as $notao) {
                    echo "<tr><td valign='top' colspan='4' style='background-color: rgba( 240, 240 , 240, 1);'><span class='notdone'><b>L" . $notao->level . " " . $notao->subheading . "</b> <br />" . $notao->description . "</span></td></tr>\n";
                }
            }

            $prevsubject = $list->subjectarea;
        }

        echo "</table>\n";
        echo "</body>\n";
        echo "</html>";
    }
}
?>