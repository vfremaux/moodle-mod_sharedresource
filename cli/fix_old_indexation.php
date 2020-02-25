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
 * This script fixes the old scalar indexation references in sharedresource_metadata, to use
 * new pathid taxon references.
 *
 * @package    mod_sharedresource
 * @subpackage cli
 * @author Valery Fremaux (valery.fremaux@gmail.com)
 * @copyright   Valery fremaux (http://www.mylearningfactory.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CLI_VMOODLE_PRECHECK;

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);
$CLI_VMOODLE_PRECHECK = true; // Force first config to be minimal.

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

if (!isset($CFG->dirroot)) {
    die ('$CFG->dirroot must be explicitely defined in moodle config.php for this script to be used');
}

require_once($CFG->dirroot.'/lib/clilib.php');         // Cli only functions.

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'help' => false,
        'host' => false,
        'debug' => false,
    ),
    array(
        'h' => 'help',
        'H' => 'host',
        'd' => 'debug',
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error("Not recognized options ".$unrecognized);
}

if ($options['help']) {
    $help = "
Reset local admin user, creating it if it w as deleted or renamed.

There are no security checks here because anybody who is able to
execute this file may execute any PHP too.

Options:
-h, --help          Print out this help
-H, --host          the virtual host you are working for
-d, --debug         the virtual host you are working for

Examples:
\$ /usr/bin/php mod/sharedresource/cli/fix_old_indexation.php

\$ /usr/bin/php mod/sharedresource/cli/fix_old_indexation.php --host=http://myvmoodle.moodlearray.com
"; // TODO: localize - to be translated later when everything is finished

    echo $help;
    exit(0);
}

if (!empty($options['host'])) {
    // Arms the vmoodle switching.
    echo('Arming for '.$options['host']."\n"); // Mtrace not yet available.
    define('CLI_VMOODLE_OVERRIDE', $options['host']);
}

// Replay full config whenever. If vmoodle switch is armed, will switch now config.

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
echo 'Config check : playing for '.$CFG->wwwroot."\n";

if (!empty($options['debug'])) {
    $CFG->debug = E_ALL;
}

require_once($CFG->dirroot.'mod/sharedresource/lib.php');

// Get all plugins.
$plugins = sharedresource_get_plugins();
$config = get_config('sharedresource');

foreach ($plugins as $nmaespace => $plugin) {
    if ($taxumarray = $plugin->getTaxumpath()) {
        $idelement = $taxumarray['id'];

        $select = ' element LIKE ? AND namespace = ?';
        $params = array($idelement.':%', $namespace);
        $metadata = $DB->get_records_select('sharedresource_metadata', $select, $params);

        if ($metadata) {
            foreach ($metadata as $mtd) {
                // If value is scalar.
                if (is_numeric($mtd->value)) {
                    assert(1);
                }
            }
        }
    }
}
