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
 * form to edit classification values
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux  valery.fremaux@gmail.com
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

require($CFG->libdir.'/formslib.php');

/**
 * Classification values form
 */
class classificationvalue_form extends moodleform {

    /**
     * Standard definition
     */
    public function definition() {
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

        $mform->addElement('text', 'value', get_string('tokenvalue', 'sharedresource'), ['size' => 48]);
        $mform->setType('value', PARAM_TEXT);
        $mform->addRule('value', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'idnumber', get_string('idnumber'), ['size' => 48]);
        $mform->setType('idnumber', PARAM_TEXT);
        $mform->setAdvanced('idnumber');

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Standard validation
     * @param object $data
     * @param array $files
     */
    public function validation($data, $files = null) {

        $errors = [];

        if (empty($data['value'])) {
            $errors['name'] = get_string('erroremptytokenvalue', 'sharedresource');
        }
        return $errors;
    }
}
