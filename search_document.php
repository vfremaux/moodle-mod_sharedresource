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
 * Global Search Engine for Moodle (@see local/search). document wrapper for Lucene search engine.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
namespace local_search;

use StdClass;
use context_course;
use context_module;
use context_system;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/search/documents/document.php');
require_once($CFG->dirroot.'/local/search/documents/document_wrapper.class.php');

define('X_SEARCH_TYPE_SHAREDRESOURCE', 'sharedresource');

/**
 * a class for representing searchable information
 */
class SharedresourceSearchDocument extends SearchDocument {

    /**
     * constructor.
     * Context may be system, or category context if resource is category limited
     * @param object $sharedresourceentry
     * @param int $contextid
     */
    public function __construct(& $sharedresourceentry, $contextid) {

        // Generic information; required.
        $doc = new StdClass;
        $doc->docid         = $sharedresourceentry['id'];
        $doc->documenttype  = X_SEARCH_TYPE_SHAREDRESOURCE;
        $doc->itemtype      = 'resource';
        $doc->contextid     = $contextid;

        // We cannot call userdate with relevant locale at indexing time.
        $doc->title         = $sharedresourceentry['title'];
        $doc->date          = $sharedresourceentry['timemodified'];

        // Remove '(ip.ip.ip.ip)' from chat author list.
        $doc->author        = '';
        $doc->contents      = strip_tags($sharedresourceentry['description']);
        $doc->url           = sharedresource_document_wrapper::make_link($sharedresourceentry['identifier']);

        // Module specific information; optional.
        $data = new StdClass;
        $data->metadata = '';

        // Construct the parent class.
        parent::__construct($doc, $data, 0, 0, 0, 'mod/'.X_SEARCH_TYPE_SHAREDRESOURCE);
    }
}

/**
 * the document wrapper itself.
 */
class sharedresource_document_wrapper extends document_wrapper {

    /**
     * constructs a valid link to a page content
     *
     * @param $instanceid the sharedresource course module
     * @return a well formed link to session display
     */
    public static function make_link($instanceid) {
        return new moodle_url('/mod/sharedresource/view.php', ['identifier' => $instanceid]);
    }

    /**
     * part of search engine API
     *
     */
    public static function get_iterator() {
        return [true];
    }

    /**
     * part of search engine API
     * @param object $instance
     */
    public static function get_content_for_index(& $instance) {
        global $DB;

        $sharedresources = $DB->get_records('sharedresource_entry');

        $documents = [];
        foreach ($sharedresources as $sharedresource) {

            if ($sharedresource->context) {
                $context = $DB->get_record('context', ['id' => $sharedresource->context]);
                if (!$context) {
                    mtrace("Failed finding shr context {$sharedresource->context}. Indexing at system level.");
                }
            }

            if (!$context) {
                $context = context_system::instance();
            }

            $sharedresource->authors = '';
            $sharedresourcearr = get_object_vars($sharedresource);
            $documents[] = new SharedresourceSearchDocument($sharedresourcearr, $context->id);
            mtrace("finished sharedresouce entry {$sharedresource->id}");
        }
        return $documents;
    }

    /**
     * returns a single data search document based on a mplayer
     * @param int $id the id of main record that represents this document
     * @param string $itemtype the type of information (page is the only type)
     */
    public static function single_document($id, $itemtype) {
        global $DB;

        $config = get_config('local_search');

        $systemcontext = \context_system::instance();
        $sharedresourceentry = $DB->get_record('sharedresource_entry', ['id' => $id]);

        if ($sharedresourceentry->context) {
            $context = context_helper::get_from_id($sharedresourceentry->context);
        } else {
            $context = $systemcontext;
        }

        $fs = get_file_storage();

        $hasdocument = !$fs->is_area_empty($systemcontext->id, 'mod_sharedresource', 'sharedresource',
                $sharedresourceentry->id, true);

        if ($hasdocument && @$config->enable_file_indexing) {
            $files = $fs->get_area_files($systemcontext->id, 'mod_sharedresource', 'sharedresource',
                    $sharedresourceentry->id, true);
            $file = array_shift($files);
            $void = [];
            $document = search_get_physical_file($void, $file, $sharedresourceentry, 'SharedresourceSearchDocument');
            $document['authors'] = ''; // TODO : Get metadata from entry to index author.
            if (!$document) {
                $mess = "Warning : this document {$sharedresourceentry->identifier}:{$sharedresourceentry->title} ";
                $mess .= 'will not be indexed';
                mtrace($mess);
            }
            return $document;
        } else {
            $sharedresourceentry->authors = ''; // TODO : Get metadata from entry to index author.
            $sharedresourceentryarr = get_object_vars($page);
            $document = new SharedresourceSearchDocument($sharedresourceentryarr, $context->id);
            return $document;
        }
    }

    /**
     * returns the var names needed to build a sql query for addition/deletions
     * // TODO cms indexable records are virtual. Should proceed in a special way
     */
    public static function db_names() {
        // Template: [primary id], [table name], [time created field name], [time modified field name].
        return ['id', 'sharedresource_entry', 'timemodified', 'timemodified', 'resource'];;
    }

    /**
     * this function handles the access policy to contents indexed as searchable documents. If this
     * function does not exist, the search engine assumes access is allowed.
     * When this point is reached, we already know that :
     * - user is legitimate in the surrounding context
     * - user may be guest and guest access is allowed to the module
     * - the function may perform local checks within the module information logic
     * @param string $path the access path to the module script code
     * @param string $itemtype the information subclassing (usefull for complex modules, defaults to 'standard')
     * @param int $thisid the item id within the information class denoted by entry_type. In cms pages, this navi_data id
     * @param object $user the user record denoting the user who searches
     * @param int $groupidunused unused but required by parent implementation
     * @return true if access is allowed, false elsewhere
     *
     * phpcs:disable moodle.Commenting.ValidTags.Invalid
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function check_text_access($path, $itemtype, $thisid, $user, $groupid, $contextidunused) {

        // TODO : apply access check rules on documents to current user.

        $config = get_config('local_sharedresources');

        if (!isloggedin() && $config->privatecatalog) {
            return false;
        }

        return true;
    }
}
