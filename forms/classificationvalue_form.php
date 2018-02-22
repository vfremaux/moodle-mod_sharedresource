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
 * @author  Valery Fremaux  valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package mod_sharedresource
 * @category mod
 */
defined('MOODLE_INTERNAL') || die();

require($CFG->libdir.'/formslib.php');

class classificationvalue_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'classificationid');
        $mform->setType('classificationid', PARAM_INT);

        $mform->addElement('hidden', 'parent');
        $mform->setType('parent', PARAM_INT);

        $mform->addElement('hidden', 'sortorder');
        $mform->setType('sortorder', PARAM_INT);

        $mform->addElement('header', 'hdr0', get_string('addtoken', 'sharedresource'), '');

        $mform->addElement('text', 'value', get_string('tokenvalue', 'sharedresource'), array('size' => 48));
        $mform->setType('value', PARAM_TEXT);
        $mform->addRule('value', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'idnumber', get_string('idnumber'), array('size' => 48));
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->setAdvanced('idnumber');

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

    }

    public function validation($data, $files = null) {

        $errors = array();

        if (empty($data['value'])) {
            $errors['name'] = get_string('erroremptytokenvalue', 'sharedresource');
        }

        return $errors;
    }

}
