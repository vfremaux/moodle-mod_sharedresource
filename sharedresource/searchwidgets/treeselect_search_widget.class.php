
<?php
/**
 *
 * @author  Valery Fremaux
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package sharedresource
 *
 */
require_once $CFG->dirroot.'/mod/sharedresource/metadatalib.php';
require_once $CFG->dirroot.'/mod/sharedresource/search_widget.class.php';

/**
* search_widget defines a widget element for the search engine of metadata.
*/


class treeselect_search_widget extends search_widget{

    /**
    * Constructor for the search_widget class
    */
    function treeselect_search_widget($pluginchoice, $id, $label, $type) {
    	parent::search_widget($pluginchoice, $id, $label, $type);
    }

	/**
    * Fonction used to display the widget. The parameter $display determines if plugins are displayed on a row or on a column
    */
    function print_search_widget($layout, $value = 0) {
    	global $CFG;

		$lowername = strtolower($this->label);
		$widgetname = get_string(str_replace(' ', '', $lowername), 'sharedresource');

		?>
		<script>
		/* XmlHttpRequest */
		function classif(name,num,key,classif,value){
			var ajaxRequest; 
		
			try{
				// Opera 8.0+, Firefox, Safari
				ajaxRequest = new XMLHttpRequest();
			} catch (e){
				// Internet Explorer Browsers
				try{
					ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try{
						ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (e){
						// Something went wrong
						alert("Your browser broke!");
						return false;
					}
				}
			}

			// Create a function that will receive data sent from the server
			ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4){
					var ajaxDisplay = document.getElementById('classif'+num);
					ajaxDisplay.innerHTML = ajaxRequest.responseText;
					maDiv = document.createElement('div');
					num2 = num + 1;
					maDiv.id = 'classif'+num2;
					document.getElementById('classif'+num).appendChild(maDiv);
				}
			}
			ajaxRequest.open('POST', "<?php echo $CFG->wwwroot ?>/mod/sharedresource/classifajax.php", true);
			var data = "name=" + name + "&num=" + num + "&key=" + key + "&classif=" + classif + "&value=" + value;
			// alert('sending data : '+data);
			ajaxRequest.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			ajaxRequest.send(data); 
		}
		</script>
		<?php
		require_once $CFG->dirroot.'/mod/sharedresource/plugins/'.$this->pluginchoice.'/plugin.class.php';
		$classifarray = unserialize(@$CFG->classifarray);
		require_once $CFG->dirroot.'/mod/sharedresource/classificationlib.php';
		echo '<table class="widget"><tr><td class="widget-label">'.get_string('taxonpath', 'sharedresource').'</td><td class="widget-input">';
		helpbutton('classificationsearch', get_string('classificationsearch', 'sharedresource'), 'sharedresource');
		echo '</td><td>';
		echo '<div id="classif0">';
		echo '<select name="classif:0" onChange="javascript:classif(this.options[selectedIndex].value,1,\'\',this.options[selectedIndex].value,this.options[this.selectedIndex].value);">';
		echo '<option selected value="defaultvalue"> </option>';
		echo print_classif2($classifarray, $value);
		echo '</select></div>';
		echo '<div id="classif1"></div>';
		echo '</td></tr></table>';
    }
	
	// catchs a value in session from CGI input
	function catch_value(&$searchfields){
		global $SESSION;
		
		$paramkey = str_replace(' ', '_', $this->label);
		$searchfields[$this->id] = @$SESSION->searchbag->$paramkey;

		$maxclassif = 0;
		foreach($_GET as $search => $value){
			if(preg_match('#^classif:#', $search) && $_GET[$search] != 'defaultvalue'){
				if(substr($search, strpos($search,':') + 1) > $maxclassif){
					$maxclassif = substr($search,strpos($search,':') + 1);
				}
			}
		}
		if($maxclassif > 0){
			$searchfields[$widget->id] = substr($_GET['classif:'.$maxclassif], strpos($_GET['classif:'.$maxclassif], '\\') + 2);
			@$SESSION->searchbag->$paramkey = substr($_GET['classif:'.$maxclassif], strpos($_GET['classif:'.$maxclassif], '\\') + 2);
		}
	}
}
?>