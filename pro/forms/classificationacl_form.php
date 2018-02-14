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

class classificationacl_form extends moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form;

        $repeat = array();
        $repeat[] = $mform->createElement('header', 'hdr0', get_string('byprofilefield', 'sharedresource').' {no}', '');
        $repeat[] = $mform->createElement('text', 'profilefield', get_string('profilefieldname', 'sharedresource'), array('size' => 16));

        $repeatnum = $this->_customdata['profnum'] + 1;

        $repeat[] = $mform->createElement('text', 'values', get_string('matchedvalues', 'sharedresource'), array('size' => 48));
        $repeatoptions['profilefield']['type'] =  PARAM_TEXT;
        $repeatoptions['values']['type'] =  PARAM_TEXT;
        $repeatoptions['profilefield']['helpbutton'] =  array('profilefieldname', 'sharedresource');
        $repeatoptions['values']['helpbutton'] =  array('matchedvalues', 'sharedresource');

        $this->repeat_elements($repeat, $repeatnum, $repeatoptions, 'profile_numfields', 'profile_add_fields', 1, null, true);

        $repeat2 = array();
        $repeat2[] = $mform->createElement('header', 'hdr1', get_string('bycapability', 'sharedresource').' {no}', '');
        $options = $DB->get_records_select_menu('capabilities', " contextlevel <= ? AND contextlevel != ? ", array(CONTEXT_COURSE, CONTEXT_USER), 'name');
        $repeat2[] = $mform->createElement('searchableselector', 'capability', '', $options, array('size' => 5));

        $options = array(CONTEXT_SYSTEM => get_string('site'),
                         CONTEXT_COURSECAT => get_string('coursecategory'),
                         CONTEXT_COURSE => get_string('course'),
                         1000 => get_string('somewhere', 'sharedresource'));
        $repeat2[] = $mform->createElement('select', 'contextlevel', '', $options);
        $mform->setType('profilefield', PARAM_TEXT);

        $repeatnum = $this->_customdata['capnum'] + 1;

        $repeat2[] = $mform->createElement('text', 'contextinstanceid', '', array('size' => 3, 'style' => 'max-width:3em'));

        $repeat2options['contextinstanceid']['type'] =  PARAM_TEXT;
        $repeat2options['contextinstanceid']['disabledif'] =  array('contextlevel{no}', 'eq', '');
        $repeat2options['contextinstanceid']['disabledif'] =  array('contextlevel{no}', 'eq', 1000);

        $this->repeat_elements($repeat2, $repeatnum, $repeat2options, 'capability_numfields', 'capability_add_fields', 1, null, true);

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

    public function decompact_acls($acls) {

        print_object($acls);
        $profix = 0;
        $capix = 0;
        $formdata = array();
        foreach ($acls as $ruletype => $ruleparams) {
            echo $ruletype;
            print_object($ruleparams);
            foreach ($ruleparams as $rule) {
                if ($ruletype == 'profilefields') {
                    $formdata['profilefield'][] = $rule->profilefield;
                    $formdata['values'][] = $rule->values;
                    $profix++;
                } else { // Is "capabilities".
                    $formdata['capability'][] = $rule->profilefield;
                    $formdata['contextlevel'][] = $rule->contextlevel;
                    $formdata['instanceid'][] = $rule->instanceid;
                    $capix++;
                }
            }
        }

        return $formdata;
    }
}
