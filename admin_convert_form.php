<?php  // $Id: admin_convert_form.php,v 1.2 2010/04/09 10:05:55 vf Exp $

/**
* forms for converting resources to sharedresources
*
* @package    mod-sharedresource
* @category   mod
* @author     Valery Fremaux <valery.fremaux@club-internet.fr>
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
* @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
*/

/**
* Includes and requires
*/
require $CFG->libdir.'/formslib.php';


class sharedresource_choosecourse_form extends moodleform{

    function __construct($courses){
        $this->courses = $courses;
        parent::moodleform();
    }

    function definition(){
        $mform = & $this->_form;
        
        $select = &$mform->addElement('select', 'course', get_string('courses'), $this->courses);
        // $select->setMultiple(true);

		// Adding submit and reset button
        $buttonarray = array();
    	$buttonarray[] = &$mform->createElement('submit', 'go_submit', get_string('submit'));
    	$buttonarray[] = &$mform->createElement('cancel', 'go_cancel', get_string('cancel'));
        
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }
}

class sharedresource_selectresources_form extends moodleform{

    function __construct(&$course, &$resources){
        $this->course = $course;
        $this->resources = $resources;
        parent::moodleform();
    }

    function definition(){
        $mform = & $this->_form;
        
        $mform->addElement('hidden', 'course');
        $mform->setDefault('course', $this->course->id);

        if (!empty($this->resources)){
            foreach($this->resources as $resource){
                $mform->addElement('header', 'hdr_'.$resource->id);
                $mform->addElement('advcheckbox', 'cnv_'.$resource->id, get_string('resource').':', $resource->name, array('group' => 1), array(0,1));
                $mform->setDefault('cnv_'.$resource->id, 1);
                $mform->addElement('static', 'lbl_'.$resource->id, get_string('description').':', $resource->summary);
            }
            $this->add_checkbox_controller(1, '', '');
            
            $convertstr = get_string('convert', 'sharedresource');
            $this->add_action_buttons(true, $convertstr);
        } else {
        }
    }
}

?>