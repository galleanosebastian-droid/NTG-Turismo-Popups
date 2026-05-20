(function () {
	'use strict';

	var popup = document.querySelector('[data-ntg-popup]');
	if (!popup) {
		return;
	}

	var config = window.ntgPopupsFrontend || {};
	var storageKey = 'ntg_turismo_popup_shown';

	if (config.displayFrequency === 'session') {
		try {
			if (window.sessionStorage.getItem(storageKey) === '1') {
				popup.classList.add('ntg-popups-hidden');
				return;
			}
		} catch (error) {
			// Fallback silencioso cuando sessionStorage no está disponible.
		}
	}

	function closePopup() {
		popup.classList.add('ntg-popups-hidden');
		if (config.displayFrequency === 'session') {
			try {
				window.sessionStorage.setItem(storageKey, '1');
			} catch (error) {
				// Fallback silencioso cuando sessionStorage no está disponible.
			}
		}
	}

	popup.addEventListener('click', function (event) {
		if (event.target && event.target.matches('[data-ntg-popup-close]')) {
			closePopup();
		}
	});
})();
