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
 * This file is a clone from main lti client form used by sharedresource.
 *
 * @package    mod_sharedresource
 * @see /mod/lti/mod_form.php
 * @copyright  2009 Marc Alier, Jordi Piguillem, Nikolas Galanis, marc.alier@upc.edu
 * @copyright  2009 Universitat Politecnica de Catalunya http://www.upc.edu
 * @author     Marc Alier
 * @author     Jordi Piguillem
 * @author     Nikolas Galanis
 * @author     Chris Scribner
 * @author     Valery Fremaux (VF Consulting)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
 * Provided to easily make a new client instance in a course from a tool definition stored in sharedresource
 * library
 *
 * The client takes most of its configuration data from the sharedresource informations, such as
 * name, description and tool end point url.
 *
 * At the moment, there is not yet provision to store some LTI secret key in that record, as
 * we lack of secure fields recoding in sharedresource metadata, and those fields would not be
 * LOM compliant. So we still need the secret be known by the user that deployes the LTI tool
 * from the library.
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

/**
 * Make LTI form.
 */
class lti_mod_form extends moodleform {

    /**
     * Standard definition
     */
    public function definition() {
        global $PAGE, $OUTPUT, $USER, $COURSE;

        $this->typeid = 0;

        $mform =& $this->_form;

        // Contextual addtolocalresource transaction params.
        $mform->addElement('hidden', 'id'); // Course id.
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'section');
        $mform->setType('section', PARAM_INT);

        $mform->addElement('hidden', 'url'); // Shared resource url.
        $mform->setType('url', PARAM_TEXT);

        $mform->addElement('hidden', 'title');
        $mform->setType('title', PARAM_TEXT);

        $mform->addElement('hidden', 'description');
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('hidden', 'provider');
        $mform->setType('provider', PARAM_TEXT);

        $mform->addElement('hidden', 'mode');
        $mform->setType('mode', PARAM_TEXT);

        // Shared resource identifier.
        $mform->addElement('hidden', 'identifier');
        $mform->setType('identifier', PARAM_TEXT);

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));
        // Adding the standard "name" field.

        $mform->addElement('text', 'name', get_string('basicltiname', 'lti'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('editor', 'introeditor', get_string('description'), null, []);
        $mform->setAdvanced('introeditor');

        // Display the label to the right of the checkbox so it looks better & matches rest of the form.
        $mform->addElement('checkbox', 'showdescription', get_string('showdescription'));
        $mform->setAdvanced('showdescription');

        $mform->addElement('checkbox', 'showtitlelaunch', '&nbsp;', ' ' . get_string('display_name', 'lti'));
        $mform->setAdvanced('showtitlelaunch');
        $mform->addHelpButton('showtitlelaunch', 'display_name', 'lti');

        $mform->addElement('checkbox', 'showdescriptionlaunch', '&nbsp;', ' '. get_string('display_description', 'lti'));
        $mform->setAdvanced('showdescriptionlaunch');
        $mform->addHelpButton('showdescriptionlaunch', 'display_description', 'lti');

        $mform->addElement('hidden', 'toolurl');
        $mform->setType('toolurl', PARAM_TEXT);

        $mform->addElement('hidden', 'securetoolurl');
        $mform->setType('securetoolurl', PARAM_TEXT);

        $launchoptions = [];
        $launchoptions[LTI_LAUNCH_CONTAINER_DEFAULT] = get_string('default', 'lti');
        $launchoptions[LTI_LAUNCH_CONTAINER_EMBED] = get_string('embed', 'lti');
        $launchoptions[LTI_LAUNCH_CONTAINER_EMBED_NO_BLOCKS] = get_string('embed_no_blocks', 'lti');
        $launchoptions[LTI_LAUNCH_CONTAINER_WINDOW] = get_string('new_window', 'lti');

        $mform->addElement('select', 'launchcontainer', get_string('launchinpopup', 'lti'), $launchoptions);
        $mform->setType('launchcontainer', PARAM_TEXT);
        $mform->setDefault('launchcontainer', LTI_LAUNCH_CONTAINER_DEFAULT);
        $mform->addHelpButton('launchcontainer', 'launchinpopup', 'lti');
        $mform->setAdvanced('launchcontainer');

        $mform->addElement('hidden', 'resourcekey');
        $mform->setType('resourcekey', PARAM_TEXT);

        $mform->addElement('passwordunmask', 'password', get_string('password', 'lti'));
        $mform->setType('password', PARAM_TEXT);
        $mform->addHelpButton('password', 'password', 'lti');

        $mform->addElement('hidden', 'instructorcustomparameters');
        $mform->setType('instructorcustomparameters', PARAM_TEXT);

        $mform->addElement('text', 'icon', get_string('icon_url', 'lti'), ['size' => '64']);
        $mform->setType('icon', PARAM_TEXT);
        $mform->setAdvanced('icon');
        $mform->addHelpButton('icon', 'icon_url', 'lti');

        $mform->addElement('text', 'secureicon', get_string('secure_icon_url', 'lti'), ['size' => '64']);
        $mform->setType('secureicon', PARAM_TEXT);
        $mform->setAdvanced('secureicon');
        $mform->addHelpButton('secureicon', 'secure_icon_url', 'lti');

        // Add privacy preferences fieldset where users choose whether to send their data.
        $mform->addElement('header', 'privacy', get_string('privacy', 'lti'));

        $mform->addElement('checkbox', 'instructorchoicesendname', '&nbsp;', ' ' . get_string('share_name', 'lti'));
        $mform->setDefault('instructorchoicesendname', '1');
        $mform->addHelpButton('instructorchoicesendname', 'share_name', 'lti');

        $mform->addElement('checkbox', 'instructorchoicesendemailaddr', '&nbsp;', ' ' . get_string('share_email', 'lti'));
        $mform->setDefault('instructorchoicesendemailaddr', '1');
        $mform->addHelpButton('instructorchoicesendemailaddr', 'share_email', 'lti');

        $mform->addElement('checkbox', 'instructorchoiceacceptgrades', '&nbsp;', ' ' . get_string('accept_grades', 'lti'));
        $mform->setDefault('instructorchoiceacceptgrades', '1');
        $mform->addHelpButton('instructorchoiceacceptgrades', 'accept_grades', 'lti');

        // Add standard elements, common to all modules.

        // Fake standard course module elements adding grade.
        $mform->addElement('header', 'gradeheader', get_string('scale'));

        $mform->addElement('modgrade', 'grade', get_string('grade'));
        $mform->setDefault('grade', 100);

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

        $params = ['sesskey' => $USER->sesskey, 'course' => $COURSE->id];
        $editurl = new moodle_url("/mod/lti/instructor_edit_tool_type.php", $params);
        $ajaxurl = new moodle_url('/mod/lti/ajax.php');

        $jsinfo = (object)[
                        'edit_icon_url' => (string)$OUTPUT->image_url('t/edit'),
                        'add_icon_url' => (string)$OUTPUT->image_url('t/add'),
                        'delete_icon_url' => (string)$OUTPUT->image_url('t/delete'),
                        'green_check_icon_url' => (string)$OUTPUT->image_url('i/valid'),
                        'warning_icon_url' => (string)$OUTPUT->image_url('warning', 'lti'),
                        'instructor_tool_type_edit_url' => $editurl->out(false),
                        'ajax_url' => $ajaxurl->out(true),
                        'courseId' => $COURSE->id,
                  ];

        $module = [
            'name'      => 'mod_lti_edit',
            'fullpath'  => '/mod/lti/mod_form.js',
            'requires'  => ['base', 'io', 'querystring-stringify-simple', 'node', 'event', 'json-parse'],
            'strings'   => [
                ['addtype', 'lti'],
                ['edittype', 'lti'],
                ['deletetype', 'lti'],
                ['delete_confirmation', 'lti'],
                ['cannot_edit', 'lti'],
                ['cannot_delete', 'lti'],
                ['global_tool_types', 'lti'],
                ['course_tool_types', 'lti'],
                ['using_tool_configuration', 'lti'],
                ['domain_mismatch', 'lti'],
                ['custom_config', 'lti'],
                ['tool_config_not_found', 'lti'],
                ['forced_help', 'lti'],
            ],
        ];

        $PAGE->requires->js_init_call('M.mod_lti.editor.init', [json_encode($jsinfo)], true, $module);
    }

    /**
     * Function overwritten to change default values using
     * global configuration
     *
     * @param array $default_values passed by reference
     */
    public function data_preprocessing(& $defaultvalues) {
        assert(true);
    }
}
