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
        global $CFG, $USER, $DB;
        $mform =& $this->_form;
   /*     $this->set_upload_manager(new upload_manager('sharedresourcefile', false, false, null, false, 0, true, true, false));*/
        $add           = optional_param('add', 0, PARAM_ALPHA);
        $update        = optional_param('update', 0, PARAM_INT);
        $return        = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
        $type          = optional_param('type', '', PARAM_ALPHANUM);
        $section       = optional_param('section', 0, PARAM_INT);
        $mode          = required_param('mode', PARAM_ALPHA);
        $course        = required_param('course', PARAM_INT);
        /* $pagestep      = optional_param('pagestep', 1, PARAM_INT); */
        $entry_id      = optional_param('entry_id', 1, PARAM_INT);

        $mform->addElement('header', 'resourceheader', get_string('resource'));

        // master identity of the resource against the end user
        $mform->addElement('text', 'title', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('title', PARAM_TEXT);
        } else {
            $mform->setType('title', PARAM_CLEANHTML);
        }
        // These are internal legacy metainformation, whatever the extension model is
        $mform->addRule('title', null, 'required', null, 'client');

        // resource entry description. Is copied into metadata also
        $mform->addElement('editor', 'description', get_string('description'));
        $mform->setType('description', PARAM_CLEANHTML);
        $mform->addHelpButton('description', 'description', 'sharedresource');

        // sharing contexts
        $contextopts[1] = get_string('systemcontext', 'sharedresource');
        sharedresource_add_accessible_contexts($contextopts);
        $mform->addElement('select', 'context', get_string('sharingcontext', 'sharedresource'), $contextopts);
        $mform->setType('context', PARAM_INT);
        $mform->addHelpButton('context', 'sharingcontext', 'sharedresource');

        if ($this->sharedresource_entry_mode == 'update') {
            $mform->addElement('static', 'url_display', get_string('url', 'sharedresource').': ', '');
            $mform->addElement('static', 'filename', get_string('file').': ', '');
        } else {
            $mform->addElement('text', 'url', get_string('url', 'sharedresource'), array('size'=>'48'));
            $mform->setType('url', PARAM_URL);
            $mform->addElement('filepicker', 'sharedresourcefile', get_string('file'), array('size' => "40"));
        }
        // let the plugins see the form definition
        $plugins = sharedresource_get_plugins();

        foreach ($plugins as $plugin) {
            $rc = $plugin->sharedresource_entry_definition($mform);
            if (!$rc) {
                break;
            }
        }

        /* if (sharedresource_extra_resource_screen()) {
            $btext = get_string('step2', 'sharedresource');
        } else { */
            $btext = get_string('gometadataform', 'sharedresource');
        // }


        $mform->addElement('hidden', 'course', $course);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('hidden', 'add', $add);
        $mform->setType('add', PARAM_ALPHA);
        $mform->addElement('hidden', 'return', $return);
        $mform->setType('return', PARAM_BOOL);
        $mform->addElement('hidden', 'section', $section);
        $mform->setType('section', PARAM_INT);
        $mform->addElement('hidden', 'mode', $mode);
        $mform->setType('mode', PARAM_ALPHA);
        $mform->addElement('hidden', 'entry_id', $entry_id);
        $mform->setType('entry_id', PARAM_INT);

        $this->add_action_buttons(true, $btext);

        // mark this as the first step page // OBSOLETE
        /* $mform->addElement('hidden', 'pagestep', 1); */
    }

    function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        if(!empty($data['sharedrsourcefile'])){
            //$file = $DB->get_record('file','')
        }

        if ($this->sharedresource_entry_mode == 'add') {
            // make sure that either the file or the URL are supplied
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

        // Not sure to keep this default metadata setyup : Old Piers trick
        if (!empty($data->IssueDate)) {
            $data->IssueDate = date("Y-m-d\TH:i:s.000\Z", $data->IssueDate);
        } else {
            $data->IssueDate = '0000-00-00T00:00:00.000Z';
        }

        return $data;
    }

    function set_data($default_values, $slashed = false) {

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
        $errors = parent::set_data($default_values, $slashed = false);
    }
}
