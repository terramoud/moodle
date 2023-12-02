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

$functions = array(
    'local_extendedfields_upload_license' => array(
        'classname'   => 'local_extendedfields_external',
        'methodname'  => 'upload_license',
        'classpath'   => 'local/extendedfields/classes/external.php',
        'description' => 'add/edit license external service',
        'type'        => 'write',
        'ajax'        => true,
    )
);
