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
 * User profile page with various forms.
 *
 * @package     local_extendedfields
 * @category    pages
 * @copyright   2023 M.C.Dean <sheleviy@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
$uid = optional_param('id', $USER->id, PARAM_INT);
require_login();

$title = 'MCDean - User profile';
$PAGE->set_url('/local/extendedprofiles/pages/user_profile.php', ['id' => $uid]);
$PAGE->set_context(context_system::instance());
// $PAGE->set_context(context_user::instance($uid));

$PAGE->set_title($title);
$PAGE->requires->jquery();
$PAGE->requires->js_call_amd('local_extendedfields/dynamicforms', 'initlicense', ['wwwroot' => $CFG->wwwroot]);
$PAGE->set_pagelayout('admin');
echo $OUTPUT->header();

$user = $DB->get_record_sql("
	SELECT *, u.id as id
	FROM {user} u
	LEFT JOIN {mcd_ef_user_aux} ua ON ua.user_id = u.id
	WHERE u.id = :userid
	", ['userid' => $uid], MUST_EXIST);

$strings = ['initial_cost', 'name', 'organization', 'renewal_cost', 'website', 'ceu_pdu_required', 'application_testing_process', 'expiration_period', 'type', 'subtype'];
$strings_object = new \StdClass;
foreach ($strings as $string) {
	$strings_object->$string = get_string($string, 'local_extendedfields');
}


// getting the user object by userid.
$conditions = array('size' => '180', 'link' => false, 'class' => 'profile');
$person_profile_pic = $OUTPUT->user_picture($user, $conditions);

$licenses = $DB->get_records_sql("
	SELECT l.id, l.initial_cost, l.name, l.organization, l.renewal_cost, l.website, l.ceu_pdu_required, l.application_testing_process, l.expiration_period, lt.type as type, lst.subtype as subtype
	FROM {mcdean_license} l
	LEFT JOIN {mcdean_license_type} lt ON lt.id = l.license_type
	LEFT JOIN {mcdean_license_subtype} lst ON lst.id = l.license_subtype
	WHERE l.deleted_status = 0
	");

$user_licenses = $DB->get_records_sql("
	SELECT ul.id, l.name, lt.type as type, ul.date_received, ul.expiration_date, ul.license_id, ul.itemid, ul.id_number, ul.ceu_pdu, ul.state
	FROM {mcdean_license_user} ul
	LEFT JOIN {mcdean_license} l ON l.id = ul.license_id
	LEFT JOIN {mcdean_license_type} lt ON lt.id = l.license_type
	WHERE l.deleted_status = 0
	AND ul.userid = :userid
	", ['userid' => $uid]);

$license_options = "";
foreach ($licenses as $l) {
	$license_options .= "<option value='".$l->id."'>".$l->name."</option>";
}

$state_options = "";
foreach (local_extendedfields\course_helpers::get_states() as $code => $state) {
	$state_options .= "<option value='".$code."'>".$state."</option>";
}

?>



<div class="container-fluid">
	<div class="container">
		<div class="row">
			<div class="col-12 main-breadcrumb"> <a href="<?= $CFG->wwwroot ?>/my/"> <strong>Dashboard</strong></a>&nbsp;&nbsp;&gt;&nbsp;&nbsp;Profile </div>
		</div>
	</div>
</div>
<div class="container-fluid">
	<div class="container">
		<div class="row">
			<div class="col-12 employee-name"><?php echo $user->firstname . " " . $user->middlename . " " . $user->lastname; ?></div>
		</div>
	</div>
</div>
<div class="container-fluid">
	<div id="profile-info">
		<div class="container">
			<div class="row">
				<div class="col-12 col-md-3 col-lg-2">
					<?php if (empty(strip_tags($person_profile_pic))) {
						echo $person_profile_pic;
					} else { ?>
						<img src="../pix/default-profile-pic.png" class="profile profile-default" alt="Profile Image">
					<?php } ?>
				</div>
				<div class="col-12 col-md-9 col-lg-7">
					<div class="row">
						<div class="col-4 col-md-3 col-lg-3">
							<div class="profile-label">Company</div>
						</div>
						<div class="col-8 col-md-9 col-lg-9">
							<div class="profile-content"><?php echo $user->ldap_company; ?></div>
						</div>
					</div>
					<div class="row">
						<div class="col-4 col-md-3 col-lg-3">
							<div class="profile-label">Employee #</div>
						</div>
						<div class="col-8 col-md-9 col-lg-9">
							<div class="profile-content"><?php echo $user->ldap_employee_number; ?></div>
						</div>
					</div>
					<div class="row">
						<div class="col-4 col-md-3 col-lg-3">
							<div class="profile-label">Department</div>
						</div>
						<div class="col-8 col-md-9 col-lg-9">
							<div class="profile-content"><?php echo $user->ldap_department; ?></div>
						</div>
					</div>
					<div class="row">
						<div class="col-4 col-md-3 col-lg-3">
							<div class="profile-label">Title</div>
						</div>
						<div class="col-8 col-md-9 col-lg-9">
							<div class="profile-content"><?php echo $user->ldap_title; ?></div>
						</div>
					</div>
					<div class="row">
						<div class="col-4 col-md-3 col-lg-3">
							<div class="profile-label">Supervisor</div>
						</div>
						<div class="col-8 col-md-9 col-lg-9">
							<div class="profile-content"><?php echo $user->ldap_manager; ?></div>
						</div>
					</div>
					<div class="row">
						<div class="col-4 col-md-3 col-lg-3">
							<div class="profile-label">MCDU Username</div>
						</div>
						<div class="col-8 col-md-9 col-lg-9">
							<div class="profile-content"><?php echo $user->firstname . " " . $user->middlename . " " . $user->lastname; ?></div>
						</div>
					</div>
					<div class="row">
						<div class="col-4 col-md-3 col-lg-3">
							<div class="profile-label">Last Access</div>
						</div>
						<div class="col-8 col-md-9 col-lg-9">
							<div class="profile-content"><?php echo date("m/d/Y H:i", $user->lastaccess) ?></div>
						</div>
					</div>
					<div class="row">
						<div class="col-4 col-md-3 col-lg-3">
							<div class="profile-label">Email</div>
						</div>
						<div class="col-8 col-md-9 col-lg-9">
							<div class="profile-content"><?php echo $user->email; ?></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>



<!-- List of licenses -->
<div class="container-fluid mt-5">
	<div class="container">
		<h2>Licenses/Certificates</h2>
		<?php
		$license_columns = ['id' => '#', 'type' => 'Type', 'name' => 'Name', 'date_received' => 'Date received', 'expiration_date' => 'Expiration date', 'actions' => 'Actions'];
		$table = new html_table();
		$table->head = array_values($license_columns);
		$table->id = "license_list";
		$table->attributes = ['data-sortable' => '', 'class' => 'table table-hover sortable-theme-bootstrap'];
		foreach ($user_licenses as $l) {
			$row = new html_table_row();
			$row->attributes = ['data-license-id' => $l->id, 'data-license-json' => json_encode($l)];
			foreach ($license_columns as $c => $column) {
			// var_dump([$c, $column]); die;
				if ($c == "actions") {
					$row->cells[] = $OUTPUT->pix_icon('i/edit', 'Edit license', 'moodle', ['class' => 'license-edit-icon']) .
					$OUTPUT->pix_icon('e/cancel', 'Remove license', 'moodle', ['class' => 'license-remove-icon']);
				} elseif (in_array($c, ['date_received', 'expiration_date'])) {
					$row->cells[] = $l->$c ? date("m/d/Y", (int)$l->$c) : "-";
				} elseif ($c == "name" && $l->itemid) {
					$row->cells[] = $l->$c . "<br>" .
					html_writer::link(
						new moodle_url($CFG->wwwroot.'/local/extendedfields/image.php', array('file' => 'Licenses/' . $l->id . '/' . $l->itemid)),
						$OUTPUT->pix_icon('attachment', 'Attachment', 'mod_wiki') . "Attachment"
					);
				} else {
					$row->cells[] = $l->$c;
				}
			}
			$table->data[] = $row;
		}
		echo html_writer::table($table);
		?>
	</div>
</div>

<!-- New license button -->
<div class="container-fluid">
	<div class="container">
		<button id="showFormBtn" class="btn btn-primary">Add License</button>
	</div>
</div>


<!-- License form -->
<div class="container-fluid ajax-form licence-form">
	<div class="container">
		<form action="<?= $CFG->wwwroot ?>/webservice/rest/server.php" id="licenseForm" enctype="multipart/form-data">
			<input type="hidden" name="user_license_id" id="user_license_id" value="">
			<div class="row mb-2">
				<div class="col-md-3 form-label">ID Number:</div>
				<div class="col-md-9"><input type="text" name="id_number" class="form-control"></div>
			</div>

			<div class="row mb-2">
				<div class="col-md-3 form-label">License name:</div>
				<div class="col-md-9">
					<select name="license_id" id="license_selector" class="selectable form-control" width="100%"><?= $license_options ?></select>
					<div class="license-properties border p-2 mt-2" id="license-properties">

					</div>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-md-3 form-label">State:</div>
				<div class="col-md-9">
					<select name="state" class="form-control"><?= $state_options ?></select>
				</div>
			</div>

			<div id="upload-image" class="row mb-2">
				<div class="col-md-3 form-label">Upload image:</div>
				<div class="col-md-9">
					<label class="file">
						<input type="file" id="file" aria-label="File upload" name="license_image" accept=".jpg, .jpeg, .png, .gif">
						<span class="file-custom"></span>
					</label>
					<progress class="progress" id="fileProgressBar" value="0" max="100" style="width:100%"></progress>
				</div>
			</div>

			<div class="row">
				<div class="col-md-3 attachment-container">
					<a href="#">
						<i class="icon fa fa-paperclip fa-fw " title="Attachment" role="img" aria-label="Attachment"></i>
					</a>
					<button id="remove-attachment" type="button" class="btn btn-sm btn-danger">Remove</button>
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-md-3 form-label">Date received:</div>
				<div class="col-md-9">
					<input type="date" name="date_received" class="dateselector form-control">
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-md-3 form-label">Expiration date:</div>
				<div class="col-md-9">
					<input type="date" name="expiration_date" class="dateselector form-control">
				</div>
			</div>

			<div class="row mb-2">
				<div class="col-md-3 form-label">CEU PDU:</div>
				<div class="col-md-9"><input type="text" name="ceu_pdu" class="form-control"></div>
			</div>
			<div class="row">
				<div class="col-md-12 text-right">
					<button class="btn btn-secondary btn-outline" id="cancelBtn">Cancel</button>
					<input type="submit" class="btn btn-default" value="Save">
				</div>
			</div>
		</form>
	</div>


</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="<?= $CFG->wwwroot ?>/local/extendedfields/libs/wtf-forms.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<link href="<?= $CFG->wwwroot ?>/local/extendedfields/css/user_profile.css" rel="stylesheet" />


<script>
	$(document).ready(function() {
		var sesskey = '<?= sesskey() ?>';
		var licenseobject = JSON.parse('<?= json_encode($licenses) ?>');
		var license_properties = JSON.parse('<?= json_encode($strings_object) ?>');


		$('#license_selector').on("change", function (e) {
			updateLicenseProperties()
		});

		function updateLicenseProperties() {
			sel = $('#license_selector').val()
			$("#license-properties").html("<table>");
			for (var i in license_properties) {
				$("#license-properties").append("<tr><td>"+license_properties[i]+":</td><td>"+licenseobject[sel][i]+"</td></tr>");
			}
			$("#license-properties").append("</table>");
		}

	    // flatpickr(".dateselector", {
	    // 	altInput: true,
	    // 	altFormat: "F j, Y",
	    // 	dateFormat: "U",
	    // 	allowInput: true,
	    // });


	    $('#license_selector').select2({
	    	width: '100%'
	    });
	    updateLicenseProperties();

	});
</script>


<?php
echo $OUTPUT->footer();
?>


