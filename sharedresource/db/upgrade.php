<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */


// This file keeps track of upgrades to 
// the resource module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_sharedresource_upgrade($oldversion=0) {

    global $CFG, $THEME, $db;

    $result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }

    if($result && $oldversion < 2010033000){
        
        // add services for resource provider and consumers
        
        if (!get_record('mnet_service', 'name', 'resource_provider')){
            $service->name = 'resource_provider';
            $service->description = get_string('resource_provider_name', 'sharedresource');
            $service->apiversion = 1;
            $service->offer = 1;
            if (!$serviceid = insert_record('mnet_service', $service)){
                notify('Error installing resource provider service.');
                $result = false;
            }
            
            // retreive remote categorisation
            $rpc->function_name = 'sharedresource_rpc_get_categories';
            $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_get_categories';
            $rpc->parent_type = 'mod';  
            $rpc->parent = 'sharedresource';
            $rpc->enabled = 0; 
            $rpc->help = 'Get remote categorisation of exposed resource.';
            $rpc->profile = '';
            if (!$rpcid = insert_record('mnet_rpc', $rpc)){
                notify('Error installing resource_provider RPC calls.');
                $result = false;
            }
            $rpcmap->serviceid = $serviceid;
            $rpcmap->rpcid = $rpcid;
            $result = $result && insert_record('mnet_service2rpc', $rpcmap);

            // retreive remote categorisation
            $rpc->function_name = 'sharedresource_rpc_get_list';
            $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_get_list';
            $rpc->parent_type = 'mod';  
            $rpc->parent = 'sharedresource';
            $rpc->enabled = 0; 
            $rpc->help = 'Get remote list of exposed resources, by page or category.';
            $rpc->profile = '';
            if (!$rpcid = insert_record('mnet_rpc', $rpc)){
                notify('Error installing resource_provider RPC calls.');
                $result = false;
            }
            $rpcmap->serviceid = $serviceid;
            $rpcmap->rpcid = $rpcid;
            $result = $result && insert_record('mnet_service2rpc', $rpcmap);

            // retreive accept a resource submission 
            $rpc->function_name = 'sharedresource_rpc_submit';
            $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_submit';
            $rpc->parent_type = 'mod';  
            $rpc->parent = 'sharedresource';
            $rpc->enabled = 0; 
            $rpc->help = 'Accepts a resource submission for appending to local repository.';
            $rpc->profile = '';
            if (!$rpcid = insert_record('mnet_rpc', $rpc)){
                notify('Error installing resource_provider RPC calls.');
                $result = false;
            }
            $rpcmap->serviceid = $serviceid;
            $rpcmap->rpcid = $rpcid;
            $result = $result && insert_record('mnet_service2rpc', $rpcmap);
        }        

        if (!get_record('mnet_service', 'name', 'resource_consumer')){
            $service->name = 'resource_consumer';
            $service->description = get_string('resource_consumer_name', 'sharedresource');
            $service->apiversion = 1;
            $service->offer = 1;
            if (!$serviceid = insert_record('mnet_service', $service)){
                notify('Error installing resource consumer service.');
                $result = false;
            }
            
            // retreive remote categorisation
            $rpc->function_name = 'sharedresource_rpc_check';
            $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_check';
            $rpc->parent_type = 'mod';  
            $rpc->parent = 'sharedresource';
            $rpc->enabled = 0; 
            $rpc->help = 'Get remote categorisation of exposed resource.';
            $rpc->profile = '';
            if (!$rpcid = insert_record('mnet_rpc', $rpc)){
                notify('Error installing resource_consumer RPC calls.');
                $result = false;
            }
            $rpcmap->serviceid = $serviceid;
            $rpcmap->rpcid = $rpcid;
            $result = $result && insert_record('mnet_service2rpc', $rpcmap);
        }        
    }

    if($result && $oldversion < 2010041500){

        if ($customerservice = get_record('mnet_service', 'name', 'resource_consumer')){

            // retreive remote categorisation
            $rpc->function_name = 'sharedresource_rpc_move';
            $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_move';
            $rpc->parent_type = 'mod';  
            $rpc->parent = 'sharedresource';
            $rpc->enabled = 0; 
            $rpc->help = 'Ask to move the resource to another provider.';
            $rpc->profile = '';
            if (!$rpcid = insert_record('mnet_rpc', $rpc)){
                notify('Error installing resource_consumer RPC calls.');
                $result = false;
            }
            $rpcmap->serviceid = $customerservice->id;
            $rpcmap->rpcid = $rpcid;
            $result = $result && insert_record('mnet_service2rpc', $rpcmap);
        }
    }

    if($result && $oldversion < 2010090904){

        if ($customerservice = get_record('mnet_service', 'name', 'resource_provider')){

            // retreive remote metadata
            $rpc->function_name = 'sharedresource_rpc_get_metadata';
            $rpc->xmlrpc_path = 'mod/sharedresource/rpclib.php/sharedresource_rpc_get_metadata';
            $rpc->parent_type = 'mod';  
            $rpc->parent = 'sharedresource';
            $rpc->enabled = 0; 
            $rpc->help = 'Ask for metadata values in the provider.';
            $rpc->profile = '';
            if (!$rpcid = insert_record('mnet_rpc', $rpc)){
                notify('Error installing resource_consumer RPC calls.');
                $result = false;
            }
            $rpcmap->serviceid = $customerservice->id;
            $rpcmap->rpcid = $rpcid;
            $result = $result && insert_record('mnet_service2rpc', $rpcmap);
        }
    }

    if($result && $oldversion < 2012010109){
    	// force conversion of all keywords to metadata
    	if ($sharedresources_entries = get_records('sharedresource_entry', '', '')){
    		include_once($CFG->dirroot.'/mod/sharedresource/sharedresource_entry.class.php');
    		foreach($sharedresources_entries as $se){
    			$sharedresource_entry = sharedresource_entry::read($se->identifier);
    			$sharedresource_entry->after_update();
    		}
    	}
    }
    
//===== 1.9.0 upgrade line ======//

    return $result;
}

?>