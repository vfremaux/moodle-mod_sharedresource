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
 * AMD module for editing metadata
 * @module     mod_sharedresource/view
 * @package    blocks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// jshint unused: true, undef:true
define(['jquery', 'core/log'], function ($, log) {

    var metadatanotice = {

        init: function() {

            $('.mtd-tab').bind('click', this.switch_tab);
            $('.mtd-tab a').bind('click', function(e) { e.preventDefault(); });

            $.proxy(metadatanotice.switch_tab, $('#id-menu-1').get())();

            log.debug('AMD Mod sharedresource metadata notice initialized');
        },

        switch_tab: function() {

            var that = $(this);
            if (!that.attr('id')) {
                that = that.parent();
            }

            var menuid = that.attr('id');

            var regexp = /id-menu-([^-]+)$/;
            var matches = that.attr('id').match(regexp);
            var tabid = 'id-tab-' + matches[1];

            $('.mtd-tab').removeClass('here');
            $('.mtd-tab').removeClass('current');
            $('#' + menuid).addClass('here');
            $('#' + menuid).addClass('current');

            $('.mtd-content').removeClass('active');
            $('.mtd-content').removeClass('on');
            $('.mtd-content').addClass('off');
            $('#' + tabid).addClass('active');
            $('#' + tabid).removeClass('off');
            $('#' + tabid).addClass('on');
        },
    };

    return metadatanotice;
});