// Edit profile page
function profileEditInit(action_url, user_city, user_country) {
	
	$('#profileEdit_profile_city').select2({
		placeholder: " ",
		allowClear: true,
		minimumInputLength: 2,
		ajax: {
			url: action_url,
			quietMillis: 100,
			data: function(term, page) {
				return {
					city_name: term
				};
			},
			results: function(data, page) {
				return {results: data};
			}
		},
		initSelection: function(element, callback) {
			var data = {id: element.val(), text: user_city, country: user_country};
			callback(data);
		},
		formatResult: function(city) {
			var markup = "<div class='city-select'>";
			markup += "<div class='name'>" + city.text + "</div>";
			markup += "<div class='location'>" + city.region + ', ' + city.country + "</div>";
			markup += "</div>";
			return markup;
		},
		formatSelection: function(city) {
			return city.text + ', ' + city.country;
		}
	});
}