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
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package    sharedresource
 * @category   mod
 */
require_once('../../config.php');

$id = required_param( 'id', PARAM_INT ); // course

if (!$course =  $DB->get_record("course", array("id" => $id))) {
    print_error('coursemisconf');
}

// Security.

require_login($course);
$context = context_course::instance($id);

if ($course->id != SITEID) {
    require_login($course->id);
}

add_to_log($course->id, "sharedresource", "view all", "index.php?id=$course->id", "");
$strresource = get_string("modulename", "sharedresource");
$strweek = get_string("week");
$strtopic = get_string("topic");
$strname = get_string("name");
$strsummary = get_string("summary");
$strlastmodified = get_string("lastmodified");

$strtitle = get_string($mode.'sharedresourcetypefile', 'sharedresource');
$url = new moodle_url('/mod/sharedresource/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');

echo $OUTPUT->header();

if (! $resources = get_all_instances_in_course("sharedresource", $course)) {
    echo $OUTPUT->notification(get_string('thereareno', 'moodle', $strresources), "../../course/view.php?id=$course->id");
    echo $OUTPUT->footer();
    exit;
}

if ($course->format == "weeks") {
    $table->head  = array ($strweek, $strname, $strsummary);
    $table->align = array ("center", "left", "left");
} else if ($course->format == "topics") {
    $table->head  = array ($strtopic, $strname, $strsummary);
    $table->align = array ("center", "left", "left");
} else {
    $table->head  = array ($strlastmodified, $strname, $strsummary);
    $table->align = array ("left", "left", "left");
}

$currentsection = "";
$options->para = false;
foreach ($resources as $resource) {
    if ($course->format == 'weeks' or $course->format == 'topics') {
        $printsection = '';
        if ($resource->section !== $currentsection) {
            if ($resource->section) {
                $printsection = $resource->section;
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $resource->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($resource->timemodified).'</span>';
    }
    if (!empty($resource->extra)) {
        $extra = urldecode($resource->extra);
    } else {
        $extra = '';
    }
    if (!$resource->visible) {
        // Show dimmed if the mod is hidden.
        $table->data[] = array ($printsection, 
                "<a class=\"dimmed\" $extra href=\"view.php?id=$resource->coursemodule\">".format_string($resource->name,true).'</a>',
                format_text($resource->summary, FORMAT_MOODLE, $options) );
    } else {
        // Show normal if the mod is visible.
        $table->data[] = array ($printsection, 
                "<a $extra href=\"view.php?id=$resource->coursemodule\">".format_string($resource->name,true)."</a>",
                format_text($resource->description, FORMAT_MOODLE, $options) );
    }
}
echo "<br />";
echo html_writer::table($table);
echo $OUTPUT->footer($course);

