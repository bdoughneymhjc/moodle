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
 * Public Profile -- a user's public profile page
 *
 * - each user can currently have their own page (cloned from system and then customised)
 * - users can add any blocks they want
 * - the administrators can define a default site public profile for users who have
 *   not created their own public profile
 *
 * This script implements the user's view of the public profile, and allows editing
 * of the public profile.
 *
 * @package    core_user
 * @copyright  2010 Remote-Learner.net
 * @author     Hubert Chathi <hubert@remote-learner.net>
 * @author     Olav Jordan <olav.jordan@remote-learner.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../config.php');
require_once($CFG->dirroot . '/my/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->libdir . '/filelib.php');

$userid = optional_param('id', 0, PARAM_INT);
$edit = optional_param('edit', null, PARAM_BOOL);    // Turn editing on and off.
$reset = optional_param('reset', null, PARAM_BOOL);

$PAGE->set_url('/user/profile.php', array('id' => $userid));

if (!empty($CFG->forceloginforprofiles)) {
    require_login();
    if (isguestuser()) {
        $PAGE->set_context(context_system::instance());
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('guestcantaccessprofiles', 'error'), get_login_url(), $CFG->wwwroot);
        echo $OUTPUT->footer();
        die;
    }
} else if (!empty($CFG->forcelogin)) {
    require_login();
}

$userid = $userid ? $userid : $USER->id;       // Owner of the page.
if ((!$user = $DB->get_record('user', array('id' => $userid))) || ($user->deleted)) {
    $PAGE->set_context(context_system::instance());
    echo $OUTPUT->header();
    if (!$user) {
        echo $OUTPUT->notification(get_string('invaliduser', 'error'));
    } else {
        echo $OUTPUT->notification(get_string('userdeleted'));
    }
    echo $OUTPUT->footer();
    die;
}

$currentuser = ($user->id == $USER->id);
$context = $usercontext = context_user::instance($userid, MUST_EXIST);

if (!user_can_view_profile($user, null, $context)) {

    // Course managers can be browsed at site level. If not forceloginforprofiles, allow access (bug #4366).
    $struser = get_string('user');
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title("$SITE->shortname: $struser");  // Do not leak the name.
    $PAGE->set_heading($struser);
    $PAGE->set_url('/user/profile.php', array('id' => $userid));
    $PAGE->navbar->add($struser);
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('usernotavailable', 'error'));
    echo $OUTPUT->footer();
    exit;
}

// Get the profile page.  Should always return something unless the database is broken.
if (!$currentpage = my_get_page($userid, MY_PAGE_PUBLIC)) {
    print_error('mymoodlesetup');
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('mypublic');
$PAGE->set_pagetype('user-profile');

// Set up block editing capabilities.
if (isguestuser()) {     // Guests can never edit their profile.
    $USER->editing = $edit = 0;  // Just in case.
    $PAGE->set_blocks_editing_capability('moodle/my:configsyspages');  // unlikely :).
} else {
    if ($currentuser) {
        $PAGE->set_blocks_editing_capability('moodle/user:manageownblocks');
    } else {
        $PAGE->set_blocks_editing_capability('moodle/user:manageblocks');
    }
}

// Start setting up the page.
$strpublicprofile = get_string('publicprofile');

$PAGE->blocks->add_region('content');
$PAGE->set_subpage($currentpage->id);
$PAGE->set_title(fullname($user) . ": $strpublicprofile");
$PAGE->set_heading(fullname($user));

if (!$currentuser) {
    $PAGE->navigation->extend_for_user($user);
    if ($node = $PAGE->settingsnav->get('userviewingsettings' . $user->id)) {
        $node->forceopen = true;
    }
} else if ($node = $PAGE->settingsnav->get('dashboard', navigation_node::TYPE_CONTAINER)) {
    $node->forceopen = true;
}
if ($node = $PAGE->settingsnav->get('root')) {
    $node->forceopen = false;
}


// Toggle the editing state and switches.
if ($PAGE->user_allowed_editing()) {
    if ($reset !== null) {
        if (!is_null($userid)) {
            if (!$currentpage = my_reset_page($userid, MY_PAGE_PUBLIC, 'user-profile')) {
                print_error('reseterror', 'my');
            }
            redirect(new moodle_url('/user/profile.php', array('id' => $userid)));
        }
    } else if ($edit !== null) {             // Editing state was specified.
        $USER->editing = $edit;       // Change editing state.
    } else {                          // Editing state is in session.
        if ($currentpage->userid) {   // It's a page we can edit, so load from session.
            if (!empty($USER->editing)) {
                $edit = 1;
            } else {
                $edit = 0;
            }
        } else {
            // For the page to display properly with the user context header the page blocks need to
            // be copied over to the user context.
            if (!$currentpage = my_copy_page($userid, MY_PAGE_PUBLIC, 'user-profile')) {
                print_error('mymoodlesetup');
            }
            $PAGE->set_context($usercontext);
            $PAGE->set_subpage($currentpage->id);
            // It's a system page and they are not allowed to edit system pages.
            $USER->editing = $edit = 0;          // Disable editing completely, just to be safe.
        }
    }

    // Add button for editing page.
    $params = array('edit' => !$edit, 'id' => $userid);

    $resetbutton = '';
    $resetstring = get_string('resetpage', 'my');
    $reseturl = new moodle_url("$CFG->wwwroot/user/profile.php", array('edit' => 1, 'reset' => 1, 'id' => $userid));

    if (!$currentpage->userid) {
        // Viewing a system page -- let the user customise it.
        $editstring = get_string('updatemymoodleon');
        $params['edit'] = 1;
    } else if (empty($edit)) {
        $editstring = get_string('updatemymoodleon');
        $resetbutton = $OUTPUT->single_button($reseturl, $resetstring);
    } else {
        $editstring = get_string('updatemymoodleoff');
        $resetbutton = $OUTPUT->single_button($reseturl, $resetstring);
    }

    $url = new moodle_url("$CFG->wwwroot/user/profile.php", $params);
    $button = $OUTPUT->single_button($url, $editstring);
    $PAGE->set_button($resetbutton . $button);
} else {
    $USER->editing = $edit = 0;
}

// Trigger a user profile viewed event.
profile_view($user, $usercontext);

// TODO WORK OUT WHERE THE NAV BAR IS!
echo $OUTPUT->header();
echo '<div class="userprofile">';

if ($user->description && !isset($hiddenfields['description'])) {
    echo '<div class="description">';
    if (!empty($CFG->profilesforenrolledusersonly) && !$currentuser &&
            !$DB->record_exists('role_assignments', array('userid' => $user->id))) {
        echo get_string('profilenotshown', 'moodle');
    } else {
        $user->description = file_rewrite_pluginfile_urls($user->description, 'pluginfile.php', $usercontext->id, 'user', 'profile', null);
        echo format_text($user->description, $user->descriptionformat);
    }
    echo '</div>';
}

// eReport added lines
if (has_capability('moodle/user:viewdetails', $usercontext) || $userid == $currentuser) {
    if (!isset($courseid)) {
        $courseid = SITEID;
    }
    echo html_writer::start_tag('dl');
    echo html_writer::tag('dt', 'DEEP');
    echo html_writer::tag('dd', '<a href="' . $CFG->wwwroot . '/DEEP/index.php?userid=' . $userid . '">DEEP Selections</a>');
    echo html_writer::tag('dt', 'Evidence:');
    echo html_writer::tag('dd', '<img src="' . $CFG->wwwroot . '/pix/safe.png" /> <a href="' . $CFG->wwwroot . '/user/evidence.php?id=' . $userid . '&course=' . $courseid . '">National Standards Evidence Bank</a>');

    $eReporting = '<table><tr><td><img src="' . $CFG->wwwroot . '/pix/ereport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/allmygrades.php?user=' . $userid . '&view=current&type=ereport&course=' . $courseid . '" target="_blank">Current Year\'s eReport</a>';

    if (has_capability('moodle/grade:viewall', $usercontext)) {
        $eReporting .= '<br /><br /><form action="email_report.php?stuid=' . $userid . '&courseid=' . $courseid . '" method="post"><input type="Submit" value="Email Report" /></form>';
    }

    $countsql = "SELECT COUNT(mdl_grade_grades.id), mdl_grade_grades.id, mdl_grade_grades.rawscaleid, mdl_user.id FROM mdl_grade_grades INNER JOIN mdl_user ON mdl_grade_grades.userid = mdl_user.id WHERE mdl_user.id = ? AND mdl_grade_grades.rawscaleid = ?";

    $eReporting .= '</td></tr><tr><td><img src="' . $CFG->wwwroot . '/pix/pereport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/allmygrades.php?user=' . $userid . '&view=all&type=ereport&course=' . $courseid . '" target="_blank">Complete eReport</a></td></tr>';
    $ncea = $DB->count_records_sql($countsql, array($userid, '11'));
    $userroles = $DB->count_records_sql("SELECT COUNT(mdl_role_assignments.id) from mdl_role_assignments WHERE mdl_role_assignments.userid = ? AND mdl_role_assignments.roleid = ?", array($USER->id, '14'));
    if ($userroles > 0 AND $ncea > 0) { // if user is allowed to see NCEA report
        $eReporting .= '<tr><td><img src="' . $CFG->wwwroot . '/pix/pereport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/recordofachievement.php?user=' . $userid . '&view=all&type=ereport&course=' . $courseid . '" target="_blank">NCEA Record of Achievement</a></td></tr>';
    }

    $natstand = $DB->count_records_sql($countsql, array($userid, '8'));

    // only show National Standards link if reports exist
    if ($natstand > 0) {
        $eReporting.= '<tr><td><img src="' . $CFG->wwwroot . '/pix/natreport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/natstandards.php?user=' . $userid . '&view=current&type=natstandards&course=' . $courseid . '" target="_blank">Current Year\'s National Standards eReport (Year 7 and 8 only)<a/></td></tr><tr><td><img src="' . $CFG->wwwroot . '/pix/pnatreport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/natstandards.php?user=' . $userid . '&view=all&type=natstandards&course=' . $courseid . '" target="_blank">Complete National Standards eReport (Year 7 and 8 only)</a></td></tr>';
    }

    $esol = $DB->get_records_sql($countsql, array($userid, '8'));

    // only show ESOL link if reports exist
    if ($esol > 0) {
        $eReporting.= '<tr><td><img src="' . $CFG->wwwroot . '/pix/esolreport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/allmygrades.php?user=' . $userid . '&view=current&type=esol&course=' . $courseid . '" target="_blank">ESOL Report<a/></td></tr>';
    }

    $eReporting.= '<tr><td><img src="' . $CFG->wwwroot . '/pix/easttlereport.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/easttlegrades.php?user=' . $userid . '&course=' . $courseid . '" target="_blank">e-asTTle Results</a></td></tr><tr><td><img src="' . $CFG->wwwroot . '/pix/selfreflection.png" /></td><td><a href="' . $CFG->wwwroot . '/grade/reflection.php?user=' . $userid . '&view=current&course=' . $courseid . '" target="_blank">My Self Reflection</a></td></tr></table>';

    echo '<dt>Live eReporting:</dt>';
    echo '<dd>' . $eReporting . '</dd>';
}

/// Print users' timetable
$sql = "SELECT mdl_user.id, mdl_user_timetable.userid, mdl_user.idnumber, mdl_user_timetable.monday, mdl_user_timetable.tuesday, mdl_user_timetable.wednesday, mdl_user_timetable.thursday, mdl_user_timetable.friday FROM mdl_user_timetable INNER JOIN mdl_user ON mdl_user_timetable.userid = mdl_user.id WHERE mdl_user.id='" . $userid . "';";
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
            if ($cleansession[1] != '') {
                $mycourse = $DB->get_record('course', array('idnumber' => $cleansession[1]));

                if ($mycourse) { // output the full course name
                    $ttoutput .= "<a href='" . $CFG->wwwroot . "/course/view.php?id=" . $mycourse->id . "'>" . $mycourse->fullname . "</a><br />";
                } else {
                    $ttoutput .= $cleansession[1] . "<br />";
                }
            } else {
                $ttoutput .= "&nbsp;<br />";
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
if (has_capability('moodle/user:viewdetails', $usercontext) || $userid == $currentuser || has_coursecontact_role($userid)) {
    echo "<dt>My Timetable</dt><dd>";
    echo $ttoutput;
    echo "</dd>";
}

echo html_writer::end_tag('dl');

echo $OUTPUT->custom_block_region('content');

// Render custom blocks.
$renderer = $PAGE->get_renderer('core_user', 'myprofile');
$tree = core_user\output\myprofile\manager::build_tree($user, $currentuser);
echo $renderer->render($tree);

echo '</div>';  // Userprofile class.

echo $OUTPUT->footer();
