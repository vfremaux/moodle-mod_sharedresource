<?php

require($CFG->libdir.'/formlib.php');

class classification_form extends moodleform {

    function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'hdr0', get_string('addclassificationtitle', 'sharedresource'), '');
        $mform->addHelpButton('hdr0', 'addclassification', 'sharedreosurce');

        $mform->addElement('text', 'name', get_string('classificationname','sharedresource'), array('size' => 50));
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('checkbox', 'enabled', get_string('classificationenabled','sharedresource'));
        $mform->setType('enabled', PARAM_BOOL);

        $mform->addElement('text', 'tablename', get_string('tablename','sharedresource'), array('size' => 50));
        $mform->setType('tablename', PARAM_TEXT);

        $mform->addElement('header', 'hdr1', get_string('sqlmapping', 'sharedresource'), '');

        $mform->addElement('text', 'sqlid', get_string('idname','sharedresource'), array('size' => 20));
        $mform->setType('sqlid', PARAM_TEXT);
        $mform->setDefault('sqlid', 'id');

        $mform->addElement('text', 'sqlparent', get_string('parentname','sharedresource'), array('size' => 20));
        $mform->setType('sqlparent', PARAM_TEXT);
        $mform->setDefault('sqlparent', 'parent');

        $mform->addElement('text', 'sqllabel', get_string('labelname','sharedresource'), array('size' => 20));
        $mform->setType('sqllabel', PARAM_TEXT);
        $mform->setDefault('sqllabel', 'value');

        $mform->addElement('text', 'sqlsortorder', get_string('orderingname','sharedresource'), array('size' => 20));
        $mform->setType('sqlsortorder', PARAM_TEXT);
        $mform->setDefault('sqlsortorder', 'sortorder');

        $mform->addElement('header', 'hdr2', get_string('sqloptions', 'sharedresource'), '');

        $orderingopts['0'] = '0';
        $orderingopts['1'] = '1';
        $mform->addElement('select', 'sqlsortorderstart', get_string('orderingname','sharedresource'), $orderingopts);
        $mform->setType('sqlsortorderstart', PARAM_INT);
        $mform->setDefault('sqlsortorderstart', '1');

        $mform->addElement('text', 'sqlrestriction', get_string('sqlrestriction','sharedresource'), array('size' => 20));
        $mform->setType('sqlrestriction', PARAM_TEXT);
        $mform->addHelpButton('sqlrestriction', 'sqlrestriction', 'sharedreosurce');

    }

}
