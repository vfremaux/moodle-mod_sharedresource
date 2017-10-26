<?php
/**
 *
 * @author  Frédéric GUILLOU
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resoruce
 * @package sharedresource
 *
 */

// This php script contains functions to display fields of 
// the metadata form which have a list type, after the user
// clicked on the add button (functions called with AJAX)
//-----------------------------------------------------------

require_once('../../../config.php');

$config = get_config('sharedresource');

$fieldnum         = required_param('fieldnum', PARAM_TEXT);
$islist           = required_param('islist', PARAM_BOOL);
$numoccur         = required_param('numoccur', PARAM_INT);
$schema     = required_param('schema', PARAM_ALPHA);
$name             = required_param('name', PARAM_TEXT);
$capability       = required_param('capability', PARAM_TEXT);
$realoccur        = optional_param('realoccur', null, PARAM_INT);

ob_start();

require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$config->schema.'/plugin.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/classificationlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');

// DO NOT TRY TO REQUIRE_JS HERE
// echo '<script src="js/metadata.js" type="text/javascript"></script>';
ob_end_clean();
$mtdclass = '\mod_sharedresource\plugin_'.$config->schema;
$mtdstandard = new $mtdclass;
metadata_make_part_form2($mtdstandard, $fieldnum, $islist, $numoccur, $name, $capability, $realoccur);