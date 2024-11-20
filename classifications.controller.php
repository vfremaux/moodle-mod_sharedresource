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
 * Controller for classification list management.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace mod_sharedresource\classification;

/**
 * MVC Controller class
 */
class classifications_controller {

    /** @var data */
    protected $data;

    /** @var marks data as received */
    protected $received;

    /** @var a form for getting attached files inside */
    protected $mform;

    /**
     * Receives data to process
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
            case 'delete':
                $this->data->classificationid = required_param('id', PARAM_INT);
                break;
            case 'enable':
                $this->data->classificationid = required_param('id', PARAM_INT);
                break;
            case 'disable':
                $this->data->classificationid = required_param('id', PARAM_INT);
                break;
        }

        $this->received = true;
    }

    /**
     * Processes the data
     * @param string $cmd
     */
    public function process($cmd) {
        global $DB;

        $config = get_config('sharedresource');
        $namespace = $config->schema;

        switch  ($cmd) {
            case 'delete': {

                /*
                 * When a classification is deleted, we must purge all
                 * metadata references to this classification.
                 * Those references are metadata value subtrees (Taxonpaths) that contain
                 * a taxon source matching this classification. All plugins are concerned
                 * when removing a classification.
                 *
                 * The process is :
                 * - find all Taxon sources that are matching the classification identifier
                 * - deduce all taxonpath instances that are attached to this source
                 * - remove taxon path subtree.
                 */
                $classification = $DB->get_record('sharedresource_classif', ['id' => $this->data->classificationid]);

                $mtdstandard = sharedresource_get_plugin($namespace);
                $mtdstandard->delete_classifications($this->data->classificationid, $namespace);

                if ($classification->tablename == 'sharedresource_taxonomy') {
                    // Delete also taxons in local taxonomy storage table.
                    $DB->delete_records('sharedresource_taxonomy', ['classificationid' => $this->data->classificationid]);
                }
                // Finally delete the classification record.
                $DB->delete_records('sharedresource_classif', ['id' => $this->data->classificationid]);
                break;
            }

            case 'enable': {
                $DB->set_field('sharedresource_classif', 'enabled', 1, ['id' => $this->data->classificationid]);
                break;
            }

            case 'disable': {
                $DB->set_field('sharedresource_classif', 'enabled', 0, ['id' => $this->data->classificationid]);
                break;
            }
        }
    }
}
