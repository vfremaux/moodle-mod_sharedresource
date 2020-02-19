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
 * @package mod_sharedresource
 * @category mod
 * @author Valery Fremaux (valery@club-internet.fr)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL
*/
defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

class import_config_form extends moodleform {

    function __construct($action) {
        parent::__construct($action);
    }

    function definition() {

        $mform = $this->_form;

        $label = get_string('dropconfig', 'mod_sharedresource');
        $mform->addElement('textarea', 'configdata', $label, array('rows' => 5, 'cols' => 100));

        $this->add_action_buttons();
    }
}