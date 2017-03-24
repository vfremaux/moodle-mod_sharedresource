<?php
// This file is part of the Certificate module for Moodle - http://moodle.org/
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
<<<<<<< HEAD
 * @reauthor  Valery Fremaux  valery.fremaux@gmail.com
 * @contributor  Wafa Adham  admin@adham.ps
 * @version 0.0.1
=======
 * @author  Valery Fremaux  valery.fremaux@gmail.com
 * @contributor  Wafa Adham  admin@adham.ps
>>>>>>> MOODLE_32_STABLE
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/taoresource is a work derived from Moodle mod/resoruce
 */

defined('MOODLE_INTERNAL') || die();

<<<<<<< HEAD
$module->version   = 2013110801;       // The current module version (Date: YYYYMMDDXX)
$module->requires  = 2012062500;       // Requires this Moodle version
$module->component = 'mod_sharedresource';     // Full name of the plugin (used for diagnostics)
$module->cron      = 0;
$module->maturity = MATURITY_RC;     // Full name of the plugin (used for diagnostics)
$module->release = '2.4.0 (Build 2013031801)';     // Full name of the plugin (used for diagnostics)

=======
$plugin->version   = 2015072701;       // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2016052300;       // Requires this Moodle version.
$plugin->component = 'mod_sharedresource';     // Full name of the plugin (used for diagnostics).
$plugin->maturity = MATURITY_RC;     // Full name of the plugin (used for diagnostics).
$plugin->release = '3.2.0 (Build 2015072700)';
$plugin->dependencies = array('local_sharedresources' => 2014032700);

// Non Moodle attributes.
$plugin->codeincrement = '3.2.0001';
>>>>>>> MOODLE_32_STABLE
