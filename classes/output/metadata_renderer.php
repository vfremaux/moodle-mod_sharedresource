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
 * @author  Valery Fremaux valery.fremaux@club-internet.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/taoresource is a work derived from Moodle mod/resoruce
 * @package    mod_sharedresource
 * @category   mod
 *
 * This is a separate configuration screen to configure any metadata stub that is attached to a shared resource. 
 */
namespace mod_sharedresource\output;

use \StdClass;
use \moodle_url;
use \html_writer;
use \mod_sharedresource\metadata;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/output/opened_core_renderer.php');

class metadata_renderer extends \plugin_renderer_base {

    protected $coreoutput;

    public function __construct($page, $target) {
        parent::__construct($page, $target);
        $this->coreoutput = new opened_core_renderer($page, $target);
    }

    public function metadata_configuration() {
        $config = get_config('sharedresource');

        $template = new StdClass;

        if (!empty($config->schema)) {
            $plugin = sharedresource_get_plugin($config->schema);
            $template->configuration = $plugin->configure($config);

            $template->defaultselectstr = get_string('defaultselect', 'sharedresource');
            $template->updatemetadatastr = get_string('updatemetadata', 'sharedresource');
            $template->backadminpagestr = get_string('backadminpage','sharedresource');
            $template->backadminpageurl = new moodle_url('/admin/settings.php', array('section' => 'modsettingsharedresource'));
        } else {
            $template->noplugin = $this->output->notification(get_string('nometadataplugin', 'sharedresource'));
        }

        $template->defaultconfigurl = new moodle_url('/mod/sharedresource/metadataconfigure.php', array('action' => 'reinitialize'));
        $template->settingsurl = new moodle_url('/admin/settings.php', array('section' => 'modsettingsharedresource'));

        return $this->output->render_from_template('mod_sharedresource/metadataconfiguration', $template);
    }

    public function notice($shrentry, $capability) {

        $namespace = get_config('sharedresource', 'schema');
        $mtdstandard = sharedresource_get_plugin($namespace);

        $template = new StdClass;

        $childs = $mtdstandard->getElementChilds(0);
        $template->nbrchilds = count($childs);
        $template->nodestr = get_string('node', 'sharedresource');
        $template->metadatadescrstr = get_string('schema', 'sharedresource');
        $template->namespace = $mtdstandard->getNamespace();
        $template->schema = get_string('pluginname', 'sharedmetadata_'.$template->namespace);
        $template->hidemetadatadesc = get_config('sharedresource', 'hidemetadatadesc');

        $template->dmusedstr = get_string('dmused','sharedresource');

        $template->dmusestr = get_string('dmuse','sharedresource');
        $template->dmdescription = get_string('dmdescription','sharedresource');

        $tabmodel = $this->detect_tab_model();
        $template->tabmodel = $tabmodel;

        $template->standarddescriptionstr = get_string('standarddescription', 'sharedmetadata_'.$mtdstandard->getNamespace());

        $i = 1;
        foreach ($childs as $nodeid => $islist) {
            $paneltpl = new StdClass;
            $paneltpl->i = $i;
            $paneltpl->hascontent = false;
            $paneltpl->childs = array();
            $standardelm = $mtdstandard->getElement($nodeid);
            $lowername = strtolower($standardelm->name);
            $elementkey = metadata::to_instance($nodeid);
            $paneltpl->tabname = get_string(clean_string_key($lowername), 'sharedmetadata_'.$namespace);

            $this->part_view($paneltpl, $shrentry, $elementkey, $capability, 0);
            if ($paneltpl->hascontent) {
                $template->panels[] = $paneltpl;
                $template->tabs[] = $this->tab($nodeid, $capability, $template, 'read');
            }
            $i++;
        }

        return $this->output->render_from_template('mod_sharedresource/notice', $template);
    }

    /**
     * Creates tabs.
     */
    function tab($nodeid, $capability, &$template, $mode = 'read') {
        global $DB;

        $namespace = get_config('sharedresource', 'schema');
        $mtdstandard = sharedresource_get_plugin($namespace);

        $tabtpl = new StdClass;
        $tabtpl->i = $nodeid;
        if (\mod_sharedresource\metadata::use_branch($nodeid, $capability, $mode)) {
            $tabtpl->tabclass = 'mtd-tab-visible';
        } else {
            $tabtpl->tabclass = 'mtd-tab-hidden';
        }

        $standardelm = $mtdstandard->getElement($nodeid);
        $lowername = strtolower($standardelm->name);
        $tabtpl->tabname = get_string(clean_string_key($lowername), 'sharedmetadata_'.$namespace);

        return $tabtpl;
    }

    /**
     * This function is used to display the entire metadata notice. The parameter are in the correct order:
     * @param string $shrentry the sharedresource id being viewed.
     * @param string $elementkey the full element key as (m_n_o:x_y_z)
     * @param string $capability tells if the field is visible or not depending of the role of the user regarding metadata
     * @param boolean $realoccur is used only in the case of classification, when a classification is deleted by an admin and does not appear anymore on the metadata notice.
     */
    function part_view(&$parenttemplate, &$shrentry, $elementkey, $capability, $realoccur = 0) {
        global $SESSION, $CFG, $DB, $OUTPUT;

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        // This is the complete representation of the metadata standard.
        $mtdstandard = sharedresource_get_plugin($namespace);

        list($nodeid, $instanceid) = explode(':', $elementkey);
        $htmlname = metadata::storage_to_html($elementkey);

        if (!$mtdstandard->hasNode($nodeid)) {
            // Trap out if not exists.
            return null;
        }

        // This is the current node definition in the standard.
        $standardelm = $mtdstandard->getElement($nodeid);

        // This is the current instance element record representation.
        $elminstance = metadata::instance($shrentry->id, $elementkey, $namespace, false);

        if (!$elminstance->node_has_capability($capability, 'read')) {
            return null;
        }

        $numoccur = $elminstance->get_instance_index();
        $template = new Stdclass;
        $template->fieldnum = $nodeid;
        $template->dottedfieldnum = str_replace('_', '.', $nodeid);
        $template->numoccur = $numoccur;
        $template->childs = array();
        $template->hascontent = false;
        $template->iscontainer = false;
        $template->isscalar = false;
        $template->islist = false;
        $template->isvcard = false;

        // Check how many occurrences of ourself we have in database.
        $maxoccur =  $elminstance->get_max_occurrence();
        if ($maxoccur < 1) {
            // Do not display occurence index if we have only one possible.
            unset($template->numoccur);
        }

        $lowername = strtolower($standardelm->name);
        $template->fieldname = $lowername;
        $template->fieldvisiblename = get_string(clean_string_key($lowername), 'sharedmetadata_'.$namespace);
        $template->fieldtype = $standardelm->type;

        // May be null on some standards. (DC)
        $taxumarray = $mtdstandard->getTaxumpath();

        // print_object($standardelm);

        $template->keyid =  $elementkey;
        $listresult = array();
        if ($standardelm->type == 'category') {

            if (!empty($taxumarray) && $nodeid == $taxumarray['main']) {
                /*
                 * If the field concerns classification, we reduce display to a scalar (classification path).
                 * Classification path is rebuilt from the taxonomy table. The taxonomy source is given by
                 * $taxumarray['source'] (as tablename of the taxonomy)
                 */

                $template->isscalar = true;

                // We check if there is metadata saved for this field.
                $sourcekey = array();
                $sourcekey['pos'] = $taxumarray['source'];
                $sourcekey['occ'] = metadata_get_node_occurence($elminstance->get_instance_id(), $sourcekey['pos']);
                $sourceelementkey = $sourcekey['pos'].':'.$sourcekey['occ'];
                $sourceelm = metadata::instance($shrentry->id, $sourceelementkey, $namespace);
                $sourceid = $sourceelm->get_value();

                if ($sourceid) {
                    // The second parameter will make the correct option selected:
                    $navigator = \local_sharedresources\browser\navigation::instance_by_id($sourceid);

                    $idkey['pos'] = $taxumarray['id'];
                    $idkey['occ'] = metadata_get_node_occurence($elminstance->get_instance_id(), $idkey['pos']);
                    $idelementkey = $idkey['pos'].':'.$idkey['occ'];
                    $idelm = metadata::instance($shrentry->id, $idelementkey, $namespace);

                    $template->mtdvalue = $navigator->get_printable_taxon_path($idelm->get_value());
                    if (!empty($template->mtdvalue)) {
                        $template->hascontent = true;
                        $parenttemplate->hascontent = true;
                    }

                    if (!empty($template->mtdvalue)) {
                        $parenttemplate->childs[] = $template;
                    }
                }
            } else {
                // We are in a category.
                $template->iscontainer = true;

                $hascontent = false;
                // Get all subs.
                $listresult = $elminstance->get_childs($nodeid, $capability, 'read', true);
                if (!empty($listresult)) {
                    // We verify if all children subbranchs of this category have been filled.
                    $hascontent = $elminstance->childs_have_content($capability, 'read');
                }

                if (!empty($hascontent)) {
                    // It's ok and we display the category, then display children recursively.
                    $template->fieldnum = $nodeid;

                    if ($numoccur > 0) {
                        $template->occur = $numoccur + 1;
                    }

                    $standardelmchilds = $mtdstandard->getElementChilds($nodeid);
                    $nbrchilds = count($standardelmchilds);
                    $parenttemplate->childs[] = $template;
                    foreach ($standardelmchilds as $childnodeid => $islist) {
                        $childkey = metadata::to_instance($childnodeid);
                        $this->part_view($template, $shrentry, $childkey, $capability, 0);
                        $parenttemplate->hascontent = $parenttemplate->hascontent || $template->hascontent;
                    }
                }

                $siblings = $elminstance->get_siblings($nodeid, $capability, 'read', true);
                if (!empty($siblings)) {
                    // All siblings will have a numoccur > 0.
                    foreach ($siblings as $sib) {
                        $this->part_view($parenttemplate, $shrentry, $sib->get_element_key(), $capability, 0);
                    }
                }
            }
        } else {
            if ($elminstance->get_instance_index() == 0 && $standardelm->islist) {
                /*
                 * If we are first element of a list of scalar values, aggregate all values of siblings in a textual
                 * list. We replace the scalar value by an array. Each type will know what to do with this array and
                 * the way to display this value set.
                 */
                $siblings = $elminstance->get_siblings($nodeid, $capability, 'read', true);
                if (!empty($siblings)) {
                    $values = array($elminstance->get_value());
                    // All siblings will have a numoccur > 0.
                    foreach ($siblings as $sib) {
                        $values[] = $sib->get_value();
                    }
                    $elminstance->set_value($values);
                }
            }

            $this->print_data($standardelm, $elminstance, $template);
            $parenttemplate->hascontent = $parenttemplate->hascontent || $template->hascontent;
            if (!empty($template->mtdvalue)) {
                $parenttemplate->childs[] = $template;
            }
        }

        // Not really necessary now.
        return $template;
    }

    /**
     * Prints a scalar result
     */
    public function print_data(&$standardelm, &$elminstance, &$template) {
        global $OUTPUT;

        $namespace = get_config('sharedresource', 'schema');
        $mtdstandard = sharedresource_get_plugin($namespace);

        // Do not print occurrence for scalars as they are merged into a text list.
        $template->occur = '';

        switch ($standardelm->type) {

            case 'text':
            case 'longtext':
            case 'codetext': {
                $template->isscalar = true;

                $value = $elminstance->get_value();
                if ($elminstance->get_node_id() == $mtdstandard->getTitleElement()->name) {
                    $template->mtdvalue = $shrentry->title;
                } else if (!empty($value)) {
                    $template->hascontent = true;
                    if (is_array($value)) {
                        $template->mtdvalue = implode(', ', $value);
                    } else {
                        $template->mtdvalue = $value;
                    }
                }
                break;
            }

            case 'sortedselect':
            case 'select': {
                $template->isscalar = true;

                $value = $elminstance->get_value();
                if (!empty($value)) {
                    $template->hascontent = true;
                    if (is_numeric($value)) {
                        $template->mtdvalue = $value;
                    } else if (is_array($value)) {
                        $mtdvalues = array();
                        foreach ($value as $item) {
                            $cleanedkey = clean_string_key($item);
                            $mtdvalues[] = get_string($cleanedkey, 'sharedmetadata_'.$namespace);
                        }
                        $template->mtdvalue = implode(', ', $mtdvalues);
                    } else {
                        $cleanedkey = clean_string_key($value);
                        $template->mtdvalue = get_string($cleanedkey, 'sharedmetadata_'.$namespace);
                    }
                }
                break;
            }

            case 'date': {
                $template->isscalar = true;

                $value = $elminstance->get_value();
                if (!empty($value)) {
                    $template->hascontent = true;
                    if (!is_array($value)) {
                        $date = strftime(get_string('datefmt', 'sharedresource'), $value);
                        $template->mtdvalue = $date;
                    } else {
                        $dates = array();
                        foreach ($value as $item) {
                            $cleanedkey = clean_string_key($item);
                            $dates[] = strftime(get_string('datefmt', 'sharedresource'), $item);
                        }
                        $template->mtdvalue = implode(', ', $dates);
                    }
                }
                break;
            }

            case 'duration': {
                $template->isscalar = true;

                $duration = get_string('durationdescr', 'sharedresource');
                $template->mtdvalue = '';
                $value = $elminstance->get_value();
                if ($value != '') {
                    $template->hascontent = true;
                    $time = \mod_sharedresource\metadata::build_time($value);
                    $template->mtdvalue = $time['day'].' '.get_string('days', 'sharedresource').' ';
                    $template->mtdvalue .= $time['hour'].' '.get_string('hours', 'sharedresource').' ';
                    $template->mtdvalue .= $time['minute'].' '.get_string('minutes', 'sharedresource'). ' ';
                    $template->mtdvalue .= $time['second'].' '.get_string('seconds', 'sharedresource');
                }
                $template->mtdvalue .= $OUTPUT->help_icon('durationdescr', 'sharedresource', $duration);
                break;
            }

            case 'vcard': {
                $template->mtdvalue = '';
                $template->isscalar = true;

                $vcard = get_string('vcard', 'sharedmetadata_'.$namespace);
                $value = $elminstance->get_value();
                if (!empty($value)) {
                    $template->hascontent = true;
                    if (is_array($value)) {
                        $template->mtdvalue = '';
                        foreach ($value as $item) {
                            $template->mtdvalue .= "<p><pre>";
                            $template->mtdvalue .= $item;
                            $template->mtdvalue .= "</pre></p>";
                        }
                    } else {
                        $template->mtdvalue = "<pre>";
                        $template->mtdvalue .= $value;
                        $template->mtdvalue .= "</pre>";
                    }
                }
                $template->mtdvalue .= $OUTPUT->help_icon('vcard', 'sharedresource', $vcard);
            }
        }
    }

    public function metadata_edit_form($capability) {

        $namespace = get_config('sharedresource', 'schema');
        $mtdstandard = sharedresource_get_plugin($namespace);

        // Get context params in.
        $add = optional_param('add', 0, PARAM_ALPHA);
        $update = optional_param('update', 0, PARAM_INT);
        $return = optional_param('return', 0, PARAM_BOOL); // Return to course/view.php if false or mod/modname/view.php if true.
        $section = optional_param('section', 0, PARAM_INT);
        $sharingcontext = optional_param('context', 1, PARAM_INT);
        $mode = required_param('mode', PARAM_ALPHA);
        $courseid = required_param('course', PARAM_INT);

        $template = new StdClass;
        $template->pluginname = $namespace;
        $template->metadatadescrstr = get_string('metadatadescr', 'sharedresource');
        $template->namespace = $namespace;
        $template->receiverurl = new moodle_url('/mod/sharedresource/metadatarep.php');
        $template->mode = $mode;
        $template->hascontent = false;
        $template->course = $courseid;
        $template->section = $section;
        $template->sharingcontext = $sharingcontext;
        $template->add = $add;
        $template->return = $return;
        $template->nodestr = get_string('node', 'sharedresource');
        $template->completeformstr = get_string('completeform', 'sharedresource');

        $template->nbrchilds = count($mtdstandard->getElementChilds(0));
        $template->dmusedstr = get_string('dmused', 'sharedresource');

        $template->dmusestr = get_string('dmuse', 'sharedresource');
        $template->dmdescriptionstr = get_string('dmdescription', 'sharedresource');

        $template->standarddescriptionstr = get_string('standarddescription', 'sharedmetadata_'.$mtdstandard->pluginname);

        $this->edit_panels($capability, $mtdstandard, $template);

        $tabmodel = $this->detect_tab_model();
        $template->tabmodel = $tabmodel;

        $template->validateformstr = get_string('validateform', 'sharedresource');
        $template->cancelformstr = get_string('cancelform', 'sharedresource');

        return $this->output->render_from_template('mod_sharedresource/metadataeditform', $template);
    }

    /*
     * This function creates content of tabs.
     */
    function edit_panels($capability, &$mtdstandard, &$template) {

        $mode = required_param('mode', PARAM_ALPHA);

        $rootnodes = $mtdstandard->getElementChilds(0);

        $template->nbrchilds = count($rootnodes);

        // Members for childs.
        $template->daysstr = get_string('days', 'sharedresource');
        $template->hoursstr = get_string('hours', 'sharedresource');
        $template->minutesstr = get_string('minutes', 'sharedresource');
        $template->secondsstr = get_string('seconds', 'sharedresource');
        $template->durationdescstr = get_string('durationdescr', 'sharedresource');
        $template->durationhelpicon = $this->output->help_icon('durationdescr', 'sharedresource', $template->durationdescstr);
        $template->vcardstr = get_string('vcard', 'sharedresource');
        $template->vcardhelpicon = $this->output->help_icon('vcard', 'sharedresource', $template->vcardstr);
        $template->addstr = get_string('add', 'sharedresource');

        $i = 1;

        foreach ($rootnodes as $rootid => $islist) {

            // Start with instance 0.
            $rootelementid = $rootid.':0';

            // Build the form.
            $paneltpl = $this->part_form($template, $rootelementid, $capability, 0, true);

            if (empty($paneltpl)) {
                // Hidden panels for current user.
                $i++;
                continue;
            }

            $paneltpl->i = $rootid;
            $lowername = strtolower($mtdstandard->METADATATREE[$rootid]['name']);
            $paneltpl->tabname = get_string(clean_string_key($lowername), 'sharedmetadata_'.$template->namespace);
            $template->tabs[] = $this->tab($rootid, $capability, $template, 'write');

            $template->hascontent = true;
            $i++;
        }
    }

    /**
     * Scans the standard structure to display existing instances form and an additional form for new instances.
     *
     * @param string $elementkey the standard element id m_n_o:x_y_z
     * @param string $capability tells if the field is visible or not depending of the category of the user
     * @param boolean $realoccur is used only in the case of classification, when a classification is deleted by an admin and does not 
     * appear anymore on the metadata form. Realoccur is the visible occurrence the user should see for the part.
     */
    function part_form(&$parenttemplate, $elementkey, $capability, $realoccur = 0, $ispanel = false) {
        global $SESSION, $CFG, $DB;
        static $taxumarray;

        $config = get_config('sharedresource');
        $namespace = $config->schema;
        $mtdstandard = sharedresource_get_plugin($namespace);
        list($nodeid, $instanceid) = explode(':', $elementkey);
        $htmlname = metadata::storage_to_html($elementkey);
        $standardelm = $mtdstandard->getElement($nodeid);
        $addelementkey = $elementkey;

        if (!$mtdstandard->hasNode($nodeid)) {
            // Trap out if not exists.
            return null;
        }

        $template = new Stdclass;
        $template->childs = array(); // Stop the uplooking recursion.
        $template->ispanel = $ispanel;
        $template->hascontent = false;
        $template->islist = $standardelm->islist;

        $lowername = strtolower($standardelm->name);
        $template->fieldvisiblename = get_string(clean_string_key($lowername), 'sharedmetadata_'.$namespace);
        $template->fieldtype = $standardelm->type;
        if (is_null($taxumarray)) {
            $taxumarray = $mtdstandard->getTaxumpath();
        }
        $template->fieldname = $standardelm->name;

        // Get the current sharedresource from the session context.
        $srentry = $SESSION->sr_entry;
        $shrentry = unserialize($srentry);
        $error = @$SESSION->error; // It's an array containing field which contains error and the name of this error.
        if ($error == 'no error' || !is_array(unserialize($error))) {
            $template->error = '';
        } else {
            $template->error = unserialize($error);
            $template->errorclass = 'error';
        }

        $template->elmname = $htmlname;
        $instancekey = metadata::html_to_storage($template->elmname);
        $template->keyid = $instancekey;

        // Get a full loaded metadata object. this object will provide all metadata instance related primitives.
        // It may not come from database.
        $elminstance = metadata::instance($shrentry->id, $instancekey, $namespace, false);
        $elminstance->numoccur = $elminstance->get_instance_index();
        $elminstance->maxoccur = $elminstance->get_max_occurrence();
        if ($elminstance->maxoccur < $elminstance->numoccur) {
            $elminstance->maxoccur = $elminstance->numoccur;
        }

        $numoccur = $elminstance->numoccur;
        $maxoccur = $elminstance->maxoccur;

        // echo "NodeID : $nodeid ";
        // echo "InstanceID : $instanceid ";
        // print_object($elminstance);
        // print_object($standardelm);
        // echo "numoccur $numoccur ";
        // echo "maxoccur $maxoccur ";

        if (!$elminstance->node_has_capability($capability, 'write')) {
            return null;
        }

        $template->occur = '';
        if (!empty($realoccur)) {
            $template->occur = $realoccur;
        }

        /*
         * an array storing the child element name references for JS.
         */
        $listresult = array();

        if ($standardelm->type == 'category') {
            // echo "// $elementkey iscategory \n";
            if (!is_null($taxumarray) && $nodeid == $taxumarray['main']) {

                // echo "// $elementkey isclassification \n";
                // If the field concerns classification :
                /*
                 * we need group the id and item fields into one unique input widget.
                 * there may be several taxons selected as a list so fetch max occurrence
                 */

                $template->isclassification = true;
                $template->nodeid = $taxumarray['id'];

                if ($numoccur >= 0 && !$realoccur) {
                    $template->occur = $numoccur + 1;
                }

                $sourceelmnodeid = $taxumarray['source'];
                $sourceelminstancekey = \mod_sharedresource\metadata::to_instance($sourceelmnodeid, $elminstance->get_instance_id());
                $sourceelm = \mod_sharedresource\metadata::instance($shrentry->id, $sourceelminstancekey, $namespace, false);
                $taxonelm = $sourceelm->get_parent(false); // May not really exist in DB.

                $value = $sourceelm->get_value();
                if (empty($value)) {
                    // echo "// initial value : Undefined \n";
                    // Not yet any value, take the first active available.
                    $params = array('enabled' => 1);
                    $instanceindex = $elminstance->get_instance_index();
                    // echo "// Node index : $instanceindex \n";
                    if ($instanceindex == 0) {
                        // echo "// Take first available source \n";
                        /*
                         * If we are the first unvalued element, take the first classification available
                         * as it will be loaded in the upper "source" select chooser
                         */
                        if ($firsts = $DB->get_records('sharedresource_classif', $params, 'name', '*', 0, 1)) {
                            // Should take the active classification of the upper node that is: 9nx_2ny_1n0
                            $sourceelm = array_pop($firsts);
                            $sourceelmid = $sourceelm->id;
                        } else {
                            // echo "// No classification source \n";
                        }
                    } else {
                        // echo "// Take parent source \n";
                        // Here we need to find wich classif was already fixed for this branch.
                        // Get immediate parent and require the source on node 0.
                        $parentinstance = $elminstance->get_parent(false); // Do not explicitely exist.
                        $sourcenodeid = $parentinstance->get_node_id().'_1';
                        $sourceinstanceid = $parentinstance->get_instance_id().'_0';
                        $sourceinstanceid = \mod_sharedresource\metadata::to_instance($sourcenodeid, $sourceinstanceid);
                        // The instance must exist.
                        $sourceinstance = \mod_sharedresource\metadata::instance($shrentry->id, $sourceinstanceid, $namespace, false);
                        // Id of the source taxonomy is the value of this source instance.
                        $params = array('id' => $sourceinstance->get_value());
                        $sourceelm = $DB->get_record('sharedresource_classif', $params);
                        $sourceelmid = $sourceelm->id;
                    }
                } else {
                    // echo "// initial value : $value \n";
                    $params = array('id' => $value);
                    $sourceelmid = $DB->get_field('sharedresource_classif', 'id', $params);
                }

                // echo "// Source element id : $sourceelmid \n";
                if (empty($sourceelmid)) {
                    $template->classificationselect = $this->output->notification(get_string('notaxonomies', 'sharedresource'));
                } else {
                    /*
                     * We check if there is metadata saved for this field.
                     * the datasource allows us to track what is the taxonomy selection source select for all
                     * subjacent taxon value selectors. It is fixed to the taxonelement htmlID so we can be sure
                     * that dynamic subsequent form parts in the same binding will all catch the binding source.
                     */
                    $classificationoptions = metadata_get_classification_options($sourceelmid);
                    $htmlsourcekey = \mod_sharedresource\metadata::storage_to_html($taxonelm->get_element_key());
                    $attrs = array('class' => 'mtd-form-input', 'id' => $template->elmname, 'data-source' => $htmlsourcekey);
                    $nochoice = array('' => get_string('none', 'sharedresource'));

                    // Get value in associated taxonid element.
                    $taxonidelmkey = \mod_sharedresource\metadata::to_instance($taxumarray['id'], $elminstance->get_instance_id());
                    $taxonidelm = \mod_sharedresource\metadata::instance($shrentry->id, $taxonidelmkey, $namespace, false);

                    $value = $taxonidelm->get_value();
                    $taxontpl = new StdClass;
                    $taxontpl->classificationselect = html_writer::select($classificationoptions, $template->elmname, $value, $nochoice, $attrs);
                    $taxontpl->occur = $template->occur;
                    $template->taxons[] = $taxontpl;

                    /*
                     * Get all other taxons that may be added to this classification set. This will shift the apparent $addelementkey
                     * puhshing it up for any available taxon sibling.
                     */
                    if ($value) {
                        /*
                         * If we have a value we are building an updating form. If we have no value on the
                         * first taxon, this is a new form or a new form part got by ajax for adding a taxon.
                         */
                        $siblings = $taxonidelm->get_siblings(1);
                        $i = 2;
                        foreach ($siblings as $sib) {
                            $taxontpl = new StdClass;
                            /*
                             * Taxon subelements are bundled in form into the parent classification taxon. As this parent is
                             * a virtual container, we do NOT require it exists in database.
                             */
                            $siblingelementkey = $sib->get_parent(false)->get_element_key();
                            $addelementkey = $siblingelementkey;
                            $htmlname = metadata::storage_to_html($siblingelementkey);
                            $taxontpl->elmname = $htmlname;
                            $attrs = array('class' => 'mtd-form-input', 'id' => $htmlname, 'data-source' => $htmlsourcekey);
                            $taxontpl->classificationselect = html_writer::select($classificationoptions, $htmlname, $sib->get_value(), $nochoice, $attrs);
                            $taxontpl->occur = $i;
                            $template->taxons[] = $taxontpl;
                            $i++;

                            // Also increment the effective template occurrence to push the "new instance button id up".
                            if ($numoccur >= 0) {
                                $template->occur = $numoccur + 1;
                            }
                        }
                    }
                }

            } else {
                $template->iscontainer = true;
                // echo "// $elementkey iscontainer \n";

                // If the category is a list, we have to check the number of occurrence of the category.
                if ($elminstance->isstored) {
                    // If a stored element, fetch the childs in the metadata storage.
                    if ($elminstance->get_level() == 1) {
                        $listresult = $elminstance->get_roots($nodeid, $capability, 'write', true);
                    } else {
                        $listresult = $elminstance->get_childs($nodeid, $capability, 'write', true);
                    }
                } else {
                    // If NOT a stored element, fetch the childs in the metadata definition.
                    $standardchilds = $mtdstandard->getElementChilds($nodeid, $capability, 'write', true);
                    foreach ($standardchilds as $chid => $islist) {
                        $elementid = metadata::to_instance($chid, $elminstance->get_instance_id());
                        $element = metadata::instance($shrentry->id, $elementid, $namespace, false);
                        if ($element->node_has_capability($capability, 'write')) {
                            $listresults[$elementid] = $element;
                        }
                    }
                }

                // Print all subelements.
                if (!empty($listresults)) {
                    // It's ok and we display the category instances, then display children recursively.

                    if ($numoccur > 0 || $maxoccur > 0) {
                        $template->occur = $numoccur + 1;
                    }

                    foreach ($listresults as $childkey => $elementinstance) {
                        // $childstandard = $mtdstandard->getElement($elementinstance->get_node_id());
                        $this->part_form($template, $childkey, $capability, 0);
                        if (count($template->childs)) {
                            $template->hascontent = true;
                        }
                    }
                }
            }
        } else {
            // echo "// $elementkey iswidget \n";
            // Final widgets always have content.
            $template->hascontent = true;
            $this->print_widget($mtdstandard, $elminstance, $standardelm, $template, $shrentry);
        }

        /*
         * If we are printing the first instance, and it was a stored record (not a new default record)
         * than we need first get all other siblings and print those instances.
         * If we were not stored, the form part is for getting a first value and is enough.
         * Do not process if we are adding a new from instance through AJAX calls.
         */
        $siblingcollector = new StdClass;
        if (( ((integer) $numoccur) === 0) && $elminstance->isstored) {
            $maxoccur = $elminstance->get_max_occurrence();
            // $siblings = $elminstance->get_siblings($nodeid, $capability, 'write', true); // Obsolete form ? 
            $siblings = $elminstance->get_siblings(0);

            if (!empty($siblings)) {
                // All siblings will have a numoccur > 0.
                foreach ($siblings as $sib) {
                    $this->part_form($siblingcollector, $sib->get_element_key(), $capability, 0);
                }
            }
        }

        $template->hasaddbutton = false;
        if ($standardelm->islist && (!defined('AJAX_SCRIPT') || !AJAX_SCRIPT)) {

            if ($numoccur <= $elminstance->maxoccur || empty($elminstance->maxoccur)) {

                /*
                 * If element is a list we need display an add button to allow adding.
                 * an aditional form fragment. This button should be disabled until the
                 * first free form has not been filled. Children are named agains the last form
                 * occurrenc available. All previous occurences are supposed to be filled.
                 */
                if (!empty($listresults)) {
                    // Provide all children identities to the add button so it can locally check if inputs are empty or filled.
                    $childkeys = array_keys($listresults);
                    foreach ($childkeys as &$ckey) {
                        $ckey = metadata::storage_to_html($ckey);
                    }
                    $template->listchildren = implode(';', $childkeys);
                }

                // If we are printing the last occurence, or have no occurence, let diplay an add button.
                // If $maxoccur is really empty, the form is a "new element form", so disable the button, untill the value is changed.
                $template->hasaddbutton = true;
                $template->nextoccur = $template->occur + 1;
                $template->addid = metadata::storage_to_html($addelementkey);
                $template->addclass = 'is-list';
                if ($elminstance->maxoccur === '') {
                    $template->adddisabled = 'disabled="disabled"';
                }
            }
        }

        // Assemble all siblings in order.
        $parenttemplate->childs[] = $template;
        // $parenttemplate->childs[$elementkey] = $template;
        if (!empty($siblingcollector->childs)) {
            foreach ($siblingcollector->childs as $sibid => $sibtpl) {
                // $parenttemplate->childs[$sibid] = $sibtpl;
                $parenttemplate->childs[] = $sibtpl;
            }
        }

        return $template;
    }

    protected function print_widget(&$mtdstandard, &$elminstance, &$standardelm, &$template, &$shrentry) {
        global $OUTPUT;

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        $elmoccur = $elminstance->get_instance_index();
        $maxoccur = $elminstance->get_max_occurrence();

        if ($elmoccur > 0 || $maxoccur > 0) {
            $template->occur = $elmoccur + 1;
        }

        $nodeid = $elminstance->get_node_id();

        // Previous fieldtype may resolve on one more generic type beneath.
        if ($standardelm->type == 'text' ||
                $standardelm->type == 'codetext' ||
                        $standardelm->type == 'longtext') {
            if ($standardelm->type == 'longtext') {
                $template->islongtext = true;
                if (!empty($standardelm->attributes)) {
                    $template->attributes = $standardelm->attributes;
                }
            } else {
                $template->istext = true;
            }

            $firstelmkey = metadata::to_instance($nodeid);
            if ($nodeid == $mtdstandard->getTitleElement()->node) {
                if ($elminstance->get_element_key() == $firstelmkey) {
                    // Lock only the first instance.
                    $template->value = $shrentry->title;
                    $template->readonly = 'readonly="readonly"';
                }
            } else if ($nodeid == $mtdstandard->getDescriptionElement()->node) {
                if ($elminstance->get_element_key() == $firstelmkey) {
                    $template->value = $shrentry->description;
                    // Lock only the first instance.
                    $template->readonly = 'readonly="readonly"';
                }
            } else if ($nodeid == $mtdstandard->getLocationElement()->node && $elminstance->get_value() == '') {
                $template->value = $shrentry->url;
            } else if ($elminstance->get_value() != '') {
                $template->value = $elminstance->get_value();
            }

        } else if ($standardelm->type == 'select' || $standardelm->type == 'sortedselect') {
            $template->isselect = true;

            if (array_key_exists('func', $mtdstandard->METADATATREE[$nodeid])) {
                // We have a dynamic options request throug a callback function.
                $classname = $mtdstandard->METADATATREE[$nodeid]['func']['class'];
                $method = $mtdstandard->METADATATREE[$nodeid]['func']['method'];
                $funccall = "$classname::$method";
                $options = call_user_func($funccall);
            } else {
                foreach ($mtdstandard->METADATATREE[$nodeid]['values'] as $value) {
                    if (is_number($value)) {
                        $options[$value] = $value;
                    } else {
                        $value = \Encoding::fixUTF8($value);
                        $str = get_string(clean_string_key($value), 'sharedmetadata_'.$namespace);
                        $options[$value] = $str;
                    }
                }
            }

            if ($standardelm->type == 'sortedselect') {
                asort($options);
            }

            $attrs = array('id' => $template->elmname, 'class' => ' mtd-form-input');
            if (!empty($mtdstandard->METADATATREE[$nodeid]['extraclass'])) {
                $attrs['class'] .= ' '.$mtdstandard->METADATATREE[$nodeid]['extraclass'];
            }
            $default = array('' => get_string('none', 'sharedresource'));
            $current = $elminstance->get_value();
            if (empty($options)) {
                $template->select = $OUTPUT->notification("Missing options for $template->elmname in standard plugin");
            } else {
                $template->select = html_writer::select($options, $template->elmname, $current, $default, $attrs);
            }
        } else if ($standardelm->type == 'date') {
            $template->isdate = true;

            list($fillyear, $fillmonth, $fillday) = array('', '', '');
            if ($elminstance->get_value() != '') {
                $date = date("Y-m-d", $elminstance->get_value());

                list($fillyear, $fillmonth, $fillday) = explode('-', $date);
            }

            $options = array();
            $options['-year-'] = get_string('year','sharedresource');

            for ($i = date('Y'); $i >= 1970; $i--) {
                $options[$i] = $i;
            }
            $attrs = array('id' => $template->elmname.'_dateyear', 'class' => 'mtd-form-date-select');
            $template->yearselect = html_writer::select($options, $template->elmname.'_dateyear', $fillyear, '', array(), $attrs);


            $options = array();
            $options['-month-'] = get_string('month','sharedresource');

            for ($i = 1; $i <= 12; $i++) {
                $month = sprintf('%02d', $i);
                $options[$month] = $month;
            }
            $attrs = array('id' => $template->elmname.'_datemonth', 'class' => 'mtd-form-date-select');
            $template->monthselect = html_writer::select($options, $template->elmname.'_datemonth', $fillmonth, '', array(), $attrs);

            $options = array();
            $options['-day-'] = get_string('day','sharedresource');

            for ($i = 1; $i <= 31; $i++) {
                $day = sprintf('%02d', $i);
                $options[$day] = $day;
            }
            $attrs = array('id' => $template->elmname.'_dateday', 'class' => 'mtd-form-date-select');
            $template->dayselect = html_writer::select($options, $template->elmname.'_dateday', $fillday, '', array(), $attrs);

        } else if ($standardelm->type == 'duration') {

            $template->isduration = true;
            $template->class = 'form-input-duration';

            $template->durationstr = get_string('durationdescr', 'sharedresource');

            if ($elminstance->get_value() != '') {
                $time = \mod_sharedresource\metadata::build_time($elminstance->get_value());
                $template->valueday = $time['day'];
                $template->valueday = $time['hour'];
                $template->valuemin = $time['minute'];
                $template->valuesec = $time['second'];
            }

        } else if ($standardelm->type == 'vcard') {
            $template->isvcard = true;
            if ($elminstance->get_value() != '') {
                $template->value = $elminstance->get_value();
            } else {
                $template->value = "BEGIN:VCARD\nVERSION:3.0\nFN:\nN:\nEND:VCARD";
            }

        }
        $template->iscontainer = false;
    }

    /**
     * Several themes have different way to render and layout tabs in moodle.
     * We sometime need to build our own tab tree, but coping with the overal 
     * theme way of doing.
     */
    protected function detect_tab_model() {

        $tabs[] = new \tabobject('fake', 'fakeurl', 'fakename');
        $tabtree = new \tabtree($tabs);

        $faketabs = $this->coreoutput->render_tabtree($tabtree);

        if (preg_match('/nav-tabs/', $faketabs)) {
            return 'nav nav-tabs';
        } else {
            return 'tabrow0';
        }
    }
}