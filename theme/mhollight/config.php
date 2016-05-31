<?php

$THEME->name = 'mhollight';
$THEME->doctype = 'html5';
$THEME->parents = array('base');
$THEME->sheets = array('core', 'calendar', 'dock');
$THEME->enable_dock = true;
$THEME->editor_sheets = array('editor');
$THEME->layouts = array(
    'base' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post',
    ),
    'standard' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post',
    ),
    'calendar' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => false, 'noblocks' => true),
    ),
    'course' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post'
    ),
    'coursecategory' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post'
    ),
    'incourse' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post',
    ),
    'frontpage' => array(
        'file' => 'frontpage.php',
        'regions' => array(/* 'side-pre', */ 'side-post'),
        'defaultregion' => 'side-post'
    ),
    'admin' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post'
    ),
    'report' => array(
        'file' => 'nocols.php',
        'regions' => array()
    ),
    'mydashboard' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post',
        'options' => array('langmenu' => true),
    ),
    'mypublic' => array(
        'file' => 'general.php',
        'regions' => array('side-pre', 'side-post'),
        'defaultregion' => 'side-post'
    ),
    'login' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('langmenu' => true)
    ),
    'popup' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'noblocks' => true, 'nonavbar' => true)
    ),
    'frametop' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter' => true)
    ),
    'maintenance' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => true)
    ),
    'embedded' => array(
        'theme' => 'canvas',
        'file' => 'embedded.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => true)
    ),
    // Should display the content and basic headers only.
    'print' => array(
        'file' => 'general.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => false, 'noblocks' => true)
    ),
);

$THEME->javascripts = array('jquery.min', 'dockmod');
//$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->blockrendermethod = 'blocks';
