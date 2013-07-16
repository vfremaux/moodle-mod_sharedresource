<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */
require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_sharedresource_mod_form extends moodleform_mod {

    var $_resinstance;

    function definition() {
        global $CFG, $DB;

        $mform =& $this->_form;

        // this hack is needed for different settings of each subtype
        if (!empty($this->_instance)) {
            if(!$res = $DB->get_record('sharedresource', array('id' => (int)$this->_instance))) {
                print_error('errorinstance', 'sharedresource');
            }
        }

        require_once($CFG->dirroot.'/mod/sharedresource/sharedresource_base.class.php');
        $this->_resinstance = new sharedresource_base();

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'sharedresource'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor(true, get_string('sharedresourceintro', 'sharedresource'));

        $mform->addElement('header', 'typedesc', get_string('resourcetypefile', 'sharedresource'));

        $this->_resinstance->setup_elements($mform);

        $this->standard_coursemodule_elements(array('groups' => false, 'groupmembersonly' => true, 'gradecat' => false));

        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values){
        $this->_resinstance->setup_preprocessing($default_values);
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
