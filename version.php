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
 *
 * @author  Piers Harding  piers@catalyst.net.nz
 * @reauthor  Valery Fremaux  valery.fremaux@gmail.com
 * @contributor  Wafa Adham  admin@adham.ps
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/taoresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */

////////////////////////////////////////////////////////////////////////////////
//  Code fragment to define the module version etc.
//  This fragment is called by /admin/index.php
////////////////////////////////////////////////////////////////////////////////

defined('MOODLE_INTERNAL') || die();

$module->version   = 2015072701;       // The current module version (Date: YYYYMMDDXX)
$module->requires  = 2015051100;       // Requires this Moodle version
$module->component = 'mod_sharedresource';     // Full name of the plugin (used for diagnostics)
$module->maturity = MATURITY_RC;     // Full name of the plugin (used for diagnostics)
$module->release = '2.9.0 (Build 2015072700)';     // Full name of the plugin (used for diagnostics)

