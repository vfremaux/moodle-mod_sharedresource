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
 * @author  Valery Fremaux  <valery.fremaux@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package mod_sharedresource
 */
namespace mod_sharedresource;

use \StdClass;
use \moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * This is the "pro" extension of the standrd entry class. It added network and access control features.
 *
 * TODO : see to protect some fields
 */
class entry_extended extends entry {

    /**
     * check if resource is local or not
     * 
     * @return bool, true = local
     */
    public function is_local_resource() {
        global $CFG;

        if (isset($this->file) && $this->file) {
            $filename = $CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH.$this->file;
            if (is_file($filename)) {
                return true;
            }
        }
        return false;
    }

    /**
     * check if resource is remote or not
     *
     * @return bool, true = remote
     */
    public function is_remote_resource() {
        return ! $this->is_local_resource();
    }

    /**
     * check if resource has been provided from internal operation or
     * has been retrieved from an externaly bound resources source.
     *
     * @return bool, true = remote
     */
    public function has_local_provider() {
        return empty($this->provider) || $this->provider == 'local';
    }

    /**
     * Checks some simple access policy on ressource.
     * A user may have a user_info_field holding an acceptable value to match
     * in resource allowed value.
     * Access filter only affect search and browse capabilities. but not access to the sharedresource
     * if the user has access to a sharedresource publication in a course.
     * The resource may be multivaluated, depending on wether the access check is allowed to register
     * multiple values or not. this is set up in global settings.
     * 
     * @param object $resourceentry
     * @return boolean value (has access or not).
     * @see local/sharedresources/lib.php get_local_resources()§137
     */
    public function has_access() {
        global $DB, $USER;
        static $userdatacache;

        if (!isset($userdatacache)) {
            $userdatacache = array();
        }

        $config = get_config('sharedresource');

        if (empty($config->accesscontrol)) {
            // Global switch in config. Disables all access conrol overhead.
            return true;
        }

        // Per resource strategy.
        /*
         * This is a fine grain resource per resource accesss control management using
         * user userfields values.
         * Each resource has one single userfield control attribute (holding a custom userfield reference per id)
         * and a set of control values (allowed values) as a coma separated list.
         */
        if (!empty($this->accessctl)) {
            $accessctl = \mod_sharedresource\access_ctl::instance($this->accessctl);
            if ($accessctl->can_use()) {
                return true;
            }
        }

        // By taxonomy access control
        /*
         * Taxonomies have some access control rules based on profile field or capabilities.
         * At least one match is required to pass, when the resource has explicit taxonomy binding.
         * The check examines all sources registered for the resource, confirmed by an actual registered
         * binding taxonid. (note there could be remanent registered sources without any taxonid, following
         * a taxon deletion in the taxonomy.)
         */
         $mtdstandard = sharedresource_get_plugin($config->schema);
         $taxumarr = $mtdstandard->getTaxumpath();

         if (empty($taxumarr)) {
            // No need to care about taxonomy access control as there is no taxonomy in the standard.
            return true;
         }

        $sources = \mod_sharedresource\metadata::instances_by_node($this->id, $config->schema, $taxumarr['source']);
        if (!empty($sources)) {
            $return = false;
            $hasrelevant = false;
            foreach ($sources as $source) {
                // Find some tokenids that are attached to this source instance.
                $idkey = \mod_sharedresource\metadata::to_instance($taxumarr['id'], $source->get_instance_id()); // Normalise element key.
                debug_trace('Searching taxonids with key '.$idkey);
                $elmids = \mod_sharedresource\metadata::instances_by_element($this->id, $config->schema, $idkey, null, true);
                if (empty($elmids)) {
                    debug_trace('No taxonids with key '.$idkey);
                    continue;
                }
                $hasrelevant = true;
                /*
                 * Pass if 
                 * - one source at least has no access control.
                 * - one source at least has access allowed for user.
                 */
                 $taxonomy = $DB->get_record('sharedresource_classif', array('id' => $source->get_value()));
                 $classif = new \local_sharedresources\browser\navigation($taxonomy);
                 debug_trace('Checking taxonomy access in '.$taxonomy->name);
                 if ($classif->can_use()) {
                     debug_trace('Access granted to '.$taxonomy->name);
                     return true;
                 }
            }
            // No taxonomy match.
            $result = !$hasrelevant;

            if (!$result) {
                if (function_exists('debug_trace')) {
                    debug_trace("Sharedresource: some taxonomies but none taxonomy match");
                }
            } else {
                if (function_exists('debug_trace')) {
                    debug_trace("Sharedresource: Some taxonomies but no relevant taxon bindings");
                }
            }

            // If no taxonomy found has relevant taxon ids, then let pass as if was not indexed.
            return $result;
        }

        return true;
    }
}
