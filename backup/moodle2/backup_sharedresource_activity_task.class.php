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
 * Defines backup_sharedresource_activity_task class
 *
 * @package     mod_sharedresource
 * @subpackage backup-moodle2
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/sharedresource/backup/moodle2/backup_sharedresource_stepslib.php');
require_once($CFG->dirroot . '/mod/sharedresource/backup/moodle2/backup_sharedresource_settingslib.php');

/**
 * Provides the steps to perform one complete backup of the sharedresource instance
 * @package mod_sharedresource
 */
class backup_sharedresource_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the sharedresource.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_sharedresource_activity_structure_step('sharedresource structure', 'sharedresource.xml'));
    }

    /**
     * Encodes URLs to the index.php, view.php and discuss.php scripts
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the content with the URLs encoded
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');

        // Link to the list of sharedresources.
        $search = "/(".$base."\/mod\/sharedresource\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@SHAREDRESOURCEINDEX*$2@$', $content);

        // Link to sharedresource view by moduleid.
        $search = "/(".$base."\/mod\/sharedresource\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@SHAREDRESOURCEVIEWBYID*$2@$', $content);

        // Link to sharedresource view by sharedresourceid.
        $search = "/(".$base."\/mod\/sharedresource\/view.php\?s\=)([0-9]+)/";
        $content = preg_replace($search, '$@SHAREDRESOURCEVIEWBYF*$2@$', $content);

        return $content;
    }
}
