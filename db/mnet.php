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
 * This file contains the mnet services for the user_mnet_host plugin
 *
 * @since 2.0
 * @package blocks
 * @subpackage sharedreosurce
 * @copyright 2012 Valery Fremaux
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$publishes = array(
    'sharedresourceservice' => array(
        'servicename' => 'sharedresourceservice',
        'description' => get_string('sharedresourceservice_name', 'sharedresource'),
        'apiversion' => 1,
        'classname'  => '',
        'filename'   => 'rpclib.php',
        'methods'    => array(
            'sharedresource_rpc_check',
            'sharedresource_rpc_get_categories',
            'sharedresource_rpc_get_list',
            'sharedresource_rpc_get_metadata',
            'sharedresource_rpc_move',
            'sharedresource_rpc_submit'
        ),
    ),
);
$subscribes = array(
    'sharedresourceservice' => array(
        'sharedresource_rpc_check' => 'mod/sharedresource/rpclib.php/sharedresource_rpc_check',
        'sharedresource_rpc_get_categories' => 'mod/sharedresource/rpclib.php/sharedresource_rpc_get_categories',
        'sharedresource_rpc_get_list' => 'mod/sharedresource/rpclib.php/sharedresource_rpc_get_list',
        'sharedresource_rpc_get_metadata' => 'mod/sharedresource/rpclib.php/sharedresource_rpc_get_metadata',
        'sharedresource_rpc_move' => 'mod/sharedresource/rpclib.php/sharedresource_rpc_move',
        'sharedresource_rpc_get_metadata' => 'mod/sharedresource/rpclib.php/sharedresource_rpc_submit'
    ),
);
