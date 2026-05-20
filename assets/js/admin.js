(function ($) {
	'use strict';

	function setupMediaField($field) {
		var frame;
		var $input = $field.find('.ntg-popups-media-id');
		var $preview = $field.find('.ntg-popups-media-preview');
		var $removeButton = $field.find('.ntg-popups-remove-media');

		$field.find('.ntg-popups-select-media').on('click', function (event) {
			event.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: ntgPopupsAdmin.title,
				button: {
					text: ntgPopupsAdmin.button
				},
				multiple: false,
				library: {
					type: 'image'
				}
			});

			frame.on('select', function () {
				var selection = frame.state().get('selection').first().toJSON();
				$input.val(selection.id);
				$preview.html('<img class="ntg-popups-image-preview" src="' + selection.url + '" alt="" />');
				$removeButton.show();
			});

			frame.open();
		});

		$removeButton.on('click', function (event) {
			event.preventDefault();
			$input.val('0');
			$preview.empty();
			$removeButton.hide();
		});
	}

	$(function () {
		$('.ntg-popups-media-field').each(function () {
			setupMediaField($(this));
		});
	});
})(jQuery);
