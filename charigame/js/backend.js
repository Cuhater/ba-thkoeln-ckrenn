
jQuery(document).ready(function ($) {
	$('#select_crm').change(function () {
		var selectedCRM = $(this).val();

		switch (selectedCRM) {
			case 'pipedrive':
				$('#pipedrive-content').show();
				$('#hubspot-content, #salesforce-content').hide();
				break;
			case 'hubspot':
				$('#hubspot-content').show();
				$('#pipedrive-content, #salesforce-content').hide();
				break;
			case 'salesforce':
				$('#salesforce-content').show();
				$('#pipedrive-content, #hubspot-content').hide();
				break;
			default:
				break;
		}
	});
});

if (typeof charigameAdmin !== 'undefined' && charigameAdmin.post_type === 'charigame-campaign') {
	jQuery(document).ready(function($) {
		setTimeout(initCampaignLogic, 1000);

		$(document).on('change input', 'input[name*="carbon_fields_compact_input"]', function() {
			setTimeout(updateCampaignInfo, 100);
		});

		$(document).on('change', 'input[type="checkbox"][name*="dispatch_date_option"]', function() {
			setTimeout(updateCampaignInfo, 200);
		});

		$(document).on('input change', 'input[type="range"][name*="code_validity_duration"]', function() {
			updateDurationDisplay();
			setTimeout(updateCampaignInfo, 100);
		});
	});
}

function initCampaignLogic() {
	updateDurationDisplay();
	setTimeout(updateCampaignInfo, 500);
}

function getCarbonFieldValue(fieldName) {
	const carbonFieldName = `carbon_fields_compact_input[_${fieldName}]`;

	const checkedRadio = document.querySelector(`input[name="${carbonFieldName}"]:checked`);
	if (checkedRadio) {
		return checkedRadio.value;
	}

	const inputField = document.querySelector(`input[name="${carbonFieldName}"]`);
	if (inputField) {
		return inputField.value;
	}

	const selectField = document.querySelector(`select[name="${carbonFieldName}"]`);
	if (selectField) {
		return selectField.value;
	}

	return null;
}

function updateCampaignInfo() {
	try {
		const dispatchOption = getCarbonFieldValue('dispatch_date_option');
		const dispatchDate = getCarbonFieldValue('dispatch_date');
		const campaignStart = getCarbonFieldValue('campaign_start');
		const dispatchTime = getCarbonFieldValue('dispatch_time');
		const duration = getCarbonFieldValue('code_validity_duration');

		let endDate = null;
		let endTime = dispatchTime || '--';

		if (dispatchOption === 'birthday' && campaignStart) {
			const startDate = new Date(campaignStart);
			if (!isNaN(startDate.getTime())) {
				startDate.setFullYear(startDate.getFullYear() + 1);
				endDate = startDate;
			}
		} else if (dispatchOption === 'dispatch' && dispatchDate && duration) {
			const startDate = new Date(dispatchDate);
			if (!isNaN(startDate.getTime())) {
				startDate.setDate(startDate.getDate() + (parseInt(duration) * 7));
				endDate = startDate;
			}
		}

		let formattedDate = '--';
		if (endDate && !isNaN(endDate.getTime())) {
			const day = String(endDate.getDate()).padStart(2, '0');
			const month = String(endDate.getMonth() + 1).padStart(2, '0');
			const year = endDate.getFullYear();
			formattedDate = `${day}.${month}.${year}`;
		}

		updateCampaignUI(formattedDate, endTime);

	} catch (error) {
		// Silent error handling
	}
}

function updateDurationDisplay() {
	const duration = getCarbonFieldValue('code_validity_duration');

	if (duration) {
		const output = document.getElementById('code_validity_duration-output');
		const wrapper = document.getElementById('duration_wrapper');

		if (output) {
			output.textContent = duration;
		}

		if (wrapper) {
			const weeksText = duration == 1 ? 'Week' : 'Weeks';
			wrapper.innerHTML = `Duration: <span id="code_validity_duration-output">${duration}</span> ${weeksText}`;
		}
	}
}

function updateCampaignUI(endDate, endTime) {
	const endDateElement = document.getElementById('end-date');
	const endTimeElement = document.getElementById('end-time');

	if (endDateElement) {
		endDateElement.textContent = endDate;
	}

	if (endTimeElement) {
		endTimeElement.textContent = endTime;
	}
}
