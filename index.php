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
 * @author      Piers Harding  piers@catalyst.net.nz
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package     mod_sharedresource
 * @category    mod
 */
require_once('../../config.php');

$id = required_param( 'id', PARAM_INT ); // Course.

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

// Security.

require_login($course);
$context = context_course::instance($id);

if ($course->id != SITEID) {
    require_login($course->id);
}

$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_sharedresource\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

$strresource = get_string('modulename', 'sharedresource');
$strresources = get_string('modulenameplural', 'sharedresource');
$strtitle = get_string('modulenameplural', 'sharedresource');
$strweek = get_string('week');
$strtopic = get_string('topic');
$strname = get_string('name');
$strsummary = get_string('summary');
$strlastmodified = get_string('lastmodified');

$url = new moodle_url('/mod/sharedresource/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'));

echo $OUTPUT->header();

$courseurl = new moodle_url('../../course/view.php', ['id' => $course->id]);
if (!$resources = get_all_instances_in_course('sharedresource', $course)) {
    echo $OUTPUT->notification(get_string('thereareno', 'moodle', $strresources), 'notifyproblem', $courseurl);
    echo $OUTPUT->footer();
    exit;
}

$table = new html_table();
$table->size = array('20%', '40%', '40%');

if ($course->format == 'weeks') {
    $table->head  = [$strweek, $strname, $strsummary];
    $table->align = ['center', 'left', 'left'];
} else if ($course->format == 'topics') {
    $table->head  = [$strtopic, $strname, $strsummary];
    $table->align = ['center', 'left', 'left'];
} else {
    $table->head  = [$strlastmodified, $strname, $strsummary];
    $table->align = ['left', 'left', 'left'];
}

$currentsection = '';
$options = new Stdclass;
$options->para = false;
$strsummary = get_string('summary');

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

    $resurl = new moodle_url('/mod/sharedresource/view.php', ['id' => $resource->coursemodule]);
    if (!$resource->visible) {
        // Show dimmed if the mod is hidden.
        $table->data[] = array($printsection,
                '<a class="dimmed" '.$extra.' href="$resurl">'.format_string($resource->name, true).'</a>',
                format_text($resource->intro, $resource->introformat, $options));
    } else {
        // Show normal if the mod is visible.
        $table->data[] = array($printsection,
                '<a '.$extra.' href="'.$resurl.'">'.format_string($resource->name, true).'</a>',
                format_text($resource->intro, $resource->introformat, $options));
    }
}
echo '<br/>';
echo html_writer::table($table);

echo '<center>';
echo $OUTPUT->single_button($courseurl, get_string('backtocourse', 'mod_sharedresource'));
echo '</center>';

echo $OUTPUT->footer($course);
