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
 * @author  Valery Fremaux  valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod_sharedresource
 * @category mod
 *
 * wraps moodle WS to mnet service layer
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/externallib.php');

class mod_sharedresource_external extends external_api {

    /* ********************************** Get metadata *********************************** */

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_metadata_parameters() {
        global $CFG;

        return new external_function_parameters(
            array(
                'element' => new external_value(PARAM_ALPHA, 'Metadata element'),
                'namespace' => new external_value(PARAM_ALPHA, 'Metadata namespace'),
            )
        );
    }

    public static function get_metadata($element, $namespace) {
        global $CFG, $USER;

        return sharedresource_rpc_get_categories($USER, $CFG->wwwroot, $rootcategory, $namespace);
    }

    /* ********************************** Get categories *********************************** */

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_categories_parameters() {
        global $CFG;

        return new external_function_parameters(
            array(
                'rootcategory' => new external_value(PARAM_TEXT, 'Root category'),
                'namespace' => new external_value(PARAM_ALPHA, 'Metadata namespace'),
            )
        );
    }

    public static function get_categories($rootcategory, $namespace) {
        global $CFG, $USER;

        return sharedresource_rpc_get_categories($USER, $CFG->wwwroot, $rootcategory, $namespace);
    }

    /* ********************************** Get list *********************************** */

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function get_list_parameters() {
        global $CFG;

        return new external_function_parameters(
            array(
                'metadatafilters' => new external_value(PARAM_TEXT, 'Active filters on ressouces'),
                'offset' => new external_value(PARAM_INT, 'Record offset'), // Set to default 0
                'page' => new external_value(PARAM_INT, 'Page size'), // Set to default 20
            )
        );
    }

    public static function get_list($metadatafilters = '', $offset = 0, $page = 20) {
        return sharedresource_rpc_get_list($USER, $CFG->localhost, $metadatafilters, $offset, $page);
    }

    public static function submit_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_INT, ''),
                'resources' => new external_single_structure(
                    array(
                        'maxobjects' => ,
                        'entries' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'title' => new external_value(PARAM_TEXT, 'Resource title'),
                                    'description' => new external_value(PARAM_TEXT, 'Resource textual descirption'),
                                    'file' => new external_value(PARAM_INT, 'Resource internal file id'),
                                    'url' => new external_value(PARAM_URL, 'Resource url'),
                                    'identifier' => new external_value(PARAM_TEXT, 'Resource identifier'),
                                    'keywords' => new external_value(PARAM_TEXT, 'Resource keywords'),
                                    'lang' => new external_value(PARAM_TEXT, 'Resource language'),
                                    'isurlproxy' => new external_value(PARAM_BOOL, 'Resource proxy status'),
                                    'scorelike' => new external_value(PARAM_INT, 'Resource likes count'),
                                    'scoreview' => new external_value(PARAM_INT, 'Resource view count'),
                                    'id' => empty($entry->id),
                                    'uses' => new external_value(PARAM_TEXT, 'Resource uses count'),
                                    'metadata' => new external_multiple_structure(
                                        new external_single_structure(
                                            array(
                                                'element' => new external_value(PARAM_TEXT, 'Internal ID'),
                                                'namespace' => new external_value(PARAM_TEXT, 'Namespace'),
                                                'value' => new external_value(PARAM_TEXT, 'Element value')
                                            )
                                        ),
                                        VALUE_OPTIONAL
                                    )
                                )
                            )
                        ),
                    )
                )
            )
        );
    }

    /* ********************************** Submit *********************************** */

    /**
     * Submits a resource as a resouce descriptor.
     *
     * @return external_function_parameters
     */
    public static function submit_parameters() {
        global $CFG;

        return new external_function_parameters(
            array(
                'entry' => new external_value(PARAM_TEXT, 'An array with resource description'),
                'metadata' => new external_value(PARAM_INT, 'a metadata recordset'),
            )
        );
    }

    public static function submit($entry, $metadata) {
        return sharedresource_rpc_submit($USER, $CFG->localhost, $entry, $metadata);
    }

    public static function submit_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Domain value code'),
                'error' => new external_value(PARAM_TEXT, 'An error message if error', VALUE_OPTIONAL),
                'resourceid' => new external_value(PARAM_INT, 'Resource internal id', VALUE_OPTIONAL),
                'resourceurl' => new external_value(PARAM_URL, 'The access url to the resource', VALUE_OPTIONAL),
            )
        );
    }

    /* ********************************** Check resource count *********************************** */

    /**
     * Submits a resource as a resouce descriptor.
     *
     * @return external_function_parameters
     */
    public static function check_parameters() {
        global $CFG;

        return new external_function_parameters(
            array(
                'identifier' => new external_value(PARAM_TEXT, 'Resource content identifier hash'),
            )
        );
    }

    public static function check($identifier) {
        return sharedresource_rpc_check($USER, $CFG->localhost, $identifier);
    }

    public static function check_returns() {
        new external_value(PARAM_INT, 'Resource count');
    }

    /* ********************************** Move *********************************** */

    /**
     * Moves a resource to a different provider.
     *
     * @return external_function_parameters
     */
    public static function move_parameters() {
        global $CFG;

        return new external_function_parameters(
            array(
                'identifier' => new external_value(PARAM_TEXT, 'Resource content identifier hash'),
                'provider' => new external_value(PARAM_TEXT, 'the remote resource provider'),
                'url' => new external_value(PARAM_TEXT, 'The local url of the resource'),
            )
        );
    }

    public static function move($resourceid, $provider, $url) {
        global $CFG;

        $return = sharedresource_rpc_move($USER->id, $CFG->wwwroot, $resourceid, $provider, $url);
        return json_decode($return);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function move_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_TEXT, 'Domain value code'),
                'error' => new external_value(PARAM_TEXT, 'Domain value label', VALUE_OPTIONAL),
            )
        );
    }

}
