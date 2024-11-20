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
 * This script produces a metadata subtree form fragment for element having a list type, after the user
 * clicked on the add button
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

define('AJAX_SCRIPT', true);

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');

$id = required_param('id', PARAM_INT);

echo '<option value="">'.get_string('none', 'sharedresource').'</option>';
if (empty($id)) {
    return;
}

require_login();

$classificationoptions = metadata_get_classification_options($id);

$html = '';
if (!empty($classificationoptions)) {
    foreach ($classificationoptions as $key => $value) {
        $html .= '<option value="'.$key.'">'.$value.'</option>';
    }
}
echo $html;
