define(['jquery', 'core/ajax'], function($, Ajax) {

	var attemptButton = $(".singlebutton.quizstartbuttondiv button[type='submit']")
	attemptButton.prop('disabled', true)
	var initlicense = function(wwwroot) {
		var fileItem

		$('#showFormBtn').on('click', showForm);
		$('#cancelBtn').on('click', hideForm);
		$('.license-edit-icon').on('click', hideForm);
		$('.license-edit-icon').click(function(){
			licenseData = JSON.parse($(this).parents('tr').attr("data-license-json"))
			console.log(licenseData)
			showForm();
			editForm(licenseData);

		})

		$("#licenseForm").submit(function(e) {
			e.preventDefault();

			var form = document.getElementById('licenseForm');
			var formData = new FormData(form);
			fileItem = formData.get('license_image');
			formData.delete("license_image");
			var object = {};
			formData.forEach((value, key) => object[key] = value);
			var json = JSON.stringify(object);


			Ajax.call([{
				methodname: 'local_extendedfields_upload_license',
				args: {jsonformdata: json},
				done: function(response) {
					responseObj = JSON.parse(response)
					
					if ($("#file").val()) {
						newFormData = new FormData();
						newFormData.append('license_id', responseObj.id)
						newFormData.append('file', fileItem)
						var xhr = new XMLHttpRequest();
						xhr.open('POST', wwwroot+"/local/extendedfields/classes/upload_handler.php", true);

						xhr.upload.addEventListener('progress', function(event) {
							if (event.lengthComputable) {
								var percentComplete = (event.loaded / event.total) * 100;
								updateProgressBar(percentComplete);
							}
						});

						xhr.onreadystatechange = function() {
							if (xhr.readyState === 4) {
								if (xhr.status === 200) {
									// alert('Form submitted successfully!');
								} else {
									alert('Error submitting form. Please try again.');
								}
								resetProgressBar();
							}
						};

						xhr.send(newFormData);
					}
					addRow(responseObj);
					hideForm();
				},
				fail: function(response) {
					alert(response)
				}
			}]);
		});

		function updateProgressBar(percent) {
			var progressBar = document.getElementById('fileProgressBar');
			progressBar.setAttribute("value", percent);
			progressBar.innerHTML = percent.toFixed(2) + '%';
		}

		function resetProgressBar() {
			var progressBar = document.getElementById('fileProgressBar');
			progressBar.setAttribute("value", 0);
			progressBar.innerHTML = '0%';
		}

		function formatDate(timestamp) {
			var date = new Date(timestamp * 1000);
			var month = date.getMonth() + 1;
			var day = date.getDate();
			var year = date.getFullYear();
			return year + '-' + month + '-' + day;
		}

		function convertDateFormat(inputDate) {
		    var parts = inputDate.split("-");
		    return parts[1] + "/" + parts[2] + "/" + parts[0];
		}

		function clearForm()  {
			$(':input','#licenseForm')
			  .not(':button, :submit, :reset')
			  .val('')
			  .prop('checked', false)
			  .prop('selected', false);
		}

		function removeRowByLicenseId(licenseId) {
		    var rowToRemove = document.querySelector('[data-license-id="' + licenseId + '"]');
		    if (rowToRemove) {
		        rowToRemove.remove();
		    }
		}

		function addRow(data) {
			response = JSON.stringify(data)
			var newRow = '<tr data-license-id="' + data.id + '" data-license-json="'+response+'">'
			+ '<td class="cell c0" style="">' + data.id + '</td>'
			+ '<td class="cell c1" style="">' + data.type + '</td>'
			+ '<td class="cell c2" style="">' + data.name + '</td>'
			+ '<td class="cell c3" style="">' + convertDateFormat(data.date_received) + '</td>'
			+ '<td class="cell c4" style="">' + convertDateFormat(data.expiration_date) + '</td>'
			+ '<td class="cell c5 lastcol" style=""><i class="icon fa fa-pencil fa-fw license-edit-icon" title="Edit license" role="img" aria-label="Edit license"></i><i class="icon fa fa-times fa-fw license-edit-icon" title="Remove license" role="img" aria-label="Remove license"></i></td>'
			+ '</tr>';

			if ($("#user_license_id").val()) {
				removeRowByLicenseId($("#user_license_id").val())
			}

	        // Append the new row to the table body
	        $('#license_list tbody').append(newRow);
	    }

	    function editForm(data) {
	    	console.log([data.license_id, formatDate(data.date_received), data.date_received])
	    	$('#licenseForm [name="id_number"]').val(data.id_number);
	    	$('#license_selector').val(parseInt(data.license_id));
	    	$('#license_selector').trigger('change');

	    	$('#user_license_id').val(data.id);
	    	$('#licenseForm [name="state"]').val(data.state);
	    	$('#licenseForm [name="date_received"]').val(formatDate(data.date_received));
	    	$('#licenseForm [name="expiration_date"]').val(formatDate(data.expiration_date));
	    	$('#licenseForm [name="ceu_pdu"]').val(data.ceu_pdu);
	    }

        function showForm() {
            $('.ajax-form.licence-form').show();
            $('#showFormBtn').hide();
        }

        function hideForm() {
            $('.ajax-form.licence-form').hide();
            $('#showFormBtn').show();
            clearForm();
        }

	}


	return {
		initlicense: initlicense,
	}
});