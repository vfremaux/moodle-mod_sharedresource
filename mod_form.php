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

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package    mod_sharedresource
 * @category   mod
 */
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_sharedresource_mod_form extends moodleform_mod {
    public $_resinstance;

    public function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        // This hack is needed for different settings of each subtype.
        if (!empty($this->_instance)) {
            if (!$res = $DB->get_record('sharedresource', array('id' => (int)$this->_instance))) {
                print_error('errorinstance', 'sharedresource');
            }
        }

        require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_base.class.php');
        if (isset($this->_cm)) {
            $this->_resinstance = new sharedresource_base($this->_cm->id);
        } else {
            $this->_resinstance = new sharedresource_base();
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'sharedresource'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('header', 'typedesc', get_string('resourcetypefile', 'sharedresource'));

        $this->_resinstance->setup_elements($mform);

        $this->standard_coursemodule_elements(array('groups' => false, 'groupmembersonly' => true, 'gradecat' => false));

        $this->add_action_buttons();
    }

    public function data_preprocessing(&$default_values) {
        $this->_resinstance->setup_preprocessing($default_values);
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
