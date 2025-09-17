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
 * Upgrade script for local_labeleditor.
 *
 * @package    local_labeleditor
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade local_labeleditor.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_local_labeleditor_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Moodle v4.5.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2025072000) {
        // Label Editor plugin upgrade to version 2025072000.
        // No database changes required for this version.
        
        upgrade_plugin_savepoint(true, 2025072000, 'local', 'labeleditor');
    }

    return true;
}
