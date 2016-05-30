<?php

require_once('../config.php');
require_once('../course/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/pear/HTML/QuickForm/input.php');

$PAGE->set_url($CFG->wwwroot . "/DEEP/booklet.php");

//$terms = explode(",", $DB->get_field('config', 'value', array('name' => "deep_manage_terms")));
$year = $DB->get_field('config', 'value', array('name' => "deep_year")); // current year for deep
$term = $DB->get_field('config', 'value', array('name' => "deep_current_term")); // current term for deep

$deepdays = $DB->get_records_sql("SELECT mdl_deep_days.id, mdl_deep_days.dayname FROM mdl_deep_days;");

$userid = optional_param('userid', 0, PARAM_INT);

echo "<html>";
echo "<head>";
header('Content-Type: text/html; charset=utf-8');
echo "<title>DEEP Options for " . $year . "</title>";
?>
<style type="text/css">
    <!--
    body {
        font-family: "Century Gothic", "Century Gothic", Helvetica, "Helvetica Neue", Arial, sans-serif; }

    h2 {
        font-size: 40px;
    }

    h3 {
        font-size: 30px;
    }

    table.bdr td h4 {
        padding: 5px;
    }

    table.bdr {
        border: thin solid black;
        border: none;
        border-spacing: 0px;
        width: 100%; }

    table.bdr td {
        border-bottom: 1px solid black;
        border-left: 1px solid black;
        border-right: 1px solid black;
        vertical-align: top;
        padding: 5px;
    }

    table.bdr td b {
        padding: 5px;
        font-size: larger;
    }

    table.bdr td p {
        padding-bottom: 20px;
        padding-left: 5px;
        padding-right: 5px;
    }

    table.bdr td p span {
        color: red;
        font-style: italic;
    }

    table.bdr td i {
        color: darkslategrey;
        padding: 5px;
    }
    --> 
</style>
<?php

echo "</head>";
echo "<body>";
// start of layout table
echo "<table width='100%'>\n";
echo "<tr><td style='padding-left: 40px;'><h2>Mission Heights Junior College</h2><h3>" . $year . " DEEP Options</h3></td><td align='right'><img src='http://online.mhjc.school.nz/file.php/1/logo/MIS_Junior_College_Logo.png' width='337' height='132' /></td></tr>\n";
//echo "<tr><td>Jump to Term: ";
//foreach ($terms as $term) {
//    echo "<a href='#" . $term . "'>" . $term . "</a> ";
//}
echo "<br /><br /></td></tr>\n";
echo "<tr><td colspan='2'>Please read through the DEEP descriptions below for " . $year . ". The DEEP courses are listed in Terms, by category (Discovery, Enrichment, Essentials, Passions and Sports), and then by alphabetical order.<br /><br /></td></tr>\n";
echo "<tr><td colspan='2' style='text-align: center;'>";

$deepcats = $DB->get_records_sql("SELECT * from mdl_deep_category ORDER BY mdl_deep_category.catname;");

//foreach ($terms as $term) {
//output header line of table
echo "<table class='bdr'>\n";
echo "<tr><td style='background: black; color: white;'><b>Term " . $term . " Options</b><a id='" . $term . "'></a></td></tr>\n";
foreach ($deepdays as $deepday) {
    echo "<tr><td style='background: black; color: white;'><b>" . $deepday->dayname . "</b></td></tr>\n";
    foreach ($deepcats as $category) {
        $sql = "SELECT mdl_deep_class.id, mdl_deep_class.name, mdl_deep_class.descr, mdl_deep_class.day, mdl_deep_class.code, mdl_deep_class.category, mdl_deep_class.term, mdl_deep_class.year, mdl_deep_class.cost, mdl_deep_class.classsize, mdl_deep_days.id as dayid, mdl_deep_days.dayname, mdl_deep_category.catname, mdl_deep_category.colour from mdl_deep_class INNER JOIN mdl_deep_days ON mdl_deep_class.day = mdl_deep_days.id INNER JOIN mdl_deep_category ON mdl_deep_class.category = mdl_deep_category.id WHERE mdl_deep_class.term = ? AND mdl_deep_class.year = ? AND mdl_deep_class.category = ? AND mdl_deep_class.day = ? ORDER BY mdl_deep_class.day, mdl_deep_class.category, mdl_deep_class.name;";
        $deepsubjects = $DB->get_records_sql($sql, array($term, $year, $category->id, $deepday->id));
        echo "<tr><td style='background: " . $category->colour . "; text-align: center;'><b>" . $category->catname . "</b></td></tr>\n";
        echo "<tr><td style='background: lightgrey;'><i>" . $category->description . "</i></td></tr>\n";

        foreach ($deepsubjects as $subject) {
            echo "<tr><td>";
            echo "<h4 style='background: " . $category->colour . "'>" . $subject->name . " (" . $subject->code . $term . ")</h4>";
            echo "<p>";
            if ($subject->cost != 0) {
                echo "<span>There is a charge of $" . $subject->cost . " for this course.</span><br />";
            }
            echo $subject->descr . "</p>";
            echo "</td></tr>\n";
        }
    }
}
echo "</table>\n";
//}
echo "</td></tr>\n";
echo "</table>\n";
echo "</body>\n";
echo "</html>";
?>