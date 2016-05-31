<?php

require_once("../config.php");
require_once("../lib/gradelib.php");
require_once("$CFG->dirroot/mod/assignment/lib.php");

$currentuser = required_param('user', PARAM_INT);  // user id
$viewtype = required_param('view', PARAM_TEXT); // view type
$courseid = optional_param('course', SITEID, PARAM_INT); // added to allow teachers etc viewing
$pdf = optional_param('pdf', 0, PARAM_BOOL);

$idnatstand = 8; // national standards scale id

if (empty($currentuser)) {            // See your own profile by default
    require_login();
}

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$coursecontext = context_course::instance($course->id);
$usercontext = context_user::instance($currentuser, MUST_EXIST);

if (has_capability('moodle/grade:view', $usercontext) || $currentuser == $USER->id || has_capability('moodle/grade:view', $coursecontext) || $pdf == TRUE) {
    $sql = "SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.timemodified, mdl_grade_grades.feedback, mdl_grade_grades.finalgrade, mdl_grade_grades.usermodified, mdl_grade_grades.rawscaleid, mdl_grade_grades.usermodified AS teacher, mdl_grade_items.itemname, mdl_grade_items.itemmodule, mdl_grade_items.iteminstance, mdl_course.fullname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id HAVING ( rawscaleid = '" . $idnatstand . "' AND mdl_grade_grades.userid = '" . $currentuser . "' AND mdl_grade_grades.finalgrade > 0) ORDER BY mdl_course.fullname, mdl_grade_grades.timemodified;";
    $scalesql = "SELECT mdl_scale.id, mdl_scale.scale FROM mdl_scale WHERE mdl_scale.id=" . $idnatstand;
} else {
    print_error('cannotviewprofile');
}

$PAGE->set_context($coursecontext);

$stuinfo = $DB->get_records_sql($sql);
$scalesrec = $DB->get_record_sql($scalesql);

$scaleitem = explode(", ", $scalesrec->scale);

$studentdetails = $DB->get_record('user', array('id' => $currentuser));
$studentname = fullname($studentdetails);

$studentyear = substr($studentdetails->department, 0, 1);

switch ($studentyear) {
    case '7':
        $hovertext = "<b>National Standards, Year 7 and 8</b><br /><br />";
        $hovertext .= "<b>Students progress towards National Standards is reported twice in a year.</b><br /><br />";
        $hovertext .= "<b>Mid year results</b> should be considered only as an indicator of where the student is ";
        $hovertext .= "<b>currently</b> achieving in relation to the standard for their year level. <b>It is not ";
        $hovertext .= "a final assessment.</b><br /><br />";
        $hovertext .= "The End of year Report shows your child's achievement in relation to the standards for ";
        $hovertext .= "their current year, (eg Year 7 or Year 8).<br /><br />";
        $hovertext .= "The report allows you to see how your child is progressing from one year to the next.<br /><br />";
        $hovertext .= "To understand what 'next learning' will assist your child to progress against the National ";
        $hovertext .= "Standards, click on the characteristics of the standards below the subject (the italic text).";
        $subjecttext['Writing'] = "<b>Writing</b><br />By <b>the end of Year 7</b> (Working towards Curriculum Level 4) to meet the Year 7 Standard, $studentname will be able to;<ul><li>write for particular audiences and purposes</li><li>use appropriate structures</li><li>use clear and logical structures</li><li>use paragraphs that link main ideas and supporting details, within and between paragraphs</li><li>write grammatically correct sentences using a range of language features and complex punctuation; eg rhetorical questions, metaphors, semicolons</li><li>use correctly- spelt words, using a range of strategies. For example, letter-sound knowledge, spelling rules and conventions, meaning and spelling of word parts and word origins, letter patterns</li><li>carefully plan his/her writing projects using a variety of strategies</li><li>show that he/she is revising and editing as well as proof-reading as he/she writes.</li></ul><i><b>Writing at Home</b><br /><br />You can make writing fun by;<ul><li>encouraging your child to listen for and use interesting words. Having a wide range of words will help your child create stories which will increase in complexity.</li><li>using technology; text messages and emails are a form of writing even if the language is not always standard English.</li><li>encouraging your child to use the computer if he/she is not keen on writing. They don’t have to think about the presentation of their work and editing does not require a complete re-write. Spell-check helps, too.</li><li>creating a message board. This could be done with magnetic letters and words on the fridge, or a whiteboard/pinboard. The messages might be instructions, reminders or praise for a job well done, as well as examples of work. Encourage your child and other family members to respond with messages, too.</li></ul>Talk about writing with your child;<br /><ul><li>talk with your child about their day. Talking helps to organise your thinking and is an important first step for any writing.</li><li>talk about new words your child is not familiar with, using a dictionary to find out more; there are dictionaries online.</li><li>be a positive audience for your child. Always respond to the effort behind the message and the message content first (regardless of how the message is written) and the presentation second. Keep in mind what your child is currently learning to do and comment just on that.</li></ul>Keep them interested;<br /><ul><li>encourage your child to read. Reading and writing are linked and success in one is likely to lead to success in the other.</li><li>buy interesting stationery for your child to use. Coloured pens and pencils can be an incentive to write, together with special paper or books. Give a diary, book or notebook as a present.</li><li>look for real reasons for writing. Encourage your child to read and write letters, messages, postcards, invitations, lists, rosters, thank-you notes, recipes, emails. Start with postcards to family and friends, if your child only wants to write a few sentences, encourage your family to write back.</li></ul>TIP: Be a role model. Show your child that you write for lots of reasons, eg replying to an email, writing a shopping list, invitation or letter, writing for your work or your own study. Use your first language – this helps your child’s learning, too.</i>";
        $subjecttext['Reading'] = "<b>Reading</b><br />By <b>the end of Year 7</b> (Working towards Curriculum Level 4) to meet the Year 7 Standard, $studentname will be able to;<ul><li>recognise most words automatically and work out more difficult words using a range of strategies. For example, letter-sound knowledge, inferring meaning from what he/she already knows about parts of words and letter patterns</li><li>select and use reading strategies, to help them understand what they’re reading</li><li>recognise and use features of grammar to support understanding of more difficult words</li><li>read a wide range of stories including both fiction and non-fiction, and with a number of layers of meaning including complicated plots, difficult themes and ideas</li><li>use his/her judgment to work out a personal response to what he/she is reading</li><li>think about the strengths and weaknesses of what he/she are reading, using a wide range of information.</li></ul><i><b>Reading at Home</b><br /><br />You can make reading fun by;<ul><li>playing card and board games and solving complicated puzzles.</li><li>helping your child to follow a recipe and cook for your family.</li></ul>Read to your child<ul><li>just because your child can read, doesn't mean that they don't enjoy listening to someone else reading. It could be a non-fiction book on a topic that they like, a magazine, a newspaper, a short story or a longer book read a chapter at a time. It could also be a more difficult book or article that your child needs your help to understand.</li><li>encourage your child to read the lyrics to their favourite songs. Talk about why the artist wrote the song. What were they trying to say? Search the Internet for more information.</li></ul>Keep them interested<br /><ul><li>find books or magazines about your child’s interests. Reading about their favourite sport, player, team or an issue they are interested in will help them to be an expert on a particular subject.</li><li>join the Botany or Manukau library (it is free to do so) and visit regularly to help your child choose books that interest them – you may want to encourage your child to read different types of books including non-fiction stories.</li><li>encourage your child to complete three 45 minute sessions of Reading Plus each week. Reading Plus is our online programme which has been proven to produce substantial gains in reading rate and comprehension for students.</li></ul>TIP: Be positive whenever your child is reading, no matter what they are reading. Respect your child’s opinion as it shows they are thinking about what they read.</i><br />";
        $subjecttext['Mathematics'] = "<b>Mathematics</b><br />By <b>the end of Year 7</b> (Working towards Curriculum Level 4) to meet the Year 7 Standard $studentname will be able to;<ul><li>solve problems involving decimals and fractions, using addition and subtraction</li><li>use a range of multiplication methods to solve problems using whole numbers  fractions  percentages and ratios</li><li>create tables, graphs and rules for repeating patterns</li><li>investigate and justify mathematical rules to see if they are always true</li><li>identify and describe how objects have been moved within patterns</li><li>measure time and objects using standard measures</li><li>sort 2D and 3D shapes and discuss their similarities and differences</li><li>find perimeters, areas and volumes of shapes</li><li>use grid references, simple scales, and points of the compass to describe a given location</li><li>investigate, sort and display information in different ways and identify patterns and variations in the information</li><li>explore probability through experimenting and comparing actual results with expected results</li></ul><i><b>Mathematics at Home</b><br /><br />Talk together and have fun with numbers and patterns<ul><li>talk about sales in town – 25% off, 30%, 10%, half price. Look for the best value and make a game of calculating the savings on items your child is interested in.</li><li>talk about current prices for items that interest your child and investigate which store offers the best price.</li></ul>Use Mathletics;<ul><li>encourage your child to use Mathletics 3 to 5 times each week.</li><li>encourage a balance between the Full Curriculum section and Live Mathletics.</li><li>encourage your child to do activities that challange, rather than those he/she finds easy.</li></ul>Involve your child in;<ul><li>cooking – explore recipes and amounts of food and costs within a budget.</li><li>investigating which supermarket offers the best deal on petrol eg 4 cents off a litre.</li></ul>For wet afternoons/school holidays/weekends;<ul><li>play games – find a new board or card game that uses strategy.</li><li>play outdoor and indoor games – frisbee, touch rugby, netball, kilikiti, cricket, soccer, bowls, snooker and darts.</li></ul>TIP: The way your child is learning to solve mathematics problems may be different to when you were at school. Get them to show you how they do it and support them in their learning.</i>";
        break;
    case '8':
        $hovertext = "<b>National Standards, Year 7 and 8</b><br /><br />";
        $hovertext .= "<b>Students progress towards National Standards is reported twice in a year.</b><br /><br />";
        $hovertext .= "<b>Mid year results</b> should be considered only as an indicator of where the student is ";
        $hovertext .= "<b>currently</b> achieving in relation to the standard for their year level. <b>It is not ";
        $hovertext .= "a final assessment.</b><br /><br />";
        $hovertext .= "The End of year Report shows your child's achievement in relation to the standards for ";
        $hovertext .= "their current year, (eg Year 7 or Year 8).<br /><br />";
        $hovertext .= "The report allows you to see how your child is progressing from one year to the next.<br /><br />";
        $hovertext .= "To understand what 'next learning' will assist your child to progress against the National ";
        $hovertext .= "Standards, click on the characteristics of the standards below the subject (the italic text).";
        $subjecttext['Writing'] = "<b>Writing</b><br />By <b>the end of Year 8</b> (Curriculum Level 4) to meet the Year 8 Standard, $studentname will be able to;<ul><li>write on his/her own, choosing language and a clear and logical structure that fits the purpose for writing</li><li>use information he/she has found through reading, to write in his/her own words about a topic or theme</li><li>write stories that are clear, short and fit the curriculum task – often including detail and/or comment supporting or explaining the main points</li><li>use words and phrases that fit the topic, audience, occasion and purpose</li><li>understand that he/she is writing for a variety of purposes and knows how to do this</li><li>create paragraphs where the ideas are clearly related and link to other paragraphs</li><li>use complex sentences that are grammatically correct</li><li>use basic punctuation correctly and attempt to use some complex punctuation; eg semicolons, colons, brackets</li><li>carefully plan his/her writing projects using a variety of strategies</li><li>show that he/she is revising and editing as well as proof-reading as he/she writes</li></ul><i><b>Writing at Home</b><br /><br />Help your child to;<ul><li>write a letter or an email to a newspaper editor, radio announcer or television broadcaster sharing your child’s opinion on a topic of interest in the news.</li><li>write a comic using drawings and graphic design to present an idea or story.</li></ul>Get together with your child to;<ul><li>play strategy games and solve word puzzles like wordfinds and crosswords.</li><li>make the weekly shopping list using supermarket flyers and finding all the bargains and savings to fit the budget.</li><li>write some descriptions for items you may wish to sell on the internet.</li></ul>Talk about writing with your child<ul><li>ask them about a piece of writing they are doing at school and/or for their homework.</li><li>tell them about some writing you are currently doing – a letter, a poem, a list for the holidays, a scrapbook, something you are doing for work or study.</li><li>help them to use dictionaries and thesaurus (both paper and Internet versions).</li></ul>TIP: Talk about what your child writes. Be interested. Use it as a way of starting conversations. Listen to your child's opinion, even if you don’t agree with it.</i>";
        $subjecttext['Reading'] = "<b>Reading</b><br />By <b>the end of Year 8</b> (Curriculum Level 4) to meet the Year 8 Standard, $studentname will be able to;<ul><li>use a growing range of strategies to help when he/she doesn't understand the meaning of the text</li><li>work out more difficult words by using a range of skills</li><li>read books with pictures, photos, boxes, diagrams, maps, charts, and graphs that relate to the story’s content</li><li>read books that need explanation, such as complicated plots, high-level (teenage) themes, and abstract (non concrete) ideas</li><li>choose the right skills and technologies (eg the Internet) to locate and use a range of stories for particular purposes across the curriculum; eg reading to find out information on a science or social studies topic</li><li>use his/her judgement to work out a personal response to what he/she is reading</li><li>think about the strengths and weaknesses of what he/she is reading, using a wide range of information</li></ul><i><b>Reading at Home</b><br /><br />Support your childs learning by;<ul><li>encourage your child to complete three 45 minute sessions of Reading Plus each week. Reading Plus is our online programme which has been proven to produce substantial gains in reading rate and comprehension for students.</li><li>helping your child gather newspaper, magazine and journal articles for a topic of interest at school.</li><li>talking to your child about what they have been reading on the Internet. What have they learnt? What questions do they still have? Where else could they find information that would be useful?</li><li>reading through your child’s homework tasks and assignments on Mission Heights Online together and talk about what they are planning to do to finish the work.</li></ul>Read togther<ul><li>read your child a children’s novel that they are interested in – try one or two chapters each day.</li><li>listen together to CDs and mp3s of your child’s favourite stories, books and songs.</li></ul>Hunt out things to read<ul><li>take a trip to the library and help your child to find books, audio books and magazines that they will enjoy reading – eg books about their favourite musician, movie star, sports celebrity or other role model.</li><li>use a map to find directions for a trip you are going on, or follow the journeys of people on travel and adventure programmes.</li><li>find some recipes together that your child might like to cook for a family treat. Be there to help your child as they read through the recipe, get all the ingredients and create the final result.</li></ul>TIP: Be a role model. Let your child see you enjoying reading – whether it’s the newspaper, a magazine, a comic, a cook book or a novel. Read magazines, newspapers and books in your first language.</i>";
        $subjecttext['Mathematics'] = "<b>Mathematics</b><br />By <b>the end of Year 8</b> (Curriculum Level 4) to meet the Year 8 Standard, $studentname will be able to;<ul><li>solve problems with decimals and integers using addition and subtraction</li><li>use a range of multiplication methods to solve problems with whole numbers and fractions</li><li>create and use tables, graphs and rules to show linear and non-linear relationships</li><li>measure objects and make simple conversions between units of measure</li><li>find perimeters and areas of rectangles, parallelograms, triangles and volumes of cuboids</li><li>explore transformation and discuss how shapes and patterns change after a transformation</li><li>describe locations and give directions using scales, bearings and co-ordinates</li><li>gather and use data that gives several pieces of information (eg age and size)</li><li>sort data and display in different ways, and discuss patterns and trends</li><li>use fractions to discuss the likelihoods of outcomes involving chance</li></ul><i><b>Mathematics at Home</b><br /><br />Talk together and have fun with numbers and patterns<ul><li>find and connect numbers around your home and on family outings, e.g., read the odometer on the car to see how many kilometres the car can go on a tank of petrol and how much it costs to refill to work out how much it costs per kilometre.</li><li>talk about sales in town – 15% off, 33%, 20%, half price. Look for the best value. What would the price of the item be after the discount? Is it better to buy two items and get one free or get 25% off the price of the items?</li><li>talk about goals and plan ahead to budget their pocket money for items that they want to buy.</li></ul>Involve your child in;<ul><li>planning to help make a dish or a full meal for the family or even a community event, working out the cost of making it at home versus buying it already made, planning the preparation and cooking time and focus on the ingredients and the amounts of fat and sugar, too.</li><li>watching documentaries, which are full of facts and information using mathematics.</li></ul>For wet afternoons/school holidays/weekends<ul><li>play games – find new card and board games that use strategy.</li><li>calculate the chance of their favourite team winning the tournament. Investigate how many points they need and work out what their competitors need as well.</li><li>play outdoor games – skateboarding, frisbee, touch rugby, kilikiti, cricket, soccer, petanque, netball.</li></ul>TIP: Being positive about mathematics is really important for your child’s learning – even if you didn’ t enjoy it or do well at it yourself at school.</i>";
        break;
    default:
        $hovertext = "Nationally all students are assessed against National Standards at the end of each year up to Year 8. <br /><br />";
        $hovertext .= "Our school reports against the National Standards twice each year for reading, writing and mathematics. These reports will, over time, show your child's progress and achievement over Years 7 and Year 8 and to gain the greatest benefit from them they should seen as a continuing record.<br /><br />";
        $hovertext .= "This eReport outlines for you the 'characteristics' of the National Standards your child needs to be developing during the year ahead.  These will be discussed also at the first student led conference.";
        $hovertext .= "The end of year report shows your child's progress and achievement in relation to the standards in the current year.";
        $hovertext .= " To understand what 'next learning' is needed for your child to progress against the national standards refer to the characteristics of the standards below. These form part of this report. They tell you in plain language the learning goals for children according to their year level.";
}

// these arrays are to clean the html comments and get rid of the funny characters

$find[] = 'â€œ';  // left side double smart quote
$find[] = 'â€';  // right side double smart quote
$find[] = 'â€˜';  // left side single smart quote
$find[] = 'â€™';  // right side single smart quote
$find[] = 'â€¦';  // elipsis
$find[] = 'â€”';  // em dash
$find[] = 'â€“';  // en dash
$find[] = '“';
$find[] = '”';
$find[] = 'é';
$find[] = 'É';
$find[] = '•';
$find[] = '–';
$find[] = 'Â·';
$find[] = 'Â';

$replace[] = '"';
$replace[] = '"';
$replace[] = "'";
$replace[] = "'";
$replace[] = "...";
$replace[] = "-";
$replace[] = "-";
$replace[] = '"';
$replace[] = '"';
$replace[] = 'e';
$replace[] = 'E';
$replace[] = '-';
$replace[] = '-';
$replace[] = ''; //'&#8226;';
$replace[] = '';

print "<!DOCTYPE HTML>\n";
print "<html>\n";
print "<head>\n";
header('Content-Type: text/html; charset=utf-8');
print "<title>Live National Standards eReport for " . $studentname . "</title>";
?>
<style type='text/css'>
    <!--
    body {
        font-family: 'Century Gothic', Helvetica, 'Helvetica Neue', Arial, sans-serif;}

    div#links a {
        color: #00626B;
        font-weight: bold;
        text-decoration: none; }

    div#links a span {
        text-decoration: none;
        display: none; }

    div#links a:hover span {
        display: block;
        padding: 5px;
        margin: 10px;
        z-index: 100;
        text-decoration: none;
        border: thin solid black;
        color: black;
        font-size: 1em;
        font-weight: normal; }

    div#greentext {
        color: #00626B;
        font-weight: bold; }

    div#datetext {
        color: #00626B;
        font-style: italic;
        font-size: smaller; }

    div#clickhere {
        font-weight: lighter;
        font-style: italic;
        font-size: x-small; }

    div#helptext {
        color: #00626B;
        text-decoration: none;
        font-size: smaller; }

    div#close {
        float: right;
        text-align: right;
        font-weight: bold; }

    table#bdr {
        border-spacing: 0px;
        width: 100%; }

    table#bdr td {
        border: thin solid black;
        vertical-align: top;
        padding: 3px;
        font-size: 0.8em; }

    td.highlight {
        text-align: center;
        font-weight: bold;
        color: white; }

    div.Mathematics {
        background-color: rgba(255, 255, 255, 0.9);
        color: #80002A;
        width: 100%;
        padding-top: 5px;
        padding-bottom: 5px;
        margin-top: 2px;
        margin-bottom: 2px;
    }

    div.Reading {
        background-color: rgba(255, 255, 255, 0.9);
        color: #41999D;
        width: 100%;
        padding-top: 5px;
        padding-bottom: 5px;
        margin-top: 2px;
        margin-bottom: 2px;
    }

    div.Writing {
        background-color: rgba(255, 255, 255, 0.9);
        color: #304E66;
        width: 100%;
        padding-top: 5px;
        padding-bottom: 5px;
        margin-top: 2px;
        margin-bottom: 2px;
    }

    td.highlight a {
        text-decoration: none;
        color: white; }

    td.highlight span.PopUp {
        display: none;
        text-align: left;
        width: 80%;
        padding: 5px;
        margin: 10px;
        z-index: 100;
        text-decoration: none;
        border: thin solid black;
        position: absolute; top: 100px; left: 10%;
        background-color: #EEEEEE;
        color: black;
        font-size: 0.8em;
        font-weight: normal; }

    td.highlight span.PopUp a {
        color: black;
        text-align: right; }

    td.nohighlight {
        text-align: center;
        background-color: #DDDDDD;
        color: #999999; }

    td.current {
        text-align: center;
        background-color: #DDDDDD; }

    td.greyd, td.greyd div#greentext, td.greyd div#datetext {
        text-align: center;
        background-color: #DDDDDD;
        color: #CCCCCC; }

    td.first {
    }

    td.above {
        background-color: #00626B;
        color: white;
    }

    td.at {
        background-color: #62878A;
        color: white;
    }

    td.below {
        background-color: #C2C8C8;
    }

    td.wellbelow {
        background-color: #FAFAFA;
    }

    .hide {
        display: none; }
    -->
    <?php

    print "</style>\n";
    print "</head>\n";
    print "<body>\n";

    /* BEGIN MAIN LAYOUT TABLE */
    print "<table border='0' cellpadding='5' cellspacing='0' width='90%' align='center'>";
    print "<tr>";
    print "<td><h3>Live National Standards eReport for</h3><h2>" . $studentname . ", " . $studentdetails->department . "</h2></td>";
    print "<td>&nbsp;</td>";
    print "<td align='right'><img src='http://online.mhjc.school.nz/file.php/1/logo/MIS_Junior_College_Logo.png' width='337' height='132' /></td>";
    print "</tr>\n";

    print "<tr>";
    print "<td colspan='3'><div id='helptext'>$hovertext</div></td>";
    print "</tr>\n";

    print "<tr>";
    print "<td>";
    if ($pdf === 0) {
        print "<a href='" . $CFG->wwwroot . "/grade/exporttopdf.php?user=" . $currentuser . "&view=" . $viewtype . "&course=" . $courseid . "&referrer=natstandards'>Download PDF version</a>";
    }
    print "&nbsp;</td>";
    print "<td>&nbsp;</td>";
    print "<td align='right'><i>" . date("jS F, Y") . "</i></td>";
    print "</tr>\n";

    /* MAIN LAYOUT TABLE REPORT CELL */
    print "<tr><td colspan='3'>";

    /* BEGIN REPORT LAYOUT TABLE */
    print "<table border='0' cellpadding='5' cellspacing='0' width='100%'>\n";

//blank temp class name
    $tempclassname = '';
    $natmarks = array('above' => NULL, 'at' => NULL, 'below' => NULL, 'well below' => NULL);

    foreach ($stuinfo as $row) {
        $roundedgrade = round($row->finalgrade) - 1;
        $studentmark_task = strtolower($scaleitem[$roundedgrade]);

        $dategraded = date("d-m-Y", $row->timemodified);
        $yeargraded = date("Y", $row->timemodified);
        $uyeargraded = $row->timemodified;
        $currentyear = date("Y");

        echo "<!-- \n";
        echo "Name: " . $row->itemname . "\n";
        echo "Date: " . $dategraded . "\n";
        echo "UYear: " . $uyeargraded . "\n";
        echo "Grade: " . round($row->finalgrade) . "\n";
        echo "Mark: " . $studentmark_task . "\n";
        echo "-->\n";

        $osql = "SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) as roundedgrade, mdl_grade_grades.usermodified, mdl_grade_items.itemmodule, mdl_grade_items.iteminstance, mdl_course.fullname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id where mdl_grade_grades.userid = '" . $currentuser . "' AND ROUND(mdl_grade_grades.finalgrade) = '" . round($row->finalgrade) . "' AND mdl_grade_items.itemmodule = '" . $row->itemmodule . "' AND mdl_grade_items.iteminstance = '" . $row->iteminstance . "' AND mdl_grade_grades.rawgrade IS NULL ORDER BY mdl_course.fullname;";
        print "<!-- " . $osql . " -->\n";
        $outcomes = $DB->get_record_sql($osql);

        if ($outcomes) {

            if ($row->fullname != $tempclassname) {
                $teacherdetails = $DB->get_record('user', array('id' => $row->teacher));

                $teachername = explode(" ", fullname($teacherdetails));
                $teachfn = substr($teachername[0], 0, 1);
                $teachsn = $teachername[1];
                print "<tr bgcolor=#999999>";
                print "<td colspan=2><b><a name='$row->fullname'>$row->fullname</a>, <i>Teacher: " . $teachfn . " " . $teachsn . "</i></b></td>";
                print "</tr>\n";
            }

            $words = explode(' ', $row->itemname);
            switch (strtolower($words[1])) {
                case "progress":
                    if ($studentyear == '7') {
                        $taskindex = $studentyear . 'progress';
                    } else {
                        if ($yeargraded == $currentyear) {
                            $taskindex = $studentyear . 'progress';
                        } else {
                            if ($studentyear == 1) {
                                $fullyear = 10;
                            } else {
                                $fullyear = $studentyear;
                            }
                            $tempyear = $fullyear - ($currentyear - $yeargraded);
                            $taskindex = $tempyear . 'progress';
                        }
                    } // need to add something else here to consider year 9 or 10 students looking back
                    $natmarks[$studentmark_task][$taskindex]['text'] .= "<span class='PopUp' id='PopUp" . $studentyear . $words[0] . "'>" . $subjecttext[$words[0]] . "<a href='#' onclick=\"document.getElementById('PopUp" . $studentyear . $words[0] . "').style.display='none'\" style='text-color=black;'><div id='close'>Close [x]</div></a></span><a href='#' onclick=\"document.getElementById('PopUp" . $studentyear . $words[0] . "').style.display='block'\"><div class='" . $words[0] . "'>" . $words[0] . "<br /><div id='clickhere'>(click here for " . $words[0] . " characteristics)</div></div></a>";
                    $dateg[$taskindex] = $dategraded;
                    break;
                case "end":
                    if ($studentyear == '7') {
                        $taskindex = $studentyear . 'end';
                    } else {
                        if ($yeargraded == $currentyear) {
                            $taskindex = $studentyear . 'end';
                        } else {
                            if ($studentyear == 1) {
                                $fullyear = 10;
                            } else {
                                $fullyear = $studentyear;
                            }
                            $tempyear = $fullyear - ($currentyear - $yeargraded);
                            $taskindex = $tempyear . 'end';
                        }
                    } // need to add something else here to consider year 9 or 10 students looking back
                    $natmarks[$studentmark_task][$taskindex]['text'] .= "<span class='PopUp' id='PopUp" . $studentyear . $words[0] . "'>" . $subjecttext[$words[0]] . "<a href='#' onclick=\"document.getElementById('PopUp" . $studentyear . $words[0] . "').style.display='none'\" style='text-color=black;'>Close [x]</a></span><a href='#' onclick=\"document.getElementById('PopUp" . $studentyear . $words[0] . "').style.display='block'\"><div class='" . $words[0] . "'>" . $words[0] . "<br /><div id='clickhere'>(click here for " . $words[0] . " characteristics)</div></div></a>";
                    $dateg[$taskindex] = $dategraded;
                    break;
            }
            $tempclassname = $row->fullname;
        }
    }

    print "<tr>";
    print "<td><b>Current Level of Achievement:</b><br /><br />";

    print "<table id='bdr'><tr>";

    print "<tr><td width='18%' style='border-top-style: none; border-left-style: none;'>&nbsp;</td><td";
    if ($studentyear == "7") {
        print " class='current'";
    } else {
        print " class='greyd'";
    }
    print " width ='18%'><div id='greentext'>Progress to Date</div>Year 7 Standards<br /><div id='datetext'>" . $dateg['7progress'] . "</div></td><td";
    if ($studentyear == "7") {
        print " class='current'";
    } else {
        print " class='greyd'";
    }
    print " width ='18%'><div id='greentext'>End of Year</div>Year 7 Standards<div id='datetext'>" . $dateg['7end'] . "</div></td><td";
    if ($studentyear == "8") {
        print " class='current'";
    } else {
        print " class='greyd'";
    }
    print " width ='18%'><div id='greentext'>Progress to Date</div>Year 8 Standards<div id='datetext'>" . $dateg['8progress'] . "</div></td><td";
    if ($studentyear == "8") {
        print " class='current'";
    } else {
        print " class='greyd'";
    }
    print " width ='18%'><div id='greentext'>End of Year</div>Year 8 Standards<div id='datetext'>" . $dateg['8end'] . "</div></td>";
    print "</tr>\n";

    $count = 0;

    foreach ($scaleitem as $tabletext) {
        print "<tr>";
        print "<td class='" . str_replace(" ", "", $tabletext) . "'>Currently working <b>$tabletext</b> the standard in:</td>";
        if (isset($natmarks[$tabletext]['7progress'])) {
            print "<td class='highlight'>" . $natmarks[$tabletext]['7progress']['text'] . "</td>";
        } else {
            print "<td>&nbsp;</td>";
        }
        if (isset($natmarks[$tabletext]['7end'])) {
            print "<td class='highlight'>" . $natmarks[$tabletext]['7end']['text'] . "</td>";
        } else {
            print "<td>&nbsp;</td>";
        }
        if (isset($natmarks[$tabletext]['8progress'])) {
            print "<td class='highlight'>" . $natmarks[$tabletext]['8progress']['text'] . "</td>";
        } else {
            print "<td>&nbsp;</td>";
        }
        if (isset($natmarks[$tabletext]['8end'])) {
            print "<td class='highlight'>" . $natmarks[$tabletext]['8end']['text'] . "</td>";
        } else {
            print "<td>&nbsp;</td>";
        }
        print "</tr>\n";
        $count++;
    }

// took out a tr below

    print "</table><br /></td>";
    print "</tr>\n";
    print "<tr>\n";
    print "<td><hr /></td>";
    print "</tr>\n";
    print "</table>";
    print "</td></tr>\n";
    print "</table>";
    print "</body>\n";
    print "</html>";
    ?>