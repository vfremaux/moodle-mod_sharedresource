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
 * AMD module for providing metadata related manipulation functions
 * @module     mod_sharedresource/metadata
 * @package    mod
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// jshint unused: true, undef:true
define(['jquery', 'core/log', 'core/str'], function ($, log, str) {

    var sharedresourceclassification = {

        strs: [],

        init: function() {

            var stringdefs = [
                {key: 'confirmclassifdeletion', component: 'sharedresource'}, // 0
            ];

            str.get_strings(stringdefs).done(function(s) {
                sharedresourceclassification.strs = s;
            });

            $('.sharedresource-delete-classification').bind('click', this.confirm);

            log.debug('AMD Sharedresources classifications initialized.');
        },

        confirm: function(e) {
            if (!confirm(sharedresourceclassification.strs[0])) {
                e.preventDefault();
                e.stopImmediatePropagation();
                return false;
            }
        }
    };

    return sharedresourceclassification;
});
