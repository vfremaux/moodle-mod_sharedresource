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
 * Form to edit a sharedresource entry.
 *
 * @package     mod_sharedresource
 * @author      Piers Harding  <piers@catalyst.net.nz>, Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');

/**
 * Entry form.
 * phpcs:disable moodle.Commenting.ValidTags.Invalid
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
class mod_sharedresource_entry_form extends moodleform {

    /**
     * Standard definition.
     */
    public function definition() {
        global $CFG, $OUTPUT;

        $config = get_config('sharedresource');

        $mform =& $this->_form;

        $mform->addElement('header', 'resourceheader', get_string('resource'));

        // Master identity of the resource against the end user.
        $mform->addElement('text', 'title', get_string('name'), ['size' => '48']);

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

        $config = get_config('sharedresource');
        $pluginname = $config->schema;
        $plugin = sharedresource_get_plugin($pluginname, null);
        $descriptionelementnodeid = $plugin->get_description_element()->node;
        $key = 'config_'.$pluginname.'_mandatory_'.$descriptionelementnodeid;
        $required = get_config('sharedmetadata_'.$pluginname, $key);

        if ($required) {
            $mform->addRule('description', get_string('required'), 'required', null, 'client');
        }

        /*
         * Sharing contex:
         * Users can share a sharedresource at public system context level, or share privately to a specific
         * course category (and subcatgories).
         */
        $contextopts[1] = get_string('systemcontext', 'sharedresource');
        sharedresource_add_accessible_contexts($contextopts);
        $mform->addElement('select', 'context', get_string('sharingcontext', 'sharedresource'), $contextopts);
        $mform->setType('context', PARAM_INT);
        $mform->addHelpButton('context', 'sharingcontext', 'sharedresource');

        /*
         * Resource access :
         * TODO : try incorporate the accesscontrol form.
         */
        if (sharedresource_supports_feature('entry/accessctl') && !empty($config->accesscontrol)) {
            assert(1);
        }

        /*
         * Url or file.
         * On update as soon as a sharedresource exists, you cannot mutate the resource type betwwen file and url.
         * Additionaly if the edited entry has a next version, it cannot be mutated any more, while metadata
         * still can be adjusted.
         */
        if ($this->_customdata['mode'] == 'update') {

            if ($this->_customdata['entry']->has_next()) {
                if ($this->_customdata['entry']->type == 'url') {
                    $lbl = $OUTPUT->notification(get_string('frozenurl', 'sharedresource'));
                    $mform->addElement('static', 'url_frozen', '', $lbl);
                } else {
                    $lbl = $OUTPUT->notification(get_string('frozenfile', 'sharedresource'));
                    $mform->addElement('static', 'file_frozen', '', $lbl);
                }
            } else {
                if ($this->_customdata['entry']->type == 'url') {
                    $mform->addElement('text', 'url', get_string('url', 'sharedresource'), ['size' => '48']);
                    $mform->addHelpButton('url', 'urlchange', 'sharedresource');
                } else {
                    $mform->addElement('static', 'url_display', get_string('url', 'sharedresource').': ', '');
                    $mform->addElement('filepicker', 'sharedresourcefile', get_string('file'), ['size' => '40']);
                }
            }
        } else {
            // Add.
            $mform->addElement('text', 'url', get_string('url', 'sharedresource'), ['size' => '48']);
            $mform->setType('url', PARAM_URL);
            $mform->addElement('filepicker', 'sharedresourcefile', get_string('file'), ['size' => '40']);
        }

        if (sharedresource_supports_feature('entry/scorable')) {
            $attrs = ['size' => '8', 'style' => 'width:8em;'];
            $mform->addElement('text', 'score', get_string('score', 'mod_sharedresource'), $attrs);
            $mform->setType('score', PARAM_INT);
            $mform->setAdvanced('score');
        }

        if (sharedresource_supports_feature('entry/customicon')) {
            $group = [];
            $options = ['accepted_types' => ['.jpg', '.gif', '.png']];
            $group[] = $mform->createElement('filepicker', 'thumbnail', get_string('thumbnail', 'sharedresource'), $options);
            $group[] = $mform->createElement('checkbox', 'clearthumbnail', '', get_string('clearthumbnail', 'sharedresource'));
            $mform->addGroup($group, 'thumbnailgroup', get_string('thumbnail', 'sharedresource'), '', [''], false);
        }

        $btext = get_string('gometadataform', 'sharedresource');

        $this->add_action_buttons(true, $btext);
    }

    /**
     * Standard validation
     * @param object $data
     * @param array $files
     */
    public function validation($data, $files) {
        global $USER;

        $errors = parent::validation($data, $files);

        $fs = get_file_storage();

        if ($this->_customdata['mode'] == 'add') {
            // Make sure that either the file or the URL are supplied.
            $usercontext = context_user::instance($USER->id);
            $nofile = $fs->is_area_empty($usercontext->id, 'user', 'draft', $data['sharedresourcefile'], true);

            if (empty($data['url']) && $nofile) {
                $errors['url'] = get_string('missingresource', 'sharedresource');
                $errors['sharedresourcefile'] = get_string('missingresource', 'sharedresource');
            }
        }

        if (count($errors) == 0) {
            return true;
        }
        return $errors;
    }

    /**
     * Get data overrdide
     * @param object $slashed
     */
    public function get_data($slashed = true) {
        $data = parent::get_data($slashed);
        if ($data == null) {
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

    /**
     * Fill data with values.
     * @param object $defaultvalues
     * @param bool $slashed
     */
    public function set_data($defaultvalues, $slashed = false) {

        // Thumbnail.
        $draftitemid = file_get_submitted_draft_itemid('thumbnail');
        $maxbytes = 35 * 1024 * 1024;
        $maxfiles = 1;
        $fileoptions = ['subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => $maxfiles];
        $entryid = $this->_customdata['entry']->id;
        file_prepare_draft_area($draftitemid, context_system::instance()->id, 'mod_sharedresource',
                                'thumbnail', $entryid, $fileoptions);
        $groupname = 'thumbnailgroup';
        $defaultvalues->$groupname = ['thumbnail' => $draftitemid];

        $draftitemid = file_get_submitted_draft_itemid('sharedresourcefile');
        $maxbytes = 35 * 1024 * 1024;
        $maxfiles = 1;
        $fileoptions = ['subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => $maxfiles];
        file_prepare_draft_area($draftitemid, context_system::instance()->id, 'mod_sharedresource',
                                'sharedresource', $entryid, $fileoptions);
        $defaultvalues->sharedresourcefile = $draftitemid;

        // Resource description.
        $description = @$defaultvalues->description;
        $defaultvalues->description = [];
        $defaultvalues->description['text'] = $description;
        $defaultvalues->description['format'] = FORMAT_HTML;
        $errors = parent::set_data($defaultvalues, $slashed = false);
    }
}
