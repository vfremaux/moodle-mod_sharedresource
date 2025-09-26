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
 * Hooks implementation.
 *
 * @package    theme_klassplace
 */
namespace mod_sharedresource\hook\output;

class before_standard_html_head {

    /**
     * Fixes an XSS risk on login form by sanitizing the received token.
     */
    public static function callback(\core\hook\output\before_standard_html_head $hook): void {
        $isframetop = optional_param('frameset', '', PARAM_ALPHA);
        if ($isframetop) {
            echo '<base target="_top" />';
        }
    }
}