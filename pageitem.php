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
 * implements a hook for the page_module block (page course format) to construct the access link to a sharedressource
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

/**
 * Page format wrapper callback
 * @param object $block
 */
function sharedresource_set_instance($block) {
    global $DB, $COURSE, $PAGE;

    $modinfo = get_fast_modinfo($block->course);
    $renderer = $PAGE->get_renderer('format_page');

    if (empty($block->config)) {
        return;
    }

    if (!array_key_exists($block->config->cmid, $modinfo->cms)) {
        return;
    }

    $html = $renderer->print_cm($COURSE, $modinfo->cms[$block->config->cmid], []);
    $block->content->text = '<div class="block-page-module-view">'.$html.'</div>';

    // Call each plugin to add something.
    $plugins = sharedresource_get_plugins();
    foreach ($plugins as $plugin) {
        if (method_exists($plugin, 'sharedresource_set_instance')) {
            $cm = get_coursemodule_from_id('sharedresource', $block->cm->id);
            $sharedresource = $DB->get_record('sharedresource', ['id' => $cm->instance]);
            $plugin->sharedresource_set_instance($block, $sharedresource);
        }
    }

    return true;
}
