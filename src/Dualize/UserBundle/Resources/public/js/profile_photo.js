var gallery_selector = '.profile_images';
var upload_url = '';
var files_limit = 0;
var position_url = '';
var delete_url = '';

function profilePhotoInit(upload_action_url, files_upload_limit, position_action_url, delete_action_url) {
	// Set basic settings
	upload_url = upload_action_url;
	files_limit = files_upload_limit;
	position_url = position_action_url;
	delete_url = delete_action_url;

	// Run methods
	ajaxUpload();
	changeItemsOrder();
	deleteImage();
}

function ajaxUpload() {
	var entity_name = 'photo';
	var field_name = 'image';
	var input_selector = '#' + entity_name + '_' + field_name;

	$(input_selector).on('change', function() {

		toggleLoadingState();

		var errors = [];
		$('.upload .errors').html('');

		var queries_num = 0;

		jQuery.each($(input_selector)[0].files, function(i, file) {
			if (i < files_limit) {
				var data = new FormData();
				data.append(entity_name + '[' + field_name + ']', file);
				data.append(entity_name + '[_token]', $('#' + entity_name + '__token').attr('value'));

				queries_num++;

				$.ajax({
					url: upload_url,
					data: data,
					cache: false,
					contentType: false,
					processData: false,
					type: 'POST',
					success: function(data) {
						var msg = $.parseJSON(data);

						if (typeof(msg.img) !== "undefined") {
							var img_container = $(gallery_selector).siblings('.thumb').clone().show();
							$(gallery_selector).append(img_container);
							$('<img src="' + msg.img + '">').load(function() {
								img_container.removeClass('img_loading');
								img_container.prepend($(this));
							});

							changeItemsOrder();
						}

						if (typeof(msg.error) !== "undefined") {
							errors.push('Не удалось загрузить файл ' + file.name + '. ' + msg.error.replace(/\s+/g, ' '));
						}
					},
					error: function(data) {
						errors.push('Не удалось загрузить файл ' + file.name);
					},
					complete: function(data) {
						queries_num--;
						if (queries_num === 0) {
							toggleLoadingState();
							for (var error in errors) {
								$('.upload .errors').append('<div>' + errors[error] + '</div>');
							}
						}
					}
				});
			} else {
				errors.push('Количество одновременно загружаемых файлов - не более ' + files_limit);
				return false;
			}
		});

		$(input_selector).val('');
	});
}

function toggleLoadingState() {
	$('.upload label, .upload .uploading').toggle();
}

function changeItemsOrder() {
	$(gallery_selector).sortable('destroy').unbind('sortupdate');
	// Runs when images positions have changed
	$(gallery_selector).sortable().bind('sortupdate', function() {
		var images = {};
		var error = false;
		$('.upload .errors').html('');

		$(gallery_selector).find('img').each(function(i, img) {
			var img_src = $(gallery_selector).find('img').eq(i).attr('src');
			var img_name = img_src.substring(img_src.lastIndexOf('/') + 1);
			images[img_name] = i + 1;
		});

		$.ajax({
			url: position_url,
			type: 'POST',
			data: JSON.stringify(images),
			success: function(msg) {
				if (msg !== 'Positions changed') {
					error = true;
				}
			},
			error: function(msg) {
				error = true;
			},
			complete: function(msg) {
				if (error === true) {
					$('.upload .errors').append('<div>Не удалось изменить порядок изображений. Обновите пожалуйста страницу или попробуйте позже.</div>');
				}
			}
		});
	});
}

function deleteImage() {
	$(gallery_selector).on('click', '.delete', function() {
		var clicked_button = $(this).removeClass('delete').addClass('deleting');

		var img_container = $(this).parent();
		var img_src = $(this).siblings('img').attr('src');
		var img_name = img_src.substring(img_src.lastIndexOf('/') + 1).replace('.', '_');

		var error = false;
		$('.upload .errors').html('');

		$.ajax({
			url: delete_url + img_name,
			type: 'GET',
			success: function(msg) {
				if (msg === 'Image was deleted') {
					img_container.remove();
					changeItemsOrder();
				} else {
					error = true;
				}
			},
			error: function(msg) {
				error = true;
			},
			complete: function(msg) {
				if (error === true) {
					$('.upload .errors').append('<div>Не удалось удалить изображение. Обновите пожалуйста страницу или попробуйте позже.</div>');
					clicked_button.addClass('delete').removeClass('deleting');
				}
			}
		});
	});
}
