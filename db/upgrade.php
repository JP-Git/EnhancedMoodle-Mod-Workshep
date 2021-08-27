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
 * Keeps track of upgrades to the workshep module
 *
 * @package    mod_workshep
 * @category   upgrade
 * @copyright  2009 David Mudrak <david.mudrak@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Performs upgrade of the database structure and data
 *
 * Workshop supports upgrades from version 1.9.0 and higher only. During 1.9 > 2.0 upgrade,
 * there are significant database changes.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_workshep_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Automatically generated Moodle v3.5.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2016120600) {
        // Add field nosubmissionrequired to the table workshep.
        $table = new xmldb_table('workshep');
        $field = new xmldb_field('nosubmissionrequired', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2016120600, 'workshep');
    }

    if ($oldversion < 2016120601) {
        // Add field autorecalculate to the table workshep.
        $table = new xmldb_table('workshep');
        $field = new xmldb_field('autorecalculate', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2016120601, 'workshep');
    }

    if ($oldversion < 2016120602) {
        // Add field calibration comparison to the table workshep.
        $table = new xmldb_table('workshep');
        $field = new xmldb_field('calibrationcomparison', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add field calibration comparison to the table workshep.
        $table = new xmldb_table('workshep');
        $field = new xmldb_field('calibrationconsistency', XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '0');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2016120602, 'workshep');
    }

    if ($oldversion < 2017081601) {

        $table = new xmldb_table('workshep_calibration');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('workshepid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('score', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null, 'userid');
        $field = new xmldb_field('score', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null, 'userid');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for workshep_calibration
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        } else {
            // Launch change of nullability for field score.
            $dbman->change_field_type($table, $field);
            $dbman->change_field_precision($table, $field);
            $dbman->change_field_notnull($table, $field);
        }

        upgrade_mod_savepoint(true, 2017081601, 'workshep');
    }

    if ($oldversion < 2018062600) {

        // Define field submissiontypetext to be added to workshep.
        $table = new xmldb_table('workshep');
        $field = new xmldb_field('submissiontypetext', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'gradedecimals');

        // Conditionally launch add field submissiontypetext.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('submissiontypefile', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1',
                'submissiontypetext');

        // Conditionally launch add field submissiontypefile.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Convert existing worksheps with attachments disabled to use the new settings.
        $worksheps = $DB->get_records('workshep', ['nattachments' => 0]);
        foreach ($worksheps as $workshep) {
            $update = (object) [
                'id' => $workshep->id,
                'submissiontypefile' => 0,
                'submissiontypetext' => 2,
                'nattachments' => 1
            ];
            $DB->update_record('workshep', $update);
        }

        // Changing the default of field nattachments on table workshep to 1.
        $field = new xmldb_field('nattachments', XMLDB_TYPE_INTEGER, '3', null, null, null, '1', 'submissiontypefile');

        // Launch change of default for field nattachments.
        $dbman->change_field_default($table, $field);

        // Workshop savepoint reached.
        upgrade_mod_savepoint(true, 2018062600, 'workshep');
    }

    // Automatically generated Moodle v3.6.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.7.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.8.0 release upgrade line.
    // Put any upgrade step following this.

    return true;
}
