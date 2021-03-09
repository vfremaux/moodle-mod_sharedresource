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
define(['jquery', 'core/str', 'core/log', 'core/config', 'mod_sharedresource/metadata'], function ($, str, log, cfg, metadata) {

    var namespace;
    var mtdstrings;

    var metadataedit = {

        bind_tabs: function() {
            log.debug('AMD Mod sharedresource metadata edition binding tab events');
            $('.mtd-tab').bind('click', this.switch_tab);
            $('.mtd-tab a').bind('click', function(e) { e.preventDefault(); });
        },

        bind_all: function() {

            log.debug('AMD Mod sharedresource metadata edition binding all events');

            $('.mtd-form-input').off('change');
            $('.taxonomy-source').off('change');
            $('.mtd-form-addbutton').off('click');
            $('.mtd-form-element select').off('change');
            $('.mtd-form-element textarea').off('keyup');
            $('.mtd-form-element input[type="text"]').off('keyup');
            $('.mtd-tab').off('click');
            $('.mtd-tab a').off('click');

            // Add check handlers to all elements.
            $('.mtd-form-element input[type="text"]').bind('keyup', this.check_empty);
            $('.mtd-form-element select').bind('change', this.check_empty);
            $('.mtd-form-element textarea').bind('keyup', this.check_empty);

            $('.mtd-form-addbutton').bind('click', this.add_node);

            // Read checkboxes may ask search widget being enabled.
            var selector = '.' + namespace + '-system-read';
            selector += ',.' + namespace + '-indexer-read';
            selector += ',.' + namespace + '-author-read';
            $(selector).off('change');
            $(selector).bind('change', this.enable_search_widget_checkbox);

            $('.taxonomy-source').bind('change', this.reload_taxonomy);
            // Change to this form : indefinitely add binding to all now and future elements.
            $('#id-mtd-form').on('change', '.taxonomy-source', null, this.reload_taxonomy);
        },

        trigger_all: function() {
            log.debug('AMD Mod sharedresource metadata edition triggering changes');
            $('.mtd-form-element input[type="text"]').trigger('keyup');
            // On selects, triggering empties preload choices on taxonomy.
            // $('.mtd-form-element select').trigger('change');
            $('.mtd-form-element textarea').trigger('keyup');
        },

        init: function(args) {

            namespace = args;

            var stringsreq = [
                {
                    key: 'fillprevious',
                    component: 'sharedresource'
                },
                {
                    key: 'fillcategory',
                    component: 'sharedresource'
                },
            ];

            str.get_strings(stringsreq).done(function(strings) {
                mtdstrings = strings;
            });

            log.debug('AMD Mod sharedresource BIND ALL CALL');
            metadataedit.bind_all();
            metadataedit.bind_tabs();
            metadataedit.trigger_all();

            // check and set the initial button state.
            $('.mtd-form-addbutton').each(metadataedit.check_button_status);

            log.debug('AMD Mod sharedresource metadata edition form initialized');

            $('.mtd-tab').removeClass('here');
            $('.mtd-tab').removeClass('current');
            $('#id-menu-1').addClass('here');
            $('#id-menu-1').addClass('current');

            $('.mtd-content').removeClass('active');
            $('.mtd-content').removeClass('on');
            $('.mtd-content').addClass('off');
            $('#id-tab-1').addClass('active');
            $('#id-tab-1').removeClass('off');
            $('#id-tab-1').addClass('on');

            log.debug('AMD Mod sharedresource metadata default switch to tab General');
        },

        check_button_status: function() {
            var that = $(this);
            log.debug('Check add button ' + that.attr('id'));
            var elementname = that.attr('id').replace('id-add-', '');
            var deps = that.attr('data');
            var depsarr = deps.split(';');
            var hassomeempty = false;
            var depelm = '';
            var dep;

            dep = depsarr.shift();
            while (dep !== undefined) {
                depelm = $('#' + dep);
                log.debug('   Check button dep ' + depelm.attr('id'));
                if (depelm.parent('.mtd-form-element').hasClass('is-empty')) {
                    hassomeempty = true;
                    break;
                }
                dep = depsarr.shift();
            }

            if (hassomeempty) {
                that.prop('disabled', true);
            } else {
                that.prop('disabled', false);
            }

            metadataedit.activate_up_chain(elementname);
        },

        switch_tab: function(e) {

            e.stopPropagation();

            // Target is a form tab link, or a form tab LI.
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

        add_node: function(e) {

            e.stopPropagation();

            // Target is the add button.
            var that = $(this);

            // Comes in order : "btn-$elmname-$fieldtype"
            /*
             * elmname : the complete occurence id as xny_wnz (ex : 1n1_1n2, which is the JS id
             * base for the second instance of element 1_1
             * fieldtype : typeof field
             */
            var regexp = /btn-([^-]+)-([^-]+)/;
            var matches = that.attr('name').match(regexp);

            var elmid = matches[1];
            var fieldtype = matches[2];
            var rgx = '';

            var realoccur = that.attr('data-occur');
            log.debug("found real occur as " + realoccur);

            if (fieldtype != 'category') {
                switch (fieldtype) {
                    case 'text':
                    case 'codetext': {
                        if ($('#' + elmid).val() === '') {
                            window.alert(mtdstrings[0]);
                            return;
                        }
                        break;
                    }

                    case 'select': {
                        if ($('#' + elmid).val() === 'basicvalue') {
                            window.alert(mtdstrings[0]);
                            return;
                        }
                        break;
                    }

                    case 'date': {
                        if ($('#' + elmid + "_dateyear").val() === '- Year -') {
                            window.alert(mtdstrings[0]);
                            return;
                        }
                        break;
                    }

                    case 'duration': {
                        if (($('#' + elmid+"_Day").val() === '' || $('#' + elmid + "_Day").val() === '0') &&
                            ($('#' + elmid + "_Hou").val() === '' || $('#' + elmid + "_Hou").val() === '0') &&
                                ($('#' + elmid + "_Min").val() === '' || $('#' + elmid + "_Min").val() === '0') &&
                                    ($('#' + elmid + "_Sec").val() === '' || $('#' + elmid+"_Sec").val() === '0')) {
                            window.alert(mtdstrings[0]);
                            return;
                        }
                        break;
                    }

                    case 'vcard': {
                        // eslint-disable-next-line no-control-regex
                        rgx = new RegExp("(\r\n|\r|\n)", "g");
                        if ($('#' + elmid).val().replace(rgx,'').replace(/ /g, '') === 'BEGIN:VCARDVERSION:FN:N:END:VCARD') {
                            window.alert(mtdstrings[0]);
                            return;
                        }
                        break;
                    }
                }

                metadataedit.add_list_item(elmid, realoccur);
            } else {
                // This is a category. We need adding a full new branch.
                //  Check we really need to add an item (something filled in the subs  ?)
                var listchildren = that.attr('data');
                var listtab = listchildren.split(' ');
                var nbremptyfield = 0;
                var childid;

                for (var i = 0; i < listtab.length; i++) {

                    childid = listtab[i];

                    if ($('#' + childid).val() === '') {
                        nbremptyfield++;
                    }
                    if ($('#' + childid).val() === 'basicvalue') {
                        nbremptyfield++;
                    }
                    if ($('#' + childid + "_dateyear").val() === '-year-') {
                        nbremptyfield++;
                    }
                    if (($('#' + childid + "_Day").val() === '' || $('#' + childid + "_Day").val() === '0') &&
                        ($('#' + childid + "_Hou").val() === '' || $('#' + childid + "_Hou").val() === '0') &&
                            ($('#' + childid + "_Min").val() === '' || $('#' + childid + "_Min").val() === '0') &&
                                ($('#' + childid + "_Sec").val() === '' || $('#' + childid + "_Sec").val() === '0') ) {
                        nbremptyfield++;
                    }
                    if ($('#' + childid).val() === 'undefined') {
                        // eslint-disable-next-line no-control-regex
                        rgx = new RegExp("(\r\n|\r|\n)", "g" );
                        if ($('#' + childid).val().replace(rgx, '').replace(/ /g, '') === 'BEGIN:VCARDVERSION:FN:N:END:VCARD') {
                            nbremptyfield++;
                        }
                    }
                }

                if (nbremptyfield == listtab.length) {
                    window.alert(mtdstrings[1]);
                } else {
                    // Our current taxonsource can be found by following the route :
                    // 1. button clicked has last taxon in 'data'.
                    // 2. last taxon select has its source select ref in 'source'
                    // 3. source select has current value of taxon source
                    var sourcetaxons = that.attr('data').split(' ');
                    var basetaxon = sourcetaxons.pop();
                    log.debug("Get source taxon in #" + metadata.parent_name(basetaxon) + '_1n0');
                    var sourceselect = $('#' + metadata.parent_name(basetaxon) + '_1n0');
                    var taxonsourceid = sourceselect.val();
                    log.debug("Resulting taxonresourceid  = " + taxonsourceid);

                    metadataedit.add_list_item(elmid, realoccur, taxonsourceid);
                }
            }
        },

        /**
         * Calls an ajax renderer to produce a new form subtree fragment.
         * keyid : the current element instanceid (m_n_o:x_y_z)
         * numoccur : the occurrence number of the node (int)
         * realoccur :
         */
        add_list_item: function(elmname, realoccur, taxonsourceid) {

            var oldid = 'id-add-' + elmname;
            var olddata = $('#' + oldid).attr('data');

            var newname = metadata.next_occurrence_name(elmname);
            realoccur++;
            log.debug("Next real occur is " + realoccur);

            // Get form fragment for next occurrence.
            var params = "elementname=" + newname;
            if (taxonsourceid) {
                params += "&taxonsourceid=" + taxonsourceid;
            }
            params += "&realoccur=" + realoccur;
            var url = cfg.wwwroot + "/mod/sharedresource/ajax/getformelement.php?" + params;

            $.get(url, function(data) {
                var zonename = "#add-zone-" + elmname;
                $(data.html).insertBefore(zonename);

                // Recode the add button id for next play, incrementing occurence index.
                oldid = CSS.escape(oldid);

                var newid = 'id-add-' + newname;
                newid = CSS.escape(newid);

                // We get the new incremented occurrence name from AJAX return.
                $('#' + oldid).attr('name', 'btn-' + data.name);
                // Change the button id so next press launches query for next occurence.
                $('#' + oldid).attr('id', newid);
                $('#' + newid).prop('disabled', true);
                $('#' + newid).attr('data-occur', realoccur);
                log.debug("Shifting button " + newid + " real occur as " + realoccur);
                // Shift add zone name to match new button.
                $('#add-zone-' + elmname).attr('id', 'add-zone-' + newname);
                $('#' + newid).attr('data', olddata);

                // Rebind all change handlers, including on new elements.
                metadataedit.bind_all();
                metadataedit.bind_tabs();

            }, 'json');
        },

        activate_up_chain: function(elementname) {

            // Unlock upper buttons in chain.
            var parentname = elementname;
            log.debug("Processing to elementkey " + elementname);
            parentname = metadata.parent_name(parentname);
            while (parentname) {
                log.debug("Escalading to parentkey " + parentname);
                log.debug("Detected " + $('#id-add-' + parentname).attr('name'));
                log.debug("Detected " + $('#id-add-' + parentname).prop('disabled'));
                $('#id-add-' + CSS.escape(parentname)).prop('disabled', false);
                parentname = metadata.parent_name(parentname);
            }
        },

        reload_taxonomy: function(e) {

            e.stopPropagation();

            var that = $(this);

            // Fetch new taxonomy.
            var url = cfg.wwwroot + '/mod/sharedresource/ajax/gettaxonomymenu.php?id=' + that.val();
            $.get(url, function(data){

                var parentid = metadata.parent_name(that.attr('id'));

                // Clear out all values and change option list.
                $('[data-source="' + parentid + '"]').each(function() {
                    $(this).html(data);
                    $(this).val('');
                });

            }, 'html');

        },

        check_empty: function() {
            var that = $(this);
            log.debug("Check " + that.attr('id'));
            var branchid = that.attr('id').substring(0, 1);

            if (that.val()) {
                // mark element as fullfilled.
                that.parent('.mtd-form-element').removeClass('is-empty');

                // mark same branch tab as fullfilled.
                var otheremptyonbranch = $('.mtd-form-element.is-mandatory-' + branchid + '.is-empty');
                if (!otheremptyonbranch || (otheremptyonbranch.length === 0)) {
                    $('#id-menu-' + branchid).removeClass('is-empty');
                }

                // unlock form submit if no more empty on whole form.
                var otherempty = $('.mtd-form-element.is-mandatory.is-empty');
                if (!otherempty || (otherempty.length === 0)) {
                    $('#id-mtd-submit').attr('disabled', false);
                    $('#id-mtd-submit').removeClass('is-disabled');
                }

            } else {
                // mark element as empty.
                that.parent('.mtd-form-element').addClass('is-empty');

                if (that.hasClass('is-mandatory')) {
                    // lock form submit.
                    $('#id-mtd-submit').attr('disabled', true);
                    $('#id-mtd-submit').addClass('is-disabled');

                    // mark same branch tab as empty.
                    $('#id-menu-' + branchid).addClass('is-empty');
                }
            }

            // Whatever the result, search the potentially affected buttons and update them.
            var affectedaddbtns = $('input[name^="btn-' + that.attr('id') + '-"]');
            affectedaddbtns.each(metadataedit.check_button_status);
        }
    };

    return metadataedit;
});