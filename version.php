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

$plugin->version   = 2021102100;       // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2020060900;       // Requires this Moodle version.
$plugin->component = 'mod_sharedresource';     // Full name of the plugin (used for diagnostics).
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '3.9.0 (Build 2021102100)';
$plugin->dependencies = array('local_sharedresources' => 201801180);
$plugin->supported = [38, 311];

// Non Moodle attributes.
$plugin->codeincrement = '3.9.0011';
$plugin->privacy = 'dualrelease';