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
class numeric_search_widget extends search_widget {

    /**
     * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
     */
    public function print_search_widget($layout, $value = 0) {
        echo $OUTPUT;

        $str = '';

        $lowername = strtolower($this->label);
        $widgetname = get_string(str_replace(' ', '', $lowername), 'sharedmetadata_'.$this->schema);

        $str .= $OUTPUT->box('<h2>'.$widgetname.' '.$OUTPUT->help_icon('numericsearch', 'sharedresource', false).'</h2>', 'header');
        $str .= $OUTPUT->box_start('content');
        $str .= '<select name="'.$this->label.'_symbol">';
        $str .= '<option selected value="basicvalue"> </option>';
        $str .= '<option value="=">=</option>';
        $str .= '<option value="<>">?</option>';
        $str .= '<option value="<"><</option>';
        $str .= '<option value=">">></option>';
        $str .= '<option value="<=">=</option>';
        $str .= '<option value=">=">=</option>';
        $str .= '</select>';
        $str .= '<input type="text" name="'.$this->label.'"/>';
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
        if (!empty($_GET[$paramkey])) {
            $searchfields[$this->id] = $_GET[$paramkey.'_symbol'].':'.$_GET[$paramkey];
            $SESSION->searchbag->$paramkey = $_GET[$paramkey.'_symbol'].':'.$_GET[$paramkey];
        }
    }
}
