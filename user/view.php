<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Display profile for a particular user
 *
 * @package core_user
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../config.php");
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/badgeslib.php');

$id = optional_param('id', 0, PARAM_INT); // User id.
$courseid = optional_param('course', SITEID, PARAM_INT); // course id (defaults to Site).
$showallcourses = optional_param('showallcourses', 0, PARAM_INT);

// See your own profile by default.
if (empty($id)) {
    require_login();
    $id = $USER->id;
}

if ($courseid == SITEID) {   // Since Moodle 2.0 all site-level profiles are shown by profile.php.
    redirect($CFG->wwwroot . '/user/profile.php?id=' . $id);  // Immediate redirect.
}

$PAGE->set_url('/user/view.php', array('id' => $id, 'course' => $courseid));

$user = $DB->get_record('user', array('id' => $id), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$currentuser = ($user->id == $USER->id);

$systemcontext = context_system::instance();
$coursecontext = context_course::instance($course->id);
$usercontext = context_user::instance($user->id, IGNORE_MISSING);

// Check we are not trying to view guest's profile.
if (isguestuser($user)) {
    // Can not view profile of guest - thre is nothing to see there.
    print_error('invaliduserid');
}

$PAGE->set_context($coursecontext);

if (!empty($CFG->forceloginforprofiles)) {
    require_login(); // We can not log in to course due to the parent hack below.
    // Guests do not have permissions to view anyone's profile if forceloginforprofiles is set.
    if (isguestuser()) {
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('guestcantaccessprofiles', 'error'), get_login_url(), $CFG->wwwroot);
        echo $OUTPUT->footer();
        die;
    }
}

$PAGE->set_course($course);
$PAGE->set_pagetype('course-view-' . $course->format);  // To get the blocks exactly like the course.
$PAGE->add_body_class('path-user');                     // So we can style it independently.
$PAGE->set_other_editing_capability('moodle/course:manageactivities');

// Set the Moodle docs path explicitly because the default behaviour
// of inhereting the pagetype will lead to an incorrect docs location.
$PAGE->set_docs_path('user/profile');

$isparent = false;

if (!$currentuser and ! $user->deleted
        and $DB->record_exists('role_assignments', array('userid' => $USER->id, 'contextid' => $usercontext->id))
        and has_capability('moodle/user:viewdetails', $usercontext)) {
    // TODO: very ugly hack - do not force "parents" to enrol into course their child is enrolled in,
    //       this way they may access the profile where they get overview of grades and child activity in course,
    //       please note this is just a guess!
    require_login();
    $isparent = true;
    $PAGE->navigation->set_userid_for_parent_checks($id);
} else {
    // Normal course.
    require_login($course);
    // What to do with users temporary accessing this course? should they see the details?
}

$strpersonalprofile = get_string('personalprofile');
$strparticipants = get_string("participants");
$struser = get_string("user");

$fullname = fullname($user, has_capability('moodle/site:viewfullnames', $coursecontext));

// Now test the actual capabilities and enrolment in course.
if ($currentuser) {
    if (!is_viewing($coursecontext) && !is_enrolled($coursecontext)) {
        // Need to have full access to a course to see the rest of own info.
        $referer = get_local_referer(false);
        if (!empty($referer)) {
            redirect($referer, get_string('notenrolled', '', $fullname));
        }
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('notenrolled', '', $fullname));
        echo $OUTPUT->footer();
        die;
    }
} else {
    // Somebody else.
    $PAGE->set_title("$strpersonalprofile: ");
    $PAGE->set_heading("$strpersonalprofile: ");

    // Check to see if the user can see this user's profile.
    if (!user_can_view_profile($user, $course, $usercontext) && !$isparent) {
        print_error('cannotviewprofile');
    }

    if (!is_enrolled($coursecontext, $user->id)) {
        // TODO: the only potential problem is that managers and inspectors might post in forum, but the link
        //       to profile would not work - maybe a new capability - moodle/user:freely_acessile_profile_for_anybody
        //       or test for course:inspect capability.
        if (has_capability('moodle/role:assign', $coursecontext)) {
            $PAGE->navbar->add($fullname);
            $notice = get_string('notenrolled', '', $fullname);
        } else {
            $PAGE->navbar->add($struser);
            $notice = get_string('notenrolledprofile', '', $fullname);
        }
        $referer = get_local_referer(false);
        if (!empty($referer)) {
            redirect($referer, $notice);
        }
        echo $OUTPUT->header();
        echo $OUTPUT->heading($notice);
        echo $OUTPUT->footer();
        exit;
    }

    if (!isloggedin() or isguestuser()) {
        // Do not use require_login() here because we might have already used require_login($course).
        redirect(get_login_url());
    }
}

$PAGE->set_title("$course->fullname: $strpersonalprofile: $fullname");
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('standard');

// Locate the users settings in the settings navigation and force it open.
// This MUST be done after we've set up the page as it is going to cause theme and output to initialise.
if (!$currentuser) {
    $PAGE->navigation->extend_for_user($user);
    if ($node = $PAGE->settingsnav->get('userviewingsettings' . $user->id)) {
        $node->forceopen = true;
    }
} else if ($node = $PAGE->settingsnav->get('usercurrentsettings', navigation_node::TYPE_CONTAINER)) {
    $node->forceopen = true;
}
if ($node = $PAGE->settingsnav->get('courseadmin')) {
    $node->forceopen = false;
}

echo $OUTPUT->header();

echo '<div class="userprofile">';
$headerinfo = array('heading' => fullname($user), 'user' => $user, 'usercontext' => $usercontext);
echo $OUTPUT->context_header($headerinfo, 2);

if ($user->deleted) {
    echo $OUTPUT->heading(get_string('userdeleted'));
    if (!has_capability('moodle/user:update', $coursecontext)) {
        echo $OUTPUT->footer();
        die;
    }
}

// OK, security out the way, now we are showing the user.
// Trigger a user profile viewed event.
profile_view($user, $coursecontext, $course);

if ($user->description && !isset($hiddenfields['description'])) {
    echo '<div class="description">';
    if (!empty($CFG->profilesforenrolledusersonly) && !$DB->record_exists('role_assignments', array('userid' => $id))) {
        echo get_string('profilenotshown', 'moodle');
    } else {
        if ($courseid == SITEID) {
            $user->description = file_rewrite_pluginfile_urls($user->description, 'pluginfile.php', $usercontext->id, 'user', 'profile', null);
        } else {
            // We have to make a little detour thought the course context to verify the access control for course profile.
            $user->description = file_rewrite_pluginfile_urls($user->description, 'pluginfile.php', $coursecontext->id, 'user', 'profile', $user->id);
        }
        $options = array('overflowdiv' => true);
        echo format_text($user->description, $user->descriptionformat, $options);
    }
    echo '</div>'; // Description class.
}

// eReport added lines
if (has_capability('moodle/grade:viewall', $coursecontext) || $id == $currentuser) {
    if ($courseid == NULL) {
        $courseid = SITEID;
    }
    echo html_writer::start_tag('dl');
    echo html_writer::tag('dt', 'DEEP');
    echo html_writer::tag('dd', '<a href="' . $CFG->wwwroot . '/DEEP/index.php?userid=' . $id . '">DEEP Selections</a>');
    echo html_writer::tag('dt', 'Evidence:');
    echo html_writer::tag('dd', '<img src="' . $CFG->wwwroot . '/pix/safe.png" /> <a href="' . $CFG->wwwroot . '/user/evidence.php?id=' . $id . '&course=' . $courseid . '">National Standards Evidence Bank</a>');

    $eReporting = '<table><tr><td><img src="' . $CFG->wwwroot . '/pix/ereport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/allmygrades.php?user=' . $id . '&view=current&type=ereport&course=' . $courseid . '" target="_blank">Current Year\'s eReport</a>';

    if (has_capability('moodle/grade:viewall', $coursecontext)) {
        $eReporting .= '<br /><br /><form action="email_report.php?stuid=' . $id . '&courseid=' . $courseid . '" method="post"><input type="Submit" value="Email Report" /></form>';
        $eReporting .= '</td></tr><tr><td><img src ="' . $CFG->wwwroot . '/pix/ereport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/allmygrades.php?user=' . $id . '&view=all&type=summary&course=' . $courseid . '" target="_blank">Summary eReport (for student\'s moving schools)</a>';
    }

    $countsql = "SELECT COUNT(mdl_grade_grades.id), mdl_grade_grades.id, mdl_grade_grades.rawscaleid, mdl_user.id FROM mdl_grade_grades INNER JOIN mdl_user ON mdl_grade_grades.userid = mdl_user.id WHERE mdl_user.id = ? AND mdl_grade_grades.rawscaleid = ?";

    $eReporting .= '</td></tr><tr><td><img src="' . $CFG->wwwroot . '/pix/pereport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/allmygrades.php?user=' . $id . '&view=all&type=ereport&course=' . $courseid . '" target="_blank">Complete eReport</a></td></tr>';
    $ncea = $DB->count_records_sql($countsql, array($id, '11'));
    $userroles = $DB->count_records_sql("SELECT COUNT(mdl_role_assignments.id) from mdl_role_assignments WHERE mdl_role_assignments.userid = ? AND mdl_role_assignments.roleid = ?", array($USER->id, '14'));
    if ($userroles > 0 AND $ncea > 0) { // if user is allowed to see NCEA report
        $eReporting .= '<tr><td><img src="' . $CFG->wwwroot . '/pix/pereport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/recordofachievement.php?user=' . $id . '&view=all&type=ereport&course=' . $courseid . '" target="_blank">NCEA Record of Achievement</a></td></tr>';
        if (has_capability('moodle/grade:viewall', $coursecontext)) {
            $eReporting .= '<tr><td>&nbsp;</td><td><form action="email_report.php?stuid=' . $id . '&reporttype=ncea&courseid=' . $courseid . '" method="post"><input type="Submit" value="Email NCEA Report" /></form></td></tr>';
        }
    }
    $natsql = "SELECT mdl_grade_grades.id, mdl_grade_grades.rawscaleid, mdl_user.id FROM mdl_grade_grades INNER JOIN mdl_user ON mdl_grade_grades.userid = mdl_user.id HAVING mdl_user.id = ? AND mdl_grade_grades.rawscaleid = ?;";

    $natstand = $DB->count_records_sql($countsql, array($id, '8'));

    // only show National Standards link if reports exist

    if ($natstand > 0) {
        $eReporting.= '<tr><td><img src="' . $CFG->wwwroot . '/pix/natreport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/natstandards.php?user=' . $id . '&view=current&type=natstandards&course=' . $courseid . '" target="_blank">Current Year\'s National Standards eReport (Year 7 and 8 only)<a/></td></tr><tr><td><img src="' . $CFG->wwwroot . '/pix/pnatreport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/natstandards.php?user=' . $id . '&view=all&type=natstandards&course=' . $courseid . '" target="_blank">Complete National Standards eReport (Year 7 and 8 only)</a></td></tr>';
    }

    $esol = $DB->count_records_sql($countsql, array($id, '9'));

    // only show ESOL link if reports exist
    if ($esol > 0) {
        $eReporting.= '<tr><td><img src="' . $CFG->wwwroot . '/pix/esolreport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/allmygrades.php?user=' . $id . '&view=current&type=esol&course=' . $courseid . '" target="_blank">ESOL Report<a/></td></tr>';
    }

    $eReporting.= '<tr><td><img src="' . $CFG->wwwroot . '/pix/easttlereport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/easttlegrades.php?user=' . $id . '&course=' . $courseid . '" target="_blank">e-asTTle Results</a></td></tr><tr><td><img src="' . $CFG->wwwroot . '/pix/selfreflection.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/reflection.php?user=' . $id . '&view=current&course=' . $courseid . '" target="_blank">My Self Reflection</a></td></tr></table>';

    echo '<dt>Live eReporting:</dt>';
    echo '<dd>' . $eReporting . '</dd>';
}

/// Print users' timetable
$sql = "SELECT mdl_user.id, mdl_user_timetable.userid, mdl_user.idnumber, mdl_user_timetable.monday, mdl_user_timetable.tuesday, mdl_user_timetable.wednesday, mdl_user_timetable.thursday, mdl_user_timetable.friday FROM mdl_user_timetable INNER JOIN mdl_user ON mdl_user_timetable.userid = mdl_user.id WHERE mdl_user.id='" . $id . "';";
$timetable = $DB->get_record_sql($sql);

if (!$timetable) {
    $ttoutput = "&nbsp;";
    // user doesn't have a timetable
} else {
    $sessionnames = "-<span class='ttday'>Session 1<br />8:30 – 9:30am</span>,-<span class='ttday'>Session 2<br />9:35 – 10:35am</span>,-<span class='ttday'>Interval<br /></span>,-<span class='ttday'>Session 3<br />11:00 – 12:00pm</span>,-<span class='ttday'>Session 4<br />12:05 – 1:05pm</span>,-<span class='ttday'>Lunch<br /></span>,-<span class='ttday'>Session 5<br />2:00 – 3:00pm</span>";
    $daynames = explode(",", ",Monday,Tuesday,Wednesday,Thursday,Friday");

    $day[0] = explode(",", $sessionnames);
    $day[1] = explode(",", $timetable->monday);
    $day[2] = explode(",", $timetable->tuesday);
    $day[3] = explode(",", $timetable->wednesday);
    $day[4] = explode(",", $timetable->thursday);
    $day[5] = explode(",", $timetable->friday);

    $currsession = 0;
    $sessions = count($day[0]);

    $ttoutput = "<h3>My Timetable</h3>\n";
    $ttoutput .= "<table border='1' width='100%' align='center' class='timetable'>\n";

    $ttoutput .= "<tr>";
    foreach ($daynames as $dayname) {
        $ttoutput .= "<td width='16%' style='background-color: lightgray;'>" . $dayname . "</td>";
    }
    $ttoutput .= "</tr>\n";

    while ($currsession < $sessions) {
        $ttoutput .= "<tr>\n";
        $days = 0;
        while ($days <= 5) {
            if ($days == 0) {
                $ttoutput .= "<td style='background-color: lightgray;'>";
            } else {
                $ttoutput .= "<td>";
            }
            $cleansession = explode("-", $day[$days][$currsession]);
            for ($x = 1; $x < 4; $x++) {
                if (!array_key_exists($x, $cleansession)) {
                    $cleansession[$x] = '';
                }
            }
            $mycourse = $DB->get_record('course', array('idnumber' => $cleansession[1]));
            if ($mycourse && !empty($cleansession[1])) { // output the full course name
                $ttoutput .= "<a href='" . $CFG->wwwroot . "/course/view.php?id=" . $mycourse->id . "'>" . $mycourse->fullname . "</a><br />";
            } else {
                $ttoutput .= $cleansession[1] . "<br />";
            }
            if ($cleansession[2] != '') {
                $myteacher = $DB->get_record('user', array('address' => $cleansession[2]));
                if ($myteacher) { // output the full teacher name
                    $ttoutput .= "<a href='mailto:" . $myteacher->email . "'>" . substr($myteacher->firstname, 0, 1) . " " . $myteacher->lastname . "</a><br />";
                } else {
                    $ttoutput .= $cleansession[2];
                }
            }
            $ttoutput .= $cleansession[3]; // room
            $days++;
            $ttoutput .= "</td>";
        }

        $ttoutput .= "\n</tr>";
        $currsession++;
    }

    $ttoutput .= "</table>";
}

/// Actualy output the timetable here
if (has_capability('moodle/user:viewdetails', $usercontext) || $id == $currentuser || has_coursecontact_role($id)) {
    echo "<dt>My Timetable</dt><dd>";
    echo $ttoutput;
    echo "</dd>";
}

// Render custom blocks.
$renderer = $PAGE->get_renderer('core_user', 'myprofile');
$tree = core_user\output\myprofile\manager::build_tree($user, $currentuser, $course);
echo $renderer->render($tree);

echo '</div>';  // Userprofile class.

echo $OUTPUT->footer();
