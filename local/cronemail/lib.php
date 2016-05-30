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
DEFINE('ONE_DAYS', 1 * 24 * 3600);
DEFINE('TWO_DAYS', 2 * 24 * 3600);
DEFINE('THREE_DAYS', 3 * 24 * 3600);
DEFINE('FOUR_DAYS', 4 * 24 * 3600);
DEFINE('FIVE_DAYS', 5 * 24 * 3600);

function local_cronemail_cron() {
    global $CFG, $DB;

    $sturoleid = 5;

    if (!isset($CFG->local_cronemail_enable) || !$CFG->local_cronemail_enable) {
        mtrace("   [Cron Email] This cron cycle will be skipped, because plugin is not enabled!");
        return;
    } else {
        mtrace("   [Cron Email] Emailing notifications about assignments...");
    }
    if ($CFG->local_cronemail_emailparent) {
        $email_parents = TRUE;
        mtrace("   [Cron Email] Sending emails to parents...");
    }
    if ($CFG->local_cronemail_emailstudent) {
        $email_students = TRUE;
        mtrace("   [Cron Email] Sending emails to students...");
    }

    //$timenow = time();
    //set time to half past the hour
    $timenow = mktime(date("H"), 30, 0, date("n"), date("j"), date("Y"));

    $timestart = $timenow + THREE_DAYS;
    $timefinish = $timenow + THREE_DAYS + 3600; // time + 60 minutes

    $selectsql = "modulename LIKE 'assign%' AND timestart BETWEEN " . $timestart . " AND " . $timefinish . ";";
    $events = $DB->get_records_select('event', $selectsql);

    mtrace("   [Cron Email] Found " . count($events) . " assignments to send reminders about");

    foreach ($events as $event) {

        $course = $DB->get_record('course', array('id' => $event->courseid));

        if (!empty($course)) {
            $context = context_course::instance($course->id);
            $emailusers = get_role_users($sturoleid, $context, true, 'u.*');

            $assigndue = date("d-m-Y", $event->timestart);

            //double check users have not submitted work already
            foreach ($emailusers as $emailuser) {
                $email_recipients = array();

                $assignsql = "assignment=" . $event->instance . " AND userid=" . $emailuser->id . ";";
                $checksub = $DB->get_record_select('assign_submission', $assignsql);
                if (empty($checksub)) { // no assignment has been submitted
                    if ($email_students) {
                        $email_recipients[] = $emailuser->email;
                    }
                    if ($email_parents) {
                        $parentdetails = $DB->get_record('user', array('idnumber' => "p" . $emailuser->idnumber));
                        $email_recipients[] = $parentdetails->email;
                    }

                    mtrace("   [Cron Email] Emailing " . $emailuser->firstname . " " . $emailuser->lastname) . " at " . $parentdetails->email . " (assignment due).";

                    $mail = new PHPMailer();
                    $mail->isHTML(true);
                    $mail->From = "noreply@mhjc.school.nz";
                    $mail->FromName = "Mission Heights Online";

                    foreach ($email_recipients as $recipient) {
                        $mail->AddAddress($recipient);
                    }

                    $mail->Subject = "Assignment: \"" . $event->name . "\" Due Soon";

                    $ebody = "<html>\n<head></head>\n";
                    $ebody .= "<body style='width: 21cm; margin-left: 1cm; margin-right: 1cm; margin-top: 1cm; font-family: \"Century Gothic\", Helvetica, \"Helvetica Neue\", Arial, sans-serif;'>\n";
                    $ebody .= "<h3>Assignment \"" . $event->name . "\" Due Soon</h3>";
                    $ebody .= "<p>This is an automated email from Mission Heights Online. You are receiving this ";
                    $ebody .= "message because no work has been submitted for the " . $event->name . " assignment. This work is due ";
                    $ebody .= "on " . $assigndue . ".</p>";
                    $ebody .= "</body></html>";

                    $plainbody = "Assignment \"" . $event->name . "\" Due Soon\n";
                    $plainbody .= "This is an automated email from Mission Heights Online. You are receiving this ";
                    $plainbody .= "message because no work has been submitted for the " . $event->name . " assignment. This work is due ";
                    $plainbody .= "on " . $assigndue . ".</p>";

                    $mail->Body = $ebody;
                    $mail->AltBody = $plainbody;
                    $mail->WordWrap = 50;

                    if (!$mail->Send()) {
                        $badrecipients = implode(";", $email_recipients);
                        mtrace("    [Cron Email] Error sending email to " . $badrecipients);
                    } else {
                        mtrace("    [Cron Email] Email sent");
                    }
                }
            }
        }
    }
    // send out messages for assignments just created in the last hour
    $timestart = $timenow - 3600; // time - 1 hour
    $timefinish = $timenow;

    $neweventssql = "SELECT mdl_assign.id as asid, mdl_event.name, mdl_event.timestart, mdl_assign.intro, mdl_assign.course AS courseid FROM mdl_event INNER JOIN mdl_assign ON mdl_event.instance = mdl_assign.id WHERE mdl_event.modulename LIKE 'assign%' AND mdl_assign.allowsubmissionsfromdate BETWEEN " . $timestart . " AND " . $timefinish . ";";
    $newevents = $DB->get_records_sql($neweventssql);

    mtrace("   [Cron Email] Found " . count($newevents) . " new assignments to send reminders about");

    foreach ($newevents as $newevent) {
        $course = $DB->get_record('course', array('id' => $newevent->courseid));

        if (!empty($course)) {
            $context = context_course::instance($course->id);

            $cm = get_coursemodule_from_instance('assign', $newevent->asid, $newevent->courseid);
            $assigncontext = context_module::instance($cm->id);
            //$assigncontext = get_context_instance(CONTEXT_MODULE, $cm->id);

            $nemailusers = get_role_users($sturoleid, $context, true, 'u.*');

            $assigndue = date("d-m-Y", $newevent->timestart);

            foreach ($nemailusers as $nemailuser) {
                $nemail_recipients = array();

                if ($email_students) {
                    $nemail_recipients[] = $nemailuser->email;
                }
                if ($email_parents) {
                    $nparentdetails = $DB->get_record('user', array('idnumber' => "p" . $nemailuser->idnumber));
                    $nemail_recipients[] = $nparentdetails->email;
                }
                mtrace("   [Cron Email] Emailing " . $nemailuser->firstname . " " . $nemailuser->lastname) . " at " . $nparentdetails->email . " (new assignment).";

                $nmail = new PHPMailer();
                $nmail->isHTML(true);
                $nmail->From = "noreply@mhjc.school.nz";
                $nmail->FromName = "Mission Heights Online";

                foreach ($nemail_recipients as $nrecipient) {
                    $nmail->AddAddress($nrecipient);
                }

                $nmail->Subject = "New Assignment: \"" . $newevent->name . "\" Created on Mission Heights Online";

                $ebody = "<html>\n<head></head>\n";
                $ebody .= "<body style='width: 21cm; margin-left: 1cm; margin-right: 1cm; margin-top: 1cm; font-family: \"Century Gothic\", Helvetica, \"Helvetica Neue\", Arial, sans-serif;'>\n";
                $ebody .= "<h3>New Assignment: \"" . $newevent->name . "\" Created on Mission Heights Online</h3>";
                $ebody .= "<p>This is an automated email from Mission Heights Online. You are receiving this ";
                $ebody .= "message because a new assignment has been set called " . $newevent->name . ". This work is due ";
                $ebody .= "on " . $assigndue . ".</p>";
                $ebody .= "<p>For more details about this assignment, please visit this link;<br />";
                $ebody .= "<a href='http://online.mhjc.school.nz/course/view.php?id=" . $newevent->courseid . "#section-" . $cm->section . "'>" . $newevent->name . "</a></p>";
                $ebody .= "<p>You will need to log on to Mission Heights Online to view the contents of this link.</p>";
                $ebody .= "</body></html>";

                $nmail->Body = $ebody;
                //$mail->AltBody = $plainbody;
                $nmail->WordWrap = 50;

                if (!$nmail->Send()) {
                    $badrecipients = implode(";", $nemail_recipients);
                    mtrace("    [Cron Email] Error sending email to " . $badrecipients);
                } else {
                    mtrace("    [Cron Email] Email sent");
                }
            }
        }
    }
}
