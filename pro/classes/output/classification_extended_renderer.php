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
 * @author  Valery Fremaux valery.fremaux@club-internet.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/taoresource is a work derived from Moodle mod/resoruce
 * @package    mod_sharedresource
 * @category   mod
 *
 * This is a separate configuration screen to configure any metadata stub that is attached to a shared resource. 
 */
namespace mod_sharedresource\output;

use \Stdclass;
use \moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');

class pro_classification_renderer extends \plugin_renderer_base {

    public function selecttaxonsform($classifid, $selectedtaxonsselector, $potentialtaxonsselector) {
        global $PAGE, $OUTPUT;

        $template = new StdClass;
        $template->formurl = new moodle_url($PAGE->url, array('id' => $classifid));
        $template->sesskey = sesskey();
        $template->classifid = $classifid;
        $template->selectedstr = get_string('selectedtaxons', 'sharedresource');
        $template->selectedtaxonsselector = $selectedtaxonsselector->display(true);
        $template->larrowstr = $OUTPUT->larrow().'&nbsp;'.get_string('add');
        $template->titleadd = get_string('add');
        $template->rarrowstr = get_string('remove').'&nbsp;'.$OUTPUT->rarrow();
        $template->titleremove = get_string('remove');
        $template->potentialtaxonsstr = get_string('potentialtaxons', 'sharedresource');
        $template->potentialtaxonsselector = $potentialtaxonsselector->display(true);

        return $this->output->render_from_template('sharedresource/selecttaxonsform', $template);
    }
}