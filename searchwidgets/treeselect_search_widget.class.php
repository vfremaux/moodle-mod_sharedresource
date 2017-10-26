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
 * @author  Valery Fremaux
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package mod_sharedresource
 *
 */
namespace mod_sharedresource;

use \StdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/search_widget.class.php');

/**
 * search_widget defines a widget element for the search engine of metadata.
 */
class treeselect_search_widget extends search_widget {

    /**
     * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
     */
    public function print_search_widget($layout, $value = 0) {
        global $OUTPUT, $CFG;

        $str = '';
        $config = get_config('sharedresource');

        $lowername = strtolower($this->label);
        $widgetname = get_string(str_replace(' ', '', $lowername), 'sharedmetadata_'.$this->schema);

        require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$this->schema.'/plugin.class.php');
        $classifarray = unserialize($config->classifarray);
        require_once $CFG->dirroot.'/mod/sharedresource/classificationlib.php';
        $helpicon = $OUTPUT->help_icon('classificationsearch', 'sharedresource', false);
        $str .= $OUTPUT->box('<h2>'.get_string('taxonpath', 'sharedmetadata_'.$this->schema).' '.$helpicon.'</h2>', 'header');
        $str .= $OUTPUT->box_start('content');
        $str .= '<div id="classif0">';
        $jshandler = 'javascript:classif(this.options[selectedIndex].value,1,\'\',this.options[selectedIndex].value,this.options[this.selectedIndex].value);';
        $str .= '<select name="classif:0" onChange="'.$jshandler.'">';
        $str .= '<option selected value="defaultvalue"> </option>';
        $str .= print_classif2($classifarray, $value);
        $str .= '</select></div>';
        $str .= '<div id="classif1"></div>';
        $str .= $OUTPUT->box_end();

        return $str;
    }

    // Catchs a value in session from CGI input.
    public function catch_value(&$searchfields) {
        global $SESSION;

        if (!isset($SESSION->searchbag)) {
            $SESSION->searchbag = new StdClass();
        }

        $paramkey = str_replace(' ', '_', $this->label);
        $searchfields[$this->id] = @$SESSION->searchbag->$paramkey;

        $maxclassif = 0;
        foreach($_GET as $search => $value) {
            if (preg_match('#^classif:#', $search) && $_GET[$search] != 'defaultvalue') {
                if (substr($search, strpos($search,':') + 1) > $maxclassif) {
                    $maxclassif = substr($search,strpos($search,':') + 1);
                }
            }
        }

        if ($maxclassif > 0) {
            /*$searchfields[$widget->id] = substr($_GET['classif:'.$maxclassif], strpos($_GET['classif:'.$maxclassif], '\\') + 2);*/
            @$SESSION->searchbag->$paramkey = substr($_GET['classif:'.$maxclassif], strpos($_GET['classif:'.$maxclassif], '\\') + 2);
        }
    }
}
