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
 * Empty class just to serve as exception marker.
 *
 * @package     mod_sharedresource
 * @author      Piers Harding  <piers@catalyst.net.nz>, Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (www.activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace mod_sharedresource;

/**
 * Important : moodle_exception CANNOT BE USED here because this class is autoloaded
 * before moodle libs (and before session creation) to enable proper unserialization of session.
 */

use Exception;

/**
 * Empty class just to serve as exception marker.
 */
class metadata_exception extends Exception {
}
