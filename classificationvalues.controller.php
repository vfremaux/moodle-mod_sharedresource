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
 * forms for converting resources to sharedresources
 *
 * @package    mod_sharedresource
 * @category   mod
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
namespace mod_sharedresource\classification;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/mod/sharedresource/treelib.php');

class classificationvalues_controller {

    protected $data;

    protected $received;

    protected $mform;

    public function receive($cmd, $data = array(), $mform = null) {
        $this->mform = $mform;

        if (!empty($data)) {
            $this->data = (object)$data;
            $this->received = true;
            return;
        } else {
            $this->data = new \StdClass;
        }

        switch ($cmd) {
            case 'up':
            case 'down':
            case 'delete':
                $this->data->classificationid = required_param('id', PARAM_INT);
                $this->data->tokenid = required_param('tokenid', PARAM_INT);
                break;
        }

        $this->received = true;
    }

    public function process($cmd) {
        global $DB;

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        $classificationrec = $DB->get_record('sharedresource_classif', array('id' => $this->data->classificationid));
        $classification = new \local_sharedresources\browser\navigation($classificationrec);

        switch  ($cmd) {
            case 'delete': {

                /*
                 * Deletes a taxon and subtree.
                 */

                if ($classificationrec->tablename == 'sharedresource_taxonomy') {
                    // Delete also taxons in local taxonomy storage table.
                    $classification->delete_token($this->data->tokenid);
                }

                break;
            }

            case 'up': {
                sharedresource_tree_up($this->data->tokenid, $classification->sqlsortorderstart);
                break;
            }

            case 'down': {
                sharedresource_tree_down($this->data->tokenid);
                break;
            }

        }
    }
}