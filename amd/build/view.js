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
 *
 * @module     mod_sharedresource/view
 * @package    blocks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// jshint unused: true, undef:true
define(['jquery', 'core/log'], function ($, log) {

    return {

        init: function(args) {

            that = this;

            $('.sharedresource-popup-link').each( function() {
                that = $(this).on('click', null, args, this.openpopup);
            });

            log.debug('Mod sharedresource AMD initialized');

        },


        openpopup : function(e) {
            that = $(this);

            url = M.cfg.wwwroot + '/mod/sharedresource/view.php';
            url += '?inpopup=1';
            url += 'id=' + e.data.cmid;

            resid = that.attr('id').replace('sharedresource-', '');
            this.target = 'resource' + resid;
            return openpopup(url, 'resource' + resid, e.data.respopup);
        }
    };
});
