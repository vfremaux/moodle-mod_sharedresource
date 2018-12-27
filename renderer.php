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
 * @package sharedresource
 * @category mod
 * @author  Valery Fremaux (valery.fremaux@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 */
defined('MOODLE_INTERNAL') || die();

class mod_sharedresource_renderer extends plugin_renderer_base {

    public function add_instance_form($section, $return) {
        global $COURSE;

        if ($COURSE->id == SITEID) {
            $context = context_system::instance();
        } else {
            $context = context_course::instance($COURSE->id);
        }

        $template = new StdClass;

        $libraryurl = new moodle_url('/local/sharedresources/index.php', array('course' => $COURSE->id, 'section' => $section, 'return' => $return));
        $template->searchbutton = $this->output->single_button($libraryurl, get_string('searchinlibrary', 'sharedresource'));
        $template->searchdesc = get_string('addinstance_search_desc', 'sharedresource');

        if (has_capability('repository/sharedresources:create', $context)) {
            $template->cancreate = true;
            $params = array('course' => $COURSE->id, 'section' => $section, 'return' => $return, 'add' => 'sharedresource', 'mode' => 'add');
            $editurl = new moodle_url('/mod/sharedresource/edit.php', $params);
            $template->createbutton = $this->output->single_button($editurl, get_string('addsharedresource', 'sharedresource'));
            $template->createdesc = get_string('addinstance_create_desc', 'sharedresource');
        }

        return $this->output->render_from_template('mod_sharedresource/addinstance', $template);
    }

<<<<<<< HEAD
    public function resourcecompare($new, $old) {
=======
    /**
     * Print resource comparison board.
     */
    public function resourcecompare($new, $old, $step = 'postdata') {
>>>>>>> MOODLE_36_STABLE

        $config = get_config('sharedresource');
        if (!$config->schema) {
            return;
        }
        $plugin = sharedresource_get_plugin($config->schema);

        $template = new StdClass;
        $template->olddescriptionstr = get_string('resourceolddescription', 'sharedresource');
        $template->newdescriptionstr = get_string('resourcenewdescription', 'sharedresource');

        $attributes = array();
        foreach ($old->metadataelements as $elm) {
            $elmkey = $elm->get_element_key();
            $stdelm = $plugin->getElement($elm->get_node_id());
            $attribute = new StdClass;
            $attribute->id = $elmkey;
            $attribute->name = $stdelm->name;
            $attribute->oldvalue = $elm->get_value();
            $attribute->newvalue = '';
            $attributes[$elmkey] = $attribute;
        }

        foreach ($new->metadataelements as $elm) {
            $elmkey = $elm->get_element_key();
            $stdelm = $plugin->getElement($elm->get_node_id());
            if (!array_key_exists($elmkey, $attributes)) {
                $attribute = new StdClass;
                $attribute->id = $elmkey;
                $attribute->name = $stdelm->name;
                $attribute->oldvalue = '';
                $attribute->newvalue = $elm->get_value();
                $attributes[$elmkey] = $attribute;
            } else {
<<<<<<< HEAD
                $attributes[$elm->get_element_key()]->newvalue = $elm->get_value();
=======
                if ($step == 'predata') {
                    $attributes[$elm->get_element_key()]->newvalue = get_string('predatanotprovided', 'sharedesource');
                } else {
                    $attributes[$elm->get_element_key()]->newvalue = $elm->get_value();
                }
>>>>>>> MOODLE_36_STABLE
            }
        }

        asort($attributes);

        $template->attributes = array_values($attributes);

        return $this->output->render_from_template('mod_sharedresource/resourcecompare', $template);
    }
}