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
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */
require_once $CFG->libdir.'/formslib.php';

class mod_sharedresource_entry_form extends moodleform {

<<<<<<< HEAD
    function mod_sharedresource_entry_form($mode) {
=======
    public function __construct($mode) {
>>>>>>> MOODLE_32_STABLE
        $this->sharedresource_entry_mode = $mode;
        parent::moodleform();
    }

<<<<<<< HEAD
    function definition(){
=======
    public function definition() {
>>>>>>> MOODLE_32_STABLE
        global $CFG, $USER, $DB;

        $mform =& $this->_form;

        $add           = optional_param('add', 0, PARAM_ALPHA);
        $update        = optional_param('update', 0, PARAM_INT);
        $return        = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
        $type          = optional_param('type', '', PARAM_ALPHANUM);
        $section       = optional_param('section', 0, PARAM_INT);
        $mode          = required_param('mode', PARAM_ALPHA);
        $course        = required_param('course', PARAM_INT);
        $entry_id      = optional_param('entry_id', 1, PARAM_INT);

        $mform->addElement('header', 'resourceheader', get_string('resource'));

        // Master identity of the resource against the end user.
        $mform->addElement('text', 'title', get_string('name'), array('size'=>'48'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('title', PARAM_TEXT);
        } else {
            $mform->setType('title', PARAM_CLEANHTML);
        }

        // These are internal legacy metainformation, whatever the extension model is.
        $mform->addRule('title', null, 'required', null, 'client');

        // Resource entry description. Is copied into metadata also.
        $mform->addElement('editor', 'description', get_string('description'));
        $mform->setType('description', PARAM_CLEANHTML);
        $mform->addHelpButton('description', 'description', 'sharedresource');

<<<<<<< HEAD
		// sharing context : 
		// users can share a sharedresource at public system context level, or share privately to a specific course category
		// (and subcatgories)
		$contextopts[1] = get_string('systemcontext', 'sharedresource');
		sharedresource_add_accessible_contexts($contextopts);
		$mform->addElement('select', 'context', get_string('sharingcontext', 'sharedresource'), $contextopts);
		$mform->setType('context', PARAM_INT);
        $mform->addHelpButton('context', 'sharingcontext', 'sharedresource');

		// Url or file
		// On update as soon as a sharedresource exists, it cannot be muted any more
=======
        // Sharing context :
        // Users can share a sharedresource at public system context level, or share privately to a specific course category (and subcatgories)
        $contextopts[1] = get_string('systemcontext', 'sharedresource');
        sharedresource_add_accessible_contexts($contextopts);
        $mform->addElement('select', 'context', get_string('sharingcontext', 'sharedresource'), $contextopts);
        $mform->setType('context', PARAM_INT);
        $mform->addHelpButton('context', 'sharingcontext', 'sharedresource');

        // Url or file
        // On update as soon as a sharedresource exists, it cannot be muted any more
>>>>>>> MOODLE_32_STABLE
        if ($this->sharedresource_entry_mode == 'update') {
            $mform->addElement('static', 'url_display', get_string('url', 'sharedresource').': ', '');
            $mform->addElement('static', 'filename', get_string('file').': ', '');
        } else {
            $mform->addElement('text', 'url', get_string('url', 'sharedresource'), array('size' => '48'));
<<<<<<< HEAD
			$mform->setType('url', PARAM_URL); 
            $mform->addElement('filepicker', 'sharedresourcefile', get_string('file'), array('size' => '40'));
        }
        
        // obsolete
        // metadata plugins now impact only the second metadata form.
        /*
        // let the plugins see the form definition
        $plugins = sharedresource_get_plugins();

        foreach ($plugins as $plugin) {
            $rc = $plugin->sharedresource_entry_definition($mform);
            if (!$rc) {
                break;
            }
        }
        */
=======
            $mform->setType('url', PARAM_URL); 
            $mform->addElement('filepicker', 'sharedresourcefile', get_string('file'), array('size' => '40'));
        }

        $group = array();
        $group[] = $mform->createElement('filepicker', 'thumbnail', get_string('thumbnail', 'sharedresource'), array('accepted_types' => array('.jpg','.gif','.png')));
        $group[] = $mform->createElement('checkbox', 'clearthumbnail', '', get_string('clearthumbnail', 'sharedresource'));
        $mform->addGroup($group, 'thumbnailgroup', get_string('thumbnail', 'sharedresource'), '', array(''), false);
>>>>>>> MOODLE_32_STABLE

        $btext = get_string('gometadataform', 'sharedresource');

        $mform->addElement('hidden', 'course', $course);
<<<<<<< HEAD
		$mform->setType('course', PARAM_INT); 

        $mform->addElement('hidden', 'add', $add);
       	$mform->setType('add', PARAM_ALPHA); 
=======
        $mform->setType('course', PARAM_INT); 

        $mform->addElement('hidden', 'add', $add);
           $mform->setType('add', PARAM_ALPHA); 
>>>>>>> MOODLE_32_STABLE

        $mform->addElement('hidden', 'return', $return);
        $mform->setType('return', PARAM_BOOL); 
        
        $mform->addElement('hidden', 'section', $section);
        $mform->setType('section', PARAM_INT);
        
        $mform->addElement('hidden', 'mode', $mode);
        $mform->setType('mode', PARAM_ALPHA); 
        
        $mform->addElement('hidden', 'entry_id', $entry_id);
        $mform->setType('entry_id', PARAM_INT);

        $this->add_action_buttons(true, $btext);
    }

    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        if ($this->sharedresource_entry_mode == 'add') {
            // Make sure that either the file or the URL are supplied.
            if (empty($data['url']) && $data['sharedresourcefile'] == null) {
                $errors['url'] = get_string('missingresource','sharedresource');
            }
        }

        return $errors;
    }

    function get_data($slashed = true) {
        $data = parent::get_data($slashed);
        if ($data == NULL) {
            return $data;
        }

        // Not sure to keep this default metadata setyup : Old Piers trick.
        if (!empty($data->IssueDate)) {
            $data->IssueDate = date("Y-m-d\TH:i:s.000\Z", $data->IssueDate);
        } else {
            $data->IssueDate = '0000-00-00T00:00:00.000Z';
        }

        return $data;
    }

    function set_data($default_values, $slashed = false) {

<<<<<<< HEAD
		/*
        // poke all the basic metadata elements into defaults so 
        // that they get set in the form
        if (isset($default_values->metadata_elements)) {
            foreach ($default_values->metadata_elements as $element) {
                if ($element->namespace == '') {
                    if ($element->element == 'IssueDate') {
                        $element->value = strtotime($element->value);
                    }
                    $key = $element->element;
                    $default_values->$key = $element->value;
                }
            }
        }
        */
        
=======
        // Thumbnail.
        $draftitemid = file_get_submitted_draft_itemid('thumbnail');
        $maxbytes = 35*1024;
        $maxfiles = 1;
        file_prepare_draft_area($draftitemid, context_system::instance()->id, 'local_sharedresources', 'thumbnail', 0, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => $maxfiles));
        $groupname = 'thumbnailgroup';
        $default_values->$groupname = array('thumbnail' => $draftitemid);

>>>>>>> MOODLE_32_STABLE
        // process description 
        $description = @$default_values->description;
        $default_values->description = array();
        $default_values->description['text'] = $description;
        $default_values->description['format'] = FORMAT_HTML;
<<<<<<< HEAD

		/*
		// in a first approach a shared resource is not physically mutable, only metadata is
		$draftitemid = file_get_submitted_draft_itemid('sharedresourcefile');		 
		$maxbytes = 100000;
		$systemcontext = context_system::instance();
		file_prepare_draft_area($draftitemid, $systemcontext->id, 'mod_sharedresource', 'sharedresource', 0, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));		 
		$data->sharedresourcefile = $draftitemid;
		*/
		
		/*
        // clean the hash off of the front as this maybe misleading - even though we need it
        // to guarantee that the file is unique on the filesystem.
        if (!empty($default_values->sharedresourcefile) && preg_match('/^\w+\-(.*?)$/', $default_values->sharedresourcefile, $matches)) {
            $default_values->sharedresourcefile = $matches[1];
        }
        */
=======
>>>>>>> MOODLE_32_STABLE
        $errors = parent::set_data($default_values, $slashed = false);
    }
}
