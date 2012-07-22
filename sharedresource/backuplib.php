<?php 
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */

    //This php script contains all the stuff to backup/restore
    //resource mods

    //This is the "graphical" structure of the sharedresource mod:
    //
    //                 sharedresource                                      
    //                 (CL,pk->id)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          CL->course level info
    //          UL->user level info
    //
    //-----------------------------------------------------------

    //This function executes all the backup procedure about this mod
    function sharedresource_backup_mods($bf,$preferences) {
        global $CFG;
        $status = true; 

        ////Iterate over sharedresource table
        $sharedresources = get_records ('sharedresource', 'course', $preferences->backup_course, 'id');
        if ($sharedresources) {
            foreach ($sharedresources as $sharedresource) {
                if (backup_mod_selected($preferences, 'sharedresource', $sharedresource->id)) {
                    $status = sharedresource_backup_one_mod($bf,$preferences,$sharedresource);
                }
            }
        }
        return $status;
    }

    
    function sharedresource_backup_one_mod($bf, $preferences, $sharedresource) {
        global $CFG;
        global $sharedresource_entry_backedup;
        
        if (is_numeric($sharedresource)) {
            $sharedresource = get_record('sharedresource', 'id', $sharedresource);
        }
    
        $status = true;
        
        //Start mod
        fwrite ($bf,start_tag("MOD",3,true));
        //Print assignment data
        fwrite ($bf,full_tag("ID",4,false,$sharedresource->id));
        fwrite ($bf,full_tag("MODTYPE",4,false,"sharedresource"));
        fwrite ($bf,full_tag("NAME",4,false,$sharedresource->name));
        fwrite ($bf,full_tag("TYPE",4,false,$sharedresource->type));
        fwrite ($bf,full_tag("IDENTIFIER",4,false,$sharedresource->identifier));
        fwrite ($bf,full_tag("DESCRIPTION",4,false,$sharedresource->description));
        fwrite ($bf,full_tag("ALLTEXT",4,false,$sharedresource->alltext));
        fwrite ($bf,full_tag("POPUP",4,false,$sharedresource->popup));
        fwrite ($bf,full_tag("OPTIONS",4,false,$sharedresource->options));
        fwrite ($bf,full_tag("TIMEMODIFIED",4,false,$sharedresource->timemodified));
        
        if ($status && ($sharedresource->type == 'file')) {
            
            //Start entries
            fwrite ($bf,start_tag("ENTRIES",4,true));
            
            // if this is a complete backup then all the entries are dumped first time round
            $entries = array();
            if ($CFG->sharedresource_backup_index && !$sharedresource_entry_backedup) {
                $entries = get_records('sharedresource_entry', '', '', '', 'identifier');
            }
            if (!$CFG->sharedresource_backup_index) {
                $entries[] = $sharedresource;
            }
            
            $base = '/'.preg_quote($CFG->wwwroot,"/").'/';
            foreach ($entries as $entry) {
                // backup the sharedresource_entry, and sharedresource_metadata values
                $sharedresource_entry = get_record('sharedresource_entry','identifier',$entry->identifier);
                $sharedresource_metadata = get_records('sharedresource_metadata', 'entry_id', $sharedresource_entry->id);
                
                // prepare the URL, so that it can be repointed on the restore
                $url = $sharedresource_entry->url;
                if ($CFG->sharedresource_backup_index && !empty($sharedresource_entry->file)) {
                    $url = preg_replace($base,'$@SHAREDRESOURCEINDEX@$', $url);
                }
                //Start entry
                fwrite ($bf,start_tag("ENTRY",5,true));
                // write out index entry data
                fwrite ($bf,full_tag("ID",6,false,$sharedresource_entry->id));
                fwrite ($bf,full_tag("TITLE",6,false,$sharedresource_entry->title));
                fwrite ($bf,full_tag("TYPE",6,false,$sharedresource_entry->type));
                fwrite ($bf,full_tag("MIMETYPE",6,false,$sharedresource_entry->mimetype));
                fwrite ($bf,full_tag("IDENTIFIER",6,false,$sharedresource_entry->identifier));
                fwrite ($bf,full_tag("REMOTEID",6,false,$sharedresource_entry->remoteid));
                fwrite ($bf,full_tag("FILE",6,false,$sharedresource_entry->file));
                fwrite ($bf,full_tag("URL",6,false,$url));
                fwrite ($bf,full_tag("LANG",6,false,$sharedresource_entry->lang));
                fwrite ($bf,full_tag("DESCRIPTION",6,false,$sharedresource_entry->description));
                fwrite ($bf,full_tag("KEYWORDS",6,false,$sharedresource_entry->keywords));
                fwrite ($bf,full_tag("TIMEMODIFIED",6,false,$sharedresource_entry->timemodified));
                fwrite ($bf,full_tag("PROVIDER",6,false,$sharedresource_entry->provider));
                fwrite ($bf,full_tag("ISVALID",6,false,$sharedresource_entry->isvalid));

                fwrite ($bf,start_tag("METADATA",6,true));
                if (!empty($sharedresource_metadata)) {
                    foreach ($sharedresource_metadata as $element) {
                        fwrite ($bf,start_tag("ELEMENT",7,true));
                        fwrite ($bf,full_tag("ID",8,false,$element->id));
                        fwrite ($bf,full_tag("ENTRY_ID",8,false,$element->entry_id));
                        fwrite ($bf,full_tag("ELEMENT",8,false,$element->element));
                        fwrite ($bf,full_tag("NAMESPACE",8,false,$element->namespace));
                        fwrite ($bf,full_tag("VALUE",8,false,$element->value));
                        $status = fwrite ($bf,end_tag("ELEMENT",7,true));
                    }
                }
                $status = fwrite ($bf,end_tag("METADATA",6,true));
                //End entry
                $status = fwrite ($bf,end_tag("ENTRY",5,true));
                
                // backup files for this sharedresource.
                if (!$CFG->sharedresource_freeze_index){
                    $status = sharedresource_backup_files($bf, $preferences, $sharedresource_entry);
                }                
            }
            //End entries
            $status = fwrite ($bf,end_tag("ENTRIES",4,true));
            $sharedresource_entry_backedup = true;
        }
        //End mod
        $status = fwrite ($bf,end_tag("MOD",3,true));
        
        return $status;
    }

    
    function sharedresource_backup_files($bf,$preferences,$sharedresource_entry) {
        global $CFG;
        require_once("$CFG->dirroot/mod/sharedresource/lib.php");
        $status = true;

        if (empty($sharedresource_entry->file)) {
            return true;
        }
        
        $filename = $CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH.$sharedresource_entry->file;
        if (!file_exists($filename)) {
            return true ; // doesn't exist but we don't want to halt the entire process so still return true.
        }
        
        $status = $status && check_dir_exists($CFG->dataroot."/temp/backup/".$preferences->backup_unique_code.SHAREDRESOURCE_RESOURCEPATH,true);
        
        // if this is somewhere deeply nested we need to do all the structure stuff first.....
        $status = $status && backup_copy_file($filename,
                                              $CFG->dataroot."/temp/backup/".$preferences->backup_unique_code.
                                              SHAREDRESOURCE_RESOURCEPATH.$sharedresource_entry->file);
        return $status;
    }

    
   ////Return an array of info (name,value)
   function sharedresource_check_backup_mods($course,$user_data=false,$backup_unique_code,$instances=null) {
       if (!empty($instances) && is_array($instances) && count($instances)) {
           $info = array();
           foreach ($instances as $id => $instance) {
               $info += sharedresource_check_backup_mods_instances($instance,$backup_unique_code);
           }
           return $info;
       }
       //First the course data
       $info[0][0] = get_string("modulenameplural","sharedresource");
       if ($ids = sharedresource_ids ($course)) {
           $info[0][1] = count($ids);
       } else {
           $info[0][1] = 0;
       }
       
       return $info;
   }

   ////Return an array of info (name,value)
   function sharedresource_check_backup_mods_instances($instance,$backup_unique_code) {
        //First the course data
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';

        return $info;
    }

    //Return a content encoded to support interactivities linking. Every module
    //should have its own. They are called automatically from the backup procedure.
    function sharedresource_encode_content_links ($content,$preferences) {

        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        //Link to the list of sharedresources
        $buscar="/(".$base."\/mod\/sharedresource\/index.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@SHAREDRESOURCEINDEX*$2@$',$content);

        //Link to sharedresource view by moduleid
        $buscar="/(".$base."\/mod\/sharedresource\/view.php\?id\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@SHAREDRESOURCEVIEWBYID*$2@$',$result);

        //Link to sharedresource view by sharedresourceid
        $buscar="/(".$base."\/mod\/sharedresource\/view.php\?r\=)([0-9]+)/";
        $result= preg_replace($buscar,'$@SHAREDRESOURCEVIEWBYR*$2@$',$result);

        return $result;
    }

    // INTERNAL FUNCTIONS. BASED IN THE MOD STRUCTURE

    //Returns an array of sharedresources id
    function sharedresource_ids ($course) {

        global $CFG;

        return get_records_sql ("SELECT a.id, a.course
                                 FROM {$CFG->prefix}sharedresource a
                                 WHERE a.course = '$course'");
    }
   
?>
