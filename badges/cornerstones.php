<?php

require_once('../config.php');

//check here that only users with the right permissions are allowed
if (has_any_capability(array(
            'moodle/badges:viewawarded',
            'moodle/badges:createbadge',
            'moodle/badges:manageglobalsettings',
            'moodle/badges:awardbadge',
            'moodle/badges:configurecriteria',
            'moodle/badges:configuremessages',
            'moodle/badges:configuredetails',
            'moodle/badges:deletebadge'), get_context_instance(CONTEXT_SYSTEM))) {

    $url = new moodle_url('/badges/cornerstones.php');
    $PAGE->set_url($url);
    $PAGE->set_context(get_context_instance(CONTEXT_SYSTEM)); //TODO: wrong
    $PAGE->set_pagelayout('calendar');
    $PAGE->set_title("Cornerstone Report");
    $PAGE->set_heading("Cornerstone Report");

    $currentuyear = strtotime("1 January " . date("Y"));

    // find all cornerstone badges issued in the specified year
    $sql = "SELECT mdl_badge_issued.id, mdl_badge_issued.badgeid, mdl_badge_issued.userid, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_badge.cornerstone, mdl_badge_issued.dateissued, SUM(mdl_badge.cornerstone='1') AS 'Academic', SUM(mdl_badge.cornerstone='2') AS 'Cultural', SUM(mdl_badge.cornerstone='3') AS 'Leadership', SUM(mdl_badge.cornerstone='4') AS 'Sports', SUM(mdl_badge.points) AS 'TotalPoints' from mdl_badge_issued INNER JOIN mdl_badge on mdl_badge_issued.badgeid = mdl_badge.id INNER JOIN mdl_user on mdl_badge_issued.userid = mdl_user.id group by mdl_badge_issued.userid having mdl_badge_issued.dateissued > '" . $ucurrentyear . "' order by TotalPoints DESC;";

    $cornerstones = $DB->get_records_sql($sql);

    echo $OUTPUT->standard_head_html();
    echo $OUTPUT->header();

    echo "<table>\n";
    echo "<tr><td><strong>First Name</strong></td><td><strong>Last Name</strong></td><td><strong>Class</strong></td><td><strong>Academic</strong></td><td><strong>Cultural</strong></td><td><strong>Leadership</strong></td><td><strong>Sports</strong></td><td><strong>Cornerstone Areas</strong></td>";

    foreach ($cornerstones as $cornerstone) {
        $cornerstoneareas = 0;
        $cornerac = 0;
        $cornercu = 0;
        $cornerle = 0;
        $cornersp = 0;
        if ($cornerstone->academic >= 1) {
            $cornerac = 1;
        }
        if ($cornerstone->cultural >= 1) {
            $cornercu = 1;
        }
        if ($cornerstone->leadership >= 1) {
            $cornerle = 1;
        }
        if ($cornerstone->sports >= 1) {
            $cornersp = 1;
        }
        $cornerstoneareas = $cornerac + $cornercu + $cornerle + $cornersp;
        echo "<tr><td>" . $cornerstone->firstname . "</td>";
        echo "<td>" . $cornerstone->lastname . "</td>";
        echo "<td>" . $cornerstone->department . "</td>";
        echo "<td>" . $cornerstone->academic . "</td>";
        echo "<td>" . $cornerstone->cultural . "</td>";
        echo "<td>" . $cornerstone->leadership . "</td>";
        echo "<td>" . $cornerstone->sports . "</td>";
        echo "<td>";
        if ($cornerstoneareas > 2) {
            echo "<b style='color: darkgreen;'>" . $cornerstoneareas . "</b>";
        } else {
            echo "<b style='color: grey;'>" . $cornerstoneareas . "</b>";
        }
        echo "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    echo $OUTPUT->footer();
}
?>
