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
define(['jquery', 'core/str', 'core/log', 'mod_sharedresource/metadata'], function ($, str, log, metadata) {

    var namespace;
    var mtdstrings;

    var metadataedit = {

        init: function(args) {
            namespace = args;

            stringsreq = [
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

            $('.mtd-form-addbutton').bind('click', this.add_node);
            $('.mtd-tab').bind('click', this.switch_tab);
            $('.mtd-form-input').bind('change', this.activate_add_button);
            $('.mtd-tab a').bind('click', function(e) { e.preventDefault(); });

            // Read checkboxes may ask search widget being enabled.
            selector = '.' + namespace + '-system-read';
            selector += ',.' + namespace + '-indexer-read';
            selector += ',.' + namespace + '-author-read';
            $(selector).bind('change', this.enable_search_widget_checkbox);
            $('.taxonomy-source').bind('change', this.reload_taxonomy);

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

        switch_tab: function(e) {

            e.stopPropagation();

            // Target is a form tab link, or a form tab LI.
            that = $(this);
            if (!that.attr('id')) {
                that = that.parent();
            }

            var menuid = that.attr('id');

            regexp = /id-menu-([^-]+)$/;
            matches = that.attr('id').match(regexp);
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
            that = $(this);

            // Comes in order : "btn-$elmname-$fieldtype"
            /*
             * elmname : the complete occurence id as xny_wnz (ex : 1n1_1n2, which is the JS id base for the second instance of element 1_1
             * fieldtype : typeof field
             */
            regexp = /btn-([^-]+)-([^-]+)/;
            matches = that.attr('name').match(regexp);

            var elmid = matches[1];
            var fieldtype = matches[2];

            var islist = that.hasClass('is-list');
            var realoccur = 0; // To be changed.

            if (fieldtype != 'category') {
                switch (fieldtype) {
                    case 'text':
                    case 'codetext': {
                        if ($('#' + elmid).val() === '') {
                            alert(mtdstrings[0]);
                            return;
                        }
                        break;
                    }

                    case 'select': {
                        if ($('#' + elmid).val() === 'basicvalue') {
                            alert(mtdstrings[0]);
                            return;
                        }
                        break;
                    }

                    case 'date': {
                        if ($('#' + elmid + "_dateyear").val() === '- Year -') {
                            alert(mtdstrings[0]);
                            return;
                        }
                        break;
                    }

                    case 'duration': {
                        if (($('#' + elmid+"_Day").val() === '' || $('#' + elmid + "_Day").val() === '0') &&
                            ($('#' + elmid + "_Hou").val() === '' || $('#' + elmid + "_Hou").val() === '0') &&
                                ($('#' + elmid + "_Min").val() === '' || $('#' + elmid + "_Min").val() === '0') &&
                                    ($('#' + elmid + "_Sec").val() === '' || $('#' + elmid+"_Sec").val() === '0')) {
                            alert(mtdstrings[0]);
                            return;
                        }
                        break;
                    }

                    case 'vcard': {
                        if ($('#' + elmid).val().replace(new RegExp("(\r\n|\r|\n)", "g" ),'').replace(/ /g, '') === 'BEGIN:VCARDVERSION:FN:N:END:VCARD') {
                            alert(mtdstrings[0]);
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
                var listtab = listchildren.split(';');
                var nbremptyfield = 0;

                for (i = 0; i < listtab.length; i++) {

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
                        if ($('#' + childid).val().replace(new RegExp("(\r\n|\r|\n)", "g" ), '').replace(/ /g, '') === 'BEGIN:VCARDVERSION:FN:N:END:VCARD') {
                            nbremptyfield++;
                        }
                    }
                }
                if (nbremptyfield == listtab.length) {
                    alert(mtdstrings[1]);
                } else {
                    metadataedit.add_list_item(elmid, realoccur)
                }
            }
        },

        /**
         * Calls an ajax renderer to produce a new form subtree fragment.
         * keyid : the current element instanceid (m_n_o:x_y_z)
         * numoccur : the occurrence number of the node (int)
         * realoccur :
         */
        add_list_item: function(elmname, realoccur) {

            newname = metadata.next_occurrence_name(elmname);

            // Get form fragment for next occurrence.
            var params = "elementname=" + newname;
            params += "&realoccur=" + realoccur;
            var url = M.cfg.wwwroot + "/mod/sharedresource/ajax/getformelement.php?" + params;

            $.get(url, function(data, status) {
                zonename = "#add-zone-" + elmname;
                $(data.html).insertBefore(zonename);

                // Recode the add button id for next play, incrementing occurence index
                var oldid = 'id-add-' + elmname;
                oldid = CSS.escape(oldid);

                var newid = 'id-add-' + newname;
                newid = CSS.escape(newid);

                // We get the new incremented occurrence name from AJAX return.
                $('#' + oldid).attr('name', 'btn-' + data.name);
                // Change the button id so next press launches query for next occurence.
                $('#' + oldid).attr('id', newid);
                $('#' + newid).prop('disabled', true);
                // Shift add zone name to match new button.
                $('#add-zone-' + elmname).attr('id', 'add-zone-' + newname);

                // Rebind all change handlers, including on new elements.
                $('#' + elmname).unbind('change');
                $('#' + newname).bind('change', metadataedit.activate_add_button);

            }, 'json');
        },

        activate_add_button: function(e) {

            e.stopPropagation();

            // Target is an input field.
            that = $(this);

            elementname = that.attr('id');

            if (that.val()) {
                $('#id-add-' + elementname).prop('disabled', false);
            } else {
                $('#id-add-' + elementname).prop('disabled', true);
            }
            parentname = elementname;
            log.debug("Processing to elementkey " + elementname);
            while (parentname = metadata.parent_name(parentname)) {
                log.debug("Escalading to parentkey " + parentname);
                log.debug("Detected " + $('#id-add-' + parentname).attr('name'));
                log.debug("Detected " + $('#id-add-' + parentname).prop('disabled'));
                $('#id-add-' + CSS.escape(parentname)).prop('disabled', false);
            }
        },

        reload_taxonomy: function(e) {

            e.stopPropagation();

            that = $(this);
            var taxonomyoptionshtml = '';

            // Fetch new taxonomy.
            url = M.cfg.wwwroot + '/mod/sharedresource/ajax/gettaxonomymenu.php?id=' + that.val();
            $.get(url, function(data){

                parentid = metadata.parent_name(that.attr('id'));

                // Clear out all values and change option list.
                $('[data-source="' + parentid + '"]').each(function(index) {
                    $(this).html(data);
                    $(this).val('');
                });

            }, 'html');

        }
    };

    return metadataedit;
});