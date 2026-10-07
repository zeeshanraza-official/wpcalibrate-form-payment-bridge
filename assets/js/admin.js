/**
 * WPCalibrate Form Payment Bridge Admin JavaScript
 *
 * @package WPCalibrate\FormPaymentBridge
 */

(function($) {
	'use strict';

	$(document).ready(function() {

		// 1. Amount Mode Radio Switching
		$('input.wpcalibrate-fpb-amount-mode-radio').on('change', function() {
			var mode = $(this).val();
			$('.wpcalibrate-fpb-mode-panel').hide();
			$('.wpcalibrate-fpb-mode-panel--' + mode).show();
		});

		// 2. Dynamic Form Fields Retrieval via AJAX
		$('#wpcalibrate_fpb_form_id').on('change', function() {
			var formId = $(this).val();
			if (!formId) {
				return;
			}

			var $selects = $('.wpcalibrate-fpb-field-select');
			$selects.prop('disabled', true);

			$.ajax({
				url: wpcalibrateFpbAdmin.ajaxUrl,
				type: 'GET',
				dataType: 'json',
				data: {
					action: 'wpcalibrate_fpb_get_form_fields',
					form_id: formId,
					nonce: wpcalibrateFpbAdmin.nonce
				},
				success: function(response) {
					if (response.success && response.data && response.data.fields) {
						updateFieldSelects(response.data.fields);
					}
				},
				complete: function() {
					$selects.prop('disabled', false);
				}
			});
		});

		function updateFieldSelects(fields) {
			$('.wpcalibrate-fpb-field-select').each(function() {
				var $select = $(this);
				var currentVal = $select.val() || $select.data('selected');
				var firstOptionText = $select.find('option:first').text();

				$select.empty();
				$select.append($('<option>', { value: '', text: firstOptionText }));

				$.each(fields, function(i, field) {
					var $opt = $('<option>', {
						value: field.id,
						text: field.label + ' (' + field.id + ')'
					});
					if (field.id === currentVal) {
						$opt.prop('selected', true);
					}
					$select.append($opt);
				});
			});
		}

		// 3. Choice-to-Price Mapping Table Row Management
		var choiceIndex = $('#wpcalibrate-fpb-choice-rows tr').length;

		$('#wpcalibrate-fpb-add-choice-btn').on('click', function(e) {
			e.preventDefault();
			choiceIndex++;

			var rowHtml = '<tr class="wpcalibrate-fpb-choice-row">' +
				'<td><input type="text" name="mapping[selectable_mappings][' + choiceIndex + '][option_value]" placeholder="Option value" class="regular-text" style="width:100%;"></td>' +
				'<td><input type="number" step="0.01" min="0" name="mapping[selectable_mappings][' + choiceIndex + '][amount]" value="0.00" class="regular-text" style="width:100%;"></td>' +
				'<td><button type="button" class="button button-link-delete wpcalibrate-fpb-remove-choice-btn">&times;</button></td>' +
				'</tr>';

			$('#wpcalibrate-fpb-choice-rows').append(rowHtml);
		});

		$(document).on('click', '.wpcalibrate-fpb-remove-choice-btn', function(e) {
			e.preventDefault();
			var $rows = $('#wpcalibrate-fpb-choice-rows tr');
			if ($rows.length > 1) {
				$(this).closest('tr').remove();
			} else {
				$(this).closest('tr').find('input').val('');
			}
		});

		// 4. Custom Metadata Rows Management
		var metaIndex = $('#wpcalibrate-fpb-meta-rows tr').length;

		$('#wpcalibrate-fpb-add-meta-btn').on('click', function(e) {
			e.preventDefault();
			metaIndex++;

			// Clone options from existing select if available
			var optionsHtml = '<option value="">-- Select Field --</option>';
			var $firstSelect = $('.wpcalibrate-fpb-field-select').first();
			if ($firstSelect.length) {
				$firstSelect.find('option').each(function() {
					var val = $(this).val();
					if (val) {
						optionsHtml += '<option value="' + val + '">' + $(this).text() + '</option>';
					}
				});
			}

			var metaRowHtml = '<tr class="wpcalibrate-fpb-meta-row">' +
				'<td><select name="mapping[custom_meta_fields][' + metaIndex + '][field_id]" class="wpcalibrate-fpb-field-select" style="width:100%;">' + optionsHtml + '</select></td>' +
				'<td><input type="text" name="mapping[custom_meta_fields][' + metaIndex + '][meta_key]" class="regular-text" style="width:100%;" placeholder="meta_key"></td>' +
				'<td><button type="button" class="button button-link-delete wpcalibrate-fpb-remove-meta-btn">&times;</button></td>' +
				'</tr>';

			$('#wpcalibrate-fpb-meta-rows').append(metaRowHtml);
		});

		$(document).on('click', '.wpcalibrate-fpb-remove-meta-btn', function(e) {
			e.preventDefault();
			$(this).closest('tr').remove();
		});

	});

})(jQuery);
