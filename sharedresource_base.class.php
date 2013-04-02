<?php
/**
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */


/**
* sharedresource_base is the base class for sharedresource types
*
* This class provides all the functionality for a sharedresource
*/

class sharedresource_base {

    var $cm;
    var $course;
    var $sharedresource;
    var $navlinks;

    /**
    * Constructor for the base sharedresource class
    *
    * Constructor for the base sharedresource class.
    * If cmid is set create the cm, course, sharedresource objects.
    * and do some checks to make sure people can be here, and so on.
    *
    * @param cmid         integer, the current course module id - not set for new sharedresources
    * @param identifier   hash, alternative direct identifier for a sharedresource - not set for new sharedresources
    */
    function sharedresource_base($cmid=0, $identifier=false) {

        global $CFG, $COURSE;
        $this->navlinks = array();
        $this->inpopup = false;

        if ($cmid) {
            if (! $this->cm = get_coursemodule_from_id('sharedresource', $cmid)) {
                error("Course Module ID was incorrect");
            }

            if (! $this->course = get_record('course', 'id', $this->cm->course)) {
                error("Course is misconfigured");
            }

            if (! $this->sharedresource = get_record('sharedresource', 'id', $this->cm->instance)) {
                error("Shared Resource ID was incorrect");
            }

            if (!$this->cm->visible and !has_capability('moodle/course:viewhiddenactivities', get_context_instance(CONTEXT_MODULE, $this->cm->id))) {
                $pagetitle = strip_tags($this->course->shortname.': '.$this->strsharedresource);
                $navigation = build_navigation($this->navlinks, $this->cm);

                print_header($pagetitle, $this->course->fullname, $navigation, '', '', true, '', navmenu($this->course, $this->cm));
                notice(get_string("activityiscurrentlyhidden"), "$CFG->wwwroot/course/view.php?id={$this->course->id}");
            }

        } else {
            $this->course = $COURSE;
            if ($identifier) {
                if (! $this->sharedresource = get_record('sharedresource_entry', 'identifier', $identifier)) {
                    error("Shared Resource ID was incorrect");
                }
            }
        }
        if (isset($this->sharedresource) && !isset($this->sharedresource->summary)) {
            $this->sharedresource->summary = $this->sharedresource->description;
        }
        $this->strsharedresource  = get_string('modulename', 'sharedresource');
        $this->strsharedresources = get_string('modulenameplural', 'sharedresource');
    }

    
    /**
    * accessor for setting the display attribute for window popup
    */
    function inpopup() {
        
        $this->inpopup = true;
    }
    
    
    /**
    * Display function does nothing in the base class
    */
    function display() {

    }


    /**
    * Display the sharedresource with the course blocks.
    */
    function display_course_blocks_start() {

        global $CFG;
        global $USER;
        global $THEME;

        require_once($CFG->libdir.'/blocklib.php');
        require_once($CFG->libdir.'/pagelib.php');
        require_once($CFG->dirroot.'/course/lib.php'); //required by some blocks

        $PAGE = page_create_object(PAGE_COURSE_VIEW, $this->course->id);
        $this->PAGE = $PAGE;
        $pageblocks = blocks_setup($PAGE);

        $blocks_preferred_width = bounded_number(180, blocks_preferred_width($pageblocks[BLOCK_POS_LEFT]), 210);

    /// Print the page headings
        $edit = optional_param('edit', -1, PARAM_BOOL);

        if (($edit != -1) and $PAGE->user_allowed_editing()) {
            $USER->editing = $edit;
        }

        $morenavlinks = array($this->strsharedresources   => 'index.php?id='.$this->course->id,
                                 $this->sharedresource->name => '');

        $PAGE->print_header($this->course->shortname.': %fullname%', $morenavlinks, '', '', 
                            update_module_button($this->cm->id, $this->course->id, $this->strsharedresource));

        echo '<table id="layout-table"><tr>';
    
        $lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
        foreach ($lt as $column) {
            $lt1[] = $column;
            if ($column == 'middle') break;
        }
        foreach ($lt1 as $column) {
            switch ($column) {
                case 'left':
                    if((blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $PAGE->user_is_editing())) {
                        echo '<td style="width: '.$blocks_preferred_width.'px;" id="left-column">';
                        print_container_start();
                        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
                        print_container_end();
                        echo '</td>';
                    }
                break;

                case 'middle':
                    echo '<td id="middle-column">';
                    print_container_start(false, 'middle-column-wrap');
                    echo '<div id="sharedresource">';
                break;

                case 'right':
                    if((blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $PAGE->user_is_editing())) {
                        echo '<td style="width: '.$blocks_preferred_width.'px;" id="right-column">';
                        print_container_start();
                        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
                        print_container_end();
                        echo '</td>';
                    }
                break;
            }
        }
    }


    /**
     * Finish displaying the sharedresource with the course blocks
     */
    function display_course_blocks_end() {

        global $CFG;
        global $THEME;

        $PAGE = $this->PAGE;
        $pageblocks = blocks_setup($PAGE);
        $blocks_preferred_width = bounded_number(180, blocks_preferred_width($pageblocks[BLOCK_POS_RIGHT]), 210);
    
        $lt = (empty($THEME->layouttable)) ? array('left', 'middle', 'right') : $THEME->layouttable;
        foreach ($lt as $column) {
            if ($column != 'middle') {
                array_shift($lt);
            } else if ($column == 'middle') {
                break;
            }
        }
        foreach ($lt as $column) {
            switch ($column) {
                case 'left':
                    if((blocks_have_content($pageblocks, BLOCK_POS_LEFT) || $PAGE->user_is_editing())) {
                        echo '<td style="width: '.$blocks_preferred_width.'px;" id="left-column">';
                        print_container_start();
                        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_LEFT);
                        print_container_end();
                        echo '</td>';
                    }
                break;

                case 'middle':
                    echo '</div>';
                    print_container_end();
                    echo '</td>';
                break;

                case 'right':
                    if((blocks_have_content($pageblocks, BLOCK_POS_RIGHT) || $PAGE->user_is_editing())) {
                        echo '<td style="width: '.$blocks_preferred_width.'px;" id="right-column">';
                        print_container_start();
                        blocks_print_group($PAGE, $pageblocks, BLOCK_POS_RIGHT);
                        print_container_end();
                        echo '</td>';
                    }
                break;
            }
        }

        echo '</tr></table>';

        print_footer($this->course);

    }


    /**
     * Finish displaying the sharedresource with the course blocks
     * Given an object containing all the necessary data,
     * (defined by the form in mod.html) this function
     * will create a new instance and return the id number
     * of the new instance.
     *
     * @param sharedresource   object, sharedresource record values
     * @return int, sharedresource id or false      
     */
    function add_instance($sharedresource) {

        $sharedresource->timemodified = time();
        return insert_record('sharedresource', $sharedresource);
    }


    /**
     * Given an object containing all the necessary data,
     * (defined by the form in mod.html) this function
     * will update an existing instance with new data.
     *
     * @param sharedresource   object, sharedresource record values
     * @return bool sharedresource insert status      
     */
    function update_instance($sharedresource) {

        $sharedresource->id = $sharedresource->instance;
        $sharedresource->timemodified = time();
        return update_record("sharedresource", $sharedresource);
    }

    /**
     * Given an object containing the sharedresource data
     * this function will permanently delete the instance
     * and any data that depends on it.
     *
     * @param sharedresource   object, sharedresource record values
     * @return bool sharedresource delete status      
     */
    function delete_instance($sharedresource) {

        $result = true;
        if (! delete_records('sharedresource', 'id', "$sharedresource->id")) {
            $result = false;
        }

        return $result;
    }

    /**
     * set up form elements for add/update of sharedresource
     *
     * @param mform   object, reference to Moodle Forms object
     */
    function setup_elements(&$mform) {
        //override to add your own options
    }

    /**
     * set up form element default values prior to display for add/update of sharedresource
     *
     * @param default_values   object, reference to form default values object
     */
    function setup_preprocessing(&$default_values){
        //override to add your own options
    }

}
?>