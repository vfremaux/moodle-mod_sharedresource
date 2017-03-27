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
class date_search_widget extends search_widget{

    /**
     * Constructor for the search_widget class
     */
    function __construct($pluginchoice, $id, $label, $type) {
        parent::__construct($pluginchoice, $id, $label, $type);
    }

    /**
     * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
     */
    function print_search_widget($layout, $value = 0) {
        global $OUTPUT;

        $lowername = strtolower($this->label);
        $widgetname = get_string(str_replace(' ', '', $lowername), 'sharedmetadata_'.$this->pluginchoice);

        echo $OUTPUT->box($widgetname.' '.$OUTPUT->help_icon('datesearch', 'sharedresource', false), 'header');
        echo $OUTPUT->box_start('content');
        echo '<input size="10" onclick="javascript:ds_sh(this);" name="'.$this->label.'_startdate" value="Begin" readonly="readonly"/> ';
        echo '<input size="10" onclick="javascript:ds_sh(this);" name="'.$this->label.'_enddate" value="End" readonly="readonly"/></td></tr></table>';
        echo '<table class="ds_box" cellpadding="0" cellspacing="0" id="ds_conclass" style="display: none;">';
        echo '<tr><td id="ds_calclass" colspan="3">';
        echo '</td></tr>';
        echo '</table>';
        echo $OUTPUT->box_end();
    }

    // catchs a value in session from CGI input
    function catch_value(&$searchfields) {
        global $SESSION;

        if (!isset($SESSION->searchbag)) {
            $SESSION->searchbag = new StdClass();
        }

        $paramkey = str_replace(' ', '_', $this->label);
        $searchfields[$this->id] = @$SESSION->searchbag->$paramkey;

        if ((isset($_GET[$paramkey.'_startdate']) && $_GET[$paramkey.'_startdate'] != 'Begin') || (isset($_GET[$paramkey.'_enddate']) && $_GET[$paramkey.'_enddate'] != 'End')) {
            $searchfields[$this->id] = $_GET[$paramkey.'_startdate'].':'.$_GET[$paramkey.'_enddate'];
        }
    }
}
