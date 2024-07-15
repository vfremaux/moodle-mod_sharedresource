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
 * @author  Valery Fremaux valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/taoresource is a work derived from Moodle mod/resoruce
 * @package    mod_sharedresource
 * @category   mod
 *
 * This is a separate configuration screen to configure any metadata stub that is attached to a shared resource.
 */
namespace mod_sharedresource\output;

use StdClass;
use moodle_url;
use html_writer;
use mod_sharedresource\metadata;
use mod_sharedresource\entry;
use Exception;
use moodle_exception;
use plugin_renderer_base;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/output/opened_core_renderer.php');

/**
 * Renderer for metadata
 */
class metadata_renderer extends plugin_renderer_base {

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
            $template->backadminpagestr = get_string('backadminpage', 'sharedresource');
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

        $template->dmusedstr = get_string('dmused', 'sharedresource');

        $template->dmusestr = get_string('dmuse', 'sharedresource');
        $template->dmdescription = get_string('dmdescription', 'sharedresource');

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
                $template->tabs[] = $this->tab($i, $nodeid, $capability, $template, 'read');
            }
            $i++;
        }

        return $this->output->render_from_template('mod_sharedresource/notice', $template);
    }

    /**
     * Creates tabs.
     */
    public function tab($i, $nodeid, $capability, &$template, $mode = 'read') {

        $namespace = get_config('sharedresource', 'schema');
        $mtdstandard = sharedresource_get_plugin($namespace);

        $tabtpl = new StdClass;
        $tabtpl->i = $i;

        if ($mode != 'read') {
            if (\mod_sharedresource\metadata::has_mandatories($nodeid)) {
                $tabtpl->mandatoryclass = 'is-mandatory is-empty';
                $tabtpl->mandatorysign = '(*)';
            } else {
                $tabtpl->mandatoryclass = '';
                $tabtpl->mandatorysign = '';
            }
        }

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
    public function part_view(&$parenttemplate, &$shrentry, $elementkey, $capability, $realoccur = 0) {
        static $mtdstandard;
        global $CFG;

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        // This is the complete representation of the metadata standard. Load once.
        if (is_null($mtdstandard)) {
            $mtdstandard = sharedresource_get_plugin($namespace);
        }

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
        $lastoccur = $elminstance->get_max_occurrence();
        if ($lastoccur < 1) {
            // Do not display occurence index if we have only one possible.
            unset($template->numoccur);
        }

        $lowername = strtolower($standardelm->name);
        $template->fieldname = $lowername;
        $template->fieldvisiblename = get_string(clean_string_key($lowername), 'sharedmetadata_'.$namespace);
        $template->fieldtype = $standardelm->type;

        // May be null on some standards. (DC)
        $taxumarray = $mtdstandard->getTaxumpath();

        if (optional_param('debug', false, PARAM_BOOL) && ($CFG->debug >= DEBUG_NORMAL)) {
            echo "NodeID : $nodeid <br/>";
            echo "NodeInstance : $elementkey <br/>";
            echo "elementName : {$standardelm->name} <br/>";
            echo "elementValue : {$elminstance->get_value()} <br/>";
            echo "elementType : {$standardelm->type} <br/>";
            echo "elementIsList : {$standardelm->islist} <br/>";
            echo "occur : {$numoccur}/{$lastoccur} <br/>";
            echo "Resource index : ".$mtdstandard->isResourceIndex($nodeid)."<br/>";
            echo "<br/>";
        }

        $template->keyid = $elementkey;
        $listresult = array();

        // We check if there is metadata saved for this field.
        $sourcekey = array();
        $sourcekey['pos'] = $taxumarray['source'];
        $sourcekey['occ'] = metadata_get_node_occurence($elminstance->get_instance_id(), $sourcekey['pos']);
        $sourceelementkey = $sourcekey['pos'].':'.$sourcekey['occ'];
        $sourceelm = metadata::instance($shrentry->id, $sourceelementkey, $namespace);

        if ($standardelm->type == 'category') {

            if (!empty($taxumarray) && $nodeid == $taxumarray['main']) {
                /*
                 * If the field concerns classification, we reduce display to a scalar (classification path).
                 * Classification path is rebuilt from the taxonomy table. The taxonomy source is given by
                 * $taxumarray['source'] (as tablename of the taxonomy)
                 */

                $template->isscalar = true;

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
                // We are in a true category (not a classification).
                $template->iscontainer = true;

                $template->hascontent = false;

                // It's ok and we display the category, then display children recursively.
                $template->fieldnum = $nodeid;

                if ($numoccur > 0) {
                    $template->occur = $numoccur + 1;
                }

                $standardelmchilds = $mtdstandard->getElementChilds($nodeid);
                if (!empty($standardelmchilds)) {
                    $template->hascontent = true;
                }

                // Start recursing in our subelements to render our content.
                $nbrchilds = count($standardelmchilds);
                $parenttemplate->childs[] = $template;
                foreach ($standardelmchilds as $childnodeid => $islist) {
                    // echo "Realizing child element given by : $childnodeid ";
                    $childkey = metadata::to_instance($childnodeid, $sourceelm->get_instance_id());
                    $this->part_view($template, $shrentry, $childkey, $capability, 0);
                    $parenttemplate->hascontent = $parenttemplate->hascontent || $template->hascontent;
                }

                // If we are the first category sibling of this element, then fetch the next category siblings to render.
                // $siblings = $elminstance->get_siblings($nodeid, $capability, 'read', true);
                if ($numoccur == 0 && $standardelm->islist) {
                    $siblings = $elminstance->get_siblings(0);
                    // echo "Siblings for {$standardelm->name}:$elementkey :";
                    // print_object($siblings);
                    if (!empty($siblings)) {
                        // All siblings will have a numoccur > 0.
                        foreach ($siblings as $sib) {
                            $this->part_view($parenttemplate, $shrentry, $sib->get_element_key(), $capability, 0);
                        }
                    }
                }
            }
        } else {
            // We are a terminal element.

            if (!empty($taxumarray) && $nodeid == $taxumarray['source']) {
                // Special case : we are the source in a taxonomy. We must get the souce name indirectly.
                // Mtdvalue contains the sharedresource_classif id. We want the name.
                if (is_numeric($elminstance->get_value())) {
                    $navigator = \local_sharedresources\browser\navigation::instance_by_id($elminstance->get_value());
                    $elminstance->set_value($navigator->name);
                    $standardelm->type = 'text';
                }
            }

            if ($numoccur == 0 && $standardelm->islist) {
                /*
                 * If we are first element of a list of scalar values, aggregate all values of siblings in a textual
                 * list. We replace the scalar value by an array. Each type will know what to do with this array and
                 * the way to display this value set.
                 */
                $siblings = $elminstance->get_siblings(0);
                // echo "List Siblings for {$standardelm->name}:$elementkey :";
                // print_object($siblings);
                if (!empty($siblings)) {
                    $values = [$elminstance->get_value()];
                    // All siblings will have a numoccur > 0.
                    foreach ($siblings as $sib) {
                        $values[] = $sib->get_value();
                    }
                    if ($mtdstandard->isResourceIndex($nodeid)) {
                        $values = $this->to_resources($values);
                    }
                    $elminstance->set_value($values);
                } else {
                    if ($mtdstandard->isResourceIndex($nodeid)) {
                        $elminstance->set_value($this->to_resources($elminstance->get_value()));
                    }
                }
            } else {
                if ($mtdstandard->isResourceIndex($nodeid)) {
                    $elminstance->set_value($this->to_resources($elminstance->get_value()));
                }
            }
            $template->hascontent = true;

            if (optional_param('debug', false, PARAM_BOOL) && ($CFG->debug >= DEBUG_NORMAL)) {
                echo "Printing element $elementkey. </br><hr/><br/>";
            }
            $this->print_data($standardelm, $elminstance, $template);
            $parenttemplate->hascontent = $parenttemplate->hascontent || $template->hascontent;
            // if (!empty($template->mtdvalue)) {
                $parenttemplate->childs[] = $template;
            // }
        }

        // Not really necessary now.
        return $template;
    }

    /**
     * Prints a scalar result
     * @param objectref &$standardelm The metadata element's definition in the standard
     * @param objectref &$elminstance The metadata effective instance with value inside
     * @param objectref &$template The rendering template context
     */
    public function print_data(&$standardelm, &$elminstance, &$template) {
        global $OUTPUT;

        $namespace = get_config('sharedresource', 'schema');
        $mtdstandard = sharedresource_get_plugin($namespace);

        // Do not print occurrence for scalars as they are merged into a text list.
        $template->occur = '';

        // Special fall-in

        $sizeelement = $mtdstandard->getSizeElement();
        if ($elminstance->get_node_id() == $sizeelement->node) {
            // For size element, use a special formatting.
            $value = $elminstance->get_value();
            $template->isscalar = true;
            $template->mtdvalue = $this->format_size($value);
            return;
        }

        // More general cases.

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
                        if (!empty($cleanedkey)) {
                            $template->mtdvalue = get_string($cleanedkey, 'sharedmetadata_'.$namespace);
                        }
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

    /**
     * Given an array of supposed resource ids, transform values to resource links.
     * First implementation will return notice links.
     * @param mixed $values a single scalar resource id or an array of resources ids.
     */
    public function to_resources($values) {
        if (is_array($values)) {
            if (!empty($values)) {
                foreach ($values as &$v) {
                    $shrentry = entry::read_by_id($v);
                    $url = $shrentry->get_notice_link();
                    $v = '<a href="'.$url.'">'.$shrentry->title.'</a>';
                }
            }
            return $values;
        } else {
            // $values is a scalar id. Process it as scalar.
            if (empty($values)) {
                return;
            }
            $shrentry = entry::read_by_id($values);
            $url = $shrentry->get_notice_link();
            return '<a href="'.$url.'">'.$shrentry->title.'</a>';
        }
    }

    public function metadata_edit_form($capability) {

        $namespace = get_config('sharedresource', 'schema');
        $mtdstandard = sharedresource_get_plugin($namespace);

        // Get context params in.
        $fromlibrary = optional_param('fromlibrary', 1, PARAM_BOOL); // Return to course or library.
        $returnpage = optional_param('returnpage', 0, PARAM_TEXT); // Return to course/view.php if false or mod/modname/view.php if course, 'browse' or 'explore' if library.
        $section = optional_param('section', 0, PARAM_INT);
        $catid = optional_param('catid', 0, PARAM_INT);
        $catpath = optional_param('catpath', '', PARAM_TEXT);
        $sharingcontext = optional_param('context', 1, PARAM_INT);
        $mode = required_param('mode', PARAM_ALPHA);
        $courseid = required_param('course', PARAM_INT);

        $template = new StdClass;
        $template->pluginname = $namespace;
        $template->metadatadescrstr = get_string('metadatadescr', 'sharedresource');
        $template->namespace = $namespace;
        $template->receiverurl = new moodle_url('/mod/sharedresource/metadatarep.php');
        $template->mode = $mode;
        $template->catid = $catid;
        $template->catpath = $catpath;
        $template->hascontent = false;
        $template->course = $courseid;
        $template->section = $section;
        $template->sharingcontext = $sharingcontext;
        $template->returnpage = $returnpage;
        $template->fromlibrary = $fromlibrary;
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
    public function edit_panels($capability, &$mtdstandard, &$template) {

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

        $i = 1;

        foreach ($rootnodes as $rootid => $islist) {

            $panelchildtpl = new StdClass;

            // Start with instance 0.
            $rootelementid = $rootid.':0';

            // Build the form. The panel parts are provided in a panelchilds array.
            $paneltpl = $this->part_form($panelchildtpl, $rootelementid, $capability, 0, true);

            if (empty($panelchildtpl->panelchilds)) {
                // Hidden panels with no content for current user.
                $i++;
                continue;
            }

            $panelchildtpl->i = $rootid;
            $lowername = strtolower($mtdstandard->METADATATREE[$rootid]['name']);
            $panelchildtpl->tabname = get_string(clean_string_key($lowername), 'sharedmetadata_'.$template->namespace);

            $template->panels[] = $panelchildtpl;
            $mode = 'write';
            $template->tabs[] = $this->tab($rootid, $rootid, $capability, $template, $mode);

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
    public function part_form(&$parenttemplate, $elementkey, $capability, $realoccur = 0, $ispanel = false) {
        global $SESSION, $CFG, $DB;
        static $taxumarray;
        static $classifsiblingsdone = false;
        static $traceindentlevel = 0;

        $traceindentlevel++;
        $CFG->traceindent = str_pad(' ', $traceindentlevel * 4);

        $config = get_config('sharedresource');
        $namespace = $config->schema;
        $mtdstandard = sharedresource_get_plugin($namespace);
        list($nodeid, $instanceid) = explode(':', $elementkey);
        $htmlname = metadata::storage_to_html($elementkey);
        $standardelm = $mtdstandard->getElement($nodeid);
        $addelementkey = $elementkey;

        if (!$mtdstandard->hasNode($nodeid)) {
            // Trap out if not exists.
            $traceindentlevel--;
            $CFG->traceindent = str_pad(' ', $traceindentlevel * 4);
            return null;
        }

        $template = new Stdclass;
        $template->debugdata = '';
        $template->childs = []; // Stop the uplooking recursion.
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

        $numoccur = $elminstance->get_instance_index();
        $lastoccur = $elminstance->get_max_occurrence();
        if ($lastoccur < $numoccur) {
            $lastoccur = $numoccur;
        }

        $debug = ">>>>\nNodeID : $nodeid\n";
        $debug .= "Namespace : $namespace \n";
        $debug .= "InstanceID : $instanceid \n";
        $debug .= "numoccur $numoccur\n";
        $debug .= "lastoccur $lastoccur\n";
        // debug_trace($debug, TRACE_DEBUG_FINE);
        // debug_trace($elminstance);
        // debug_trace($standardelm);

        if (!$elminstance->node_has_capability($capability, 'write')) {
            $traceindentlevel--;
            $CFG->traceindent = str_pad(' ', $traceindentlevel * 4);
            return null;
        }

        /*
         * occur is the printable suffix of the list instances.
         */
        $template->occur = '';
        if (!empty($realoccur)) {
            $template->occur = $realoccur;
        } else {
            $template->occur = (!empty($numoccur)) ? $numoccur : '';
        }

        /*
         * an array storing the child element name references for JS.
         */
        $listresult = array();

        if ($standardelm->type == 'category') {
            if (!is_null($taxumarray) && ($nodeid == $taxumarray['main'])) {

                if (function_exists('debug_trace')) {
                    debug_trace('Processing taxonomy element', TRACE_DEBUG);
                }

                // echo "// $elementkey isclassification \n";
                // If the field concerns classification :
                /*
                 * we need group the id and item fields into one unique input widget.
                 * there may be several taxons selected as a list so fetch max occurrence
                 */

                $template->isclassification = true;
                $template->nodeid = $taxumarray['id'];

                if ($numoccur >= 0 && !$realoccur) {
                    // Adjust for printing from 1.
                    $template->occur = $numoccur + 1;
                }

                $sourceelmnodeid = $taxumarray['source'];
                $sourceelminstancekey = \mod_sharedresource\metadata::to_instance($sourceelmnodeid, $elminstance->get_instance_id());
                $sourceelm = \mod_sharedresource\metadata::instance($shrentry->id, $sourceelminstancekey, $namespace, false);
                $taxonelm = $sourceelm->get_parent(false); // May not really exist in DB.

                $sourcevalue = $sourceelm->get_value();
                if (empty($sourcevalue)) {
                    // initial value : Undefined \n";
                    if (function_exists('debug_trace')) {
                        debug_trace(" Taxon Source initial value is undefined", TRACE_ERRORS);
                    }
                    // Not yet any value, take the first active available.
                    $params = array('enabled' => 1);
                    $instanceindex = $elminstance->get_instance_index();
                    if ($instanceindex == 0) {
                        // echo "// Take first available source \n";
                        /*
                         * If we are the first unvalued element, take the first classification available
                         * as it will be loaded in the upper "source" select chooser
                         */
                        if (function_exists('debug_trace')) {
                            debug_trace(" Taxon Source initial : Take first one", TRACE_DEBUG);
                        }
                        if ($firsts = $DB->get_records('sharedresource_classif', $params, 'name', '*', 0, 1)) {
                            // Should take the active classification of the upper node that is: 9nx_2ny_1n0
                            $sourceelm = array_pop($firsts);
                            $sourceelmid = $sourceelm->id;
                        } else {
                            // echo "// No classification source \n";
                            assert(1);
                        }
                    } else {
                        // echo "// Take parent source \n";
                        // Here we need to find wich classif was already fixed for this branch.
                        // Get immediate parent and require the source on node 0.
                        $parentinstance = $elminstance->get_parent(false); // Do not explicitely exist.
                        $sourcenodeid = $parentinstance->get_node_id().'_1';
                        $sourceinstanceid = $parentinstance->get_instance_id().'_0';
                        $sourceinstanceid = metadata::to_instance($sourcenodeid, $sourceinstanceid);
                        /*
                         * The instance must exist. If it does not, this is because it has NOT YET be recorded, f.e.
                         * when trying to multi-taxon a just recently client-side added form-part. In this case
                         * will we get it from the "branch" data of the template comming from ajax input.
                         */
                        $sourceinstance = metadata::instance($shrentry->id, $sourceinstanceid, $namespace, false);
                        // Id of the source taxonomy is the value of this source instance.
                        if ($sourceinstance->isstored) {
                            $params = array('id' => $sourceinstance->get_value());
                            $sourceelm = $DB->get_record('sharedresource_classif', $params);
                            $sourceelmid = $sourceelm->id;
                        } else {
                            // sourceelmid comes from some data in branch. Let's decode id.
                            $sourceelmid = $parenttemplate->taxonsourceid;
                            // echo "// Final sourcelmid $sourceelmid ";
                        }
                    }
                } else {
                    // echo "// initial value : $value \n";
                    $params = array('id' => $sourcevalue);
                    $sourceelmid = $DB->get_field('sharedresource_classif', 'id', $params);
                }

                if (empty($sourceelmid)) {
                    /*
                     * No taxonomy available for this id.
                     */
                    $taxontpl = new StdClass;
                    $taxontpl->classificationselect = $this->output->notification(get_string('notaxonomies', 'sharedresource'));
                    $taxontpl->occur = $template->occur;
                    $template->taxons[] = $taxontpl;
                } else {
                    /*
                     * We check if there is metadata saved for this field.
                     * the datasource allows us to track what is the taxonomy selection source select for all
                     * subjacent taxon value selectors. It is fixed to the taxonelement htmlID so we can be sure
                     * that dynamic subsequent form parts in the same binding will all catch the binding source.
                     */
                    $classificationoptions = metadata_get_classification_options($sourceelmid);
                    $htmlsourcekey = metadata::storage_to_html($taxonelm->get_element_key());
                    $attrs = array('class' => 'mtd-form-input', 'id' => $template->elmname, 'data-source' => $htmlsourcekey);
                    $nochoice = array('' => get_string('none', 'sharedresource'));

                    // Get value in associated taxonid element.
                    $taxonidelmkey = metadata::to_instance($taxumarray['id'], $elminstance->get_instance_id());
                    $taxonidelm = metadata::instance($shrentry->id, $taxonidelmkey, $namespace, false);

                    $value = $taxonidelm->get_value();
                    $taxontpl = new StdClass;
                    $sourceselect = html_writer::select($classificationoptions, $template->elmname, $value, $nochoice, $attrs);
                    // debug_trace($sourceselect);
                    $taxontpl->classificationselect = $sourceselect;
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
                            $realoccur++;
                            $i++;
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
                    $listresults = array();
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

                    foreach ($listresults as $childkey => $elementinstance) {
                        // $childstandard = $mtdstandard->getElement($elementinstance->get_node_id());
                        $this->part_form($template, $childkey, $capability, $elementinstance->get_instance_index());
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
         * This is the general case. But special sibling collector may exist from previous processing (@see classification).
         */
        if (!isset($siblingcollector)) {
            $siblingcollector = new StdClass;

            if (( ((integer) $numoccur) === 0)) {
                $siblings = $elminstance->get_siblings(0);

                if (function_exists('debug_trace')) {
                    debug_trace("START Element siblings for ".$elminstance->get_element_key(), TRACE_DEBUG_FINE);
                }
                if (!empty($siblings)) {
                    // All siblings will have a numoccur > 0.
                    $i = 1;
                    foreach ($siblings as $sib) {
                        if (function_exists('debug_trace')) {
                            debug_trace("Calling form for element sibling ".$sib->get_element_key(), TRACE_DEBUG_FINE);
                        }
                        $this->part_form($siblingcollector, $sib->get_element_key(), $capability, $i);
                        if (function_exists('debug_trace')) {
                            debug_trace("Call out", TRACE_DEBUG_FINE);
                        }
                        $i++;
                    }
                    // Reajust maxoccur on last numoccur
                    $lastoccur = $i;
                }
                if (function_exists('debug_trace')) {
                    debug_trace("END Element siblings", TRACE_DEBUG_FINE);
                }
            }
        }

        $template->hasaddbutton = false;
        if ($standardelm->islist) {
            // if ($standardelm->islist && (!defined('AJAX_SCRIPT') || !AJAX_SCRIPT)) {
            // debug_trace($elminstance, TRACE_DATA);
            // debug_trace("Realoccur:{$realoccur};LastOccur:{$lastoccur};MaxOccur:".@$elminstance->maxoccur.";IsAjaxRoot:".@$parenttemplate->is_ajax_root, TRACE_DEBUG_FINE);
            $template->debugdata .= "Realoccur:{$realoccur};LastOccur:{$lastoccur};MaxOccur:".@$elminstance->maxoccur.";IsAjaxRoot:".@$parenttemplate->is_ajax_root;

            $printaddbutton = false;
            if (empty($parenttemplate->is_ajax_root)) {
                if (!empty($elminstance->maxoccur) && ($lastoccur >= $elminstance->maxoccur)) {
                    // If there is a hard limit in the element definition, play it if reached.
                    if ($realoccur == $elminstance->maxoccur - 1) {
                        $printaddbutton = true;
                    }
                } else {
                    if ($realoccur == $lastoccur) {
                        // Print add button on last element occurrence.
                        $printaddbutton = true;
                    }
                }
            }

            if ($printaddbutton) {
                if (function_exists('debug_trace')) {
                    debug_trace("PrintAddButton", TRACE_DEBUG_FINE);
                }
                $template->debugdata .= " Print Add Button ";

                /*
                 * If element is a list we need display an add button to allow adding.
                 * an aditional form fragment. This button should be disabled until the
                 * first free form has not been filled. Children are named agains the last form
                 * occurrence available. All previous occurences are supposed to be filled.
                 */
                $childkeys = array();
                if (!empty($listresults)) {
                    // Provide all children identities to the add button so it can locally check if inputs are empty or filled.
                    $childkeys = array_keys($listresults);
                    foreach ($childkeys as &$ckey) {
                        $ckey = metadata::storage_to_html($ckey);
                    }
                    // Add current element as it is part of the dependencies.
                }
                $childkeys[] = $htmlname;
                // space is important here, because let us search using ~= attribute css operator.
                $template->listdeps = implode(' ', $childkeys);

                // If we are printing the last occurence, or have no occurence, let NOT display an add button.
                // If $lastoccur is really empty, the form is a "new element form", so disable the button, untill the value is changed.
                $template->hasaddbutton = true;
                if (!empty($template->isclassification)) {
                    // Mark this button as is-taxon-level
                    // This will help a lot to grap current taxonomy when adding a taxon.
                    $template->buttonistaxonlevel = 'is-taxon-level';
                }
                $template->addstr = get_string('add', 'sharedresource');
                if (is_numeric($realoccur)) {
                    $template->nextoccur = $realoccur + 1;
                } else {
                    $template->nextoccur = 1;
                }
                $template->addid = metadata::storage_to_html($addelementkey);
                $template->addclass = 'is-list';
                if ($lastoccur === '') {
                    $template->adddisabled = 'disabled="disabled"';
                }
            }
        }

        if (($CFG->debug != DEBUG_DEVELOPER) || !optional_param('debug', false, PARAM_BOOL)) {
            $template->debugdata = '';
        }

        // Assemble all siblings in order.
        if ($ispanel) {
            $template->iscontainer = true;

            $childcontainer = new StdClass;
            $childcontainer->childs[] = $template;
            $parenttemplate->panelchilds[] = $childcontainer;
        } else {
            $parenttemplate->childs[] = $template;
        }
        // $parenttemplate->childs[$elementkey] = $template;
        if (!empty($siblingcollector->childs)) {
            foreach ($siblingcollector->childs as $sibid => $sibtpl) {
                // $parenttemplate->childs[$sibid] = $sibtpl;
                if ($ispanel) {
                    $sibtpl->iscontainer = true;
                    $childcontainer = new StdClass;
                    $childcontainer->childs[] = $sibtpl;
                    $parenttemplate->panelchilds[] = $childcontainer;
                } else {
                    $parenttemplate->childs[] = $sibtpl;
                }
            }
        }

        if (function_exists('debug_trace')) {
            debug_trace("<<<<\n");
        }
        $traceindentlevel--;
        $CFG->traceindent = str_pad(' ', $traceindentlevel * 4);
        return $template;
    }

    protected function print_widget(&$mtdstandard, $elminstance, &$standardelm, &$template, &$shrentry) {
        global $OUTPUT;

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        $elmoccur = $elminstance->get_instance_index();
        $lastoccur = $elminstance->get_max_occurrence();

        $template->mandatoryclass = '';
        $template->mandatorysign = '';
        if ($elminstance->node_is_mandatory()) {
            if ($elminstance->get_value() != '') {
                $template->mandatoryclass = 'is-mandatory is-mandatory-'.$elminstance->get_branch_id();
            } else {
                $template->mandatoryclass = 'is-mandatory is-empty is-mandatory-'.$elminstance->get_branch_id();
            }
            $template->mandatorysign = '(*)';
        }

        if ($elmoccur > 0 || $lastoccur > 0) {
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
                    // Lock only the first instance, as it is given by first form.
                    $template->value = $shrentry->title;
                    $template->readonly = 'readonly="readonly"';
                    if (!empty($template->mandatoryclass)) {
                        // Remove the is-empty potential initial state.
                        $template->mandatoryclass = 'is-mandatory';
                    }
                }
            } else if ($nodeid == $mtdstandard->getDescriptionElement()->node) {
                if ($elminstance->get_element_key() == $firstelmkey) {
                    $template->value = $shrentry->description;
                    // Lock only the first instance, as it is given by first form.
                    $template->readonly = 'readonly="readonly"';
                    if (!empty($template->mandatoryclass)) {
                        // Remove the is-empty potential initial state.
                        $template->mandatoryclass = 'is-mandatory';
                    }
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
                        $str = get_string(clean_string_key(strtolower($value)), 'sharedmetadata_'.$namespace);
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
            $options['-year-'] = get_string('year', 'sharedresource');

            for ($i = date('Y'); $i >= 1970; $i--) {
                $options[$i] = $i;
            }
            $attrs = array('id' => $template->elmname.'_dateyear', 'class' => 'mtd-form-date-select');
            $template->yearselect = html_writer::select($options, $template->elmname.'_dateyear', $fillyear, '', array(), $attrs);

            $options = array();
            $options['-month-'] = get_string('month', 'sharedresource');

            for ($i = 1; $i <= 12; $i++) {
                $month = sprintf('%02d', $i);
                $options[$month] = $month;
            }
            $attrs = array('id' => $template->elmname.'_datemonth', 'class' => 'mtd-form-date-select');
            $template->monthselect = html_writer::select($options, $template->elmname.'_datemonth', $fillmonth, '', array(), $attrs);

            $options = array();
            $options['-day-'] = get_string('day', 'sharedresource');

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

        $tabmodel = new StdClass;

        if (preg_match('/nav-tabs/', $faketabs)) {
            $tabmodel->ul = 'nav nav-tabs';
            $tabmodel->li = 'nav-item';
            $tabmodel->link = 'nav-link';
        } else {
            $tabmodel->ul = 'tabrow0';
            $tabmodel->li = '';
            $tabmodel->link = '';
        }
        return $tabmodel;
    }

    /** 
     * A special formatter that processes a physical file size :
     * - If a unit is detected, do not change value.
     * - If no unit is detected consider in bytes and split/format in upper dimensions.
     */
    public function format_size($sizevalue) {

        if (empty($sizevalue)) {
            return '';
        }

        $unitpattern = '/[^0-9]+[\\s]*$/'; // Finishing with non numeric chars ?
        if (preg_match($unitpattern, $sizevalue)) {
            // Leave as it is.
            return $sizevalue;
        }

        // Reduce to highest multiplicator possible.
        $dim = '';
        if ($sizevalue > 1000) {
            $dim = ' ko';
            $sizevalue = $sizevalue / 1000;
        }
        if ($sizevalue > 1000) {
            $dim = ' Mo';
            $sizevalue = $sizevalue / 1000;
        }
        if ($sizevalue > 1000) {
            $dim = ' Go';
            $sizevalue = $sizevalue / 1000;
        }

        return sprintf('%01.1f', $sizevalue).$dim;
    }
}