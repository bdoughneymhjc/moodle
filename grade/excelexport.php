<?php

require_once('../config.php');
require_once('../course/lib.php');
require_once('../lib/gradelib.php');
require_once("$CFG->libdir/blocklib.php");
require_once("$CFG->dirroot/mod/assignment/lib.php");

/// user has pressed the save button, lets save the text
$dlchoice = $_POST['whanau'];

$currentyear = date("Y");
$currentuyear = strtotime("1 January " . $currentyear);

switch ($dlchoice) {
    // current year coast
    case "coast":
        $viewtype = "current";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_user.department LIKE '%C%' AND mdl_assignment.grade = '-2' ORDER BY mdl_user.lastname;";
        break;
    // all years coast
    case "coasta":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_user.department LIKE '%C%' AND mdl_assignment.grade = '-2' ORDER BY mdl_user.lastname;";
        break;
    // current year coast national standards
    case "coastnat":
        $viewtype = "current";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%C%' ORDER BY mdl_user.lastname;";
        break;
    // previous year coast national standards
    case "coastnatp":
        $viewtype = "previous";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%C%' ORDER BY mdl_user.lastname;";
        break;
    // all years coast national standards
    case "coastnata":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%C%' ORDER BY mdl_user.lastname;";
        break;
    // current year forest
    case "forest":
        $viewtype = "current";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_user.department LIKE '%F%' AND mdl_assignment.grade = '-2' ORDER BY mdl_user.lastname;";
        break;
    // all years forest
    case "foresta":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_user.department LIKE '%F%' AND mdl_assignment.grade = '-2' ORDER BY mdl_user.lastname;";
        break;
    // current year forest national standards
    case "forestnat":
        $viewtype = "current";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%F%' ORDER BY mdl_user.lastname;";
        break;
    // previous year forest national standards
    case "forestnatp":
        $viewtype = "previous";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%F%' ORDER BY mdl_user.lastname;";
        break;
    // all years forest national standards
    case "forestnata":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%F%' ORDER BY mdl_user.lastname;";
        break;
    // current year water
    case "water":
        $viewtype = "current";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_user.department LIKE '%W%' AND mdl_assignment.grade = '-2' ORDER BY mdl_user.lastname;";
        break;
    // all years water
    case "watera":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_user.department LIKE '%W%' AND mdl_assignment.grade = '-2' ORDER BY mdl_user.lastname;";
        break;
    // current year water national standards
    case "waternat":
        $viewtype = "current";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%W%' ORDER BY mdl_user.lastname;";
        break;
    // previous year water national standards
    case "waternatp":
        $viewtype = "previous";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%W%' ORDER BY mdl_user.lastname;";
        break;
    // all years water national standards
    case "waternata":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%W%' ORDER BY mdl_user.lastname;";
        break;
    // current year mountains
    case "mountains":
        $viewtype = "current";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_user.department LIKE '%M%' AND mdl_assignment.grade = '-2' ORDER BY mdl_user.lastname;";
        break;
    case "mountainsa":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_user.department LIKE '%M%' AND mdl_assignment.grade = '-2' ORDER BY mdl_user.lastname;";
        break;
    // current year mountains national standards
    case "mountainsnat":
        $viewtype = "current";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%M%' ORDER BY mdl_user.lastname;";
        break;
    // previous year forest mountains standards
    case "mountainsnatp":
        $viewtype = "previous";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%M%' ORDER BY mdl_user.lastname;";
        break;
    // all years mountains national standards
    case "mountainsnata":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_user.department LIKE '%M%' ORDER BY mdl_user.lastname;";
        break;
    // current year all whanau
    case "all":
        $viewtype = "current";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-2' AND mdl_assignment_submissions.timemarked > " . $currentuyear . " ORDER BY mdl_assignment_submissions.id;";
        break;
    // all years all whanau
    case "alla":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-2' ORDER BY mdl_assignment_submissions.id;";
        break;
    case "natstandardsm":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_assignment.name LIKE '%Mathematics%' ORDER BY mdl_user.lastname;";
        break;
    case "natstandardsw":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_assignment.name LIKE '%Writing%' ORDER BY mdl_user.lastname;";
        break;
    case "natstandardsr":
        $viewtype = "all";
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND mdl_assignment.name LIKE '%Reading%' ORDER BY mdl_user.lastname;";
        break;
    case "natstandkamar":
        $viewtype = "current";
        $rawexport = TRUE;
        $sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid
FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND (mdl_assignment.name LIKE '%Reading%' OR mdl_assignment.name LIKE '%Writing%' OR mdl_assignment.name LIKE '%Mathematics%') ORDER BY mdl_user.lastname;";
        break;
    case "natstandkamarp":
        $viewtype = "previous";
        $rawexport = TRUE;
        $shortyear = $currentyear - 2001;
        //$sql = "SELECT mdl_assignment_submissions.id AS asid, mdl_user.idnumber, mdl_assignment_submissions.assignment, mdl_user.id AS muid, mdl_user.username, mdl_user.firstname, mdl_user.lastname, mdl_user.department, mdl_assignment.name, mdl_assignment_submissions.grade, mdl_assignment_submissions.teacher, mdl_assignment_submissions.timemarked, mdl_assignment.course, mdl_course.fullname, mdl_grade_items.iteminstance, mdl_grade_items.outcomeid FROM mdl_assignment_submissions INNER JOIN mdl_user ON mdl_assignment_submissions.userid = mdl_user.id INNER JOIN mdl_assignment ON mdl_assignment_submissions.assignment = mdl_assignment.id INNER JOIN mdl_grade_items ON mdl_assignment_submissions.assignment = mdl_grade_items.iteminstance INNER JOIN mdl_grade_outcomes ON mdl_grade_items.outcomeid = mdl_grade_outcomes.id INNER JOIN mdl_course ON mdl_assignment.course = mdl_course.id WHERE mdl_assignment.grade = '-8' AND (mdl_assignment.name LIKE '%Reading%' OR mdl_assignment.name LIKE '%Writing%' OR mdl_assignment.name LIKE '%Mathematics%') ORDER BY mdl_user.lastname;";
        $sql = "SELECT q.id as asid, q.idnumber, q.userid, q.finalgrade, q.roundedgrade, q.timemodified, q.feedback, q.shortname, q.department, q.iteminstance, q.scaleid, q.itemid, q.itemname AS qname, q.outcomeid, q.firstname, q.lastname FROM (SELECT mdl_grade_grades.id, mdl_grade_grades.userid, mdl_grade_grades.finalgrade, ROUND(mdl_grade_grades.finalgrade) AS roundedgrade, mdl_grade_grades.timemodified, mdl_grade_grades.feedback, mdl_course.shortname, mdl_user.department, mdl_user.idnumber, mdl_user.suspended, mdl_grade_items.iteminstance, mdl_grade_items.scaleid, mdl_grade_grades.itemid, mdl_grade_items.itemname, mdl_grade_items.outcomeid, mdl_user.firstname, mdl_user.lastname FROM mdl_grade_grades INNER JOIN mdl_grade_items ON mdl_grade_grades.itemid = mdl_grade_items.id INNER JOIN mdl_course ON mdl_grade_items.courseid = mdl_course.id INNER JOIN mdl_user on mdl_grade_grades.userid = mdl_user.id WHERE mdl_grade_grades.finalgrade > 0 AND mdl_grade_items.scaleid = 8 AND (mdl_grade_items.itemname LIKE 'Reading%' OR mdl_grade_items.itemname LIKE 'Writing%' OR mdl_grade_items.itemname LIKE 'Mathematics%') AND mdl_course.shortname LIKE '%" . $shortyear . "') AS q WHERE q.feedback IS NULL;";
        break;
    default:
        echo "An error with the SQL query has occured";
}

$stuinfo = $DB->get_records_sql($sql);

$fhw = tmpfile();

if ($rawexport) {
    fwrite($fhw, "\"IDNum_Type_Number_Version\",\"Grade_Marks\"\n");
} else {
    fwrite($fhw, "\"ID Number\",\"First Name\",\"Last Name\",\"Class Number\",\"Class Name\",\"Assessment Name\",\"Mark\",\"WAME\"\n");
}

foreach ($stuinfo as $row) {
    $studentyear = substr($row->department, 0, 1);
    if ($row->roundedgrade > 0 AND $row->roundedgrade < 5) {
        $yeargraded = date("Y", $row->timemodified);
        if (($yeargraded != $currentyear) AND ( $viewtype == "current")) {
            $dummy = 1;
        } else {
            if (($yeargraded == $currentyear - 1) AND ( $viewtype == "previous")) {
                // if we are exporting the previous year's data
                switch ($row->roundedgrade) { // Numeric marks for nat standards are opposite to the logical fashion- 1= best, 4= worst
                    case 1:
                        $expgrade = 'W';
                        $convertedgrade = '4';
                        break;
                    case 2:
                        $expgrade = 'A';
                        $convertedgrade = '3';
                        break;
                    case 3:
                        $expgrade = 'M';
                        $convertedgrade = '2';
                        break;
                    case 4:
                        $expgrade = 'E';
                        $convertedgrade = '1';
                        break;
                }
                if ($rawexport) {
                    if (strpos($row->qname, "Mathematics") !== false) {
                        if (strpos($row->qname, "Progress") !== false) {
                            if ($row->suspended == 1) {
                                $kamarcode = "_S_OTJMM" . ($studentyear);
                            } else {
                                $kamarcode = "_S_OTJMM" . ($studentyear - 1);
                            }
                        } else {
                            if ($row->suspended == 1) {
                                $kamarcode = "_O_OTJM" . ($studentyear);
                            } else {
                                $kamarcode = "_O_OTJM" . ($studentyear - 1);
                            }
                        }
                    } elseif (strpos($row->qname, "Reading") !== false) {
                        if (strpos($row->qname, "Progress") !== false) {
                            if ($row->suspended == 1) {
                                $kamarcode = "_S_OTJMR" . ($studentyear);
                            } else {
                                $kamarcode = "_S_OTJMR" . ($studentyear - 1);
                            }
                        } else {
                            if ($row->suspended == 1) {
                                $kamarcode = "_O_OTJR" . ($studentyear);
                            } else {
                                $kamarcode = "_O_OTJR" . ($studentyear - 1);
                            }
                        }
                    } elseif (strpos($row->qname, "Writing") !== false) {
                        if (strpos($row->qname, "Progress") !== false) {
                            if ($row->suspended == 1) {
                                $kamarcode = "_S_OTJMW" . ($studentyear);
                            } else {
                                $kamarcode = "_S_OTJMW" . ($studentyear - 1);
                            }
                        } else {
                            if ($row->suspended == 1) {
                                $kamarcode = "_O_OTJW" . ($studentyear);
                            } else {
                                $kamarcode = "_O_OTJW" . ($studentyear - 1);
                            }
                        }
                    }
                    fwrite($fhw, "\"" . $row->idnumber . $kamarcode . "_1\",\"$convertedgrade\"\n");
                } else {
                    fwrite($fhw, "\"$row->idnumber\",\"$row->firstname\",\"$row->lastname\",\"$row->department\",\"$row->fullname\",\"$row->name\",\"$row->grade\",\"$expgrade\"\n");
                }
            } else {
                switch ($row->roundedgrade) { // Numeric marks for nat standards are opposite to the logical fashion- 1= best, 4= worst
                    case 1:
                        $expgrade = 'W';
                        $convertedgrade = '4';
                        break;
                    case 2:
                        $expgrade = 'A';
                        $convertedgrade = '3';
                        break;
                    case 3:
                        $expgrade = 'M';
                        $convertedgrade = '2';
                        break;
                    case 4:
                        $expgrade = 'E';
                        $convertedgrade = '1';
                        break;
                }
                if ($rawexport) {
                    if (strpos($row->qname, "Mathematics") !== false) {
                        if (strpos($row->qname, "Progress") !== false) {
                            $kamarcode = "_S_OTJMM" . $studentyear;
                        } else {
                            $kamarcode = "_O_OTJM" . $studentyear;
                        }
                    } elseif (strpos($row->qname, "Reading") !== false) {
                        if (strpos($row->qname, "Progress") !== false) {
                            $kamarcode = "_S_OTJMR" . $studentyear;
                        } else {
                            $kamarcode = "_O_OTJR" . $studentyear;
                        }
                    } elseif (strpos($row->qname, "Writing") !== false) {
                        if (strpos($row->qname, "Progress") !== false) {
                            $kamarcode = "_S_OTJMW" . $studentyear;
                        } else {
                            $kamarcode = "_O_OTJW" . $studentyear;
                        }
                    }
                    fwrite($fhw, "\"" . $row->idnumber . $kamarcode . "_1\",\"$convertedgrade\"\n");
                } else {
                    fwrite($fhw, "\"$row->idnumber\",\"$row->firstname\",\"$row->lastname\",\"$row->department\",\"$row->fullname\",\"$row->name\",\"$row->grade\",\"$expgrade\"\n");
                }
            }
        }
    }
}

$meta_data = stream_get_meta_data($fhw);
$filename = $meta_data["uri"];

fseek($fhw, 0);
$filesize = filesize($filename);

/* Disabled 'direct download' header as it was killing php's memory */
header("Expires: 0");
header("Cache-Control: private");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=" . $dlchoice . "_moodleexport.csv");
header("Content-Length: " . $filesize);

echo fread($fhw, $filesize);

fclose($fhw);
?>