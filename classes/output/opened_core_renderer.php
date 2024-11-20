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
 * This is an overriden core renderer to open some protected methods.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/*
 * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
 */
namespace mod_sharedresource\output;

use core_renderer;
use tabtree;

/**
 *
 */
class opened_core_renderer extends core_renderer {

    /**
     * Renders a tabtree. We changed the privacy.
     * @param tabtree $tabs
     */
    public function render_tabtree(tabtree $tabs) {
        return parent::render_tabtree($tabs);
    }

}
