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
					width: '600px',
					onSelection: (result) => {
						console.log( locationOptions.radarFields['zip-code'] );
						// street.value = result.addressLabel;
						// city.value  = result.city;
						// state.value = result.state;
						// zip.value   = result.postalCode;
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
