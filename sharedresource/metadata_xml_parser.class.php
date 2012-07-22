<?php

abstract class metadata_xml_parser{

	function __construct(){
	}
	
	abstract function add_identifier(&$metadata, $catalog, $identifier, $entry_id);

    function get_metadata_value($path){
    	foreach($this->metadata as $id => $elem){
    		if ($this->metadata[$id]->element == $path){
    			return $this->metadata[$id]->value;
    		}
    	}
    }    

}

?>