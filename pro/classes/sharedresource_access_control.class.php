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
 * @author  Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package sharedresource
 *
 */
namespace mod_sharedresource;

use \StdClass;
use \moodle_exception;
use \coding_exception;

defined('MOODLE_INTERNAL') || die;

/**
 * \mod_sharedresource\accessctl defines an access control data structure for controling
 * use of taxonomies (classifications) or to documented resources.
 *
 * The access control structure is stored in a serialized form into the
 * accessctl field in table sharedresource_classif (or in sharedresource_entry table for per recource control)
 * and defines access control rules for the associated taxonomy (resp. resource).
 *
 * It will provide some helper methods to help implement the access control in other parts
 * of the sharedresource implementation.
 */
class access_ctl {

    /**
     * an array of profile_field names and accepted values.
     * profilefield keys are prefixed as "user:fieldname" for standard user attributes, or
     * "profile_field:" for cstom profile fields.
     */
    protected $profilefields;

    /**
     * an array of required capabilities and related context
     */
    protected $capabilities;

    /**
     * Constructor for the sharedresource_metadata class
     */
    public function __construct($profilefields = array(), $capabilities = array()) {
        $this->profilefields = $profilefields;
        $this->capabilities = $capabilities;
    }

    /**
     * @param int $serialized
     */
    public static function instance($serialized) {
        $data = unserialize($serialized);

        return new access_ctl(@$data->profilefields, @$data->capabilities);
    }

    /**
     * Checks if the current user complies any rule that might allow
     * him to use. (Or pattern).
     *
     * TODO : think how to optimize by adding caches whereever same data is used 
     * at each call.
     */
    public function can_use() {
        global $USER, $DB;

        if (!empty($this->profilefields)) {
            foreach ($this->profilefields as $ruleid => $pfrule) {
                debug_trace("Sharedresource: Checking profile rule ".print_r($pfrule, true));
                if (preg_match('/^user:/', $pfrule->profilefield)) {
                    $fieldname = str_replace('user:', '', $pfrule->profilefield);
                    $profilevalue = $USER->$fieldname;
                } else if (preg_match('/^profile_field:/', $pfrule->profilefield)) {
                    // Get user data in $USER ? or get it in DB;
                    $fieldname = str_replace('profile_field:', '', $pfrule->profilefield);
                    $profilefieldid = $DB->get_field('user_info_field', 'id', array('shortname' => $fieldname));
                    $params = array('userid' => $USER->id, 'fieldid' => $profilefieldid);
                    $profilevalue = $DB->get_field('user_info_data', 'data', $params);
                }

                if (strpos($pfrule->values, '~') === 0) {
                    // Pattern matching operator.
                    $value = preg_replace('/^~/', '', $pfrules->values);
                    if (preg_match($value, $profilevalue)) {
                        return true;
                    }
                } else {
                    // Exact matching operator.
                    if ($profilevalue == $pfrule->values) {
                        return true;
                    }
                }
            }
        } else {
            debug_trace("Sharedresource: No profile rule ");
        }

        if (!empty($this->capabilities)) {
            foreach ($this->capabilities as $ruleid => $caprule) {
                debug_trace("Sharedresource: Checking capability rule ".print_r($caprule, true));
                switch ($caprule->contextlevel) {
                    case CONTEXT_SYSTEM: {
                        $context = \context_system::instance();
                        break;
                    }

                    case CONTEXT_COURSECAT: {
                        $context = \context_coursecat::instance($caprule->instanceid);
                        break;
                    }

                    case CONTEXT_COURSE: {
                        $context = \context_course::instance($caprule->instanceid);
                        break;
                    }

                    case 1000: {
                        // invoke has_capability_anywhere
                        break;
                    }
                }

				$capname = $DB->get_field('capabilities', 'name', array('id' => $caprule->capability));
                if (has_capability($capname, $context)) {
                    return true;
                }
            }
        } else {
            debug_trace("Sharedresource: No capability rule ");
        }

        debug_trace("Sharedresource: Access denied ");
        return false;
    }

    /**
     * Rasterizes the content of an acces control object for human readability.
     */
    public function to_string() {
        global $OUTPUT;

        $template = new Stdclass;

        if (!empty($this->profilefields)) {
            foreach ($this->capabilities as $profrule) {
                $profiletpl = new StdClass;
                $profiletpl->shortname = $profrule->profilefield;
                $profiletpl->name = $DB->get_field('user_info_field', 'name', array('shortname' => $profrule->profilefield));
                if (preg_match('/^~/', $profrule->values)) {
                    $profiletpl->operator = get_string('matches', 'sharedresource');
                } else {
                    $profiletpl->operator = get_string('equals', 'sharedresource');
                }
                $profiletpl->value = $profrule->values;
                $template->profilerules[] = $profiletpl;
                $template->hasprofilerules = true;
            }
        }

        if (!empty($this->capabilities)) {
            foreach ($this->capabilities as $caprule) {
                $captpl = new StdClass;
                $captpl->shortname = $caprule->capability;
                $captpl->name = get_capability_string($caprule->capability);

                switch ($caprule->contextlevel) {
                    case CONTEXT_SYSTEM: {
                        $captpl->rulestr = get_string('insystem', 'sharedresource');
                        $captpl->target = '';
                        break;
                    }

                    case CONTEXT_COURSE: {
                        $captpl->rulestr = get_string('incourse', 'sharedresource');
                        $coursetarget = $DB->get_record('course', array('id' => $caprule->instanceid), 'id,shortname,fullname');
                        $captpl->targetshort = $coursetarget->shortname;
                        $captpl->targetname = $coursetarget->fullname;
                        break;
                    }

                    case CONTEXT_COURSECAT: {
                        $captpl->rulestr = get_string('incategory', 'sharedresource');
                        $coursecattarget = $DB->get_record('course_categories', array('id' => $caprule->instanceid), 'id,idnumber,name');
                        $captpl->targetshort = $coursecattarget->idnumber;
                        $captpl->targetname = format_string($coursecattarget->name);
                        break;
                    }

                    case 1000: {
                        $captpl->rulestr = get_string('somewhere', 'sharedresource');
                        $captpl->target = '';
                        break;
                    }
                }

                $template->capabilityrules[] = $captpl;
                $template->hascapabilityrules = true;
            }
        }

        return $OUTPUT->render_from_template('mod_sharedresource/accessrules', $template);
    }
}
