<?php

require_once("../config.php");

$filepath = required_param('image', PARAM_TEXT);

$fs = get_file_storage();

if (!$file = $fs->get_file_by_hash(sha1($filepath))) {
    print "file not found";
} else {
    $outputfile = $file->get_content();
    header('Content-disposition: attachment; filename="' . $file->get_filename() . '"');
    header('Content-type: ' . $file->get_mimetype());
    echo $outputfile;
}
?>