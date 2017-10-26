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
class freetext_search_widget extends search_widget {

    /**
     * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
     */
    public function print_search_widget($layout, $value = '') {
        global $OUTPUT;

        $str = '';

        $lowername = strtolower($this->label);
        $widgetname = get_string(str_replace(' ', '', $lowername), 'sharedmetadata_'.$this->schema);

        if (!empty($value)) {
            preg_match('/^([^:]+):(.*)/', $value, $matches);
            $operator = $matches[1];
            $value = $matches[2];
        } else {
            $operator = '';
            $value = '';
        }

        $includesselected = ($operator == 'includes') ? 'selected="selected"' : '';
        $equalsselected = ($operator == 'equals') ? 'selected="selected"' : '';
        $beginswithselected = ($operator == 'beginswith') ? 'selected="selected"' : '';
        $endswithselected = ($operator == 'endswith') ? 'selected="selected"' : '';

        $str .= $OUTPUT->box('<h2>'.$widgetname.' '.$OUTPUT->help_icon('textsearch', 'sharedresource', false).'</h2>', 'header');
        $str .= $OUTPUT->box_start('content');
        $str .= '<select name="'.$this->label.'_option">';
        $str .= "<option value=\"includes\" $includesselected >".get_string('contains', 'sharedresource').'</option>';
        $str .= "<option value=\"equals\" $equalsselected >".get_string('equalto', 'sharedresource').'</option>';
        $str .= "<option value=\"beginswith\" $beginswithselected >".get_string('startswith', 'sharedresource').'</option>';
        $str .= "<option value=\"endswith\" $endswithselected >".get_string('endswith', 'sharedresource').'</option>';
        $str .= '</select>';
        $str .= '<input type="text" name="'.$this->label.'" value="'.$value.'"/>';
        $str .= $OUTPUT->box_end();

        return $str;
    }

    /**
     * catchs a value in session from CGI input
     * @param arrayref &$searchfields
     * @return true if filter configuration has changed
     */
    public function catch_value(&$searchfields) {
        global $SESSION;

        if (!isset($SESSION->searchbag)) {
            $SESSION->searchbag = new StdClass();
        }

        $paramkey = str_replace(' ', '_', $this->label);
        $searchfields[$this->id] = @$SESSION->searchbag->$paramkey;
        if (isset($_GET[$paramkey])) {
            if ($_GET[$paramkey] != '') {
                $searchfields[$this->id] = $_GET[$paramkey.'_option'].':'.$_GET[$paramkey];
                $SESSION->searchbag->$paramkey = $_GET[$paramkey.'_option'].':'.$_GET[$paramkey];
            } else {
                $searchfields[$this->id] = '';
                $SESSION->searchbag->$paramkey = '';
            }
            return true;
        }

        return false;
    }
}
