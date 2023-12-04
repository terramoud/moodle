define(['jquery', 'core/ajax'], function($, Ajax) {

	var attemptButton = $(".singlebutton.quizstartbuttondiv button[type='submit']")
	attemptButton.prop('disabled', true)
	var initlicense = function(wwwroot) {
		var fileItem

		$('#showFormBtn').on('click', showForm);
		$('#cancelBtn').on('click', hideForm);
		$('.license-edit-icon').on('click', hideForm);
		$('#license_list').on('click', '.license-edit-icon', function() {
			let licenseData = JSON.parse($(this).parents('tr').attr("data-license-json"))
			console.log(licenseData)
			showForm(licenseData);
			editForm(licenseData);
		})

		$('#license_list').on('click', '.license-remove-icon', function () {
			let licenseData = JSON.parse($(this).parents('tr').attr("data-license-json"))
			Ajax.call([{
					methodname: 'local_extendedfields_remove_license',
					args: {
						id: licenseData.id
					},
					done: function (response) {
						$('#license_list').find(`tr[data-license-id="${licenseData.id}"]`).remove();
						alert(JSON.parse(response).message);
					},
					fail: function (response) {
						alert(response);
					}
				}]);
		})

		$('#remove-attachment').on('click', function() {
			let licenseId = $(this).attr("data-attachment-id");
			if (!licenseId) {
				$('#remove-attachment').attr('data-attachment-id', '');
			}
			Ajax.call([{
				methodname: 'local_extendedfields_remove_license_file',
				args: {
					id: licenseId
				},
				done: function (response) {
					$('.attachment-container').hide();
					$('#upload-image').show();
					// TODO: move logic to function updateRow(response)
					$('#license_list').find(`tr[data-license-id="${licenseId}"] a`).remove();
					let licenseData = JSON.parse($('#license_list')
						.find(`tr[data-license-id="${licenseId}"]`)
						.attr("data-license-json"));
					licenseData.itemid = null;
					$('#license_list')
						.find(`tr[data-license-id="${licenseId}"]`)
						.attr("data-license-json", JSON.stringify(licenseData));
					alert(JSON.parse(response).message);
				},
				fail: function (response) {
					alert(response);
				}
			}]);
		});

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
					let responseObj = JSON.parse(response)

					const isFileAdded = $("#file").val();
					if (isFileAdded) {
						let newFormData = new FormData();
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
									let xhrResponse = JSON.parse(xhr.responseText);
									responseObj.itemid = xhrResponse.itemid;
									addRow(responseObj);
									hideForm();
								} else {
									alert('Error submitting form. Please try again.');
								}
								resetProgressBar();
							}
						};

						xhr.send(newFormData);
						return;
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

		/**
		 * @deprecated This function is marked for removal in future versions.
		 * @param {number} timestamp - The timestamp to format.
		 * @returns {string} The formatted date.
		 */
		function formatDate(timestamp) {
			var date = new Date(timestamp * 1000);
			var month = date.getMonth() + 1;
			var day = date.getDate();
			var year = date.getFullYear();
			return year + '-' + month + '-' + day;
		}

		/**
		 * @deprecated This function is marked for removal in future versions.
		 * @param {string} inputDate - The date to convert.
		 * @returns {string} The converted date format.
		 */
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

		/**
		 * Escapes double quotes in a JSON string by replacing them with HTML entity &quot;.
		 *
		 * @param {string} jsonString - The JSON string to escape double quotes in.
		 * @returns {string} The modified JSON string with double quotes escaped.
		 *
		 * @example
		 * // Returns 'This is a &quot;sample&quot; string.'
		 * const escapedString = escapeDoubleQuotes('This is a "sample" string.');
		 */
		function escapeDoubleQuotes(jsonString) {
			return jsonString.replace(/"/g, '&quot;');
		}

		function addRow(data) {
			let response = escapeDoubleQuotes(JSON.stringify(data))
			let attachmentLink = data?.itemid
				? '<br>'
					+ '<a href="'+wwwroot+'/local/extendedfields/image.php?file=Licenses%2F' + data.id + '%2F' + data.itemid + '">'
						+ '<i class="icon fa fa-paperclip fa-fw " title="Attachment" role="img" aria-label="Attachment"></i>'
						+ 'Attachment'
					+ '</a>'
				: '';
			var newRow = '<tr data-license-id="' + data.id + '" data-license-json="'+response+'">'
			+ '<td class="cell c0" style="">' + data.id + '</td>'
			+ '<td class="cell c1" style="">' + data.type + '</td>'
			+ '<td class="cell c2" style="">' + data.name + attachmentLink + '</td>'
			+ '<td class="cell c3" style="">' + (data.date_received || "-") + '</td>'
			+ '<td class="cell c4" style="">' + (data.expiration_date || "-") + '</td>'
			+ '<td class="cell c5 lastcol" style=""><i class="icon fa fa-pencil fa-fw license-edit-icon" title="Edit license" role="img" aria-label="Edit license"></i><i class="icon fa fa-times fa-fw license-remove-icon" title="Remove license" role="img" aria-label="Remove license"></i></td>'
			+ '</tr>';

			if ($("#user_license_id").val()) {
				removeRowByLicenseId($("#user_license_id").val())
			}

	        // Append the new row to the table body
	        $('#license_list tbody').append(newRow);
	    }

	    function editForm(data) {
	    	// console.log([data.license_id, formatDate(data.date_received), data.date_received])
	    	// console.log([data.date_received, data.expiration_date]);
	    	$('#licenseForm [name="id_number"]').val(data.id_number);
	    	$noConflictjQuery('#license_selector').val(parseInt(data.license_id));
	    	$noConflictjQuery('#license_selector').trigger('change');

	    	$('#user_license_id').val(data.id);
	    	$('#licenseForm [name="state"]').val(data.state);
	    	$('#licenseForm [name="date_received"]').val(data.date_received);
	    	$('#licenseForm [name="expiration_date"]').val(data.expiration_date);
	    	$('#licenseForm [name="ceu_pdu"]').val(data.ceu_pdu);
	    }

        function showForm(data) {
			// upload an attachment from the license being edited
			if (data.itemid) {
				$('.attachment-container').show();
				$('#upload-image').hide();
				$('.attachment-container a').attr('href', `${wwwroot}/local/extendedfields/image.php?file=Licenses%2F${data.id}%2F${data.itemid}`);
				$('.attachment-container a').text(data.itemid);
				$('#remove-attachment').attr('data-attachment-id', data.id);
			}
			if (!data.itemid) {
				$('.attachment-container').hide();
				$('#upload-image').show();
			}
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