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
 * @package sharedresource
 *
 */
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');
require_once($CFG->dirroot.'/mod/sharedresource/search_widget.class.php');

/**
 * search_widget defines a widget element for the search engine of metadata.
 */
class selectmultiple_search_widget extends search_widget {

    /**
     * Constructor for the search_widget class
     */
    function __construct($pluginchoice, $id, $label, $type) {
        parent::__construct($pluginchoice, $id, $label, $type);
    }

    /**
     * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
     * @param string $layout
     * @param mixed $value
     */
    function print_search_widget($layout, $value = 0) {
<<<<<<< HEAD
    	global $CFG, $OUTPUT;

		$lowername = strtolower($this->label);
		$widgetname = get_string(clean_string_key($lowername), 'sharedresource');

		require_once $CFG->dirroot.'/mod/sharedresource/plugins/'.$this->pluginchoice.'/plugin.class.php';
		$object = 'sharedresource_plugin_'.$this->pluginchoice;
		$mtdstandard = new $object;

		echo $OUTPUT->box($widgetname.' '.$OUTPUT->help_icon('selectsearch', 'sharedresource', false), 'header');
		echo $OUTPUT->box_start('content');
		$selectallstr = get_string('selectall', 'sharedresource');
		$unselectallstr = get_string('unselectall', 'sharedresource');
		echo '<a class="smalltext" href="Javascript:search_widget_selectall(\''.$this->id.'\')">'.$selectallstr.'</a> / <a class="smalltext" href="Javascript:search_widget_unselectall(\''.$this->id.'\')">'.$unselectallstr.'</a><br/>';

		/**
		echo '<select multiple name="'.$this->label.'[]">';
		echo '<option value="defaultvalue"> </option>';
		foreach($mtdstandard->METADATATREE[$this->id]['values'] as $optvalue) {
			$selected = ($this->checkvalue($optvalue, $value)) ? ' selected ' : '' ;
			echo '<option value="'.$optvalue."\" $selected >".get_string(clean_string_key($optvalue), 'sharedresource').'</option>';
		}
		echo '</select>';
		**/
		foreach($mtdstandard->METADATATREE[$this->id]['values'] as $optvalue) {
			$checked = ($this->checkvalue($optvalue, $value)) ? ' checked ' : '' ;
			echo '<input type="checkbox" name="'.$this->label.'[]" value="'.$optvalue."\" $checked /> ".get_string(clean_string_key($optvalue), 'sharedresource').'<br/>';
		}

		echo $OUTPUT->box_end();
=======
        global $CFG, $OUTPUT;

        $str = '';

        $lowername = strtolower($this->label);
        $widgetname = get_string(clean_string_key($lowername), 'sharedmetadata_'.$this->pluginchoice);

        require_once $CFG->dirroot.'/mod/sharedresource/plugins/'.$this->pluginchoice.'/plugin.class.php';
        $object = 'sharedresource_plugin_'.$this->pluginchoice;
        $mtdstandard = new $object;

        $str .= $OUTPUT->box('<h2>'.$widgetname.' '.$OUTPUT->help_icon('selectsearch', 'sharedresource', false).'</h2>', 'header');
        $str .= $OUTPUT->box_start('content');
        $selectallstr = get_string('selectall', 'sharedresource');
        $unselectallstr = get_string('unselectall', 'sharedresource');
        $str .= '<a class="smalltext" href="Javascript:search_widget_selectall(\''.$this->id.'\')">'.$selectallstr.'</a> / <a class="smalltext" href="Javascript:search_widget_unselectall(\''.$this->id.'\')">'.$unselectallstr.'</a><br/>';

        /**
        $str .= '<select multiple name="'.$this->label.'[]">';
        $str .= '<option value="defaultvalue"> </option>';
        foreach($mtdstandard->METADATATREE[$this->id]['values'] as $optvalue) {
            $selected = ($this->checkvalue($optvalue, $value)) ? ' selected ' : '' ;
            $str .= '<option value="'.$optvalue."\" $selected >".get_string(clean_string_key($optvalue), 'sharedresource').'</option>';
        }
        $str .= '</select>';
        **/
        foreach ($mtdstandard->METADATATREE[$this->id]['values'] as $optvalue) {
            $checked = ($this->checkvalue($optvalue, $value)) ? ' checked ' : '';
            $str .= '<input type="checkbox" name="'.$this->label.'[]" value="'.$optvalue."\" $checked /> ".get_string(clean_string_key($optvalue), 'sharedmetadata_'.$this->pluginchoice).'<br/>';
        }

        $str .= $OUTPUT->box_end();

        return $str;
    }

    // catchs a value in session from CGI input
    function catch_value(&$searchfields) {
        global $SESSION;

        if (!isset($SESSION->searchbag)) {
            $SESSION->searchbag = new StdClass();
        }

        $paramkey = str_replace(' ', '_', $this->label);
        $searchfields[$this->id] = @$SESSION->searchbag->$paramkey;

        if (isset($_GET[$paramkey])) {
            $valueset = array();
            if (is_array($_GET[$paramkey])) {
                $selectvalue = implode(',', array_values($_GET[$paramkey]));
            } else {
                $selectvalue = $_GET[$paramkey];
            }
            if ($selectvalue != '') {
                $searchfields[$this->id] = $selectvalue;
                @$SESSION->searchbag->$paramkey = $selectvalue;
            }
        }
    }

    /**
     * checks an options against a selection given as scalar or array valueset.
     * @param mixed $opt the option to check in selection
     * @param mixed $value the selection as an array or a textual list
     */
    function checkvalue($opt, $value) {
        if (is_array($value)) {
            return in_array($opt, $value);
        }
        if (is_string($value)) {
            $opt = str_replace('/', '\\/', $opt);
            return preg_match("/\\b{$opt}\\b/", $value);
        }
        return false;
>>>>>>> MOODLE_32_STABLE
    }
}
