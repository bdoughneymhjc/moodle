<?php

require_once('../config.php');
require_once('../course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/pear/HTML/QuickForm/input.php');

if ($_SERVER['REQUEST_METHOD'] != 'POST') { // user has not pressed save
    $subreplace[] = "The Arts";
    $subreplace[] = "The Arts";
    $subreplace[] = "The Arts";
    $subreplace[] = "Health and Physical Education";
    $subreplace[] = "Learning Languages";
    $subreplace[] = "Learning Languages";
    $subreplace[] = "Mathematics and Statistics";
    $subreplace[] = "Social Sciences";
    $subreplace[] = "Technology";
    $subreplace[] = "English";

    $subfind[] = "Music";
    $subfind[] = "Drama";
    $subfind[] = "Art";
    $subfind[] = "PE and Health";
    $subfind[] = "Spanish";
    $subfind[] = "Japanese";
    $subfind[] = "Mathematics";
    $subfind[] = "Global Studies";
    $subfind[] = "Technology";
    $subfind[] = "English";

    $courseid = required_param('courseid', PARAM_INT);  // course id
    $currlevel = optional_param('level', 3, PARAM_INT);
    $currsubject = optional_param('subject', 0, PARAM_TEXT);
    $cao = optional_param('cao', 0, PARAM_INT);
    $aao = optional_param('aao', 0, PARAM_INT);
    $saved = optional_param('aos', NULL, PARAM_TEXT);

    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

    $coursename = explode(" ", $course->fullname, 2); //split up the course name to find the level and rest of course name

    if ($currsubject == "0") {
        $currsubject = str_replace($subfind, $subreplace, $coursename[1]);
    }

    if (strlen($coursename[0]) < 3) {
        $coursename[0] = $course->shortname;
    }

    $coursecontext = get_context_instance(CONTEXT_COURSE, $courseid);
    $PAGE->set_context($coursecontext);
    $PAGE->set_course($course);
    $PAGE->set_pagetype('course-view-' . $course->format);
    $PAGE->set_url($CFG->wwwroot . "/objectives/index.php");
    $PAGE->set_title("Achievement Objectives");
    $PAGE->set_heading("Achievement Objectives");

    $aosubjects[] = "English";
    $aosubjects[] = "The Arts";
    $aosubjects[] = "Health and Physical Education";
    $aosubjects[] = "Learning Languages";
    $aosubjects[] = "Mathematics and Statistics";
    $aosubjects[] = "Science";
    $aosubjects[] = "Social Sciences";
    $aosubjects[] = "Technology";
    $aosubjects[] = "Careers Education and Guidance";

    $aolevel[] = "3";
    $aolevel[] = "4";
    $aolevel[] = "5";
    $aolevel[] = "6";
    $aolevel[] = "7";

    $sql = "SELECT mdl_achievement_objectives_cat.id AS catid, mdl_achievement_objectives_cat.subjectarea, mdl_achievement_objectives_cat.subheading FROM mdl_achievement_objectives_cat WHERE mdl_achievement_objectives_cat.subjectarea = '" . $currsubject . "';";

    $subheadings = $DB->get_records_sql($sql);

    // Print MOL header
    echo $OUTPUT->header();

    echo "<script type=\"text/javascript\" src=\"http://code.jquery.com/jquery-latest.js\"></script>\n";
    echo "<script type=\"text/javascript\">\n";
    echo "function reload(form)\n";
    echo "{\n";
    echo "var sub=form.subject.options[form.subject.options.selectedIndex].value;\n";
    echo "var lev=form.level.options[form.level.options.selectedIndex].value;\n";
    echo "self.location='index.php?courseid=" . $courseid . "&subject=' + sub + '&level=' + lev;\n";
    echo "}\n\n";

    include('jquery.autoSuggest.js');

    echo "</script>\n";
    echo "<h3>Achievement Objective Management for " . $coursename[0] . "</h3>\n";

    if ($saved == "saved") {
        echo "<p style='color: green; font-weight: bold; font-size: large;'>Changes Saved Successfully &#8212 ";
        echo "<a href='report.php?courseid=" . $courseid . "'>View class AO's here</a></p>";
    }

    echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . "' autocomplete='off'>\n";
    echo "<table border='1'>\n";
    echo "<tr><td colspan='3'><select name='subject' onchange='reload(this.form)'>";

    foreach ($aosubjects as $subject) {
        echo "<option ";

        if ($currsubject == $subject) {
            echo "selected='yes' ";
        }
        echo "value='" . $subject . "'>" . $subject . "</option>";
    }

    echo "</select>&nbsp;<select name='level' onchange='reload(this.form)'>";

    foreach ($aolevel as $level) {
        echo "<option ";

        if ($currlevel == $level) {
            echo "selected='yes' ";
        }

        echo "value='" . $level . "'>Level " . $level . "</option>";
    }

    echo "</select></td></tr>\n";

    echo "<tr><td style='font-weight: bold;'>Achievement Objective Description</td><td style='background-color: #FFFCE0; color: black; font-weight: bold;'>Covered</td><td style='background-color: #E0FFF1; color: black; font-weight: bold;'>Assessed</td></tr>\n";

    foreach ($subheadings as $subheading) {
        echo "<tr><td colspan='3' style='background: #EDEDED; color: black; font-weight: bold;'>" . $subheading->subheading . "</td></tr>";
        $aosql = "SELECT mdl_achievement_objectives.id, mdl_achievement_objectives.category, mdl_achievement_objectives.description, mdl_achievement_objectives.level FROM mdl_achievement_objectives WHERE mdl_achievement_objectives.category = " . $subheading->catid;
        if (isset($currlevel)) {
            $aosql .= " AND mdl_achievement_objectives.level = '" . $currlevel . "';";
        } else {
            $aosql .= ";";
        }
        $aorecords = $DB->get_records_sql($aosql);
        foreach ($aorecords as $ao) {
            $coveredcount = 0;
            $assessedcount = 0;

            $aodone = $DB->get_record('achievement_objectives_class', array('aoid' => $ao->id, 'courseid' => $courseid), '*', IGNORE_MISSING);

            echo "<tr>";

            if ($aodone == FALSE) {
                echo "<td rowspan='2'>" . $ao->description . "</td>";
                echo "<td style='background-color: #FFFCE0; color: black;'><input type='checkbox' class='covered" . $ao->id . "' name='covered[]' value='" . $ao->id . "." . $coursename[0] . "' /></td>";
                echo "<td style='background-color: #E0FFF1; color: black;'><input type='checkbox' class='assessed" . $ao->id . "' name='assessed[]' value='" . $ao->id . "." . $coursename[0] . "' /></td>";
                echo "</tr>\n";
                echo "<tr><td colspan='2'><input type='text' name='context" . $ao->id . "' /><select name='term" . $ao->id . "'><option value='1'>Term 1</option><option value='2'>Term 2</option><option value='3'>Term 3</option><option value='4'>Term 4</option></td></tr>\n";
            } else {
                echo "<td rowspan='2' style='color: lightgrey;'>" . $ao->description . "</td>";
                if ($aodone->asscov == "a") {
                    echo "<td style='background-color: #FFFCE0; color: lightgrey;'>X</td><td style='background-color: #E0FFF1; color: lightgrey;'>X</td>";
                } else {
                    echo "<td style='background-color: #FFFCE0; color: lightgrey;'>X</td><td style='background-color: #E0FFF1; color: lightgrey;'>&nbsp;</td>";
                }
                echo "</tr>\n";
                echo "<tr><td colspan='2' style='color: lightgrey;'>Context: " . $aodone->context . ", Term: " . $aodone->term . "</td></tr>\n";
            }
        }
    }

    echo "<tr><td colspan='3' align='right'><input type='hidden' name='courseid' value='" . $courseid . "' /><input type='hidden' name='url' value='" . $_SERVER['QUERY_STRING'] . "' /><input type='submit' value='Save' /></td></tr>\n";
    echo "</table>";
    echo "</form>";

    /// Print MOL footer
    echo $OUTPUT->footer();
} else { //user has pressed save
    $datenow = time();

    $aorec = new stdClass();

    $subject = $_POST['subject'];
    $level = $_POST['level'];
    $courseid = $_POST['courseid'];

    $redirect = $_POST['url'];

    if (isset($_POST['covered'])) {
        foreach ($_POST['covered'] as $covered) {
            $covered = explode('.', $covered);
            $aorec->aoid = $covered[0];
            $aorec->date = $datenow;
            $aorec->asscov = 'c';
            $aorec->class = $covered[1];
            $aorec->teacherid = $USER->id;
            $aorec->courseid = $courseid;
            $aorec->context = addslashes($_POST['context' . $covered[0]]);
            $aorec->term = $_POST['term' . $covered[0]];

            $existingao = $DB->get_record_sql("SELECT * FROM mdl_achievement_objectives_class WHERE aoid='$aorec->aoid' AND class='$aorec->class' AND courseid='$aorec->courseid' AND context='$aorec->context' AND term='$aorec->term';");

            if ($existingao != NULL) {
                //covered should already be set if there is a record, but perhaps it isn't
                $aorec->id = $existingao->id;

                if (is_null($existingao->asscov)) {
                    // update the database (covered has not been set yet for some reason)
                    $DB->update_record('achievement_objectives_class', $aorec);
                }
            } else {
                // insert new record
                $DB->insert_record('achievement_objectives_class', $aorec);
            }
        }
    }

    if (isset($_POST['assessed'])) {
        foreach ($_POST['assessed'] as $assessed) {
            $assessed = explode('.', $assessed);
            $aorec->aoid = $assessed[0];
            $aorec->date = $datenow;
            $aorec->asscov = 'a';
            $aorec->class = $assessed[1];
            $aorec->teacherid = $USER->id;
            $aorec->courseid = $courseid;
            $aorec->context = $_POST['context' . $assessed[0]];
            $aorec->term = $_POST['term' . $assessed[0]];

            $existingao = $DB->get_record_sql("SELECT * FROM mdl_achievement_objectives_class WHERE aoid='$aorec->aoid' AND class='$aorec->class' AND courseid='$aorec->courseid' AND context='$aorec->context' AND term='$aorec->term';");

            if ($existingao != NULL) {
                $aorec->id = $existingao->id;
                // update the database (assessed has not been set yet)
                $DB->update_record('achievement_objectives_class', $aorec);
            } else {
                // insert new record
                $DB->insert_record('achievement_objectives_class', $aorec);
            }
        }
    }
    $PAGE->navigation->clear_cache();
    redirect($CFG->wwwroot . '/objectives/index.php?' . $redirect . '&aos=saved');
}
?>