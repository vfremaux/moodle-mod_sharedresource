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
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */
namespace mod_sharedresource;

use \StdClass;
use \moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * \mod_sharedresource\base is the base class for sharedresource types
 *
 * This class provides all the functionality for a sharedresource
 */
class base {

    public $cm; // If representing a course module.

    public $sharedresource; // The sharedresouce record.

    public $sharedresourceentry; // If representing both course module or single resource entry.

    public $navlinks;

    public $parameters;

    public $maxparameters = 5;

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
    public function __construct($cmid = 0, $identifier = false) {
        global $COURSE, $DB, $PAGE, $OUTPUT, $SITE;

        $this->sharedresource = new StdClass();
        $this->sharedresource->type = 'file'; // Cannot be anything else.
        $this->sharedresource->course = $COURSE->id; // This is a default in case of.
        $this->sharedresource->introformat = FORMAT_MOODLE;
        $this->navlinks = array();
        $this->inpopup = false;

        // If course module is given, we should get all information pulled from the course module instance.
        if ($cmid) {
            if (! $this->cm = get_coursemodule_from_id('sharedresource', $cmid)) {
                print_error('invalidcoursemodule');
            }
            if (!$this->sharedresource = $DB->get_record('sharedresource', array('id' => $this->cm->instance))) {
                print_error('invalidsharedresource', 'sharedresource');
            }
            if (! $this->sharedresourceentry = $DB->get_record('sharedresource_entry', array('identifier' => $this->sharedresource->identifier))) {
                $returnurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
                print_error('errorinvalididentifier', 'sharedresource', $returnurl, $this->sharedresource->identifier);
            }

            $coursename = $DB->get_field('course', 'shortname', array('id' => $this->sharedresource->course));

            if (!$this->cm->visible &&
                    !has_capability('moodle/course:viewhiddenactivities', \context_module::instance($this->cm->id))) {
                $pagetitle = strip_tags($coursename.': '.$this->strsharedresource);
                $navigation = build_navigation($this->navlinks, $this->cm);
                $coursecontext = \context_course::instance($this->sharedresource->course);
                $PAGE->set_pagelayout('standard');
                $PAGE->set_context($coursecontext);
                $url = new moodle_url('/mod/sharedresource/view.php');
                $PAGE->set_url($url);
                $PAGE->set_title($SITE->fullname);
                $PAGE->set_heading($pagetitle);
                $PAGE->navbar->add('view sharedresource info', 'view.php', 'misc');
                $PAGE->set_focuscontrol('');
                $PAGE->set_cacheable(false);

                echo $OUTPUT->header();

                $returnurl = new moodle_url('/course/view.php', array('id' => $this->sharedresource->course));
                echo $OUTPUT->notification(get_string("activityiscurrentlyhidden"), $returnurl);
                echo $OUTPUT->footer();
                die;
            }
        } else if ($identifier) {
            // This may be a new instance so not course module yet.
            if (! $this->sharedresourceentry = $DB->get_record('sharedresource_entry', array('identifier' => $identifier))) {
                print_error('errorinvalididentifier', 'sharedresource', new moodle_url('/course/view.php', array('id' => $COURSE->id)), $identifier);
            }
            $this->sharedresource->identifier = $identifier;
        } else {
            assert(1)
            // Empty sharedresource
        }

        if (isset($this->sharedresource) && !isset($this->sharedresource->intro) && isset($this->sharedresourceentry)) {
            $this->sharedresource->intro = $this->sharedresourceentry->description;
        }

        $this->strsharedresource  = get_string('modulename', 'sharedresource');
        $this->strsharedresources = get_string('modulenameplural', 'sharedresource');
    }

    /**
     * accessor for setting the display attribute for window popup
     */
    public function inpopup() {
        $this->inpopup = true;
    }

    /**
     * form post process for preparing layout parameters properly
     */
    public function _postprocess() {
        global $SHR_WINDOW_OPTIONS;

        $alloptions = $SHR_WINDOW_OPTIONS;

        $resource = $this->sharedresource;

        if (!empty($resource->forcedownload)) {
            $resource->popup = '';
            $resource->options = 'forcedownload';
        } else if (@$resource->windowpopup) {
            $optionlist = array();
            foreach ($alloptions as $option) {
                $optionlist[] = $option."=".$resource->$option;
                unset($resource->$option);
            }
            $resource->popup = implode(',', $optionlist);
            unset($resource->windowpopup);
            $resource->options = '';
        } else {
            if (empty($resource->framepage)) {
                $resource->options = '';
            } else {
                $resource->options = 'frame';
            }
            unset($resource->framepage);
            $resource->popup = '';
        }
        $optionlist = array();
        for ($i = 0; $i < $this->maxparameters; $i++) {
            $parametername = "parameter$i";
            $parsename = "parse$i";
            if (!empty($resource->$parsename) and $resource->$parametername != "-") {
                $optionlist[] = $resource->$parametername."=".$resource->$parsename;
            }
            unset($resource->$parsename);
            unset($resource->$parametername);
        }
        $resource->alltext = implode(',', $optionlist);
    }

    // Magic setter.
    public function __set($field, $value) {
        if (in_array($field, array('id', 'course', 'name', 'identifier', 'intro', 'introformat', 'alltext', 'popup', 'options'))) {
            $this->sharedresource->$field = $value;
        }
    }

    // Magic getter.
    public function __get($field) {
        if (in_array($field, array('id', 'course', 'name', 'identifier', 'intro', 'introformat', 'alltext', 'popup', 'options'))) {
            return $this->sharedresource->$field;
        }
    }

    /**
     * Display the file resource
     *
     * Displays a file resource embedded, in a frame, or in a popup.
     * Output depends on type of file resource.
     *
     */
    public function display() {
        global $CFG, $THEME, $USER, $PAGE, $OUTPUT, $SITE, $DB, $FULLME;

        $config = get_config('sharedresource');

        // Set up some shorthand variables.
        $cm = $this->cm;
        $course = $DB->get_record('course', array('id' => $this->sharedresource->course));
        $resource = $this->sharedresource;
        $sharedresourceentry = $this->sharedresourceentry;

        $DB->set_field('sharedresource_entry', 'scoreview', $sharedresourceentry->scoreview + 1, array('id' => $sharedresourceentry->id));

        // If we dont get the resource then fail.
        if (!$this->sharedresourceentry) {
            sharedresource_not_found($course->id);
        }
        $resource->reference = (!empty($sharedresourceentry->file)) ? $sharedresourceentry->file : $sharedresourceentry->url;

        if (isset($resource->name)) {
            $resource->title = $resource->name;
        } else {
            $resource->title = $sharedresourceentry->title;
        }

        $this->set_parameters(); // Set the parameters array.

        // First, find out what sort of file we are dealing with.
        require_once($CFG->libdir.'/filelib.php');
        $querystring = '';
        $resourcetype = '';
        $embedded = false;
        $mimetype = mimeinfo('type', $resource->reference);
        $pagetitle = strip_tags($course->shortname.': '.format_string($resource->title));
        $formatoptions = new stdClass();
        $formatoptions->noclean = true;

        if ($this->inpopup || (isset($resource->options) && $resource->options != 'forcedownload')) {
            if (in_array($mimetype, array('image/gif', 'image/jpeg', 'image/png'))) {
                $resourcetype = 'image';
                $embedded = true;
            } else if ($mimetype == 'audio/mp3') {    // It's an MP3 audio file
                $resourcetype = 'mp3';
                $embedded = true;
            } else if ($mimetype == 'video/x-flv') {    // It's a Flash video file
                $resourcetype = 'flv';
                $embedded = true;
            } else if (substr($mimetype, 0, 10) == 'video/x-ms') {   // It's a Media Player file
                $resourcetype = 'mediaplayer';
                $embedded = true;
            } else if ($mimetype == 'video/quicktime') {   // It's a Quicktime file
                $resourcetype = 'quicktime';
                $embedded = true;
            } else if ($mimetype == 'application/x-shockwave-flash') {   // It's a Flash file
                $resourcetype = 'flash';
                $embedded = true;
            } else if ($mimetype == 'video/mpeg') {   // It's a Mpeg file
                $resourcetype = 'mpeg';
                $embedded = true;
            } else if ($mimetype == 'text/html') {    // It's a web page
                $resourcetype = "html";
            } else if ($mimetype == 'application/zip') {    // It's a zip archive
                $resourcetype = 'zip';
                $embedded = true;
            } else if ($mimetype == 'application/pdf' || $mimetype == 'application/x-pdf') {
                $resourcetype = 'pdf';
                $embedded = true;
            } else if ($mimetype == 'audio/x-pn-realaudio') {   // It's a realmedia file
                $resourcetype = 'rm';
                $embedded = true;
            }
        }
        $isteamspeak = (stripos($resource->reference, 'teamspeak://') === 0);

        // Form the parse string.
        $querys = array();
        if (!empty($resource->alltext)) {
            $parray = explode(',', $resource->alltext);
            foreach ($parray as $fieldstring) {
                list($moodleparam, $urlname) = explode('=', $fieldstring);
                $value = urlencode($this->parameters[$moodleparam]['value']);
                $querys[urlencode($urlname)] = $value;
                $querysbits[] = urlencode($urlname) . '=' . $value;
            }
            if ($isteamspeak) {
                $querystring = implode('?', $querysbits);
            } else {
                $querystring = implode('&', $querysbits);
            }
        }

        // Set up some variables.
        $inpopup = optional_param('inpopup', 0, PARAM_BOOL);
        if (sharedresource_is_url($sharedresourceentry->url) && empty($sharedresourceentry->file)) {

            // Shared resource is a pure URL.
            $fullurl = $sharedresourceentry->url;
            if (!empty($querystring)) {
                $urlpieces = parse_url($sharedresourceentry->url);
                if (empty($urlpieces['query']) or $isteamspeak) {
                    $fullurl .= '?'.$querystring;
                } else {
                    $fullurl .= '&'.$querystring;
                }
            }

            if ($fullurl == $FULLME) {
                print_error(get_string('sharedresourcelooperror', 'sharedresource'));
            }
        } else {

            // Normal uploaded file.
            $forcedownloadsep = '?';
            if (isset($resource->options) && $resource->options == 'forcedownload') {
                $querys['forcedownload'] = '1';
            }
            $fullurl = sharedresource_get_file_url($this, $sharedresourceentry, $querys);
        }

        // Check whether this is supposed to be a popup, but was called directly.
        if (isset($resource->popup) && $resource->popup && !$inpopup) {
            // Make a page and a pop-up window.
            $coursecontext = \context_course::instance($course->id);
            $url = new moodle_url('/mod/sharedresource/view.php');
            $PAGE->set_url($url);
            $PAGE->set_pagelayout('popup');
            $PAGE->set_title($pagetitle);
            $PAGE->set_heading($SITE->fullname);
            $PAGE->navbar->add($course->fullname, 'view.php', 'misc');

            $PAGE->set_focuscontrol('');
            $PAGE->set_cacheable(false);
            $PAGE->set_button('');

            $viewdata = array();
            $viewdata['popupoptions'] = $resource->popup;
            $viewdata['cmid'] = $cm->id;
            $PAGE->requires->js_call_amd('mod_sharedresource/view', 'init', $viewdata);

            echo $OUTPUT->header();

            $template = new StdClass;
            $template->resid = $resource->id;
            if (trim(strip_tags($resource->intro))) {
                $template->infobox = $OUTPUT->box(format_text($resource->intro, $resource->introformat, $formatoptions), "center");
            }
            $template->linkurl = new moodle_url('/mod/sharedresource/view.php', array('inpopup' => true, 'id' => $cm->id));
            $template->popupoptions = $resource->popup;

            $template->title = format_string($resource->title, true);
            $template->strpopupresource = get_string('popupresource', 'resource', $template->linkurl);
            $template->strpopupresourcelink = get_string('popupresourcelink', 'resource', $template->linkurl);

            echo $OUTPUT->render_from_template('mod_sharedresource/directpopup', $template);

            echo $OUTPUT->footer($course);
            die;
        }

        // Now check whether we need to display a frameset.
        $frameset = optional_param('frameset', '', PARAM_ALPHA);
        if (empty($frameset) &&
                !$embedded &&
                        !$inpopup &&
                                (isset($resource->options) &&
                                        $resource->options == "frame") &&
                                                empty($USER->screenreader)) {
            @header('Content-Type: text/html; charset=utf-8');
            echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">\n";
            echo '<html dir="ltr">'."\n";
            echo '<head>';
            echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
            echo '<title>' . format_string($course->shortname) . ': '.strip_tags(format_string($resource->title, true)).'</title></head>'."\n";
            echo '<frameset rows="'.$config->framesize.',*">';
            $topurl = new moodle_url('/mod/sharedresource/view.php', array('id' => $cm->id, 'type' => $resource->type, 'frameset' => 'top'));
            echo '<frame src="'.$topurl.'" title="'. get_string('modulename', 'resource').'"/>';
            echo '<frame src="'.$fullurl.'" title="'.get_string('modulename', 'sharedresource').'"/>';
            echo '</frameset>';
            echo '</html>';
            exit;
        }

        // If we are in a frameset, just print the top of it
        if (!empty( $frameset ) and ($frameset == 'top') ) {
            $navigation = build_navigation($this->navlinks, $cm);
            $PAGE->set_pagelayout('frametop');
            $PAGE->set_context($system_context);
            $PAGE->set_title($pagetitle);
            $PAGE->set_heading($SITE->fullname);
            /* SCANMSG: may be additional work required for $navigation variable */
            $PAGE->navbar->add($strtitle, 'view.php', 'misc');
            $PAGE->set_focuscontrol('');
            $PAGE->set_cacheable(false);
            $PAGE->set_button(update_module_button($cm->id, $course->id, $this->strresource));
            $PAGE->set_headingmenu(navmenu($course, $cm, 'parent'));
            $url = new moodle_url('/mod/sharedresource/view.php');
            $PAGE->set_url($url);

            echo $OUTPUT->header();

            $options = new stdClass();
            $options->para = false;
            echo '<div class="summary">'.format_text($resource->intro, $resource->introformat, $options).'</div>';

            echo $OUTPUT->footer('empty');
            exit;
        }

        // Display the actual resource.
        if ($embedded) {
            // Display resource embedded in page.
            $strdirectlink = get_string('directlink', 'sharedresource');
            $coursecontext = context_course::instance($course->id);

            if ($inpopup) {
                $PAGE->set_pagelayout('embedded');
            } else {
                $PAGE->set_pagelayout('popup');
            }

            $url = new moodle_url('/mod/sharedresource/view.php');
            $PAGE->set_url($url);

            $PAGE->set_title($pagetitle);
            $PAGE->set_heading($SITE->fullname);
            $PAGE->navbar->add($pagetitle, 'view.php', 'misc');
            $PAGE->set_focuscontrol('');
            $PAGE->set_cacheable(false);
            $PAGE->set_button('');

            echo $OUTPUT->header();

            if ($resourcetype == 'image') {
                echo '<div class="resourcecontent resourceimg">';
                echo "<img title=\"".strip_tags(format_string($resource->title, true))."\" class=\"resourceimage\" src=\"$fullurl\" alt=\"\" />";
                echo '</div>';
            } else if ($resourcetype == 'mp3') {
                if (!empty($THEME->resource_mp3player_colors)) {
                    // You can set this up in your theme/xxx/config.php
                    $c = $THEME->resource_mp3player_colors;
                } else {
                    $c = 'bgColour=000000&btnColour=ffffff&btnBorderColour=cccccc&iconColour=000000&'.
                         'iconOverColour=00cc00&trackColour=cccccc&handleColour=ffffff&loaderColour=ffffff&'.
                         'font=Arial&fontColour=FF33FF&buffer=10&waitForPlay=no&autoPlay=yes';
                }
                $c .= '&volText='.get_string('vol', 'sharedresource').'&panText='.get_string('pan', 'sharedresource');
                $c = htmlentities($c);
                $id = 'filter_mp3_'.time(); // We need something unique because it might be stored in text cache.
                $cleanurl = ($fullurl);
                // If we have Javascript, use UFO to embed the MP3 player, otherwise depend on plugins.
                echo '<div class="resourcecontent resourcemp3">';
                echo '<span class="mediaplugin mediaplugin_mp3" id="'.$id.'"></span>'.
                     '<script type="text/javascript">'."\n".
                     '//<![CDATA['."\n".
                       'var FO = { movie:"'.$CFG->wwwroot.'/lib/mp3player/mp3player.swf?src='.$cleanurl.'",'."\n".
                         'width:"600", height:"70", majorversion:"6", build:"40", flashvars:"'.$c.'", quality: "high" };'."\n".
                       'UFO.create(FO, "'.$id.'");'."\n".
                     '//]]>'."\n".
                     '</script>'."\n";
                echo '<noscript>';
                echo "<object type=\"audio/mpeg\" data=\"$fullurl\" width=\"600\" height=\"70\">";
                echo "<param name=\"src\" value=\"$fullurl\" />";
                echo '<param name="quality" value="high" />';
                echo '<param name="autoplay" value="true" />';
                echo '<param name="autostart" value="true" />';
                echo '</object>';
                echo '<p><a href="' . $fullurl . '">' . $fullurl . '</a></p>';
                echo '</noscript>';
                echo '</div>';
            } else if ($resourcetype == 'flv') {
                $id = 'filter_flv_'.time(); // We need something unique because it might be stored in text cache.
                $cleanurl = ($fullurl);
                // If we have Javascript, use UFO to embed the FLV player, otherwise depend on plugins.
                echo '<div class="resourcecontent resourceflv">';
                echo '<span class="mediaplugin mediaplugin_flv" id="'.$id.'"></span>'.
                     '<script type="text/javascript">'."\n".
                     '//<![CDATA['."\n".
                       'var FO = { movie:"'.$CFG->wwwroot.'/filter/mediaplugin/flvplayer.swf?file='.$cleanurl.'",'."\n".
                         'width:"600", height:"400", majorversion:"6", build:"40", allowscriptaccess:"never", quality: "high" };'."\n".
                       'UFO.create(FO, "'.$id.'");'."\n".
                     '//]]>'."\n".
                     '</script>'."\n";
                echo '<noscript>';
                echo "<object type=\"video/x-flv\" data=\"$fullurl\" width=\"600\" height=\"400\">";
                echo "<param name=\"src\" value=\"$fullurl\" />";
                echo '<param name="quality" value="high" />';
                echo '<param name="autoplay" value="true" />';
                echo '<param name="autostart" value="true" />';
                echo '</object>';
                echo '<p><a href="' . $fullurl . '">' . $fullurl . '</a></p>';
                echo '</noscript>';
                echo '</div>';
            } else if ($resourcetype == 'mediaplayer') {
                echo '<div class="resourcecontent resourcewmv">';
                echo '<object type="video/x-ms-wmv" data="' . $fullurl . '">';
                echo '<param name="controller" value="true" />';
                echo '<param name="autostart" value="true" />';
                echo "<param name=\"src\" value=\"$fullurl\" />";
                echo '<param name="scale" value="noScale" />';
                echo "<a href=\"$fullurl\">$fullurl</a>";
                echo '</object>';
                echo '</div>';
            } else if ($resourcetype == 'mpeg') {
                echo '<div class="resourcecontent resourcempeg">';
                echo '<object classid="CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95"
                              codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsm p2inf.cab#Version=5,1,52,701"
                              type="application/x-oleobject">';
                echo "<param name=\"fileName\" value=\"$fullurl\" />";
                echo '<param name="autoStart" value="true" />';
                echo '<param name="animationatStart" value="true" />';
                echo '<param name="transparentatStart" value="true" />';
                echo '<param name="showControls" value="true" />';
                echo '<param name="Volume" value="-450" />';
                echo '<!--[if !IE]>-->';
                echo '<object type="video/mpeg" data="' . $fullurl . '">';
                echo '<param name="controller" value="true" />';
                echo '<param name="autostart" value="true" />';
                echo "<param name=\"src\" value=\"$fullurl\" />";
                echo "<a href=\"$fullurl\">$fullurl</a>";
                echo '<!--<![endif]-->';
                echo '<a href="' . $fullurl . '">' . $fullurl . '</a>';
                echo '<!--[if !IE]>-->';
                echo '</object>';
                echo '<!--<![endif]-->';
                echo '</object>';
                echo '</div>';
            } else if ($resourcetype == 'rm') {
                echo '<div class="resourcecontent resourcerm">';
                echo '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="320" height="240">';
                echo '<param name="src" value="' . $fullurl . '" />';
                echo '<param name="controls" value="All" />';
                echo '<!--[if !IE]>-->';
                echo '<object type="audio/x-pn-realaudio-plugin" data="' . $fullurl . '" width="320" height="240">';
                echo '<param name="controls" value="All" />';
                echo '<a href="' . $fullurl . '">' . $fullurl .'</a>';
                echo '</object>';
                echo '<!--<![endif]-->';
                echo '</object>';
                echo '</div>';
            } else if ($resourcetype == 'quicktime') {
                echo '<div class="resourcecontent resourceqt">';
                echo '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"';
                echo '        codebase="http://www.apple.com/qtactivex/qtplugin.cab">';
                echo "<param name=\"src\" value=\"$fullurl\" />";
                echo '<param name="autoplay" value="true" />';
                echo '<param name="loop" value="true" />';
                echo '<param name="controller" value="true" />';
                echo '<param name="scale" value="aspect" />';
                echo '<!--[if !IE]>-->';
                echo "<object type=\"video/quicktime\" data=\"$fullurl\">";
                echo '<param name="controller" value="true" />';
                echo '<param name="autoplay" value="true" />';
                echo '<param name="loop" value="true" />';
                echo '<param name="scale" value="aspect" />';
                echo '<!--<![endif]-->';
                echo '<a href="' . $fullurl . '">' . $fullurl . '</a>';
                echo '<!--[if !IE]>-->';
                echo '</object>';
                echo '<!--<![endif]-->';
                echo '</object>';
                echo '</div>';
            }  else if ($resourcetype == 'flash') {
                echo '<div class="resourcecontent resourceswf">';
                echo '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
                echo "<param name=\"movie\" value=\"$fullurl\" />";
                echo '<param name="autoplay" value="true" />';
                echo '<param name="loop" value="true" />';
                echo '<param name="controller" value="true" />';
                echo '<param name="scale" value="aspect" />';
                echo '<!--[if !IE]>-->';
                echo "<object type=\"application/x-shockwave-flash\" data=\"$fullurl\">";
                echo '<param name="controller" value="true" />';
                echo '<param name="autoplay" value="true" />';
                echo '<param name="loop" value="true" />';
                echo '<param name="scale" value="aspect" />';
                echo '<!--<![endif]-->';
                echo '<a href="' . $fullurl . '">' . $fullurl . '</a>';
                echo '<!--[if !IE]>-->';
                echo '</object>';
                echo '<!--<![endif]-->';
                echo '</object>';
                echo '</div>';
            } else if ($resourcetype == 'zip') {
                echo '<div class="resourcepdf">';
                echo get_string('clicktoopen', 'resource') . '<a href="' . $fullurl . '">' . format_string($resource->title) . '</a>';
                echo '</div>';
            } else if ($resourcetype == 'pdf') {
                echo '<div class="resourcepdf">';
                echo '<object data="' . $fullurl . '" type="application/pdf">';
                echo get_string('clicktoopen', 'resource') . '<a href="' . $fullurl . '">' . format_string($resource->title) . '</a>';
                echo '</object>';
                echo '</div>';
            }
            if (trim($resource->intro)) {
                echo $OUTPUT->box(format_text($resource->intro, $resource->introformat, $formatoptions, $course->id), "center");
            }
            if ($inpopup) {
                // Suppress the banner that gets cutoff with large images.
                echo '<style> body.HAT-narrowbg {background:none};</style>';
                echo "<div class=\"popupnotice\">(<a href=\"$fullurl\">$strdirectlink</a>)</div>";
                echo "</div>"; // MDL-12098
            } else {
                print_spacer(20, 20);
            }
        } else {
            // Display the resource on it's own.
            redirect($fullurl);
        }
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
    public function add_instance() {
        global $DB;

        $this->_postprocess();
        $this->sharedresource->timemodified = time();
        return $DB->insert_record('sharedresource', $this->sharedresource);
    }

    /**
     * Given an object containing all the necessary data,
     * (defined by the form in mod.html) this function
     * will update an existing instance with new data.
     *
     * @param sharedresource   object, sharedresource record values
     * @return bool sharedresource insert status
     */
    public function update_instance() {
        global $DB;

        $this->_postprocess();
        $this->sharedresource->id = $this->sharedresource->instance;
        $this->sharedresource->timemodified = time();
        return $DB->update_record('sharedresource', $this->sharedresource);
    }

    /**
     * Given an object containing the sharedresource data
     * this function will permanently delete the instance
     * and any data that depends on it.
     *
     * @param sharedresource   object, sharedresource record values
     * @return bool sharedresource delete status
     */
    public function delete_instance() {
        global $DB;

        $result = true;
        if (!$DB->delete_records('sharedresource', array('id' => $this->sharedresource->id))) {
            $result = false;
        }
        return $result;
    }

    /**
     * Sets the parameters property of the extended class
     *
     * @uses $USER  global object
     * @uses $CFG   global object
     */
    public function set_parameters() {
        global $USER, $CFG, $DB;

        $site = get_site();
        $littlecfg = new stdClass();
        $littlecfg->wwwroot = $CFG->wwwroot;

        $course = $DB->get_record('course', array('id' => $this->sharedresource->course));

        $this->parameters = array(
                'label2'          => array('langstr' => "",
                                           'value'   => '/optgroup'),
                'label3'          => array('langstr' => get_string('course'),
                                           'value'   => 'optgroup'),
                'courseid'        => array('langstr' => 'id',
                                           'value'   => $this->sharedresource->course),
                'coursefullname'  => array('langstr' => get_string('fullname'),
                                           'value'   => $course->fullname),
                'courseshortname' => array('langstr' => get_string('shortname'),
                                           'value'   => $course->shortname),
                'courseidnumber'  => array('langstr' => get_string('idnumbercourse'),
                                           'value'   => $course->idnumber),
                'coursesummary'   => array('langstr' => get_string('summary'),
                                           'value'   => $course->summary),
                'courseformat'    => array('langstr' => get_string('format'),
                                           'value'   => $course->format),
                'label4'          => array('langstr' => '',
                                           'value'   => '/optgroup'),
                'label5'          => array('langstr' => get_string('miscellaneous'),
                                           'value'   => 'optgroup'),
                'lang'            => array('langstr' => get_string('preferredlanguage'),
                                           'value'   => current_language()),
                'sitename'        => array('langstr' => get_string('fullsitename'),
                                           'value'   => format_string($site->fullname)),
                'serverurl'       => array('langstr' => get_string('serverurl', 'sharedresource', $littlecfg),
                                           'value'   => $littlecfg->wwwroot),
                'currenttime'     => array('langstr' => get_string('time'),
                                           'value'   => time()),
                'label6'          => array('langstr' => "",
                                           'value'   => '/optgroup')
        );

        if (!empty($USER->id)) {
            $userparameters = array(
                'label1'          => array('langstr' => get_string('user'),
                                           'value'   => 'optgroup'),
                'userid'          => array('langstr' => 'id',
                                           'value'   => $USER->id),
                'userusername'    => array('langstr' => get_string('username'),
                                           'value'   => $USER->username),
                'useridnumber'    => array('langstr' => get_string('idnumber'),
                                           'value'   => $USER->idnumber),
                'userfirstname'   => array('langstr' => get_string('firstname'),
                                           'value'   => $USER->firstname),
                'userlastname'    => array('langstr' => get_string('lastname'),
                                           'value'   => $USER->lastname),
                'userfullname'    => array('langstr' => get_string('fullname'),
                                           'value'   => fullname($USER)),
                'useremail'       => array('langstr' => get_string('email'),
                                           'value'   => $USER->email),
                'usericq'         => array('langstr' => get_string('icqnumber'),
                                           'value'   => $USER->icq),
                'userphone1'      => array('langstr' => get_string('phone').' 1',
                                           'value'   => $USER->phone1),
                'userphone2'      => array('langstr' => get_string('phone2').' 2',
                                           'value'   => $USER->phone2),
                'userinstitution' => array('langstr' => get_string('institution'),
                                           'value'   => $USER->institution),
                'userdepartment'  => array('langstr' => get_string('department'),
                                           'value'   => $USER->department),
                'useraddress'     => array('langstr' => get_string('address'),
                                           'value'   => $USER->address),
                'usercity'        => array('langstr' => get_string('city'),
                                           'value'   => $USER->city),
                'usertimezone'    => array('langstr' => get_string('timezone'),
                                           'value'   => 0),
                'userurl'         => array('langstr' => get_string('webpage'),
                                           'value'   => $USER->url)
             );
             $this->parameters = $userparameters + $this->parameters;
        }
    }

    /**
     * set up form elements for add/update of sharedresource
     *
     * @param mform   object, reference to Moodle Forms object
     */
    public function setup_elements(&$mform) {
        global $SHR_WINDOW_OPTIONS, $DB;

        $config = get_config('sharedresource');

        $add     = optional_param('add', 0, PARAM_ALPHA);
        $update  = optional_param('update', 0, PARAM_INT);
        $return  = optional_param('return', 0, PARAM_BOOL); // Return to course/view.php if false or mod/modname/view.php if true.
        $type    = optional_param('type', 'file', PARAM_ALPHANUM);
        $section = optional_param('section', null, PARAM_INT);
        $courseid  = optional_param('course', null, PARAM_INT);

        if (!empty($add)) {
            // We may just have created one.
            $entryid = optional_param('entryid', false, PARAM_INT);
            // Have we selected a resource yet ?
            if (empty($entryid)) {
                $params = array('course' => $courseid,
                                'section' => $section,
                                'type' => $type,
                                'add' => $add,
                                'return' => $return,
                                'entryid' => $entryid);
                redirect(new moodle_url('/mod/sharedresource/search.php', $params));
            } else {
                // We have our reference Shared resource.
                if (!$shrentry = \mod_sharedresource\entry::read_by_id($entryid)) {
                    print_error('errorinvalididentifier', 'sharedresource', $entryid);
                }
            }
        } else if (!empty($update)) {
            if (! $cm = get_coursemodule_from_id('sharedresource', $update)) {
                print_error('invalidcoursemodule');
            }
            if (! $resource = $DB->get_record('sharedresource', array('id' => $cm->instance))) {
                print_error('errorinvalidresource', 'sharedresource');
            }
            if (!$shrentry = \mod_sharedresource\entry::read($resource->identifier)) {
                print_error('errorinvalididentifier', 'sharedresource', $resource->identifier);
            }
        }
        // Set the parameter array for the form.
        $this->set_parameters();
        $mform->addElement('hidden', 'entryid', $shrentry->id);
        $mform->setType('entryid', PARAM_INT);

        $mform->addElement('hidden', 'identifier', $shrentry->identifier);
        $mform->setType('identifier', PARAM_TEXT);

        $mform->setDefault('name', $shrentry->title);
        $mform->setDefault('description', ($shrentry->description));

        $location = $mform->addElement('static', 'origtitle', get_string('title', 'sharedresource').': ', ($shrentry->title));

        $strpreview = get_string('preview', 'sharedresource');
        if (empty($config->foreignurl)) {
            $params = array('identifier' => $shrentry->identifier, 'inpopup' => true);
            $resurl = new moodle_url('/mod/sharedresource/view.php', $params);
            $link = '<a href="'.$resurl.'" '
              . "onclick=\"this.target='resource{$shrentry->id}'; return openpopup('".$resurl."', "
              . "'resource{$shrentry->id}','resizable=1,scrollbars=1,directories=1,location=0,menubar=0,toolbar=0,status=1,width=800,height=600');\">(".$strpreview.")</a>";
        } else {
            $url = str_replace('<%%ID%%>', $shrentry->identifier, $config->foreignurl);
            $link = '<a href="'.$url.'" target="_blank">('.$strpreview.')</a>';
        }

        $location = $mform->addElement('static', 'url', get_string('location', 'sharedresource').': ', $link);

        $searchbutton = $mform->addElement('submit', 'searchsharedresource', get_string('searchsharedresource', 'sharedresource'));
        $params = array('course' => $this->sharedresource->course,
                        'section' => $section,
                        'type' => $type,
                        'add' => $add,
                        'return' => $return);
        $searchurl = new moodle_url('/mod/sharedresource/search.php', $params);
        $buttonattributes = array('title' => get_string('searchsharedresource', 'sharedresource'),
                                  'onclick' => " window.location.href ='".$searchurl."'; return false;");
        $searchbutton->updateAttributes($buttonattributes);

        $mform->addElement('header', 'displaysettings', get_string('display', 'sharedresource'));

        $mform->addElement('checkbox', 'forcedownload', get_string('forcedownload', 'sharedresource'));
        $mform->disabledIf('forcedownload', 'windowpopup', 'eq', 1);

        $woptions = array(0 => get_string('pagewindow', 'sharedresource'), 1 => get_string('newwindow', 'sharedresource'));
        $mform->addElement('select', 'windowpopup', get_string('display', 'sharedresource'), $woptions);
        $mform->setType('windowpopup', PARAM_INT);
        $mform->setDefault('windowpopup', (empty($config->popup) ? 1 : 0));
        $mform->disabledIf('windowpopup', 'forcedownload', 'checked');

        $mform->addElement('checkbox', 'framepage', get_string('keepnavigationvisible', 'sharedresource'));
        $mform->addHelpButton('framepage', 'frameifpossible', 'sharedresource');
        $mform->setDefault('framepage', 0);
        $mform->disabledIf('framepage', 'windowpopup', 'eq', 1);
        $mform->disabledIf('framepage', 'forcedownload', 'checked');
        $mform->setAdvanced('framepage');

        foreach ($SHR_WINDOW_OPTIONS as $option) {
            if ($option == 'height' or $option == 'width') {
                $mform->addElement('text', $option, get_string('new'.$option, 'sharedresource'), array('size' => '4'));
                $mform->setType($option, PARAM_TEXT);
                $mform->setDefault($option, $config->{'popup'.$option});
                $mform->disabledIf($option, 'windowpopup', 'eq', 0);
            } else {
                $mform->addElement('checkbox', $option, get_string('new'.$option, 'sharedresource'));
                $mform->setDefault($option, $config->{'popup'.$option});
                $mform->setType($option, PARAM_INT);
                $mform->disabledIf($option, 'windowpopup', 'eq', 0);
            }
            $mform->setAdvanced($option);
        }
        $mform->addElement('header', 'parameters', get_string('parameters', 'sharedresource'));

        $options = array();
        $options['-'] = get_string('chooseparameter', 'sharedresource').'...';
        $optgroup = '';
        foreach ($this->parameters as $pname => $param) {
            if ($param['value'] == '/optgroup') {
                $optgroup = '';
                continue;
            }
            if ($param['value'] == 'optgroup') {
                $optgroup = $param['langstr'];
                continue;
            }
            $options[$pname] = $optgroup.' - '.$param['langstr'];
        }
        for ($i = 0; $i < $this->maxparameters; $i++) {
            $parametername = "parameter$i";
            $parsename = "parse$i";
            $group = array();
            $group[] =& $mform->createElement('text', $parsename, '', array('size' => '12')); // TODO: accessiblity.
            $group[] =& $mform->createElement('select', $parametername, '', $options); // TODO: accessiblity.
            $label = get_string('variablename', 'sharedresource').'='.get_string('parameter', 'sharedresource');
            $mform->addGroup($group, 'pargroup'.$i, $label, ' ', false);
            $mform->setAdvanced('pargroup'.$i);
            $mform->setType($parsename, PARAM_RAW);
            $mform->setDefault($parametername, '-');
        }
    }

    /**
     * set up form element default values prior to display for add/update of sharedresource
     *
     * @param default_values   object, reference to form default values object
     */
    public function setup_preprocessing(&$default_values) {
        // Override to add your own options.
        assert(1);
    }
}
