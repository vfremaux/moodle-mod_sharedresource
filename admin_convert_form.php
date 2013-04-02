<?php  // $Id: admin_convert_form.php,v 1.1 2013-02-13 21:56:39 wa Exp $

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

    function __construct(&$course, &$resources, &$urls){
        $this->course = $course;
        $this->resources = $resources;
        $this->urls = $urls;
        parent::moodleform();
    }

    function definition(){
        $mform = & $this->_form;
        
        $mform->addElement('hidden', 'course');
        $mform->setDefault('course', $this->course->id);

    	$hasitems = false;

        if (!empty($this->resources)){
        	$hasitems = true;
            foreach($this->resources as $r){
                $mform->addElement('header', 'hdr_'.$r->id);
                $mform->addElement('advcheckbox', 'rcnv_'.$r->id, get_string('resource').':', $r->name, array('group' => 1), array(0,1));
                $mform->setDefault('rcnv_'.$r->id, 1);
                $mform->addElement('static', 'lbl_'.$r->id, get_string('description').':', @$r->summary);
            }
            
            $convertstr = get_string('convert', 'sharedresource');
        }

        if (!empty($this->urls)){
        	$hasitems = true;
            foreach($this->urls as $u){
                $mform->addElement('header', 'hdr_'.$u->id);
                $mform->addElement('advcheckbox', 'ucnv_'.$u->id, get_string('url').':', $u->name, array('group' => 1), array(0,1));
                $mform->setDefault('ucnv_'.$u->id, 1);
                $mform->addElement('static', 'lbl_'.$u->id, get_string('description').':', @$u->intro);
            }
        }

		if ($hasitems){
	        $this->add_checkbox_controller(1, '', '');
	    }
        $convertstr = get_string('convert', 'sharedresource');
        $this->add_action_buttons(true, $convertstr);
    }
}

?>