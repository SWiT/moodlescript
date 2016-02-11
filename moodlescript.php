<?php
/* In case you want to run this script with xdebug run the following command:
*       export XDEBUG_CONFIG="idekey=netbeans-xdebug"
*  Then run the script:
*       php moodlescript.php [path_to_moodle_root_dir]
*/

define('CLI_SCRIPT', true);

// Check for the minimum number of parameters.
// If there are too few then exit.
$numargv = count($argv);
if ($numargv < 2) {
    print("not enough arguments.");
    exit(1);
}

// Get the Moodle root directory
$moodlerootdir = $argv[$numargv-1];

// Check if config.php exists
if (!file_exists($moodlerootdir."/config.php")) {
    echo "\nERROR: The Moodle root directory \"$moodlerootdir\" does not contain config.php.\n\n";
    exit(1);
}

// Include the Moodle config libraries we'll need.
require($moodlerootdir."/config.php");      // Load the Moodle config.php
require_once($CFG->libdir.'/clilib.php');   // cli only functions
require_once($CFG->dirroot.'/my/lib.php');

// Check if the Moodle instance is under maintenance.
if (CLI_MAINTENANCE) {
    echo "CLI maintenance mode active, backup execution suspended.\n";
    exit(1);
}
if (moodle_needs_upgrading()) {
    echo "Moodle upgrade pending, backup execution suspended.\n";
    exit(1);
}


// Do the things you got to do.
echo "\nMoodle Admin script\n";
echo "--------------------------------------------------\n";
print("Running against ".$moodlerootdir."\n");

// Check if each course is "empty". Our default course template contains only a single News Forum activity.
// Count the number of coursemoduleids is each section of the course. If there is more than one then the 
// course content has changed from the course default.
$courses = $DB->get_records("course");
foreach ($courses as $course) {
    print("Courseid: ".$course->id);
    $sections = $DB->get_records("course_sections",array("course"=>$course->id));
    $totalcmids = 0;    
    foreach ($sections as $section) {
        if (!empty($section->sequence)) {
            $arr_cmids = explode(",", $section->sequence);
            $totalcmids += count($arr_cmids);
        }
    }
    if ($totalcmids == 1) {
        // The course may be empty.
        print(" may be empty.\n");
    } else {
        // The course is not empty. The default content was modified some how.
        print(" is not empty.\n");
    }
}

?>
