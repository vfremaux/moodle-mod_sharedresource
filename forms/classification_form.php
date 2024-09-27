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
 * Form for classifiction of the resource.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux  valery.fremaux@gmail.com
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

require($CFG->libdir.'/formslib.php');

/**
 * classification form.
 */
class classification_form extends moodleform {

    /**
     * Standard definition
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'hdr0', get_string('addclassificationtitle', 'sharedresource'), '');
        $mform->addHelpButton('hdr0', 'addclassification', 'sharedresource');

        $mform->addElement('text', 'shortname', get_string('shortname'), ['size' => 48]);
        $mform->setType('shortname', PARAM_TEXT);
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('classificationname', 'sharedresource'), ['size' => 70]);
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('checkbox', 'enabled', get_string('enabled', 'sharedresource'));
        $mform->setType('enabled', PARAM_BOOL);

        $mform->addElement('text', 'tablename', get_string('tablename', 'sharedresource'), ['size' => 50]);
        $mform->setType('tablename', PARAM_TEXT);
        $mform->setDefault('tablename', 'sharedresource_taxonomy');
        $mform->addRule('tablename', get_string('required'), 'required', null, 'client');

        $mform->addElement('header', 'hdr1', get_string('sqlmapping', 'sharedresource'), '');

        $mform->addElement('text', 'sqlid', get_string('idname', 'sharedresource'), ['size' => 20]);
        $mform->setType('sqlid', PARAM_TEXT);
        $mform->setDefault('sqlid', 'id');
        $mform->addRule('sqlid', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'sqlparent', get_string('parentname', 'sharedresource'), ['size' => 20]);
        $mform->setType('sqlparent', PARAM_TEXT);
        $mform->setDefault('sqlparent', 'parent');
        $mform->addRule('sqlparent', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'sqllabel', get_string('labelname', 'sharedresource'), ['size' => 20]);
        $mform->setType('sqllabel', PARAM_TEXT);
        $mform->setDefault('sqllabel', 'value');
        $mform->addRule('sqllabel', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'sqlsortorder', get_string('orderingname', 'sharedresource'), ['size' => 20]);
        $mform->setType('sqlsortorder', PARAM_TEXT);
        $mform->setDefault('sqlsortorder', 'sortorder');
        $mform->addRule('sqlsortorder', get_string('required'), 'required', null, 'client');

        $mform->addElement('header', 'hdr2', get_string('sqloptions', 'sharedresource'), '');

        $orderingopts['0'] = '0';
        $orderingopts['1'] = '1';
        $mform->addElement('select', 'sqlsortorderstart', get_string('orderingname', 'sharedresource'), $orderingopts);
        $mform->setType('sqlsortorderstart', PARAM_INT);
        $mform->setDefault('sqlsortorderstart', '1');

        $mform->addElement('text', 'sqlrestriction', get_string('sqlrestriction', 'sharedresource'), ['size' => 20]);
        $mform->setType('sqlrestriction', PARAM_TEXT);
        $mform->addHelpButton('sqlrestriction', 'sqlrestriction', 'sharedresource');

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

        if (empty($data['name'])) {
            $errors['name'] = get_string('erroremptyname', 'sharedresource');
        }

        if (empty($data['tablename'])) {
            $errors['name'] = get_string('erroremptytablename', 'sharedresource');
        }

        return $errors;
    }
}
