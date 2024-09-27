<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Loads classes before anything is known.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * Because classes preloading (SHAREDRESOURCE_INTERNAL) pertubrates MOODLE_INTERNAL detection.
 * phpcs:disable moodle.Files.MoodleInternal.MoodleInternalGlobalState
 * Autoloader comes before any Moodle internal statement.
 * phpcs:disable moodle.Files.RequireLogin.Missing
 */
define('SHAREDRESOURCE_INTERNAL', true);

$currentdir = dirname(__FILE__);
$classes = glob($currentdir.'/sharedresource*');
foreach ($classes as $classfile) {
    include_once($classfile);
}
