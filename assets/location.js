(function (document, window) {
	'use strict';
	if ( typeof window.Radar === 'undefined') {
		console.log( 'Radar SDK not initialized.' );
		return;
	}

	let Radar           = window.Radar || {};
	let locationOptions = window.nami_location_collection || {};

	Radar.initialize( locationOptions.apiKey );

	document.addEventListener(
		'DOMContentLoaded',
		function () {
			let autocompleteField = locationOptions.radarFields.autocomplete;

			Radar.ui.autocomplete(
				{
					container: document.getElementById( autocompleteField ),
					responsive: true,
					countryCode: locationOptions.countryCode,
					width: '600px',
					layers: 'postalCode',
					onSelection: (result) => {
						for (let r in result) {
							if ( ! ! ! locationOptions.radarFields[r]) {
								continue;
							}

							let field = document.getElementById( locationOptions.radarFields[r] );
							if ( ! ! ! field) {
								console.error( 'Mapped field not found: ' + locationOptions.radarFields[r] );
								continue;
							}
							field.value = result[r];
						}

						// @todo: implement address check logic
						// addressChecked.value = 1;
					},
					onResults: (res) => {
						// addressChecked.value = '';
					},
					onError: (res) => {
						// addressChecked.value = '';
					}
				}
			);

		}
	);

}(document, window));
