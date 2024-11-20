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
 * Provides an alternative view for page format.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * Embedds the resource in a page format course page.
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_base.class.php');

/**
 * Page format wrapper callback
 * @param object $block
 */
function sharedresource_set_instance($block) {
    global $DB;

    $modinfo = get_fast_modinfo($block->course);

    $cm = get_coursemodule_from_id('sharedresource', $block->cm->id);
    $resourceinstance = new \mod_sharedresource\base($cmid, null);
    $resourceinstance->embedded = true;
    $block->content->text = '<div class="block-page-module-view">'.$sharedresource->display().'</div>';

    // Call each plugin to add something.
    $plugins = sharedresource_get_plugins();
    foreach ($plugins as $plugin) {
        if (method_exists($plugin, 'sharedresource_set_instance')) {
            $sharedresource = $DB->get_record('sharedresource', ['id' => $cm->instance]);
            $plugin->sharedresource_set_instance($block, $sharedresource);
        }
    }

    return true;
}
