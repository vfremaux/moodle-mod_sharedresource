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
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod_sharedresource
 * @category mod
 */
defined('MOODLE_INTERNAL') || die();

require($CFG->libdir.'/formslib.php');

class token_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        $classif = $mform->_customdata['classif'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('select', $classif->sqlparent, get_string('parent', 'sharedresource'), $classif->parents);
        $mform->setType($classif->sqlparent, PARAM_INT);

        $mform->addElement('text', 'name', get_string('classificationname', 'sharedresource'), array('size' => 70));
        $mform->setType('name', PARAM_TEXT);

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

    }

    public function validation($data, $files = null) {

        $errors = array();

        if (empty($data['label'])) {
            $errors['label'] = get_string('erroremptytokenname', 'sharedresource');
        }

        return $errors;
    }

}
