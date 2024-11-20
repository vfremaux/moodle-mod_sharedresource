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
 * A class factory for a sharedresource_entry.
 *
 * @author  Piers Harding  <piers@catalyst.net.nz>, Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod_sharedresource
 *
 */

/*
 * Because classes preloading (SHAREDRESOURCE_INTERNAL) pertubrates MOODLE_INTERNAL detection.
 * phpcs:disable moodle.Files.MoodleInternal.MoodleInternalGlobalState
 */
namespace mod_sharedresource;

if (!defined('SHAREDRESOURCE_INTERNAL')) {
    defined('MOODLE_INTERNAL') || die();
}

/**
 * A class factory for a sharedresource_entry.
 */
class entry_factory {

    /**
     * A factory class that choses the adequate class implementation to play with.
     */
    public static function get_entry_class() {
        global $CFG;

        if (sharedresource_supports_feature('entry/extended')) {
            include_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_entry.class.php');
            include_once($CFG->dirroot.'/mod/sharedresource/pro/classes/sharedresource_entry.class.php');
            return '\\mod_sharedresource\\entry_extended';
        } else {
            include_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_entry.class.php');
            return '\\mod_sharedresource\\entry';
        }
    }
}
