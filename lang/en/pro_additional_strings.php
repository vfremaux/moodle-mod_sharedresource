<?php

$string['plugindist'] = 'Plugin distribution';
$string['plugindist_desc'] = '
<p>This plugin is the community version and is published for anyone to use as is and check the plugin\'s
core application. A "pro" version of this plugin exists and is distributed under conditions to feed the life cycle, upgrade, documentation
and improvement effort.</p>
<p>Please contact one of our distributors to get "Pro" version support.</p>
<p><a href="http://www.mylearningfactory.com/index.php/documentation/Distributeurs?lang=en_utf8">MyLF Distributors</a></p>';

require_once($CFG->dirroot.'/mod/sharedresource/lib.php'); // to get xx_supports_feature();
if ('pro' == sharedresource_supports_feature()) {
    include($CFG->dirroot.'/mod/sharedresource/pro/lang/en/pro.php');
}
