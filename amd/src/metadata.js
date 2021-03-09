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
define(['jquery', 'core/log'], function ($, log) {

    var metadata = {

        init: function() {
            log.debug('AMD Mod sharedresource metadata utilities initialized');
        },

        /**
         * Given an element id as m_n_o:x_y_z, gives the next to come
         * leaf element in sequence
         */
        next_occurrence : function(elementid) {
            var parts = elementid.split(':');
            var instanceid = parts[1];
            var pathelms = instanceid.split('_');
            var lastpart = pathelms.pop();
            lastpart++;
            pathelms.push(lastpart);
            instanceid = pathelms.join('_');
            parts[1] = instanceid;
            elementid = parts.join(':');
            return elementid;
        },

        /**
         * Given an element id as mnx_nny_onz, gives the next to come
         * leaf element in sequence, that is ; mnx_nny_on(z + 1)
         */
        next_occurrence_name : function(elmname) {
            var parts = elmname.split('n');
            var lastoccurrence = parts.pop();
            lastoccurrence++;
            parts.push(lastoccurrence);
            elmname = parts.join('n');
            return elmname;
        },

        /**
         * Given an element name as mnx_nny_onz, gives the first parent as
         * mnx_nny
         */
        parent_name : function(elementname) {
            var parts = elementname.split('_');
            parts.pop();
            return parts.join('_');
        }

    };

    return metadata;
});