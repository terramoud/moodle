<?php

namespace local_extendedfields;

defined('MOODLE_INTERNAL') || die;


class course_helpers {

    static function get_extended_fields($courseid = null) {
        global $DB;
        if (!$courseid) return [];
        return $DB->get_records('mcd_ef_course_aux', ['course_id' => $courseid]);
    }


    static function get_states() {
        return [
         "AL" => "AL-Alabama", 
         "AK" => "AK-Alaska", 
         "AZ" => "AZ-Arizona", 
         "AR" => "AR-Arkansas", 
         "CA" => "CA-California", 
         "CO" => "CO-Colorado", 
         "CT" => "CT-Connecticut", 
         "DE" => "DE-Delaware", 
         "FL" => "FL-Florida", 
         "GA" => "GA-Georgia", 
         "HI" => "HI-Hawaii", 
         "ID" => "ID-Idaho", 
         "IL" => "IL-Illinois", 
         "IN" => "IN-Indiana", 
         "IA" => "IA-Iowa", 
         "KS" => "KS-Kansas", 
         "KY" => "KY-Kentucky", 
         "LA" => "LA-Louisiana", 
         "ME" => "ME-Maine", 
         "MD" => "MD-Maryland", 
         "MA" => "MA-Massachusetts", 
         "MI" => "MI-Michigan", 
         "MN" => "MN-Minnesota", 
         "MS" => "MS-Mississippi", 
         "MO" => "MO-Missouri", 
         "MT" => "MT-Montana", 
         "NE" => "NE-Nebraska", 
         "NV" => "NV-Nevada", 
         "NH" => "NH-New Hampshire", 
         "NJ" => "NJ-New Jersey", 
         "NM" => "NM-New Mexico", 
         "NY" => "NY-New York", 
         "NC" => "NC-North Carolina", 
         "ND" => "ND-North Dakota", 
         "OH" => "OH-Ohio", 
         "OK" => "OK-Oklahoma", 
         "OR" => "OR-Oregon", 
         "PA" => "PA-Pennsylvania", 
         "RI" => "RI-Rhode Island", 
         "SC" => "SC-South Carolina", 
         "SD" => "SD-South Dakota", 
         "TN" => "TN-Tennessee", 
         "TX" => "TX-Texas", 
         "UT" => "UT-Utah", 
         "VT" => "VT-Vermont", 
         "VA" => "VA-Virginia", 
         "WA" => "WA-Washington", 
         "WV" => "WV-West Virginia", 
         "WI" => "WI-Wisconsin", 
         "WY" => "WY-Wyoming" 
     ];
 }


    


    static function save_extended_fields($courseid, $formdata) {
        global $DB;

        $record_exists = $DB->record_exists('mcd_ef_course_aux', ['course_id' => $courseid]);
        $formdata['internal_instructors'] = implode(', ', array_values($formdata['internal_instructors']));
        $formdata['course_id'] = $courseid;

        // take care of external table data
        if ($formdata['vendor_id'] && !is_numeric($formdata['vendor_id'])) {
            // create new vendor
            $insert = new \StdClass;
            $insert->vendor = $formdata['vendor_id'];
            $formdata['vendor_id'] = $DB->insert_record('mcd_ef_vendors', $insert);
        }

        if ($formdata['location_id'] && !is_numeric($formdata['location_id'])) {
            // create new location
            $insert = new \StdClass;
            $insert->location = $formdata['location_id'];
            $formdata['location_id'] = $DB->insert_record('mcd_ef_locations', $insert);
        }

        if ($formdata['location_address_id'] && !is_numeric($formdata['location_address_id'])) {
            // create new location address
            $insert = new \StdClass;
            $insert->location_address = $formdata['location_address_id'];
            $formdata['location_address_id'] = $DB->insert_record('mcd_ef_location_addr', $insert);
        }

        if ($record_exists) {
            // update course_aux table if custom data exists for this course
            $dbrecord = (array)$DB->get_record('mcd_ef_course_aux', ['course_id' => $courseid]);
            $record = array_replace($dbrecord, $formdata);
            $DB->update_record('mcd_ef_course_aux', $record);
        } else {
            // create new course_aux entry for new course
            $record = $formdata;
            $DB->insert_record('mcd_ef_course_aux', $record);
        }
    }
}
