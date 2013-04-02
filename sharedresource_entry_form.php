<?php
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

    function mod_sharedresource_entry_form($mode) {
        $this->sharedresource_entry_mode = $mode;
        parent::moodleform();
    }
    
    function definition(){
        global $CFG, $USER;

        $mform =& $this->_form;
        $this->set_upload_manager(new upload_manager('sharedresourcefile', false, false, null, false, 0, true, true, false));

        $mform->addElement('header', 'resourceheader', get_string('resource'));

        // master identity of the resource against the end user
        $mform->addElement('text', 'title', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('title', PARAM_TEXT);
        } else {
            $mform->setType('title', PARAM_CLEAN);
        }
        
        // These are internal legacy metainformation, whatever the extension model is 
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->addElement('htmleditor', 'description', get_string('description'));
        $mform->setType('description', PARAM_CLEANHTML);
        $mform->setHelpButton('description', array('description', get_string('description'), 'sharedresource'));
        
        if ($this->sharedresource_entry_mode == 'update') {
            $mform->addElement('static', 'url_display', get_string('url', 'sharedresource').': ', '');
            $mform->addElement('static', 'sharedresourcefile', get_string('file').': ', '');
        } else {
            $mform->addElement('text', 'url', get_string('url', 'sharedresource'), array('size'=>'48'));
            $mform->addElement('file', 'sharedresourcefile', get_string('file'), 'size="40"');
        }

        // let the plugins see the form definition
        $plugins = sharedresource_get_plugins();
        foreach ($plugins as $plugin) {
            $rc = $plugin->sharedresource_entry_definition($mform);
            if (!$rc) {
                break;
            }
        }

        $btext = '';
        if (sharedresource_extra_resource_screen()) {
            $btext = get_string('step2', 'sharedresource');
        } else {
            $btext = get_string('gometadataform', 'sharedresource');
        }
        $this->add_action_buttons(true, $btext);

        // mark this as the first step page
        $mform->addElement('hidden', 'pagestep', 1);        
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($this->sharedresource_entry_mode == 'add') {
            // make sure that either the file or the URL are supplied
            if (empty($data['url']) && $_FILES['sharedresourcefile']['size'] <= 0) {
                $errors['url'] = get_string('missingresource','sharedresource');
            }
            
            // if file is uploaded - check that there was no problem
            if (empty($data['url']) && $_FILES['sharedresourcefile']['error'] != 0) {
                // error - physical upload of file failed
                $errors['sharedresourcefile'] = get_string('fileuploadfailed','sharedresource');
            }
            
            // check that this resource signature does not allready exist
            if (!empty($data['url'])) {
                $hash = sha1($data['url']);
                $result = count_records('sharedresource_entry', 'identifier', $hash) + count_records('sharedresource_entry', 'url', $data['url']);
                if ($result > 0) {
                    $errors['url'] = get_string('resourceexists','sharedresource');
                }
            } else {
                $tempfile = $_FILES['sharedresourcefile']['tmp_name'];
                $hash = sharedresource_sha1file($tempfile);
                $sharedresource_entry->identifier = $hash;
                $uri = $hash.'-'.$_FILES['sharedresourcefile']['name'];
                $result = count_records('sharedresource_entry', 'identifier', $hash) + count_records('sharedresource_entry', 'url', $uri);
                if ($result > 0) {
                    $errors['sharedresourcefile'] = get_string('resourceexists','sharedresource');
                }
            }
        }

        // let the plugins see the form validation
        $plugins = sharedresource_get_plugins();
        foreach ($plugins as $plugin) {
            $rc = $plugin->sharedresource_entry_validation($data, $files, $errors, $this->sharedresource_entry_mode);
            if (!$rc) {
                break;
            }
        }
        
        return $errors;
    }

    function get_data($slashed=true) {
        $data = parent::get_data($slashed);
        if ($data == NULL) {
            return $data;
        }
        if (!empty($data->IssueDate)) {
            $data->IssueDate = date("Y-m-d\TH:i:s.000\Z", $data->IssueDate);
        }
        else {
            $data->IssueDate = '0000-00-00T00:00:00.000Z';
        }
        return $data;
    }
    
    
    function set_data($default_values, $slashed=false) {
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
        
        // clean the hash off of the front as this maybe misleading - even though we need it
        // to guarantee that the file is unique on the filesystem.
        if (!empty($default_values->sharedresourcefile) && preg_match('/^\w+\-(.*?)$/', $default_values->sharedresourcefile, $matches)) {
            $default_values->sharedresourcefile = $matches[1];
        }
        $errors = parent::set_data($default_values, $slashed=false);
    }
}

?>