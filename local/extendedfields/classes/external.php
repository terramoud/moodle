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
            SELECT ul.id, l.name, lt.type as type, DATE_FORMAT(FROM_UNIXTIME(ul.date_received), '%Y-%m-%d') as date_received, DATE_FORMAT(FROM_UNIXTIME(ul.expiration_date), '%Y-%m-%d') as expiration_date, ul.license_id, ul.itemid, ul.id_number, ul.ceu_pdu, ul.state 
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

    /**
     * Provides information about the parameters for the remove_license function.
     *
     * @return external_function_parameters Returns an external function parameters object
     *   specifying the parameter types and descriptions.
     */
    public static function remove_license_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'The ID of the license to be removed.', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Provides information about the return type for the remove_license function.
     *
     * @return external_value Returns a JSON data representing the result of removing a license.
     *   The format of the JSON: {"message": "Success/Error message", "id": ID}
     */
    public static function remove_license_returns() {
        return new external_value(PARAM_RAW, 'JSON data representing the result of removing a license');
    }

    /**
     * Remove a license.
     *
     * @param int $id The ID of the license to be removed.
     *
     * @return string JSON data representing the result of removing a license.
     *   Format: {"message": "Success/Error message", "id": ID}
     *
     * @global moodle_database $DB
     */
    public static function remove_license($id) {
        global $DB, $CFG;
        // Validate license ID (should be an integer)
        if (!is_numeric($id)) {
            return json_encode(['message' => 'Invalid license ID', 'id' => $id]);
        }
        try {
            $returnData = $DB->get_record_sql("
                SELECT ul.itemid
                FROM {mcdean_license_user} ul
                WHERE ul.id = :id
            ", ['id' => $id]);
            $licenseFileName = $returnData ? $returnData->itemid : 0;
            $destination = "$CFG->dataroot/Licenses/$id/$licenseFileName";
            // Attempt to remove the file
            if (file_exists($destination) && !unlink($destination)) {
                return json_encode(['message' => 'Failed to remove the license file', 'id' => $id]);
            }
            if ($DB->delete_records('mcdean_license_user', array('id' => $id))) {
                return json_encode(['message' => 'License removed successfully.', 'id' => $id]);
                //TODO: invert if condition
            }
            return json_encode(['message' => 'An error occurred while removing license.', 'id' => $id]);
        } catch (dml_exception $e) {
            return json_encode(['message' => $e->getMessage(), 'id' => $id]);
        }
    }

    /**
     * Provides information about the parameters for removing a license file.
     *
     * @return external_function_parameters Returns an external function parameters object
     *   specifying the parameter types and descriptions.
     */
    public static function remove_license_file_parameters() {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'The ID of license for which the file needs to be removed', VALUE_REQUIRED),
            )
        );
    }

    /**
     * Provides information about the return type for removing a license file.
     *
     * @return external_value Returns a JSON data representing the result of removing a license file.
     *   The format of the JSON: {"message": "Success/Error message", "id": ID}
     */
    public static function remove_license_file_returns() {
        return new external_value(PARAM_RAW, 'JSON data representing the result of removing a license file');
    }

    /**
     * Remove the file associated with a license.
     *
     * @param int $id The ID of the license for which the file needs to be removed.
     *
     * @return string JSON data representing the result of removing a license file.
     *   Format: {"message": "Success/Error message", "id": ID}
     *
     * @global moodle_database $DB
     * @global object $CFG
     */
    public static function remove_license_file($id) {
        global $DB, $CFG;
        // Validate license ID (should be an integer)
        if (!is_numeric($id)) {
            return json_encode(['message' => 'Invalid license ID', 'id' => $id]);
        }
        try {
            $returnData = $DB->get_record_sql("
                SELECT ul.itemid
                FROM {mcdean_license_user} ul
                WHERE ul.id = :id
            ", ['id' => $id]);
            $licenseFileName = $returnData ? $returnData->itemid : 0;
            $destination = "$CFG->dataroot/Licenses/$id/$licenseFileName";
            // Attempt to remove the file
            if (file_exists($destination) && !unlink($destination)) {
                return json_encode(['message' => 'Failed to remove the license file', 'id' => $id]);
            }
            if (!$DB->update_record('mcdean_license_user', (object)['id' => $id, 'itemid' => null])) {
                return json_encode(['message' => 'Failed to update license file name from db', 'id' => $id]);
            }
            return json_encode(['message' => 'License file removed successfully.', 'id' => $id]);
        } catch (dml_exception $e) {
            return json_encode(['message' => $e->getMessage(), 'id' => $id]);
        }
    }
}