<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod-sharedresource
 *
 */

/**
* sharedresource_entry defines a sharedresource including the metadata
*
* This class provides all the functionality for a sharedresource
* defined locally or remotely
* 
* A locally defined resource is essentially one where the user has uploaded
* a file, therefore this local moodle has to serve it.
* 
* A remote resource is one that has a fully qualified URI that does not rely
* on this local Moodle instance to serve the physical data eg. PDF, PNG etc.
* 
* mod/sharedresource uses the presence of a $sharedresource_entry->file attribute 
* to determine if this resource is hosted locally (the physical file must
* also exist in the course independent repository).
* 
* mod/sharedresource uses a course independent file repository.  By default, this
* is located in $CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH where
* SHAREDRESOURCE_RESOURCEPATH is '/sharedresources/'.
* 
*/

class sharedresource_entry {

    var $sharedresource_entry;
    var $metadata_elements;
    var $file;
    var $id;
    
    /**
     * Internal method that processes the plugins for the search
     * interface.
     * 
     * @param criteria   object, reference to Moodle Forms populated
     *        values.
     * @return results, return an array of sharedresource_entry objects that
     *         will be formated and displayed in the search results screen.
     */
    static function search (&$criteria) {
        // get the plugins
        $plugins = sharedresource_get_plugins();
        $results = array();
        
        // process each plugins search function - there is a default called local
        foreach ($plugins as $plugin) {
            // if we get a positive return then we don't use any more plugins 
            // $results is passed by reference so plugins can doctor the incremental results
            $plugin->search($criteria, $results);
        }
        return $results;
    }

    /**
     * Hydrate a sharedresource_entry object reading by identifier
     * 
     * @param identifier   hash, sha1 hash identifier
     * @return sharedresource_entry object
     */
    static function read($identifier) {
        global $CFG, $DB;
    
        if (! $sharedresource_entry = $DB->get_record('sharedresource_entry', array('identifier'=> $identifier))) {
            return false;
        }

        $sharedresource_entry = new sharedresource_entry($sharedresource_entry);
        return $sharedresource_entry;
    }

    /**
     * Hydrate a sharedresource_entry object reading by id
     * 
     * @param id   int, internal id of sharedresource_entry object
     * @return sharedresource_entry object
     */
    static function read_by_id($entry_id) {
    global  $DB;
        if (! $sharedresource_entry =  $DB->get_record('sharedresource_entry', array('id'=> $entry_id))) {
            return false;
        }

        $sharedresource_entry = new sharedresource_entry($sharedresource_entry);
        return $sharedresource_entry;
    }

    /**
     * Hydrate a sharedresource_entry object reading by id
     * 
     * @param id   int, internal id of sharedresource_entry object
     * @return sharedresource_entry object
     */
    static function get_by_id($entry_id) {
    
        return sharedresource_entry::read_by_id($entry_id);
    }

    /**
     * Hydrate a sharedresource_entry object reading by identifier
     * 
     * @param identifier   hash, sha1 hash identifier
     * @return sharedresource_entry object
     */
    static function get_by_identifier($identifier) {
    
        return $sharedresource_entry = sharedresource_entry::read($identifier);
    }

    /**
    * Constructor for the base sharedresource class
    *
    * Constructor for the base sharedresource class.
    * If cmid is set create the cm, course, sharedresource objects.
    * and do some checks to make sure people can be here, and so on.
    *
    * @param sharedresource_entry   object, sharedresource_entry object, or table row
    * 
    */
    function sharedresource_entry($sharedresource_entry = false) {
        global $DB;

        global $SHAREDRESOURCE_CORE_ELEMENTS;
        $this->metadata_elements = array();
        if (is_object($sharedresource_entry)) {
            foreach ($SHAREDRESOURCE_CORE_ELEMENTS as $key) {
                $this->add_element($key, $sharedresource_entry->$key);
            }
            if ($elements =  $DB->get_records('sharedresource_metadata',array('entry_id'=> $this->id))) {
                foreach ($elements as $element) {
                    $this->add_element($element->element, $element->value, $element->namespace);
                }
            }
        }
    }
    
    /**
     * Internal method that processes the plugins for the before save
     * interface.
     * 
     * @return bool, returns true.
     */
    function before_save () {
		global $CFG;
        // get the plugins
        $plugins = sharedresource_get_plugins($this->id);
        $currentplugin = $plugins[$CFG->{'pluginchoice'}];
		
        // process each plugins before_save function - there is a default called local
        foreach ($plugins as $plugin) {
            // if we get a positive return then we don't use any more plugins 
			if($plugin == $currentplugin){
				$rc = $plugin->before_save($this);
				if (!$rc) {
					break;
				}
			}
        }
        return true;
    }
    
    /**
     * Internal method that processes the plugins for the after save
     * interface.
     * 
     * @return bool, returns true.
     */
    function after_save () {
		global $CFG;
        // get the plugins
        $plugins = sharedresource_get_plugins($this->id);
		$currentplugin = $plugins[$CFG->{'pluginchoice'}];

        // process each plugins before_save function - there is a default called local
        foreach ($plugins as $plugin) {
            // if we get a positive return then we don't use any more plugins 
			if($plugin == $currentplugin){
				$rc = $plugin->after_save($this);
				if (!$rc) {
					break;
				}
			}
        }
        return true;
    }
    
    /**
     * Internal method that processes the plugins for the before update
     * interface.
     * 
     * @return bool, returns true.
     */
    function before_update () {
		global $CFG;
        // get the plugins
        $plugins = sharedresource_get_plugins($this->id);
        $currentplugin = $plugins[$CFG->{'pluginchoice'}];
		
        // process each plugins before_save function - there is a default called local
        foreach ($plugins as $plugin) {
            // if we get a positive return then we don't use any more plugins 
			if($plugin == $currentplugin){
				$rc = $plugin->before_update($this);
				if (!$rc) {
					break;
				}
			}
        }
        return true;
    }

   /**
     * Internal method that processes the plugins for the after update
     * interface.
     * 
     * @return bool, returns true.
     */
    function after_update () {
		global $CFG;
        // get the plugins
		
        $plugins = sharedresource_get_plugins($this->id);
		$currentplugin = $plugins[$CFG->{'pluginchoice'}];
        // process each plugins after_update function - there is a default called local
        foreach ($plugins as $plugin) {
            // if we get a positive return then we don't use any more plugins 
			if($plugin == $currentplugin){
				$rc = $plugin->after_update($this);
				if (!$rc) {
					break;
				}
			}
        }
        return true;
    }
    
    /**
     * set a core sharedresource_entry attribute, or add a metadata element (allways appended)
     * 
     * @param element   string, name of sharedresource_entry attribute or metadata element
     * @param value     string, value of sharedresource_entry attribute or metadata element
     * @param namespace string, namespace of metadata element only
     */
    function add_element($element, $value, $namespace = '') {
        global $SHAREDRESOURCE_CORE_ELEMENTS;
        // add the core ones to the main table entry - everything else goes in the metadata table
        if (in_array($element, $SHAREDRESOURCE_CORE_ELEMENTS) && empty($namespace)) {
            $this->$element = $value;
        } else {
            $this->metadata_elements[] = new sharedresource_metadata($this->id, $element, $value, $namespace);
        }
    }

    /**
     * access the value of a core sharedresource_entry attribute or metadata element
     * 
     * @param element   string, name of sharedresource_entry attribute or metadata element
     * @param namespace string, namespace of metadata element only
     * @return string, value of attribute or metadata element
     */
    function element($element, $namespace = '') {
        global $SHAREDRESOURCE_CORE_ELEMENTS;

        if (in_array($element, $SHAREDRESOURCE_CORE_ELEMENTS) && empty($namespace) && isset($this->$element)) {
            return $this->$element;
        } else {
        	// print_object($this->metadata_elements);
        	if (!empty($this->metadata_elements)){
	            foreach ($this->metadata_elements as $el) {
	                if ($el->element == $element && $el->namespace == $namespace) {
	                    return $el->value;
	                }
	            }
	        }
        }
        return false;
    }

    /**
     * amend a core sharedresource_entry attribute, or metadata element - if metadata element
     * is not found then it is appended.
     * 
     * @param element   string, name of sharedresource_entry attribute or metadata element
     * @param value     string, value of sharedresource_entry attribute or metadata element
     * @param namespace string, namespace of metadata element only
     */
    function update_element($element, $value, $namespace = '') {
        global $SHAREDRESOURCE_CORE_ELEMENTS;
        // add the core ones to the main table entry - everything else goes in the metadata table
        if (in_array($element, $SHAREDRESOURCE_CORE_ELEMENTS) && empty($namespace) && !empty($value)) {
            $this->$element = $value;
        } else {
            $location = false;
            foreach ($this->metadata_elements as $key => $el) {
                if ($el->element == $element && $el->namespace == $namespace) {
                    $location = $key;
                    break;
                }
            }
            if ($location !== false) {
                $this->metadata_elements[$location] = new sharedresource_metadata($this->id, $element, $value, $namespace);
            } else {
                $this->metadata_elements []= new sharedresource_metadata($this->id, $element, $value, $namespace);
            }
        }
    }

    /**
     * check if resource is local or not
     * 
     * @return bool, true = local
     */
    function is_local_resource() {
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
    function is_remote_resource() {
        return ! $this->is_local_resource();
    }
    
    /**
     * check if resource has been provided from internal operation or
     * has been retrieved from an externaly bound resources source.
     * 
     * @return bool, true = remote
     */
    function has_local_provider() {        
        return empty($this->provider) || $this->provider == 'local';
    }
    
    /**
     * Commit the new Shared resource to the database
     * 
     * @return bool, true = success
     */
    function add_instance() {
    // Given an object containing all the necessary data,
    // (defined by the form in mod.html) this function
    // will create a new instance and return the id number
    // of the new instance.
    
        global $CFG, $DB;

        // is this a local resource or a remote one?
        if (empty($this->identifier) && !empty($this->url) && empty($this->file)) {
            $this->identifier = sha1($this->url);
            $this->mimetype = mimeinfo("type", $this->url);
        }
        
        if ( isset($this->url) && $this->url && !$this->is_local_resource()) {
            $this->file = '';
        } else if (empty($this->url) && isset($this->file) && $this->file) {
            /*
            if (!sharedresource_check_and_create_moddata_sharedresource_dir()) {
                // error - can't create resources temp dir
                error("Error - can't create Shared resources dir");
            }
            */
            // $filename = $CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH.$this->file;
            /*  if (!sharedresource_copy_file($this->tempfilename, $filename, true)) {
                error("Error - can't copy temporary resource file ({$this->tempfilename}) to resource path ($filename)");
            }*/
            
            if (empty($CFG->sharedresource_foreignurl)){
                $this->url = $CFG->wwwroot.'/mod/sharedresource/view.php?identifier='.$this->identifier;
            } else {
                $this->url = str_replace('<%%ID%%>', $this->identifier, $CFG->sharedresource_foreignurl);
            }
            
            // tidy up temp file
            /*  if (!sharedresource_delete_file($this->tempfilename)) {
                error("Error - can't delete temporary resource file ({$this->tempfilename})");
            }*/
        }

        // one way or another we must have a URL by now
        if (!$this->url) {
            print_error('erroremptyurl', 'sharedresource');
        }
        
        // localise resource to this node for a resource network
        if (empty($this->provider)) $this->provider = 'local';
        
        // trigger the before save plugins
        $this->before_save();
    
        $this->timemodified = time();
        // we need silent return as duplicating denotes we already have this entry
        /*
        $this->description = stripslashes(@$this->description);
        $this->title = stripslashes($this->title);
        */
        //$this->keywords = stripslashes(@$this->keywords);

		// print_object($this->metadata_elements);

		// add a proxy for keyword values
		require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->{'pluginchoice'}.'/plugin.class.php');
		$object = 'sharedresource_plugin_'.$CFG->{'pluginchoice'};
		$mtdstandard = new $object;

		// remap metadata elements array to cope with setKeywordValues expected format
		$metadataelements = array();
		foreach($this->metadata_elements as $elm){
			$metadataelements[$elm->element] = $elm;
		}
		$this->keywords = $mtdstandard->getKeywordValues($metadataelements);
        
        // Fix description from tinymce
        
		$this->description = @$this->description['text'];
		if ($oldres = $DB->get_record('sharedresource_entry', array('identifier' => $this->identifier))){
			$this->id = $oldres->id;
			$DB->update_record('sharedresource_entry', $this);
		} else {
			if (!$this->id =  $DB->insert_record('sharedresource_entry', $this)) {
			return false;
			}
		}
        
		// now do the LOM metadata elements
		foreach ($this->metadata_elements as $element) {
			$element->entry_id = $this->id;
			if (! $element->add_instance()) {
				return false;
			}
		}
		
		// trigger the after save plugins
		$this->after_save();
		
		return true;
    }

	/**
	 * Commit the updated Shared resource to the database
	 * 
	 * @return bool, true = success
	 */
	function update_instance() {
	   
	// Given an object containing all the necessary data,
	// (defined by the form in mod.html) this function
	// will update an existing instance with new data.

		global $CFG, $DB;

		$this->timemodified = time();
		
		// trigger the before save plugins
		//$this->before_update();
		
		// remove and recreate metadata records
		if (!  $DB->delete_records('sharedresource_metadata',array('entry_id'=> $this->id))) {
			return false;
		}
		foreach ($this->metadata_elements as $element) {
			$element->add_instance();
		}
		
		$this->description = @$this->description['text'];
		$this->title = $this->title;

		// add a proxy for keyword values
		require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->{'pluginchoice'}.'/plugin.class.php');
		$object = 'sharedresource_plugin_'.$CFG->pluginchoice;
		$mtdstandard = new $object;
		
		// remap metadata elements array to cope with setKeywordValues expected format
		$metadataelements = array();
		foreach($this->metadata_elements as $elm){
			$metadataelements[$elm->element] = $elm;
		}
		$this->keywords = $mtdstandard->getKeywordValues($metadataelements);

		if (! $DB->update_record('sharedresource_entry', ($this))) {
			return false;
		}
		
		// trigger the after save plugins
		//$this->after_update();
		
		return true;
	}

	/**
	 * delete the current Shared resource from the database, and
	 * any locally attached files.
	 * 
	 * @return bool, true = success
	 */
	function delete_instance() {
	    global $DB;
	// Given an object containing the sharedresource data
	// this function will permanently delete the instance
	// and any data that depends on it, including local file.

		if (! $DB->delete_records('sharedresource_metadata', array('entry_id'=> $this->id))) {
			return false;
		}
        
        if ($this->is_local_resource()) {
            $filename = $CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH.$this->file;
            if (!sharedresource_delete_file($filename)) {
                print_error('errordeletesharedresource', 'sharedresource', $filename);
            }
        }
        
        if (! $DB->delete_records('sharedresource_entry', array('id'=> $this->id))) {
            return false;
        }
        return true;
    }
}
?>