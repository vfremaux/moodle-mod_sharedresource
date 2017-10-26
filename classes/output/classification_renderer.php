<?php

namespace mod_sharedresource\output;

defined('MOODLE_INTERNAL', true);

require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');

class classification_renderer extends \plugin_base_renderer {

    function classifications($classifications) {
        global $OUTPUT;

        $template = new StdClass;

        $template->strdelete = get_string('delete');
        $template->stredit = get_string('edit');
        $template->strenable = get_string('enable');
        $template->strdisable = get_string('disable');

        $template->deleteiconurl = $OUTPUT->pix_url('t/delete');
        $template->editiconurl = $OUTPUT->pix_url('t/edit');
        $template->enablediconurl = $OUTPUT->pix_url('t/show');
        $template->disablediconurl = $OUTPUT->pix_url('t/hide');

        foreach ($classiications as $classification) {
            $classification->isdeletable = sharedresource_uses_taxonomy($classification);
        }

        $this->render_from_template('mod_sharedresource/classifications', $classifications);
    }

}