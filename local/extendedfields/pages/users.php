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
 * Users management page.
 *
 * @package     local_extendedfields
 * @category    pages
 * @copyright   2023 M.C.Dean <sheleviy@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once("{$CFG->libdir}/adminlib.php");
admin_externalpage_setup('editusers');
$title = 'MCDean - User management';
$PAGE->set_title($title);
$PAGE->requires->jquery();
$PAGE->set_pagelayout('admin');
echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$users = $DB->get_records_sql("
	SELECT *, u.id as id FROM {user} u
	LEFT JOIN {mcd_ef_user_aux} ua ON ua.user_id = u.id
	ORDER BY u.id desc
	");
$columns = explode(",", get_config('local_extendedfields', 'usertableheaders'));
$table = new html_table();
$table->head = $columns;
$table->id = "usersmanagementtable";
$table->attributes = ['data-sortable' => '', 'class' => 'table table-hover sortable-theme-bootstrap'];
foreach ($users as $user) {
	$row = [];
	foreach ($columns as $i => $column) {
		if ($i == 0) {
			$row[] = html_writer::link(
				new moodle_url($CFG->wwwroot.'/local/extendedfields/pages/user_profile.php', array('id' => $user->id)),
				$user->$column
			);
		} else {
			$row[] = $user->$column;
		}
	}
	$table->data[] = $row;
}
echo html_writer::table($table);

?>
<script src="https://unpkg.com/tablefilter@0.7.2/dist/tablefilter/tablefilter.js"></script>

<script>

	var filtersConfig = {
	    base_path: '<?= $CFG->wwwroot ?>/local/extendedfields/libs/tablefilter/',
	    alternate_rows: true,
	    single_filter: true,
	    auto_filter: {
            delay: 500
        },
        extensions:[{
        	name: 'sort'
        }],
        // paging: {
        // 	results_per_page: ['Records: ', [10, 25, 50, 100]]
        // },
        state: {
        	types: ['local_storage'],
        	filters: true,
        	page_number: true,
        	page_length: true,
        	sort: true
        },
	};
	tf = new TableFilter('usersmanagementtable', filtersConfig);
	tf.init();
	// tf.setFilterValue(6, '!–');
	// tf.filter();
</script>

<?php 
echo $OUTPUT->footer();

