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
 * Controller for classification edition.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace mod_sharedresource\classification;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/mod/sharedresource/treelib.php');

/**
 * MVC controller class
 */
class classificationvalues_controller {

    /** @var $data to process in action */
    protected $data;

    /** @var marks data has been received */
    protected $received;

    /** @var form where attached files come from */
    protected $mform;

    /**
     * Receive data
     * @param string $cmd
     * @param array $data
     * @param object $mform
     */
    public function receive($cmd, $data = [], $mform = null) {
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
                $this->data->parent = optional_param('parent', 0, PARAM_INT);
                $this->data->tokenid = required_param('tokenid', PARAM_INT);
                break;
        }

        $this->received = true;
    }

    /**
     * Process the action
     * @param string $cmd
     */
    public function process($cmd) {
        global $DB;

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        $classificationrec = $DB->get_record('sharedresource_classif', ['id' => $this->data->classificationid]);
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
                sharedresource_tree_up($this->data->tokenid, $classification);
                break;
            }

            case 'down': {
                sharedresource_tree_down($this->data->tokenid);
                break;
            }

        }
    }
}
