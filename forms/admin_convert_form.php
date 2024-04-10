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
 * forms for converting resources to sharedresources
 *
 * @package    mod_sharedresource
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class sharedresource_choosecourse_form extends moodleform {

    public function __construct($courses) {
        $this->courses = $courses;
        parent::__construct();
    }

    public function definition() {
        $mform = & $this->_form;

        $select = &$mform->addElement('select', 'course', get_string('courses'), $this->courses);

        // Adding submit and reset button.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
        $buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
}

class sharedresource_selectresources_form extends moodleform {

    public function definition() {

        $mform = & $this->_form;

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);

        $hasitems = false;

        if (!empty($this->_customdata['resources'])) {

            $hasitems = true;

            foreach ($this->_customdata['resources'] as $r) {
                $name = format_string($r->name);
                $label = get_string('resource').':';
                $mform->addElement('advcheckbox', 'rcnv_'.$r->id, $label, $name, array('group' => 1), array(0, 1));
                $mform->setDefault('rcnv_'.$r->id, 1);
                $mform->addElement('static', 'lbl_'.$r->id, '', format_string($r->intro, $r->introformat));
            }

            $convertstr = get_string('convert', 'sharedresource');
        }

        if (!empty($this->_customdata['urls'])) {

            $hasitems = true;

            foreach ($this->_customdata['urls'] as $u) {
                $label = get_string('url').':';
                $mform->addElement('advcheckbox', 'ucnv_'.$u->id, $label, $u->externalurl, array('group' => 1), array(0, 1));
                $mform->setDefault('ucnv_'.$u->id, 1);
                $mform->addElement('static', 'lbl_'.$u->id, get_string('description').':', @$u->intro);
            }
        }

        if ($hasitems) {
            $this->add_checkbox_controller(1, '', '');
        }

        $convertstr = get_string('convert', 'sharedresource');
        $this->add_action_buttons(true, $convertstr);
    }
}
