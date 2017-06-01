// Search users page
function userSearchInit(action_url) {
	
	$('#userSearch_locationId').select2({
		placeholder: " ",
		allowClear: true,
		minimumInputLength: 2,
		ajax: {
			url: action_url,
			quietMillis: 100,
			data: function(term, page) {
				return {
					location_name: term
				};
			},
			results: function(data, page) {
				return {results: data};
			}
		},
		initSelection: function(element, callback) {
			var type = $('#userSearch_locationType').val();
			var title = $('#userSearch_locationTitle').val();
			if (type === 'city' || type === 'region') {
				var data = {
					id: element.val(),
					text: title.split(', ')[0],
					type: type,
					country: title.split(', ')[1]
				};
			} else if (type === 'country') {
				var data = {
					id: element.val(),
					text: title,
					type: type
				};
			}
			callback(data);
		},
		formatResult: function(location) {
			var markup = "<div class='location-select'>";
			markup += "<div class='name'>" + location.text + "</div>";
			if (location.type === 'country') {
				markup += "<div class='type'>Страна</div>";
			}
			if (location.type === 'region') {
				markup += "<div class='type'>Регион</div>";
				markup += "<div class='area'>" + location.country + "</div>";
			}
			if (location.type === 'city') {
				markup += "<div class='type'>Город</div>";
				markup += "<div class='area'>" + location.region + ', ' + location.country + "</div>";
			}
			markup += "</div>";
			return markup;
		},
		formatSelection: function(location) {
			if (location.type === 'region' || location.type === 'city') {
				return location.text + ', ' + location.country;
			} else if (location.type === 'country') {
				return location.text;
			}
		}
	}).on('change', function(element) {
		// Set type and title (format: Moscow, Russia)
		if (typeof element.added !== 'undefined') {
			var location = element.added;

			$('#userSearch_locationType').val(location.type);

			if (location.type === 'region' || location.type === 'city') {
				$('#userSearch_locationTitle').val(location.text + ', ' + location.country);
			} else if (location.type === 'country') {
				$('#userSearch_locationTitle').val(location.text);
			}
		} else {
			$('#userSearch_locationType').val('');
			$('#userSearch_locationTitle').val('');
		}
	});
}