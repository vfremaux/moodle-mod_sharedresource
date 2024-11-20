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
 * Standard course module form.
 *
 * @package     mod_sharedresource
 * @author      Piers Harding  <piers@catalyst.net.nz>
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/sharedresource_base.class.php');

/**
 * Course module form.
 */
class mod_sharedresource_mod_form extends moodleform_mod {

    /** @var sharedresource instance */
    public $resourceinstance;

    /**
     * Standard definition
     */
    public function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        if (isset($this->_cm)) {
            $this->resourceinstance = new \mod_sharedresource\base($this->_cm->id);
        } else {
            /*
             * this is a new instance we do not have cm yet, but should have received entryid from query string
             * as an addition result.
             */
            $entryid = optional_param('entryid', false, PARAM_INT);
            if ($entryid) {
                $identifier = $DB->get_field('sharedresource_entry', 'identifier', ['id' => $entryid]);
                $this->resourceinstance = new \mod_sharedresource\base(null, $identifier);
            } else {
                $this->resourceinstance = new \mod_sharedresource\base(null, null);
            }
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'sharedresource'), ['size' => '48']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $config = get_config('sharedresource');
        $pluginname = $config->schema;
        $plugin = sharedresource_get_plugin($pluginname, null);
        $descriptionelementnodeid = $plugin->get_description_element()->node;
        $key = 'config_'.$pluginname.'_mandatory_'.$descriptionelementnodeid;
        $required = get_config($key, 'sharedmetadata_'.$pluginname);

        if ($required) {
            $mform->addRule('introeditor', get_string('required'), 'required', null, 'client');
        }

        $mform->addElement('header', 'typedesc', get_string('resourcetypefile', 'sharedresource'));

        $this->resourceinstance->setup_elements($mform);

        $this->standard_coursemodule_elements(['groups' => false, 'groupmembersonly' => true, 'gradecat' => false]);

        $this->add_action_buttons();
    }

    /**
     * preprocesses form data
     * @param array $default_values
     */
    public function data_preprocessing(& $defaultvalues) {
        $this->resourceinstance->setup_preprocessing($defaultvalues);
    }

    /**
     * Standard validation
     * @param object $data
     * @param array $files
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
