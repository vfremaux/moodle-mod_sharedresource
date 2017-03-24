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
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package mod-sharedresource
 *
 */

/**
<<<<<<< HEAD
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
* mod/sharedresource uses a course independent file repository. Files are stored into
* a system context, course independant, special filearea.
* 
*/

class sharedresource_entry {
	
	// a sharedresource entry is a combination of:
	
	// a DB record	
    var $sharedresource_entry;

	// A set of metadata
    var $metadata_elements;

	// An eventual physical file stored somewhere in the Moodle filesystem
    var $storedfile;
=======
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
 * mod/sharedresource uses a course independent file repository. Files are stored into
 * a system context, course independant, special filearea.
 *
 * TODO : see to protect some fields
 */
class sharedresource_entry {

    // a sharedresource entry is a combination of:

    // a DB record
    public $sharedresource_entry;

    // A set of metadata
    public $metadata_elements;

    // An eventual physical file stored somewhere in the Moodle filesystem
    public $storedfile;
>>>>>>> MOODLE_32_STABLE
    
    /**
     * Internal method that processes the plugins for the search
     * interface.
     * 
     * @param criteria   object, reference to Moodle Forms populated
     *        values.
     * @return results, return an array of sharedresource_entry objects that
     *         will be formated and displayed in the search results screen.
     */
    static public function search (&$criteria) {
        // Get the plugins.
        $plugins = sharedresource_get_plugins();
        $results = array();
        
        // Process each plugins search function - there is a default called local.
        foreach ($plugins as $plugin) {
            /* If we get a positive return then we don't use any more plugins 
             * $results is passed by reference so plugins can doctor the incremental results
             */
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
    static public function read($identifier) {
        global $CFG, $DB;
    
        if (! $sharedresource_entry = $DB->get_record('sharedresource_entry', array('identifier' => $identifier))) {
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
<<<<<<< HEAD
    static function read_by_id($entry_id) {
    	global  $DB;
=======
    static public function read_by_id($entry_id) {
        global  $DB;
>>>>>>> MOODLE_32_STABLE
        if (! $sharedresource_entry =  $DB->get_record('sharedresource_entry', array('id'=> $entry_id))) {
            return false;
        }

        $sharedresource_entry = new sharedresource_entry($sharedresource_entry);
        return $sharedresource_entry;
    }

    /**
     * Same as read_by_id(). Hydrate a sharedresource_entry object reading by id
     * 
     * @param id   int, internal id of sharedresource_entry object
     * @return sharedresource_entry object
     */
    static public function get_by_id($entry_id) {
        return sharedresource_entry::read_by_id($entry_id);
    }

    /**
     * Same as read(). Hydrate a sharedresource_entry object reading by identifier
     * 
     * @param identifier   hash, sha1 hash identifier
     * @return sharedresource_entry object
     */
    static public function get_by_identifier($identifier) {
        return $sharedresource_entry = sharedresource_entry::read($identifier);
    }

    /**
<<<<<<< HEAD
    * Constructor for the base sharedresource class.
    * Loads metadata from DB if any.
    *
    * @param object $sharedresource_entry sharedresource_entry table row
    * 
    */
    function sharedresource_entry($sharedresource_entry = false) {
        global $DB;

        if (is_object($sharedresource_entry)) {
        	$this->sharedresource_entry = $sharedresource_entry;

			if ($this->file){
				$fs = get_file_storage();
	        	$this->storedfile = $fs->get_file_by_id($sharedresource_entry->file);
	        }
        } else {
        	$this->sharedresource_entry = new StdClass();
        	$this->id = 0;
        	$this->provider = 'local';
        	$this->isvalid = 1;
        	$this->displayed = 1;
        	$this->context = 1;
        	$this->scoreview = 0;
        	$this->scorelike = 0;
        }

        $this->metadata_elements = array();

		/*
        global $SHAREDRESOURCE_CORE_ELEMENTS;
=======
     * Constructor for the base sharedresource class.
     * Loads metadata from DB if any.
     *
     * @param object $sharedresource_entry sharedresource_entry table row
     *
     */
    public function sharedresource_entry($sharedresource_entry = false) {
        global $DB;

>>>>>>> MOODLE_32_STABLE
        if (is_object($sharedresource_entry)) {
            $this->sharedresource_entry = $sharedresource_entry;

            if ($this->file) {
                $fs = get_file_storage();
                $this->storedfile = $fs->get_file_by_id($sharedresource_entry->file);
            }
<<<<<<< HEAD
=======
        } else {
            $this->sharedresource_entry = new StdClass();
            $this->id = 0;
            $this->provider = 'local';
            $this->isvalid = 1;
            $this->displayed = 1;
            $this->context = 1;
            $this->scoreview = 0;
            $this->scorelike = 0;
        }

        $this->metadata_elements = array();

        if ($this->id) {
>>>>>>> MOODLE_32_STABLE
            if ($elements =  $DB->get_records('sharedresource_metadata', array('entry_id' => $this->id))) {
                foreach ($elements as $element) {
                    $this->add_element($element->element, $element->value, $element->namespace);
                }
            }
        }
<<<<<<< HEAD
        */

		if ($this->id){
	        if ($elements =  $DB->get_records('sharedresource_metadata', array('entry_id' => $this->id))) {
	            foreach ($elements as $element) {
	                $this->add_element($element->element, $element->value, $element->namespace);
	            }
	        }
	    }
    }
    
    /**
    * magic getter
    *
    */
    function __get($attr){
    	
    	if ($attr == 'id'){
    		return 0 + @$this->sharedresource_entry->$attr;
    	}
    	
    	if (in_array($attr, array('id', 'title', 'type', 'mimetype', 'identifier', 'remoteid', 'file', 'url', 'lang', 'description', 'keywords', 'timemodified', 'provider', 'isvalid', 'displayed', 'context', 'scoreview', 'scorelike'))){
    		return $this->sharedresource_entry->$attr;
    	} else {
    		print_error('memberwrongaccess', 'local_sharedresources', $attr);
    	}
    }

    /**
    * magic setter
    *
    */
    function __set($attr, $value){
    	if (in_array($attr, array('id', 'title', 'type', 'mimetype', 'identifier', 'remoteid', 'file', 'url', 'lang', 'description', 'keywords', 'timemodified', 'provider', 'isvalid', 'displayed', 'context', 'scoreview', 'scorelike'))){
    		if ($attr == 'description'){
    			if (is_array($value)){
		    		if (preg_match('/^<p>?(.*)<\/p>$/', $value['text'])){
			    		$this->sharedresource_entry->$attr = preg_replace('/^<p>?(.*)<\/p>$/', "$1", format_string($value['text'], $value['format']));
			    	} else {
			    		$valuestr = format_string($value['text'], $value['format']);
			    		$this->sharedresource_entry->$attr = $valuestr;
			    	}
		    	} else {
		    		$this->sharedresource_entry->$attr = $value;
		    	}
    		} else {
	    		$this->sharedresource_entry->$attr = $value;
	    	}
    	}
    }
    
=======
    }

    /**
     * magic getter
     *
     */
    public function __get($attr) {

        if ($attr == 'id') {
            return 0 + @$this->sharedresource_entry->$attr;
        }

        if (in_array($attr, array('id', 'title', 'type', 'mimetype', 'identifier', 'remoteid', 'file', 'url', 'lang', 'description', 'keywords', 'timemodified', 'provider', 'isvalid', 'displayed', 'context', 'scoreview', 'scorelike'))) {
            return $this->sharedresource_entry->$attr;
        } else {
            print_error('memberwrongaccess', 'local_sharedresources', $attr);
        }
    }

    /**
     * magic setter
     *
     */
    public function __set($attr, $value) {
        if (in_array($attr, array('id', 'title', 'type', 'mimetype', 'identifier', 'remoteid', 'file', 'url', 'lang', 'description', 'keywords', 'timemodified', 'provider', 'isvalid', 'displayed', 'context', 'scoreview', 'scorelike'))) {
            if ($attr == 'description') {
                if (is_array($value)) {
                    if (preg_match('/^<p>?(.*)<\/p>$/', $value['text'])) {
                        $this->sharedresource_entry->$attr = preg_replace('/^<p>?(.*)<\/p>$/', "$1", format_string($value['text'], $value['format']));
                    } else {
                        $valuestr = format_string($value['text'], $value['format']);
                        $this->sharedresource_entry->$attr = $valuestr;
                    }
                } else {
                    $this->sharedresource_entry->$attr = $value;
                }
            } else {
                $this->sharedresource_entry->$attr = $value;
            }
        }
    }

>>>>>>> MOODLE_32_STABLE
    /**
     * Internal method that processes the plugins for the before save
     * interface.
     * 
     * @return bool, returns true.
     */
<<<<<<< HEAD
    function before_save() {
		global $CFG;
        // get the plugins
=======
    public function before_save() {
        global $CFG;

        // Get the plugins.
>>>>>>> MOODLE_32_STABLE
        $plugins = sharedresource_get_plugins($this->id);
        $currentplugin = $plugins[$CFG->{'pluginchoice'}];

        // Process each plugins before_save function - there is a default called local.
        foreach ($plugins as $plugin) {
            // If we get a positive return then we don't use any more plugins.
            if ($plugin == $currentplugin) {
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
    function after_save() {
<<<<<<< HEAD
		global $CFG;
        // get the plugins
=======
        global $CFG;

        // Get the plugins.
>>>>>>> MOODLE_32_STABLE
        $plugins = sharedresource_get_plugins($this->id);
        $currentplugin = $plugins[$CFG->{'pluginchoice'}];

        // Process each plugins before_save function - there is a default called local.
        foreach ($plugins as $plugin) {
            // If we get a positive return then we don't use any more plugins.
            if ($plugin == $currentplugin) {
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
    function before_update() {
<<<<<<< HEAD
		global $CFG;
        // get the plugins
=======
        global $CFG;

        // Get the plugins.
>>>>>>> MOODLE_32_STABLE
        $plugins = sharedresource_get_plugins($this->id);
        $currentplugin = $plugins[$CFG->{'pluginchoice'}];
        
        // Process each plugins before_save function - there is a default called local.
        foreach ($plugins as $plugin) {
            // If we get a positive return then we don't use any more plugins.
            if ($plugin == $currentplugin) {
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
<<<<<<< HEAD
    function after_update() {
		global $CFG;
        // get the plugins
		
=======
    public function after_update() {
        global $CFG;

        // Get the plugins.
>>>>>>> MOODLE_32_STABLE
        $plugins = sharedresource_get_plugins($this->id);
        $currentplugin = $plugins[$CFG->{'pluginchoice'}];

        // Process each plugins after_update function - there is a default called local.
        foreach ($plugins as $plugin) {
            // if we get a positive return then we don't use any more plugins 
            if ($plugin == $currentplugin) {
                $rc = $plugin->after_update($this);
                if (!$rc) {
                    break;
                }
            }
        }
        return true;
    }
    
    /**
     * set a core sharedresource_entry attribute, or add a metadata element (always appended)
     * 
     * @param element   string, name of sharedresource_entry attribute or metadata element
     * @param value     string, value of sharedresource_entry attribute or metadata element
     * @param namespace string, namespace of metadata element only
     */
    function add_element($element, $value, $namespace = '') {
        global $SHAREDRESOURCE_CORE_ELEMENTS;

<<<<<<< HEAD
        // add the core ones to the main table entry - everything else goes in the metadata table
=======
        $this->update_element($element, $value, $namespace);
        /*
        Dangerous and obsolete
        // add the core ones to the main table entry - everything else goes in the metadata table.
>>>>>>> MOODLE_32_STABLE
        if (in_array($element, $SHAREDRESOURCE_CORE_ELEMENTS) && empty($namespace)) {
            $this->$element = $value;
        } else {
            $this->metadata_elements[] = new sharedresource_metadata($this->id, $element, $value, $namespace);
        }
<<<<<<< HEAD
=======
        */
>>>>>>> MOODLE_32_STABLE
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
<<<<<<< HEAD
        	// print_object($this->metadata_elements);
        	if (!empty($this->metadata_elements)){
	            foreach ($this->metadata_elements as $el) {
	                if ($el->element == $element && $el->namespace == $namespace) {
	                    return $el->value;
	                }
	            }
	        }
=======
            if (!empty($this->metadata_elements)) {
                foreach ($this->metadata_elements as $el) {
                    if ($el->element == $element) {
                        return $el->value;
                    }
                }
            }
>>>>>>> MOODLE_32_STABLE
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

        // add the core ones to the main table entry - everything else goes in the metadata table.
        if (in_array($element, $SHAREDRESOURCE_CORE_ELEMENTS) && empty($namespace) && !empty($value)) {
            $this->$element = $value;
        } else {
            $location = false;
            $i = 0;
            foreach ($this->metadata_elements as $el) {
                if ($el->element == $element) {
                    $location = $i;
                    break;
                }
                $i++;
            }
            if ($location === false) {
                $this->metadata_elements[] = new sharedresource_metadata($this->id, $element, $value, $namespace);
            } else {
                $this->metadata_elements[$location] = new sharedresource_metadata($this->id, $element, $value, $namespace);
<<<<<<< HEAD
            } else {
                $this->metadata_elements []= new sharedresource_metadata($this->id, $element, $value, $namespace);
=======
>>>>>>> MOODLE_32_STABLE
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
<<<<<<< HEAD
    function has_local_provider() {        
=======
    function has_local_provider() {
>>>>>>> MOODLE_32_STABLE
        return empty($this->provider) || $this->provider == 'local';
    }

    /**
     * Commit the new Shared resource to the database
     * 
     * @return bool, true = success
     */
    function add_instance() {
        global $CFG, $DB;
<<<<<<< HEAD
    // Given an object containing all the necessary data,
    // (defined by the form in mod.html) this function
    // will create a new instance and return the id number
    // of the new instance.
    
=======

        /* Given an object containing all the necessary data,
         * (defined by the form in mod.html) this function
         * will create a new instance and return the id number
         * of the new instance.
         */

>>>>>>> MOODLE_32_STABLE
        // is this a local resource or a remote one?
        if (empty($this->identifier) && !empty($this->url) && empty($this->file)) {
            $this->identifier = sha1($this->url);
            $this->mimetype = mimeinfo("type", $this->url);
        }
        
        $url = $this->url;
        $file = $this->file;
<<<<<<< HEAD
        
        if (!empty($url) && !$this->is_local_resource()) {
            $this->file = '';
        } elseif ((empty($url)) && (!empty($file))) {
            if (empty($CFG->sharedresource_foreignurl)){
=======

        if (!empty($url) && !$this->is_local_resource()) {
            $this->file = '';
        } elseif ((empty($url)) && (!empty($file))) {
            if (empty($CFG->sharedresource_foreignurl)) {
>>>>>>> MOODLE_32_STABLE
                $this->url = $CFG->wwwroot.'/local/sharedresources/view.php?identifier='.$this->identifier;
            } else {
                $this->url = str_replace('<%%ID%%>', $this->identifier, $CFG->sharedresource_foreignurl);
            }
        } else {
<<<<<<< HEAD
        	print_error("bad case ");
=======
            print_error("bad case ");
>>>>>>> MOODLE_32_STABLE
        }

        // One way or another we must have a URL by now.
        if (!$this->url) {
            print_error('erroremptyurl', 'sharedresource');
        }
        
        // Localise resource to this node for a resource network.
        if (empty($this->provider)) $this->provider = 'local';

        // Trigger the before save plugins.
        $this->before_save();
<<<<<<< HEAD
    
=======

>>>>>>> MOODLE_32_STABLE
        $this->timemodified = time();

        // Add a proxy for keyword values.
        require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');
        $object = 'sharedresource_plugin_'.$CFG->{'pluginchoice'};
        $mtdstandard = new $object;

<<<<<<< HEAD
		// remap metadata elements array to cope with setKeywordValues expected format
		$metadataelements = array();
		foreach($this->metadata_elements as $elm){
			$metadataelements[$elm->element] = $elm;
		}
		$this->keywords = $mtdstandard->getKeywordValues($metadataelements);
        
        // Fix description from tinymce
        if (is_array(@$this->description)){
			$this->description = @$this->description['text'];
		}

		if ($oldres = $DB->get_record('sharedresource_entry', array('identifier' => $this->identifier))){
			$this->id = $oldres->id;
			$DB->update_record('sharedresource_entry', $this->sharedresource_entry);
		} else {
			if (!$this->id = $DB->insert_record('sharedresource_entry', $this->sharedresource_entry)) {
				return false;
			}
		}
				
		// now we know the itemid for this resource, if it has a real file 
		// $this->file still holds the draft area file record at this time
		if (!empty($file)){

			$fs = get_file_storage();

			$filerec = new StdClass();
			$systemcontext = context_system::instance();
			$filerec->contextid = $systemcontext->id;
			$filerec->component = 'mod_sharedresource';
			$filerec->filearea = 'sharedresource';
			$filerec->itemid = $this->id;
			$filerec->path = '/';

			$definitive = $fs->create_file_from_storedfile($filerec, $file);
			
			// now we post udate the sharedresource_entry record to reflect changes
			$this->sharedresource_entry->file = $definitive->get_id();
			
			$DB->set_field('sharedresource_entry', 'file', $definitive->get_id(), array('id' => $this->id));
		}		

		// clean up any prexisting elements (in case of bounces)
		$DB->delete_records('sharedresource_metadata', array('entry_id' => $this->id));
        
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
		$this->before_update();
		
		// remove and recreate metadata records
		$DB->delete_records('sharedresource_metadata', array('entry_id' => $this->id));
		
		foreach ($this->metadata_elements as $element) {
			$element->entry_id = $this->id;
			$element->add_instance();
		}
		
		if (is_array(@$this->description)){
			$this->description = @$this->description['text'];
		}
		$this->title = $this->title;

		// add a proxy for keyword values
		require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');
		$object = 'sharedresource_plugin_'.$CFG->pluginchoice;
		$mtdstandard = new $object;
		
		// remap metadata elements array to cope with setKeywordValues expected format
		$metadataelements = array();
		foreach($this->metadata_elements as $elm){
			$metadataelements[$elm->element] = $elm;
		}
		$this->keywords = $mtdstandard->getKeywordValues($metadataelements);

		if (! $DB->update_record('sharedresource_entry', $this->sharedresource_entry)) {
			return false;
		}
		
		// trigger the after save plugins
		$this->after_update();
		
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
=======
        // Remap metadata elements array to cope with setKeywordValues expected format.
        $metadataelements = array();
        foreach ($this->metadata_elements as $elm) {
            $metadataelements[$elm->element] = $elm;
        }
        $this->keywords = $mtdstandard->getKeywordValues($metadataelements);
        
        // Fix description from tinymce
        if (is_array(@$this->description)) {
            $this->description = @$this->description['text'];
        }

        if ($oldres = $DB->get_record('sharedresource_entry', array('identifier' => $this->identifier))) {
            $this->id = $oldres->id;
            $DB->update_record('sharedresource_entry', $this->sharedresource_entry);
        } else {
            if (!$this->id = $DB->insert_record('sharedresource_entry', $this->sharedresource_entry)) {
                return false;
            }
        }

        // now we know the itemid for this resource, if it has a real file 
        // $this->file still holds the draft area file record at this time
        $fs = get_file_storage();
        if (!empty($file)) {

            $filerec = new StdClass();
            $systemcontext = context_system::instance();
            $filerec->contextid = $systemcontext->id;
            $filerec->component = 'mod_sharedresource';
            $filerec->filearea = 'sharedresource';
            $filerec->itemid = $this->id;
            $filerec->path = '/';

            $definitive = $fs->create_file_from_storedfile($filerec, $file);

            // Now we post udate the sharedresource_entry record to reflect changes.
            $this->sharedresource_entry->file = $definitive->get_id();

            $DB->set_field('sharedresource_entry', 'file', $definitive->get_id(), array('id' => $this->id));
        }

        // Process eventual thumbnail
        if (!empty($this->thumbnail)) {

            $filerec = new StdClass();
            $systemcontext = context_system::instance();
            $filerec->contextid = $systemcontext->id;
            $filerec->component = 'mod_sharedresources';
            $filerec->filearea = 'thumbnail';
            $filerec->itemid = $this->id;
            $filerec->path = '/';

            $fs->create_file_from_storedfile($filerec, $this->thumbnail);
            // Thumbnail ID is NOT stored into sharedresource record.
        }

        // Clean up any prexisting elements (in case of bounces).
        $DB->delete_records('sharedresource_metadata', array('entry_id' => $this->id));

        // Now do the LOM metadata elements.
        foreach ($this->metadata_elements as $element) {
            $element->entry_id = $this->id;
            if (! $element->add_instance()) {
                return false;
            }
        }

        // Trigger the after save plugins.
        $this->after_save();

        return true;
    }

    /**
     * Commit the updated Shared resource to the database
     *
     * @return bool, true = success
     */
    function update_instance() {

    /* Given an object containing all the necessary data,
     * (defined by the form in mod.html) this function
     * will update an existing instance with new data.
     */

        global $CFG, $DB;

        $this->timemodified = time();

        // Trigger the before save plugins.
        $this->before_update();

        // Remove and recreate metadata records.
        $DB->delete_records('sharedresource_metadata', array('entry_id' => $this->id));

        foreach ($this->metadata_elements as $element) {
            $element->entry_id = $this->id;

            // Todo recheck this. this is a pass through quick fix
            if (empty($element->namespace)) {
                $element->namespace = 'lom';
            }

            $element->add_instance();
        }

        if (is_array(@$this->description)) {
            $this->description = @$this->description['text'];
        }
        $this->title = $this->title;

        // Add a proxy for keyword values.
        require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');
        $object = 'sharedresource_plugin_'.$CFG->pluginchoice;
        $mtdstandard = new $object;

        // Remap metadata elements array to cope with setKeywordValues expected format.
        $metadataelements = array();
        foreach($this->metadata_elements as $elm) {
            $metadataelements[$elm->element] = $elm;
        }
        $this->keywords = $mtdstandard->getKeywordValues($metadataelements);

        if (! $DB->update_record('sharedresource_entry', $this->sharedresource_entry)) {
            return false;
        }

        $fs = get_file_storage();
        $systemcontext = context_system::instance();

        // Process eventual thumbnail
        $fs->delete_area_files($systemcontext->id, 'mod_sharedresource', 'thumbnail', $this->sharedresource_entry->id);
        if (!empty($this->thumbnail)) {

            $filerec = new StdClass();
            $filerec->contextid = $systemcontext->id;
            $filerec->component = 'mod_sharedresources';
            $filerec->filearea = 'thumbnail';
            $filerec->itemid = $this->id;
            $filerec->path = '/';

            $definitive = $fs->create_file_from_storedfile($filerec, $this->thumbnail);
        }

        // Trigger the after save plugins.
        $this->after_update();

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

        /* Given an object containing the sharedresource data
         * this function will permanently delete the instance
         * and any data that depends on it, including local file.
         */
>>>>>>> MOODLE_32_STABLE

		if (! $DB->delete_records('sharedresource_metadata', array('entry_id'=> $this->id))) {
			return false;
		}
        
        if ($this->is_local_resource()) {
            $filename = $CFG->dataroot.SHAREDRESOURCE_RESOURCEPATH.$this->file;
            if (!sharedresource_delete_file($filename)) {
                print_error('errordeletesharedresource', 'sharedresource', $filename);
            }
        }
        
        if (! $DB->delete_records('sharedresource_entry', array('id' => $this->id))) {
            return false;
        }
        return true;
    }
    
<<<<<<< HEAD
    function exists(){
    	global $DB;
    	
    	return $DB->record_exists('sharedresource_entry', array('identifier' => $this->identifier));
=======
    function exists() {
        global $DB;

        return $DB->record_exists('sharedresource_entry', array('identifier' => $this->identifier));
>>>>>>> MOODLE_32_STABLE
    }
}
