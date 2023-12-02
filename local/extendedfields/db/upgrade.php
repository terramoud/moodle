<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin upgrade steps are defined here.
 *
 * @package     local_extendedfields
 * @category    upgrade
 * @copyright   2023 M.C.Dean <sheleviy@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute local_extendedfields upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_extendedfields_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023112900) {
        // Add table for additional user profile fields.
        $table = new xmldb_table('mcd_ef_user_aux');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // for now it's all text fields
        $table->add_field('ldap_department', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_class', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_cn', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_c', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_title', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_delivery_office', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_telephone_number', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_display_name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_co', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_country_code', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_samaccountname', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_ip_phone', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_manager', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_personal_pager', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_extension_attribute', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_company', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_employee_number', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_employee_type', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_user_principal_name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_mail', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_mobile', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_thumbnail_photo', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('division', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('company', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('ldap_date_of_hire', XMLDB_TYPE_CHAR, '255', null, null, null, null);

        // Adding keys to table mcd_ef_user_aux.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid_key', XMLDB_KEY_FOREIGN, ['user_id'], 'user', ['id']);
        $table->add_key('unique_key', XMLDB_KEY_UNIQUE, ['user_id']);

        // Conditionally launch create table for mcd_ef_user_aux.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // local_extendedfields savepoint reached.
        upgrade_plugin_savepoint(true, 2023112900, 'local', 'extendedfields');
    }

    return true;
}
