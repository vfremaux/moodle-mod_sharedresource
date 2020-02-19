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
 * @package    mod_sharedresource
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_sharedresource\selectors;

require_once($CFG->dirroot.'/mod/sharedresource/pro/classes/selector/taxon_selector_base.php');

defined('MOODLE_INTERNAL') || die();

/**
 * taxon selector subclass for the list of potential taxons,
 *
 * This returns only self enrollable taxons.
 */
class potential_taxons_selector extends taxon_selector_base {

    public function __construct($classifid, $options = array()) {

        if (empty($classifid)) {
            throw new coding_exception('This taxon selector needs a classification description to be chosen');
        }

        $selectorname = 'taxonpotentialselector';
        parent::__construct($selectorname, $classifid);
    }

    public function find_taxons($search) {
        global $DB;

        $whereclauses = array();
        $selected = explode(',', $this->classification->taxonselection);
        list($insql, $inparams) = $DB->get_in_or_equal($selected, SQL_PARAMS_QM, 'param', false);

        $fields      = "SELECT DISTINCT {$this->classification->sqlid}, {$this->classification->sqllabel}";
        $countfields = "SELECT COUNT(DISTINCT {$this->classification->sqlid})";

        $params = array();
        // if (!empty($this->classification->taxonselection)) {
            $whereclauses[] = 'id '.$insql;
            $params = $inparams;
        // }

        if (!empty($this->condition->sqlrestriction)) {
            $whereclauses[] = $this->condition->sqlrestriction;
        }

        $restriction = '';
        if (!empty($whereclauses)) {
            $restriction = implode(' AND ', $whereclauses);
            $restriction = ' WHERE '.$restriction;
        }

        $sql   = " 
            FROM
                {{$this->classification->tablename}} c
            {$restriction}
        ";

        $order = "
            ORDER BY
                {$this->classification->sqlsortorder}
        ";

        // Check to see if there are too many to show sensibly.
        if (!$this->is_validating()) {
            $potentialtaxonscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialtaxonscount > $this->maxtaxonsperpage) {
                return $this->too_many_results($search, $potentialtaxonscount);
            }
        }

        // If not, show them.
        $availabletaxons = $DB->get_records_sql($fields . $sql . $order, $params);

        if (empty($availabletaxons)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('pottaxonsmatching', 'sharedresource', $search);
        } else {
            $groupname = get_string('pottaxons', 'sharedresource');
        }
        return array($groupname => $availabletaxons);
    }
}
