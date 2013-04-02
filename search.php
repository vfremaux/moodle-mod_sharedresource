<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */
    require_once('../../config.php');
    require_once('search_form.php');
    require_once('lib.php');
    $course         = required_param('course', PARAM_INT);
    $add            = optional_param('add', 0, PARAM_ALPHA);
    $return         = optional_param('return', 0, PARAM_BOOL); //return to course/view.php if false or mod/modname/view.php if true
    $type           = optional_param('type', 'file', PARAM_ALPHANUM);
    $section        = optional_param('section', 0, PARAM_ALPHANUM);
    $id             = optional_param('id', false, PARAM_INT);
    $page           = optional_param('page', false, PARAM_INT);

/// query string parameters to ignore
    $exclude_inputs = array('course', 'section', 'add', 'update', 'return', 'type', 'id', 'page', 'MAX_FILE_SIZE', 'submitbutton');

    if (!$course = $DB->get_record('course', array('id' => $course))) {
        print_error('coursemisconf');
    }

// Security 

    $system_context = context_system::instance();
    $context = context_course::instance($course->id);
    require_login($course);
    require_capability('moodle/course:manageactivities', $context);

    $strtitle = get_string('metadata_configure', 'sharedresource');
    $PAGE->set_pagelayout('standard');
    $url = new moodle_url('/mod/sharedresource/metadataconfigure.php');
    $PAGE->set_url($url);
    $PAGE->set_context($system_context);
    $PAGE->set_title($strtitle);
    $PAGE->set_heading($SITE->fullname);
    /* SCANMSG: may be additional work required for $navigation variable */
    $PAGE->navbar->add($strtitle,'metadataconfigure.php','misc');
    $PAGE->navbar->add(get_string('modulenameplural', 'sharedresource'), "{$CFG->wwwroot}/mod/sharedresource/index.php?id={$course->id}&section={$section}", 'activity');
    $PAGE->navbar->add(get_string('searchsharedresource', 'sharedresource'));
    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(false);
    $PAGE->set_button('');
    $PAGE->set_headingmenu('');

/// process search form
	$mform = new mod_sharedresource_search_form();
	if ( $mform->is_cancelled() ){
	    //cancel - go back to course    
	    redirect($CFG->wwwroot."/course/view.php?id={$course->id}");
	}

    echo $OUTPUT->header();

/// get language strings
    $strrepository  = get_string('repository', 'sharedresource');
    $strpreview     = get_string('preview', 'sharedresource');
    $strchoose      = get_string('choose', 'sharedresource');
    $stredit        = get_string('edit', 'sharedresource');
/// add in hidden navigational elements
/// handle a search query
    // this can be either:
    //    a browse request from the block
    //    a paging request from the paginator
    //    a search POST event
    if (($fromform = $mform->get_data()) || $id || $section == 'block' || $page !== false){
    	if (!isset($fromform)) $fromform = new StdClass;
        if ($page !== false) {
            // deserialise the search query
            $search_parameters = base64_decode(optional_param('search', '', PARAM_ALPHANUM));
            if ($search_parameters) {
                // grab each parameter + value and populate the form data
                $search_parameters = explode('&', $search_parameters);
                foreach ($search_parameters as $search_parameter) {
                    $parts = explode('=', $search_parameter, 2);
                    if (!empty($parts[0]) && !empty($parts[1])) {
                        $fromform->$parts[0] = $parts[1];
                    }
                }
            }
        }
        $mform->set_data($fromform);
        $mform->display();
        $fromform->search = isset($fromform->search) ? clean_param($fromform->search, PARAM_CLEANHTML) : clean_param(optional_param('search', '', PARAM_RAW), PARAM_CLEANHTML);
        $fromform->section = $section;
        $resources = array();
        // if we have an id then we must have come here from the add page
        if (!empty($fromform->id)) {
            $resources[] = sharedresource_entry::get_by_id($fromform->id);
        } else if ($id) {
            $resources[] = sharedresource_entry::get_by_id($id);
        } else {
            $resources = sharedresource_entry::search($fromform);
        }
    	// output results in same format as ims finder.php
    	if ($resources) {
            $totalcount = count($resources);
            $baseurl = $CFG->wwwroot."/mod/sharedresource/search.php?course={$course->id}&section={$section}&type={$type}&add=sharedresource&return={$return}&";
            // serialise the search query and append to the paging URI
            $search_parameters = '';
            foreach ($fromform as $fld => $val) {
                if (!in_array($fld, $exclude_inputs)) {
                    if ($val === true) {
                        $val = 'true';
                    } else if ($val === false) {
                        $val = 'false';
                    }
                    $search_parameters .= "{$fld}={$val}&";
                }
            }
            if ($search_parameters) {
                $baseurl .= 'search='.base64_encode($search_parameters).'&';
            }
            // Grab this pages worth
            if ($totalcount > SHAREDRESOURCE_RESULTS_PER_PAGE) {
                $startpos = $page * SHAREDRESOURCE_RESULTS_PER_PAGE;
                $resources_subset = array_slice($resources, $startpos, SHAREDRESOURCE_RESULTS_PER_PAGE, true);
            } else {
                $resources_subset = $resources;
            }

		// start output
  	    	echo '<div id="sharedresource-results" class="generalbox mform">';
			$pagingbar = new paging_bar($totalcount, $page, SHAREDRESOURCE_RESULTS_PER_PAGE, $baseurl, $pagevar='page');
    	    print($OUTPUT->render($pagingbar));
        	echo '<ul>'; 
    		foreach($resources_subset as $resource) {
    			echo "<li> " .$resource->title;
                echo "&nbsp;";
    			if ($section != 'block') {
        			echo '<a href=\''.$CFG->wwwroot.
                         "/course/modedit.php?course={$course->id}&section={$section}&type={$type}&add=sharedresource&return={$return}&entry_id={$resource->id}".'\'>('.get_string('choose').')</a>';
    			}
                echo "&nbsp;";
                if (empty($CFG->sharedresource_foreignurl)){
                    echo  "<a href=\"$CFG->wwwroot/mod/sharedresource/view.php?identifier={$resource->identifier}&amp;inpopup=true\" "
                          . "onclick=\"this.target='resource{$resource->id}'; return openpopup('/mod/sharedresource/view.php?inpopup=true&amp;identifier={$resource->identifier}', "
                          . "'resource{$resource->id}','resizable=1,scrollbars=1,directories=1,location=0,menubar=0,toolbar=0,status=1,width=800,height=600');\">(".$strpreview.")</a>";
                } else {
                    $url = str_replace('<%%ID%%>', $resource->identifier, $CFG->sharedresource_foreignurl);
                    echo "<a href=\"{$url}\" target=\"_blank\">(".$strpreview.")</a>";
                }      
                if (has_capability('moodle/course:manageactivities', $context)) {
                    echo "&nbsp;";
                    echo '<a href=\''.$CFG->wwwroot."/mod/sharedresource/edit.php?course={$course->id}&section={$section}&type={$type}&add=sharedresource&return={$return}&mode=update&entry_id={$resource->id}".'\'>('.get_string("update").')</a>';
                }
    			echo "</li>\n";
    		}
    		echo '</ul>';
    		$pagingbar = new paging_bar($totalcount, $page, SHAREDRESOURCE_RESULTS_PER_PAGE, $baseurl, $pagevar='page');
            print($OUTPUT->render($pagingbar));
            echo '</div>';
    	} else {
    		echo get_string('noresourcesfound','sharedresource');
    	}
    } else {
/// render form
    	$toform = new stdClass();
        $mform->set_data($toform);
        $mform->display();
    }
 	echo '</center>';

    echo $OUTPUT->footer();
