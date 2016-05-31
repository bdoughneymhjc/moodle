<?php
require_once('../config.php');
require_once('../course/lib.php');
require_once('../lib/gradelib.php');
require_once("$CFG->libdir/blocklib.php");
require_once("$CFG->dirroot/mod/assignment/lib.php");

function errormsg($message, $link = '') {
    global $CFG, $SESSION;

    print_header(get_string('error'));
    echo '<br />';

    $message = clean_text($message);

    print_simple_box('<span style="font-family:monospace;color:#000000;">' . $message . '</span>', 'center', '', '#FFBBBB', 5, 'errorbox');

    if (!$link) {
        if (!empty($SESSION->fromurl)) {
            $link = $SESSION->fromurl;
            unset($SESSION->fromurl);
        } else {
            $link = $CFG->wwwroot . '/';
        }
    }
    print_continue($link);
    print_footer();
    die;
}

require_login();
$currentuser = $USER->id;
// user is logged in as someone, lets see if it is a member of the SLT team

$sql = "SELECT mdl_role_assignments.id, mdl_role_assignments.roleid, mdl_role_assignments.userid from mdl_role_assignments WHERE mdl_role_assignments.userid=$currentuser AND mdl_role_assignments.roleid=10;";
$userroles = $DB->get_records_sql($sql);
if (!$userroles) {
    errormsg('You must be authorised to export grades.');
}

if (!$site = get_site()) {
    errormsg('Could not find site-level course');
}

$strchoose = get_string('choose');

$coursecontext = get_context_instance(CONTEXT_SYSTEM);
$PAGE->set_context($coursecontext);
$PAGE->set_url($CFG->wwwroot . "/grade/exportgrade.php");
$PAGE->set_title("Export Grades");
$PAGE->set_heading("Export Grades");

echo $OUTPUT->header();
?>
<table border="0" cellpadding="5" cellspacing="0" width="100%">
    <tr><td width="50%" align="center">
            <table border="0" cellpadding="0" cellspacing="0"><tr><td colspan="2">
                        This download takes a little while to process due to the large amount of marks in the system. Please only press the download button ONCE.
                        <form method="post" action="excelexport.php">
                            <?php
                            print "<h3>Grades to Download:</h3><br />";
                            print "<select name='whanau'>";
                            print "<option value='coast' selected='selected'>Coast (Current Year)</option>";
                            print "<option value='coasta'>Coast (All)</option>";
                            print "<option value='coastnat'>Coast National Standards (Current Year)</option>";
                            print "<option value='costnatp'>Coast National Standards (Previous Year)</option>";
                            print "<option value='coastnata'>Coast National Standards (All)</option>";
                            print "<option value='forest'>Forest (Current Year)</option>";
                            print "<option value='foresta'>Forest (All)</option>";
                            print "<option value='forestnat'>Forest National Standards (Current Year)</option>";
                            print "<option value='forestnatp'>Forest National Standards(Previous Year)</option>";
                            print "<option value='forestnata'>Forest National Standards (All)</option>";
                            print "<option value='water'>Water (Current Year)</option>";
                            print "<option value='watera'>Water (All)</option>";
                            print "<option value='waternat'>Water National Standards (Current Year)</option>";
                            print "<option value='waternatp'>Water National Standards (Previous Year)</option>";
                            print "<option value='waternata'>Water National Standards (All)</option>";
                            print "<option value='mountains'>Mountains (Current Year)</option>";
                            print "<option value='mountainsa'>Mountains (All)</option>";
                            print "<option value='mountainsnat'>Mountains National Standards (Current Year)</option>";
                            print "<option value='mountainsnatp'>Mountains National Standards (Previous Year)</option>";
                            print "<option value='mountainsnata'>Mountains National Standards (All)</option>";
                            print "<option value='all'>All Whanau (Current Year)</option>";
                            print "<option value='alla'>All Whanau (All)</option>";
                            print "<option value='natstandardsm'>National Standards Mathematics</option>";
                            print "<option value='natstandardsw'>National Standards Writing</option>";
                            print "<option value='natstandardsr'>National Standards Reading</option>";
                            print "<option value='natstandkamar'>National Standards KAMAR Export</option>";
                            print "<option value='natstandkamarp'>National Standards KAMAR Export Previous Year</option>";
                            print "</select>";
                            ?>
                    </td></tr>
                <tr><td>&nbsp;</td><td align="right"><br />
                        <input type="submit" value="Download" /></form>
                    </td></tr></table>
        </td><td align="left" valign="top">
        </td></tr>
</table>

<?php
/// Print MOL footer
echo $OUTPUT->footer();
?>