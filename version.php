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
 * Version details.
 *
 * @package mod_sharedresource
 * @category mod
 * @author  Piers Harding  piers@catalyst.net.nz
 * @author  Valery Fremaux  valery.fremaux@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/taoresource is a work derived from Moodle mod/resoruce
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2018041000;       // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2016112900;       // Requires this Moodle version.
$plugin->component = 'mod_sharedresource';     // Full name of the plugin (used for diagnostics).
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '3.2.0 (Build 2018041000)';
$plugin->dependencies = array('local_sharedresources' => 201801180);

// Non Moodle attributes.
$plugin->codeincrement = '3.2.0008';
$plugin->privacy = 'dualrelease';