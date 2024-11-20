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
 * AMD module for configuring metadata
 * @module     mod_sharedresource/view
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// jshint unused: true, undef:true
define(['jquery', 'core/log'], function ($, log) {

    var namespace;

    return {
        init: function(args) {

            namespace = args;

            $('#id-system-mtd-selectall-write').bind('click', this.selectall_system_write);
            $('#id-indexer-mtd-selectall-write').bind('click', this.selectall_indexer_write);
            $('#id-author-mtd-selectall-write').bind('click', this.selectall_author_write);

            $('#id-system-mtd-selectall-read').bind('click', this.selectall_system_read);
            $('#id-indexer-mtd-selectall-read').bind('click', this.selectall_indexer_read);
            $('#id-author-mtd-selectall-read').bind('click', this.selectall_author_read);

            $('#id-mandatory-mtd-selectall').bind('click', this.selectall_mandatory);

            $('#id-system-mtd-selectnone-write').bind('click', this.selectnone_system_write);
            $('#id-indexer-mtd-selectnone-write').bind('click', this.selectnone_indexer_write);
            $('#id-author-mtd-selectnone-write').bind('click', this.selectnone_author_write);

            $('#id-system-mtd-selectnone-read').bind('click', this.selectnone_system_read);
            $('#id-indexer-mtd-selectnone-read').bind('click', this.selectnone_indexer_read);
            $('#id-author-mtd-selectnone-read').bind('click', this.selectnone_author_read);

            $('#id-mandatory-mtd-selectnone').bind('click', this.selectnone_mandatory);

            $('.mtd-check').bind('change', this.toggle_childs);
            $('.mtd-parent').bind('init', this.init_parent);
            $('.mtd-parent').trigger('init');
            $('.mtd-widget').bind('rowchange', this.widget_check);

            log.debug('AMD Mod sharedresource metadata configuration form initialized');
        },


        selectall_system_write: function (){
            $('.' + namespace + '-system-write').prop('checked', true);
            $('.' + namespace + '-system-read').prop('checked', true);
            $('.' + namespace + '-system-read').prop('disabled', true);
        },

        selectall_system_read: function (){
            $('.' + namespace + '-system-read').prop('checked', true);
        },

        selectall_indexer_write: function (){
            $('.' + namespace + '-indexer-write').prop('checked', true);
            $('.' + namespace + '-indexer-read').prop('checked', true);
            $('.' + namespace + '-indexer-read').prop('disabled', true);
        },

        selectall_indexer_read: function (){
            $('.' + namespace + '-indexer-read').prop('checked', true);
        },

        selectall_author_write: function (){
            $('.' + namespace + '-author-write').prop('checked', true);
            $('.' + namespace + '-author-read').prop('checked', true);
            $('.' + namespace + '-author-read').prop('disabled', true);
        },

        selectall_author_read: function (){
            $('.' + namespace + '-author-read').prop('checked', true);
        },

        selectall_mandatory: function (){
            $('.' + namespace + '-mandatory').prop('checked', true);
        },

        selectnone_system_write: function (){
            $('.' + namespace + '-system-write').prop('checked', false);
            $('.' + namespace + '-system-read').prop('disabled', false);
        },

        selectnone_system_read: function (){
            $('.' + namespace + '-system-read').prop('checked', false);
        },

        selectnone_indexer_write: function (){
            $('.' + namespace + '-indexer-write').prop('checked', false);
            $('.' + namespace + '-indexer-read').prop('disabled', false);
        },

        selectnone_indexer_read: function (){
            $('.' + namespace + '-indexer-read').prop('checked', false);
        },

        selectnone_author_write: function (){
            $('.' + namespace + '-author-write').prop('checked', false);
            $('.' + namespace + '-author-read').prop('disabled', false);
        },

        selectnone_author_read: function (){
            $('.' + namespace + '-author-read').prop('checked', false);
        },

        selectnone_mandatory: function (){
            $('.' + namespace + '-mandatory').prop('checked', false);
        },

        init_parent: function () {

            var that = $(this);

            var regexp = /([^-]*)-([^-]*)-([^-]*)-(.*)$/;
            var matches = that.attr('id').match(regexp);
            if (matches) {
                var fieldtype = matches[2];
                var readwrite = matches[3];
                var nodeid = matches[4];

                var ischecked = that.prop('checked');
                if (ischecked === true) {
                    // Let initial node of children
                    $('.' + namespace + '-' + fieldtype + '-' + readwrite + ' ' + nodeid).prop('disabled', false);
                } else {
                    $('.' + namespace + '-' + fieldtype + '-' + readwrite + ' ' + nodeid).prop('checked', false);
                    $('.' + namespace + '-' + fieldtype + '-' + readwrite + ' ' + nodeid).prop('disabled', true);
                }
                $('.' + namespace + '-' + 'widget' + '-' + nodeid).trigger('rowchange');
            }
        },

        toggle_childs: function () {

            log.debug('AMD Mod sharedresource metadata TOGGLE CHILD');
            var that = $(this);

            var regexp = /([^-]*)-([^-]*)-([^-]*)-(.*)$/;
            var matches = that.attr('id').match(regexp);
            if (matches) {
                var fieldtype = matches[2];
                var readwrite = matches[3];
                var nodeid = matches[4];

                var nodeparents = [];
                var nodeidbuf = nodeid;
                nodeidbuf = nodeidbuf.replace(/_[^_]+$/, '');
                if (nodeidbuf != nodeid) {
                    // Do not change self.
                    var i = 0;
                    do {
                        // Stops when single num index.
                        nodeparents.push(nodeidbuf);
                        nodeidbuf = nodeidbuf.replace(/_[^_]+$/, '');
                        i++;
                    } while (!nodeidbuf.match(/^\d+$/) && i < 100);
                }

                var ischecked = that.prop('checked');
                // Check all my subdependancies.
                if (ischecked === true) {
                    // All childs.
                    $('.' + namespace + '-' + fieldtype + '-' + readwrite + '-' + nodeid).prop('checked', true);
                    $('.' + namespace + '-' + fieldtype + '-' + readwrite + '-' + nodeid).prop('disabled', false);

                    // The read and child's read.
                    if (readwrite === 'write') {
                        // My read.
                        $('#' + namespace + '-' + fieldtype + '-read-' + nodeid).prop('checked', true);
                        $('#' + namespace + '-' + fieldtype + '-read-' + nodeid).prop('disabled', true);
                        // My child's read.
                        $('.' + namespace + '-' + fieldtype + '-read-' + nodeid).prop('checked', true);
                        $('.' + namespace + '-' + fieldtype + '-read-' + nodeid).prop('disabled', true);
                    }

                    // Check all my parents
                    var parentid = nodeparents.shift();
                    while (parentid) {
                        $('#' + namespace + '-' + fieldtype + '-' + readwrite + '-' + parentid).prop('checked', true);
                        $('#' + namespace + '-' + fieldtype + '-' + readwrite + '-' + parentid).prop('disabled', false);

                        if (readwrite === 'write') {
                            $('#' + namespace + '-' + fieldtype + '-read-' + parentid).prop('checked', true);
                            $('#' + namespace + '-' + fieldtype + '-read-' + parentid).prop('disabled', true);
                        }
                        parentid = nodeparents.shift();
                        log.debug('AMD Mod sharedresource metadata Checking parent');
                    }

                } else {
                    // Free constraint on childs.
                    $('.' + namespace + '-' + fieldtype + '-' + readwrite + '-' + nodeid).prop('checked', false);
                    $('.' + namespace + '-' + fieldtype + '-' + readwrite + '-' + nodeid).prop('disabled', true);

                    if (readwrite === 'write') {
                        // My read.
                        $('#' + namespace + '-' + fieldtype + '-read-' + nodeid).prop('disabled', false);

                        // My child's  read.
                        $('.' + namespace + '-' + fieldtype + '-read-' + nodeid).prop('disabled', false);
                    }
                }
                $('#' + namespace + '-' + 'widget' + '-' + nodeid).trigger('rowchange');

            }
        },

        widget_check: function () {

            log.debug('AMD Mod sharedresource metadata WIDGET CHECK');

            var that = $(this);

            var regexp = /([^-]*)-([^-]*)-(.*)$/;

            var matches = that.attr('id').match(regexp);
            if (matches) {
                // var fieldtype = matches[2]; // Should be widget if proxied on a widget.
                var nodeid = matches[3];

                // Check widget if exists on same line.
                if ($('#' + namespace + '-widget' + '-' + nodeid).length) {
                    var schecked = $('#' + namespace + '-system-read-' + nodeid).prop('checked');
                    var ichecked = $('#' + namespace + '-indexer-read-' + nodeid).prop('checked');
                    var achecked = $('#' + namespace + '-author-read-' + nodeid).prop('checked');
                    if (achecked || ichecked || schecked) {
                        $('#' + namespace + '-widget-' + nodeid).prop('disabled', false);
                    } else {
                        $('#' + namespace + '-widget-' + nodeid).prop('checked', false);
                        $('#' + namespace + '-widget-' + nodeid).prop('disabled', true);
                    }
                }
            }
        }
    };
});