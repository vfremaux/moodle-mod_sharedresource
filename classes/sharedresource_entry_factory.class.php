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

namespace mod_sharedresource;

defined('MOODLE_INTERNAL') || defined('SHAREDRESOURCE_INTERNAL') || die("Not loadable directly. Use __autoload.php instead.");

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