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

class classification_renderer extends \plugin_renderer_base {

    public function classifications($classifications) {

        $template = new StdClass;

        $template->strdelete = get_string('delete');
        $template->stredit = get_string('edit');
        $template->strenable = get_string('enable');
        $template->strdisable = get_string('disable');

        $template->deleteiconurl = $this->output->pix_url('t/delete');
        $template->editiconurl = $this->output->pix_url('t/edit');
        $template->enablediconurl = $this->output->pix_url('t/show');
        $template->disablediconurl = $this->output->pix_url('t/hide');

        foreach ($classifications as $classification) {
            $classification->isdeletable = sharedresource_uses_taxonomy($classification);
        }

        $this->output->render_from_template('mod_sharedresource/classifications', $template);
    }

    public function backadminpage() {

        $template = new StdClass;

        $label = get_string('backadminpage', 'sharedresource');
        $hrefurl = new moodle_url('/admin/settings.php', array('section' => 'modsettingsharedresource'));
        $template->backbutton = $this->output->continue_button($hrefurl, $label);

        return $this->output->render_from_template('mod_sharedresource/backadminpage', $template);
    }

}