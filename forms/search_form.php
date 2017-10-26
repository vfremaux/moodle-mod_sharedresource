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
 * @author  Piers Harding  piers@catalyst.net.nz
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod_sharedresource
 * @category mod
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class mod_sharedresource_search_form extends moodleform {

    public function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        $searchinlibrary = get_string('searchinlibrary', 'sharedresource');
        $mform->addElement('header', 'searchheader',  get_string('searchheader', 'sharedresource'));

        $addbutton = $mform->addElement('submit', 'sharedresource', $searchinlibrary);

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);

        $mform->addElement('hidden', 'add');
        $mform->setType('add', PARAM_ALPHA);

        $mform->addElement('hidden', 'return');
        $mform->setType('return', PARAM_BOOL);

        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_ALPHANUM);

        $mform->addElement('hidden', 'section');
        $mform->setType('section', PARAM_ALPHANUM);

        if (!empty($mform->_customdata['addsharedresourceurl'])) {
            $mform->addElement('header', 'addheader',  get_string('addheader', 'sharedresource'));
            $addbutton2 = $mform->addElement('submit', 'addsharedresource', get_string('addsharedresource', 'sharedresource'));
            $buttonattributes2 = array('title' => get_string('addsharedresource', 'sharedresource'), 'onclick' => "location.href = '"
                               .$mform->_customdata['addsharedresourceurl']."'; return false;");
            $addbutton2->updateAttributes($buttonattributes2);
        }

        $this->add_action_buttons(false, get_string('addsharedresource', 'sharedresource'));
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
