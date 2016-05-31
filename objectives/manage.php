<?php

	require_once('../config.php');
	require_once('../course/lib.php');
	require_once($CFG->libdir.'/formslib.php');
	require_once($CFG->libdir.'/blocklib.php');
	require_once($CFG->libdir.'/filelib.php');
	require_once($CFG->libdir.'/pear/HTML/QuickForm/input.php');
	
	if ($_SERVER['REQUEST_METHOD'] != 'POST') {

		 $coursecontext = get_context_instance(CONTEXT_SYSTEM);
		 $PAGE->set_context($coursecontext);
		 $PAGE->set_url($CFG->wwwroot."/objectives/manage.php");
		 $PAGE->set_title("Manage Achievement Objectives");
		 $PAGE->set_heading("Manage Achievement Objectives");
		 
		 @$aosubject=$_GET['subject'];
		 
		 if($aosubject == NULL) {
			 $aosubjects[] = "Select One...";
			 $aosubjects[] = "English";
			 $aosubjects[] = "The Arts";
			 $aosubjects[] = "Health and Physical Education";
			 $aosubjects[] = "Learning Languages";
			 $aosubjects[] = "Mathematics and Statistics";
			 $aosubjects[] = "Science";
			 $aosubjects[] = "Social Sciences";
			 $aosubjects[] = "Technology";
                         $aosubjects[] = "Careers Education and Guidance";
		 } else {
			 $aosubjects[] = $aosubject;
			 $aocatsql = "SELECT mdl_achievement_objectives_cat.id, mdl_achievement_objectives_cat.subjectarea, mdl_achievement_objectives_cat.subheading FROM mdl_achievement_objectives_cat HAVING mdl_achievement_objectives_cat.subjectarea = '$aosubject' ORDER BY mdl_achievement_objectives_cat.subheading;";
			 $aocategories = $DB->get_records_sql($aocatsql);
		 }
		 
		 $aolevel[] = "3";
		 $aolevel[] = "4";
		 $aolevel[] = "5";
		 $aolevel[] = "6";
		 $aolevel[] = "7";
		 
		 // Print MOL header
		 echo $OUTPUT->header();
		 
		 echo "<script type=\"text/javascript\">";
		 echo "function reload(form)";
		 echo "{";
		 echo "var val=form.subject.options[form.subject.options.selectedIndex].value;";
		 echo "self.location='manage.php?subject=' + val;";
		 echo "}";
		 echo "</script>";
		 
		 // The page output begins here
		 
		 echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
		 echo "<table>";
		 echo "<tr><td>Subject Area: <select name='subject' onchange='reload(this.form)'>";
		 
		 foreach ($aosubjects as $subject)
		 {
			 echo "<option value='".$subject."'>".$subject."</option>";
		 }
		 
		 echo "</select></td><td>Curriculum Level: <select name='level'>";
		 
		 foreach ($aolevel as $level)
		 {
			 echo "<option value='".$level."'>Level ".$level."</option>";
		 }
		 
		 echo "</select></td></tr>";
		 echo "<tr><td colspan='2'>AO Subcategory: <select name='aocat'>";
		 
		 foreach ($aocategories as $category)
		 {
			 echo "<option value='".$category->id."'>".$category->subheading."</option>";
		 }
		 
		 echo "</select></td></tr>";
		 echo "<tr><td colspan='2'><textarea rows='5' cols='80' name='aotext'>Enter AO Here</textarea></td></tr>";
		 echo "<tr><td>&nbsp;</td><td align='right'><input type='submit' value='Save' /></td></tr>";
		 echo "</table>";
		 echo "</form>";

		 /// Print MOL footer
		 echo $OUTPUT->footer();
	} else {
		// process the form here
		
		//$ao->subject = $_POST['subject'];
		$ao->level = $_POST['level'];
		$ao->category = $_POST['aocat'];
		$ao->description = $_POST['aotext'];
		
		$DB->insert_record('achievement_objectives', $ao);
		
		$PAGE->navigation->clear_cache();
		redirect($_SERVER['PHP_SELF']);
 	}
?>