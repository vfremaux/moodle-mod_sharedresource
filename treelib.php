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
 * Library of tree dedicated operations.
 *
 * @package     mod_sharedresource
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @author      LUU Tao Meng, So Gerard (parts of treelib.php), Guillaume Magnien, Olivier Petit
 * @copyright   Valery Fremaux  (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

/**
 * deletes into tree a full branch. note that it will work either
 * @param int $id the root node id
 * @param string $table the table where the tree is in
 * @param boolean $istree if istree is not set, considers table as a simple ordered list
 * @return an array of deleted ids
 */
function sharedresource_tree_delete($id, $table, $istree = 1) {
    sharedresource_tree_updatesortorder($id, $table, $istree);
    return sharedresource_tree_delete_rec($id, $table, $istree);
}

/**
 * deletes recursively a node and its subnodes. this is the recursion deletion
 * @param int $id
 * @param object $table
 * @param bool $istree
 * @param string $predeletecallback
 * @param string $postdeletecallback
 * @return an array of deleted ids
 */
function sharedresource_tree_delete_rec($id, $table, $istree, $predeletecallback = '', $postdeletecallback = '') {
    global $DB;

    $deleted = [];
    if (empty($id)) {
        return $deleted;
    }

    // Getting all subnodes to delete if is tree.
    if ($istree) {
        $sql = "
            SELECT
                id,id
            FROM
                {{$table}}
            WHERE
                parent = {$id}
        ";
        // Deleting subnodes if any.
        if ($subs = $DB->get_records_sql($sql)) {
            foreach ($subs as $asub) {
                if (!empty($predeletecallback)) {
                    $predeletecallback($asub);
                }
                $deleted = array_merge($deleted, tree_delete_rec($asub->id, $table, $istree));
                if (!empty($postdeletecallback)) {
                    $postdeletecallback($asub);
                }
            }
        }
    }
    // Deleting current node.
    $DB->delete_records($table, ['id' => $id]);
    $deleted[] = $id;
    return $deleted;
}

/**
 * raises a node in the tree, resortorder all what needed
 * @param int $id the id of the raised node
 * @param object $classif classification
 * @return void
 */
function sharedresource_tree_up($id, $classif) {
    global $DB;

    $res = $DB->get_record('sharedresource_taxonomy', ['id' => $id]);
    if (!$res) {
        return;
    }

    if ($res->sortorder > $classif->sqlsortorderstart) {
        $result = false;
        $newsortorder = $res->sortorder - 1;
        $select = " classificationid = ? AND sortorder = ? AND parent = ? ORDER BY sortorder";
        $params = [$res->classificationid, $newsortorder, $res->parent];
        if ($resid = $DB->get_field_select('sharedresource_taxonomy', 'id', $select, $params)) {
            // Swapping.
            $object = new StdClass();
            $object->id = $resid;
            $object->sortorder = $res->sortorder;
            $DB->update_record('sharedresource_taxonomy', $object);
        }

        $object = new StdClass();
        $object->id = $id;
        $object->sortorder = $newsortorder;
        $DB->update_record('sharedresource_taxonomy', $object);
    }
}

/**
 * lowers a node on its branch. this is done by swapping sortorder.
 * @param int $id the node id
s * @param boolean $istree if not set, performs swapping on a single list
 */
function sharedresource_tree_down($id) {
    global $DB;

    $res = $DB->get_record('sharedresource_taxonomy', ['id' => $id]);

    $select = " parent = ?  AND classificationid = ? ";
    $params = [$res->parent, $res->classificationid];
    $maxsortorder = $DB->get_field_select('sharedresource_taxonomy', " MAX(sortorder) ", $select, $params);

    if ($res->sortorder < $maxsortorder) {
        $newsortorder = $res->sortorder + 1;
        $select = " classificationid = ? AND sortorder = ? AND parent = ? ";
        $params = [$res->classificationid, $newsortorder, $res->parent];
        if ($resid = $DB->get_field_select('sharedresource_taxonomy', 'id', $select, $params)) {
            // Swapping.
            $object = new StdClass;
            $object->id = $resid;
            $object->sortorder = $res->sortorder;
            $DB->update_record('sharedresource_taxonomy', $object);
        }

        $object = new StdClass;
        $object->id = $id;
        $object->sortorder = $newsortorder;
        $DB->update_record('sharedresource_taxonomy', $object);
    }
}
