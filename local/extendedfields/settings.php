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
 * Plugin administration pages are defined here.
 *
 * @package     local_extendedfields
 * @category    admin
 * @copyright   2023 M.C.Dean <sheleviy@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_extendedfields_course', "MCDean custom fields settings");

    $settings->add(new admin_setting_heading('local_extendedfields/pluginname', '',
    "MCDean custom fields settings"));

    $settings->add(new admin_setting_configtextarea(
        'local_extendedfields/currencies',
        'Available currencies',
        'Currencies available in Custom Fields section drop down when editing the course page. Separated by new line.',
        "USD\nEUR"
    ));

    $ADMIN->add('courses', $settings);


    $settings = new admin_settingpage('local_extendedfields_user', "MCDean custom fields settings");
    $settings->add(new admin_setting_heading('local_extendedfields/pluginname', '',
    "MCDean custom fields settings"));
    $userfields = ['username', 'email', 'firstname', 'lastname', 'lastaccess', 'ldap_department', 'ldap_class', 'ldap_cn', 'ldap_c', 'ldap_title', 'ldap_delivery_office', 'ldap_telephone_number', 'ldap_display_name', 'ldap_co', 'ldap_country_code', 'ldap_samaccountname', 'ldap_ip_phone', 'ldap_manager', 'ldap_personal_pager', 'ldap_extension_attribute1', 'ldap_extension_attribute11', 'ldap_extension_attribute13', 'ldap_extension_attribute14', 'ldap_extension_attribute5', 'ldap_company', 'ldap_employee_number', 'ldap_employee_type', 'ldap_name', 'ldap_user_principal_name', 'ldap_mail', 'ldap_mobile', 'ldap_thumbnail_photo', 'division', 'company', 'ldap_date_of_hire'];
    $settings->add(new admin_setting_configmultiselect(
        'local_extendedfields/usertableheaders',
        'User management table columns',
        '',
        ['username', 'email', 'firstname', 'lastname', 'lastaccess'],
        array_combine($userfields, $userfields)
    ));

    $ADMIN->add('accounts', $settings);

    $url = $CFG->wwwroot . '/local/extendedfields/pages/users.php';
    $ADMIN->add('accounts', new admin_externalpage('localexternalfields', "MCDean users management", $url));
}
