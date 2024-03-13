<?php

/**
 * Loads classes before anything is known.
 *
 */
define('SHAREDRESOURCE_INTERNAL', true);

$currentdir = dirname(__FILE__);
$classes = glob($currentdir.'/sharedresource*');
foreach ($classes as $classfile) {
    include_once($classfile);
}