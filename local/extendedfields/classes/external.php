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
 * External functions.
 *
 * @package     local_extendedfields
 * @category    external
 * @copyright   2023 M.C.Dean <sheleviy@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;


class local_extendedfields_external extends external_api {


    public static function upload_license_parameters() {
        return new external_function_parameters (
            array(
                'jsonformdata' => new external_value(PARAM_RAW, 'JSON form data')
            )
        );
    }


    public static function upload_license($jsonformdata) {
        global $CFG, $USER, $DB;

        $formdata = json_decode($jsonformdata);

        $arrayObject = array(
            "id_number" => $formdata->id_number,
            "license_id" => $formdata->license_id,
            "state" => $formdata->state,
            "date_received" => strtotime($formdata->date_received),
            "expiration_date" => strtotime($formdata->expiration_date),
            "ceu_pdu" => $formdata->ceu_pdu,
            "userid" => $USER->id,
            "createddate" => time()
        );

        if ($formdata->user_license_id > 0) {
            $arrayObject['id'] = $id = $formdata->user_license_id;
            $DB->update_record('mcdean_license_user', (object)$arrayObject);
        } else {
            $id = $DB->insert_record('mcdean_license_user', (object)$arrayObject);
        }


        $returnData = $DB->get_records_sql("
            SELECT ul.id, l.name, lt.type as type, DATE_FORMAT(FROM_UNIXTIME(ul.date_received), '%Y-%m-%d') as date_received, DATE_FORMAT(FROM_UNIXTIME(ul.expiration_date), '%Y-%m-%d') as expiration_date, ul.license_id 
            FROM {mcdean_license_user} ul
            LEFT JOIN {mcdean_license} l ON l.id = ul.license_id
            LEFT JOIN {mcdean_license_type} lt ON lt.id = l.license_type
            WHERE l.deleted_status = 0
            AND ul.id = :id
            ", ['id' => $id]);

        return json_encode(array_values($returnData)[0]);
    }

    /**
     * Describes the get_user_badges return value.
     *
     * @return external_single_structure
     * @since Moodle 3.1
     */
    public static function upload_license_returns() {
        return new external_value(PARAM_RAW, 'json data');
    }
}
