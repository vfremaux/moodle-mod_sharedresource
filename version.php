<?php
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

$module->version   = 2013110800;       // The current module version (Date: YYYYMMDDXX)
$module->requires  = 2012062500;       // Requires this Moodle version
$module->component = 'mod_sharedresource';     // Full name of the plugin (used for diagnostics)
$module->cron      = 0;
$module->maturity = MATURITY_BETA;     // Full name of the plugin (used for diagnostics)
$module->release = '2.4.0 (Build 2013031800)';     // Full name of the plugin (used for diagnostics)

