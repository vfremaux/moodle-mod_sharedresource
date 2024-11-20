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
 * Restore tasks.
 *
 * @package     mod_sharedresource
 * @subpackage backup-moodle2
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

// Because it exists (must).
require_once($CFG->dirroot . '/mod/sharedresource/backup/moodle2/restore_sharedresource_stepslib.php');

/**
 * sharedresource restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */
class restore_sharedresource_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
        return true;
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Choice only has one structure step.
        $this->add_step(new restore_sharedresource_activity_structure_step('sharedresource_structure', 'sharedresource.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('sharedresource', ['intro'], 'sharedresource');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        $rules = [];

        // List of sharedresources in course.
        $rules[] = new restore_decode_rule('SHAREDRESOURCEINDEX', '/mod/sharedresource/index.php?id=$1', 'course');

        $rules[] = new restore_decode_rule('SHAREDRESOURCEVIEWBYID', '/mod/sharedresource/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('SHAREDRESOURCEVIEWBYF', '/mod/sharedresource/view.php?s=$1', 'sharedresource');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the processor when restoring
     * sharedresource logs. It must return one array
     * of rule objects
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('sharedresource', 'add', 'view.php?id={course_module}', '{sharedresource}');
        $rules[] = new restore_log_rule('sharedresource', 'update', 'view.php?id={course_module}', '{sharedresource}');
        $rules[] = new restore_log_rule('sharedresource', 'view', 'view.php?id={course_module}', '{sharedresource}');

        return $rules;
    }
}
