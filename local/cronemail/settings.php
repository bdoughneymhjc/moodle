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
 * Reminder plugin version information
 *
 * @package    local_cronemail
 * @copyright  2013 Ben Doughney
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    require_once($CFG->dirroot . '/local/cronemail/lib.php');

    $settings = new admin_settingpage('local_cronemail', get_string('admintreelabel', 'local_cronemail'));
    $ADMIN->add('localplugins', $settings);

    // load all roles in the moodle
    $systemcontext = context_system::instance();
    $allroles = role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL);
    $rolesarray = array();
    $roles = array();
    $rolesdefaults = array();
    if (!empty($allroles)) {
        foreach ($allroles as $arole) {
            //$rolesarray[$arole->shortname] = ' ' . $arole->localname;
            $roles[$arole->id] = $arole->localname;
            $rolesdefaults[$arole->id] = 0;
        }
    }

    // default settings for recieving reminders according to role
    // need to add parent here
    $defaultrolesforcourse = array('student' => 1);

    // adds a checkbox to enable/disable sending reminders
    $settings->add(new admin_setting_configcheckbox('local_cronemail_enable', get_string('enabled', 'local_cronemail'), get_string('enableddescription', 'local_cronemail'), 1));

    $settings->add(new admin_setting_configcheckbox('local_cronemail_emailparent', get_string('emailparent', 'local_cronemail'), get_string('emailparentdesc', 'local_cronemail'), 1));
    $settings->add(new admin_setting_configcheckbox('local_cronemail_emailstudent', get_string('emailstudent', 'local_cronemail'), get_string('emailstudentdesc', 'local_cronemail'), 1));
}