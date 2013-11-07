<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */
require_once ($CFG->libdir.'/formslib.php');
class mod_sharedresource_search_form extends moodleform {

    function definition() {
        global $CFG,$DB;

        $mform =& $this->_form;

        $add     = optional_param('add', 0, PARAM_ALPHA);
        $return  = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
        $type    = optional_param('type', 'file', PARAM_ALPHANUM);
        $section = required_param('section', PARAM_INT);
        $course  = required_param('course', PARAM_INT);

	    $searchinlibrary = get_string('searchinlibrary', 'sharedresource');
		$mform->addElement('header', 'searchheader',  get_string('searchheader', 'sharedresource'));

		$addbutton = $mform->addElement('submit', 'sharedresource', $searchinlibrary);
        $buttonattributes = array('title'=> $searchinlibrary, 'onclick'=>"location.href = '".$CFG->wwwroot."/local/sharedresources/index.php?course={$course}'; return false;");
		$addbutton->updateAttributes($buttonattributes);

        $mform->addElement('hidden', 'course', $course);
        $mform->setType('course', PARAM_INT);
        
        $mform->addElement('hidden', 'add', $add);
        $mform->setType('add', PARAM_ALPHA);

        $mform->addElement('hidden', 'return', $return);
        $mform->setType('return', PARAM_BOOL);

        $mform->addElement('hidden', 'type', $type);
        $mform->setType('type', PARAM_ALPHANUM);

        $mform->addElement('hidden', 'section', $section);
        $mform->setType('section', PARAM_ALPHANUM);

        if (! $course = $DB->get_record('course', array('id' => $course))) {
            print_error('coursemisconf');
        }

        $context = context_course::instance($course->id);

        if (has_capability('moodle/course:manageactivities', $context)) {
            $mform->addElement('header', 'addheader',  get_string('addheader', 'sharedresource'));
    		$addbutton2 = $mform->addElement('submit', 'addsharedresource', get_string('addsharedresource', 'sharedresource'));
            $buttonattributes2 = array('title'=> get_string('addsharedresource', 'sharedresource'), 'onclick'=>"location.href = '"
                              . $CFG->wwwroot."/mod/sharedresource/edit.php?course={$course->id}&section={$section}&type={$type}&add={$add}&return={$return}&mode=add'; return false;");

            $addbutton2->updateAttributes($buttonattributes2);
        }
	}

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
?>