<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../config.php");
require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->libdir . '/formslib.php');

$systemcontext = context_system::instance();

class librarysearch_form extends moodleform {

    function definition() {
        $mform = $this->_form;
        $mform->addElement('html', '<h3>Search MHOL Resources</h3>');
        $mform->addElement('text', 'search', 'Search:', array('size' => '50'));
        $mform->setType('search', PARAM_TEXT);
        $this->add_action_buttons(true, 'Search');
    }

}

$PAGE->set_context($systemcontext);
$PAGE->set_url($CFG->wwwroot . "/library/library.php");

require_login();
if (isguestuser()) {
    redirect($CFG->wwwroot);
}

$PAGE->set_title("MHOL Resource Library");
$PAGE->set_heading("MHOL Resource Library");
//$PAGE->navbar->add($studentname);

echo $OUTPUT->header();

$mform = new librarysearch_form(null);
$mform->display();

/// If data submitted, then process and store.
if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot);
} elseif ($data = $mform->get_data()) {
    $searchterm = $data->search;

    // get all course sections where keyword is found
    $searchsections = $DB->get_records_sql('SELECT mdl_course_modules.id, mdl_course_modules.course, mdl_course_modules.section, mdl_course_modules.instance, mdl_course_modules.added, mdl_context.id as contextid, mdl_course_sections.section as sectionno FROM mdl_course_modules INNER JOIN mdl_course_sections ON mdl_course_modules.section = mdl_course_sections.id INNER JOIN mdl_context on mdl_course_modules.instance = mdl_context.instanceid WHERE mdl_course_sections.summary LIKE lower( ? ) AND mdl_course_modules.module = ? ORDER BY mdl_course_modules.added DESC;', array('%' . $searchterm . '%', '13'));

    echo $OUTPUT->heading("Resources relating to the search: &quot;" . $searchterm . "&quot;");

    echo "<table>";

    foreach ($searchsections as $section) {
        $itemcontext = $DB->get_record('context', array('instanceid' => $section->id, 'contextlevel' => '70'), '*');
        $courseinfo = $DB->get_record('course', array('id' => $section->course), '*');

        $fs = get_file_storage();
        $files = $fs->get_area_files($itemcontext->id, 'mod_resource', 'content', false, 'timemodified', false);
        foreach ($files as $file) {
            //var_dump($file);
            $filename = $file->get_filename();

            $path = implode('/', array($file->get_contextid(), 'mod_resource', 'content', $file->get_itemid(), $file->get_filepath(), $filename));
            $url = moodle_url::make_file_url('/pluginfile.php', "/" . $path);
            echo "<tr><td>" . date("d/m/y", $section->added);
            echo "</td><td>";
            echo html_writer::link($url, $filename);
            echo "</td><td><a href='" . $CFG->wwwroot . "/course/view.php?id=" . $section->course . "#section-" . $section->sectionno . "' target='_blank'>" . $courseinfo->shortname . "</a>";
            echo "</td><td>" . $file->get_author();
            echo "</td></tr>";
        }
    }

    echo "</table>";
}

echo $OUTPUT->footer();
