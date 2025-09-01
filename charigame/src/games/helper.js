const setTotalBonus = (gameScore) => {
	const donationDist = helper_vars.dist || [];
	const donationType = helper_vars.gametype;

	if (!donationDist.length) return 0;

	// Wenn kein Typ gesetzt ist, nutze "größer gleich" Logik
	if (!donationType) {
		for (let i = 0; i < donationDist.length; i++) {
			if (gameScore >= donationDist[i].limit) {
				return parseFloat(donationDist[i].spendenbetrag) || 0;
			}
		}
		if (gameScore < donationDist[donationDist.length - 1].limit) {
			return 0;
		}
	} else {
		// Wenn Typ gesetzt ist, nutze "kleiner gleich" Logik
		for (let i = 0; i < donationDist.length; i++) {
			if (gameScore <= donationDist[i].limit) {
				return parseFloat(donationDist[i].spendenbetrag) || 0;
			}
		}
		if (gameScore > donationDist[donationDist.length - 1].limit) {
			return 0;
		}
	}
	return 0;
}

/**
 * Verbessert die Farbanpassung: Nutzt HSL statt RGB für harmonischere Ergebnisse.
 * amt > 0: heller, amt < 0: dunkler
 */
function LightenDarkenColor(hex, amt) {
	hex = hex.replace('#', '');
	if (hex.length === 3) {
		hex = hex.split('').map(x => x + x).join('');
	}
	const num = parseInt(hex, 16);
	let r = (num >> 16) & 255;
	let g = (num >> 8) & 255;
	let b = num & 255;

	// Umwandlung in HSL
	r /= 255; g /= 255; b /= 255;
	const max = Math.max(r, g, b), min = Math.min(r, g, b);
	let h, s, l = (max + min) / 2;

	if (max === min) {
		h = s = 0;
	} else {
		const d = max - min;
		s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
		switch (max) {
			case r: h = (g - b) / d + (g < b ? 6 : 0); break;
			case g: h = (b - r) / d + 2; break;
			case b: h = (r - g) / d + 4; break;
		}
	}
	h /= 6;

	// Helligkeit anpassen
	l = Math.max(0, Math.min(1, l + amt / 100));

	// Rückumwandlung in RGB
	let q = l < 0.5 ? l * (1 + s) : l + s - l * s;
	let p = 2 * l - q;
	const hue2rgb = (p, q, t) => {
		if (t < 0) t += 1;
		if (t > 1) t -= 1;
		if (t < 1 / 6) return p + (q - p) * 6 * t;
		if (t < 1 / 2) return q;
		if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
		return p;
	};
	r = Math.round(hue2rgb(p, q, h + 1 / 3) * 255);
	g = Math.round(hue2rgb(p, q, h) * 255);
	b = Math.round(hue2rgb(p, q, h - 1 / 3) * 255);

	return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('');
}

// Resolve AJAX URL portable
const getAjaxUrl = () => {
    return (typeof myAjax !== 'undefined' && myAjax.ajaxurl)
        ? myAjax.ajaxurl
        : '/wp-admin/admin-ajax.php';
};

// Try to determine campaign ID from DOM (injected landing content)
const getCampaignIdFromDOM = () => {
    const container = document.querySelector('.charigame-landing-content');
    if (container && container.dataset && container.dataset.campaignId) {
        return parseInt(container.dataset.campaignId, 10) || 0;
    }
    const hiddenCode = document.getElementById('hidden-code');
    if (hiddenCode && hiddenCode.closest('.charigame-landing-content')) {
        const parent = hiddenCode.closest('.charigame-landing-content');
        if (parent && parent.dataset && parent.dataset.campaignId) {
            return parseInt(parent.dataset.campaignId, 10) || 0;
        }
    }
    return 0;
};

// Fetch frontend data for injected pages
const fetchFrontendData = (campaignId) => {
    return new Promise((resolve, reject) => {
        if (!campaignId) return reject(new Error('campaignId missing'));
        jQuery.ajax({
            url: getAjaxUrl(),
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'charigame_get_frontend_data',
                campaign_id: campaignId
            },
            success: function (response) {
                if (response && response.success && response.data) {
                    window.helper_vars = response.data;
                    resolve(response.data);
                } else {
                    reject(new Error(response?.data?.message || 'Failed to load frontend data'));
                }
            },
            error: function (xhr, status, error) {
                reject(error);
            }
        });
    });
};

// Ensure helper_vars is present and includes memory_images/pairs_count
const ensureHelperVars = async () => {
    const hasBase = (typeof helper_vars !== 'undefined' && helper_vars && helper_vars.recipients && Object.keys(helper_vars.recipients).length);
    const hasMemory = (typeof helper_vars !== 'undefined' && Array.isArray(helper_vars.memory_images) && helper_vars.memory_images.length && typeof helper_vars.pairs_count !== 'undefined');
    if (hasBase && hasMemory) {
        return helper_vars;
    }
    const cid = (typeof helper_vars !== 'undefined' && helper_vars?.campaign_id) || getCampaignIdFromDOM();
    if (!cid) return typeof helper_vars !== 'undefined' ? helper_vars : null;
    try {
        const data = await fetchFrontendData(cid);
        // Merge into existing helper_vars to keep already-present fields
        window.helper_vars = Object.assign({}, (typeof helper_vars !== 'undefined' ? helper_vars : {}), data);
        return window.helper_vars;
    } catch (e) {
        console.error('Failed to ensure helper_vars:', e);
        return (typeof helper_vars !== 'undefined') ? helper_vars : null;
    }
};

const createDonationTriangle = () => {
    let recipients = {
        recipient_1: { title: "Empfänger 1" },
        recipient_2: { title: "Empfänger 2" },
        recipient_3: { title: "Empfänger 3" }
    };
    let tertiary_color = '#6B7280';
    let logo = '';

    if (typeof helper_vars !== 'undefined') {
        recipients = helper_vars.recipients || recipients;
        tertiary_color = helper_vars.teritary_color || tertiary_color;
        logo = helper_vars.logo || '';

        // Debug-Ausgabe
        console.log('Recipients aus helper_vars:', helper_vars.recipients);

        // Prüfen auf logo_url und nutzen falls vorhanden
        if (recipients.recipient_1 && recipients.recipient_1.logo_url) {
            console.log('Logo URL für Empfänger 1:', recipients.recipient_1.logo_url);
        }
        if (recipients.recipient_2 && recipients.recipient_2.logo_url) {
            console.log('Logo URL für Empfänger 2:', recipients.recipient_2.logo_url);
        }
        if (recipients.recipient_3 && recipients.recipient_3.logo_url) {
            console.log('Logo URL für Empfänger 3:', recipients.recipient_3.logo_url);
        }
    }

	const triangleColor = LightenDarkenColor(tertiary_color, -20);

	$('.picker').trianglePicker({
		polygon: {
			width: null,
			fillColor: triangleColor,
			line: {
				width: 1,
				color: 'white',
				centerLines: true,
				centerLineWidth: null
			}
		},
		handle: {
			color: '#BF1D1D',
			backgroundImage: logo,
			width: null,
			height: null,
			borderRadius: null
		},
		inputs: {
			bottomRight: {
				name: recipients.recipient_2.title,
				id: 'score[]',
				class: ''
			},
			topMiddle: {
				name: recipients.recipient_3.title,
				id: 'score[]',
				class: ''
			},
			bottomLeft: {
				name: recipients.recipient_1.title,
				id: 'score[]',
				class: ''
			},
			decimalPlaces: 0 // Keine Dezimalstellen
		}
	}, function (name, values) {
		// Prozentanzeige immer als ganze Zahl
		$('.topMiddleLabel').html(recipients.recipient_3.title + ' <span class="font-main text-secondary">' + Math.round(values[recipients.recipient_3.title]) + ' %</span>')
		$('.bottomLeft').html(recipients.recipient_1.title + ' <span class="font-main text-secondary">' + Math.round(values[recipients.recipient_1.title]) + ' %</span>')
		$('.bottomRight').html(recipients.recipient_2.title + ' <span class="font-main text-secondary">' + Math.round(values[recipients.recipient_2.title]) + ' %</span>')
	})
}

/**
 * UI-Element Helfer-Funktionen für einfachere DOM-Manipulationen
 */
const UI = {
    get: (id) => document.getElementById(id),

    addClass: (id, className) => {
        const element = UI.get(id);
        if (element) element.classList.add(className);
    },
    removeClass: (id, className) => {
        const element = UI.get(id);
        if (element) element.classList.remove(className);
    },

    show: (id) => UI.removeClass(id, 'hidden'),
    hide: (id) => {
        const element = UI.get(id);
        if (element) {
            element.classList.add('hidden');
        } else {
            console.warn(`Element with ID '${id}' not found for hiding`);
        }
    },

    setText: (id, text) => {
        const element = UI.get(id);
        if (element) element.innerHTML = text;
    },

    scrollTo: (selector, offset = 0) => {
        const element = $(selector);
        if (element.length) {
            $('html, body').animate({
                scrollTop: element.offset().top + offset
            }, 1000);
        }
    },

    showElements: (ids) => ids.forEach(id => UI.show(id)),
    hideElements: (ids) => ids.forEach(id => UI.hide(id))
};

/**
 * Spielzustände mit den zugehörigen UI-Konfigurationen
 */
const GameStates = {
    DONATION_DISTRIBUTION: {
        title: 'Spende verteilen',
        show: ['btn-submit-score', 'picker-container', 'picker'],
        hide: ['bottom-seperator', 'intro', 'container', 'modal-game-end', 'how-to-play'],
        classes: {
            add: {
                'gamesection-headline': ['mt-8', 'text-secondary'],
                'picker-container': ['py-12']
            },
            remove: {
                'gamesection-headline': ['-mt-8', 'text-white'],
                'spiel': ['bg-secondary']
            }
        },
        scroll: '#picker',
        scrollOffset: -50
    },

    RESTART_GAME: {
        title: 'Das Spiel',
        show: ['intro', 'container', 'spiel'],
        hide: ['btn-submit-score', 'btn-play-again-end', 'btn-play-again-end-container', 'how-to-play'],
        classes: {
            add: {
                'cta-end': ['hidden']
            },
            remove: {
                'spiel': ['mt-8']
            }
        },
        scroll: '#spiel',
        scrollOffset: 200
    },

    GAME_OVER: {
        title: 'Sie möchten es noch mal versuchen?',
        show: ['btn-play-again-end', 'btn-play-again-end-container'],
        hide: ['picker', 'recipients', 'picker-container', 'how-to-play'],
        classes: {
            add: {
                'btn-submit-score': ['hidden']
            },
            remove: {
                'picker-container': ['py-12']
            }
        },
        scroll: 'body',
        scrollOffset: 0
    }
};

/**
 * Setzt die UI basierend auf einem definierten Spielzustand
 */
const setGameState = (state) => {
    const config = GameStates[state];
    if (!config) return;

    if (config.title) {
        UI.setText('gamesection-headline', config.title);
    }

    if (config.show) UI.showElements(config.show);
    if (config.hide) UI.hideElements(config.hide);

    if (config.classes) {
        if (config.classes.add) {
            Object.entries(config.classes.add).forEach(([id, classes]) => {
                classes.forEach(className => UI.addClass(id, className));
            });
        }

        if (config.classes.remove) {
            Object.entries(config.classes.remove).forEach(([id, classes]) => {
                classes.forEach(className => UI.removeClass(id, className));
            });
        }
    }

    if (config.scroll) {
        UI.scrollTo(config.scroll, config.scrollOffset || 0);
    }
};

/**
 * Zeigt die Spendenverteilung an und aktualisiert die Spendensummen
 */
const distributeDonation = () => {
    ensureHelperVars().then(() => {
        // Aktuelle Spendensumme abrufen und anzeigen
        if (typeof helper_vars !== 'undefined' && helper_vars.campaign_id) {
            jQuery.ajax({
                url: getAjaxUrl(),
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'charigame_get_donations',
                    campaign_id: helper_vars.campaign_id
                },
                success: function(response) {
                    if (response.success) {
                        updateDonationDisplay(response.data);
                        console.log('Updated donation display for distribution');
                    }
                }
            });
        }

        setGameState('DONATION_DISTRIBUTION');
        createDonationTriangle();
    });
}


/**
 * Spiel neu starten
 */
const playAgain = () => {
    window.onkeydown = function (e) {
        return !(e.key === " " || e.code === "Space");
    };
    setGameState('RESTART_GAME');
    window.game.restartGame();
}

/**
 * Highscore aktualisieren und Spiel beenden
 */
/**
 * Aktualisiert den Highscore und beendet das Spiel
 */
const updateHighscore = () => {
    let gameCode = UI.get('hidden-code')?.dataset?.gamecode || '';
    let nonce = '';
    let campaign_id = 0;

    if (typeof helper_vars !== 'undefined') {
        nonce = helper_vars.nonce || '';
        campaign_id = helper_vars.campaign_id || 0;
    }

    const ajaxUrl = (typeof myAjax !== 'undefined' && myAjax.ajaxurl)
        ? myAjax.ajaxurl
        : '/wp-admin/admin-ajax.php';

    let scoreElements = document.querySelectorAll('input[name="score[]"]');

    // Current time in milliseconds - timezone conversion will happen server-side
    let now = new Date();
    let lastPlayed = now.getTime();

    setGameState('GAME_OVER');

    // Ensure helper_vars before saving score
    ensureHelperVars().then(() => {
        // Re-read after ensure
        if (typeof helper_vars !== 'undefined') {
            nonce = helper_vars.nonce || nonce;
            campaign_id = helper_vars.campaign_id || campaign_id;
        }
        jQuery.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'charigame_save_score',
                nonce: nonce,
                last_played: lastPlayed,
                highscore: this.game.highscore,
                code: gameCode,
                campaign_id: campaign_id,
                recipient_1: scoreElements[0]?.value || 0,
                recipient_2: scoreElements[1]?.value || 0,
                recipient_3: scoreElements[2]?.value || 0,
            },
            success: function(response) {
                if (response.success) {
                    updateDonationDisplay(response.data.statistics);
                    console.log('Score saved successfully:', response.data.message);

                    // Konfetti wird jetzt direkt durch Event-Listener im confetti.js ausgelöst
                    console.log('Score saved successfully - confetti should trigger automatically');
                } else {
                    console.error('Error saving score:', response.data?.message || 'Unknown error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    });
};

/**
 * Setzt den last_played Timestamp in der Datenbank beim Spielstart
 * @returns {Promise} Ein Promise, der nach dem AJAX-Aufruf aufgelöst wird
 */
const setLastPlayedTimestamp = () => {
    let gameCode = UI.get('hidden-code')?.dataset?.gamecode || '';
    let nonce = '';

    if (typeof helper_vars !== 'undefined') {
        nonce = helper_vars.nonce || '';
    }

    if (!gameCode) {
        console.warn('ChariGame: No game code found for setting last_played timestamp');
        return Promise.reject('No game code found');
    }

    const ajaxUrl = (typeof myAjax !== 'undefined' && myAjax.ajaxurl)
        ? myAjax.ajaxurl
        : '/wp-admin/admin-ajax.php';

    // Get current time as UTC timestamp in milliseconds
    // This will be converted to the correct timezone on the server
    const now = new Date();
    const lastPlayed = now.getTime();

    return new Promise((resolve, reject) => {
        jQuery.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'charigame_set_last_played',
                nonce: nonce,
                code: gameCode,
                last_played: lastPlayed
            },
            success: function(response) {
                if (response.success) {
                    console.log('Last played timestamp updated successfully');
                    resolve(response);
                } else {
                    console.warn('Error updating last_played timestamp:', response.data?.message || 'Unknown error');
                    reject(response.data?.message || 'Unknown error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error when setting last_played:', error);
                reject(error);
            }
        });
    });
};


/**
 * Aktualisiert die Anzeige der Spendenstatistiken
 * @param {Object} statistics - Die Statistikdaten
 */
function updateDonationDisplay(statistics) {

    let donationSum = statistics.total_donation || 0;

    const donationContainer = UI.get('donation-display');
    if (donationContainer) {
        // Immer auf ganze Zahlen runden und im Format 10,00 € anzeigen
        const roundedSum = Math.round(donationSum);
        donationContainer.innerHTML = roundedSum.toFixed(2).replace('.', ',');
    }
    const recipients = statistics.recipients || {};
    const recipientIds = Object.keys(recipients);

    const bars = [0, 1, 2].map(i => UI.get(`recipient-bar-${i}`));
    const nums = [0, 1, 2].map(i => UI.get(`recipient-num-${i}`));

    bars.forEach((bar, index) => {
        if (bar) bar.style.width = '0%';
        if (nums[index]) nums[index].innerHTML = '0%';
    });

    if (donationSum > 0 && recipientIds.length > 0) {
        recipientIds.forEach((recipientId, index) => {
            if (index < 3 && bars[index] && nums[index]) {
                const recipient = recipients[recipientId];
                const percentage = recipient.percentage || 0;

                // Prozentanzeige immer als ganze Zahl
                const wholePercentage = Math.round(percentage);
                bars[index].style.width = `${wholePercentage}%`;
                nums[index].innerHTML = `${wholePercentage}%`;
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
	const button = document.getElementById('show-donation-triangle');
	const distribute = document.getElementById('btn-submit-score');
	const playAgainEnd = document.getElementById('btn-play-again-end');

	if (button) {
		button.addEventListener('click', distributeDonation);
	}
	if (distribute) {
		distribute.addEventListener('click', updateHighscore);
	}
	if (playAgainEnd) {
		playAgainEnd.addEventListener('click', playAgain);
	}

	let confetti = new Confetti('btn-submit-score');
	confetti.setCount(2500);
	confetti.setSize(1);
	confetti.setPower(35);
	confetti.setFade(false);
	let primaryColor = '#1D4ED8';
	let secondaryColor = '#D97706';

	if (typeof helper_vars !== 'undefined') {
	    primaryColor = helper_vars.primary_color || primaryColor;
	    secondaryColor = helper_vars.secondary_color || secondaryColor;
	} else {
        // Try to load colors via ensureHelperVars for injected pages
        ensureHelperVars().then((data) => {
            if (data) {
                primaryColor = data.primary_color || primaryColor;
                secondaryColor = data.secondary_color || secondaryColor;
                confetti.setColors([
                    LightenDarkenColor(secondaryColor, 20),
                    LightenDarkenColor(primaryColor, 20),
                    "#ffffff"
                ]);
            }
        });
    }

	confetti.setColors([
		LightenDarkenColor(secondaryColor, 20),
		LightenDarkenColor(primaryColor, 20),
		"#ffffff"
	]);
	confetti.destroyTarget(false);

	if( document.querySelector('a[href="#spiel"]')) {
		document.querySelector('a[href="#spiel"]').addEventListener('click', function(event) {
			event.preventDefault();
			document.querySelector('#spiel').scrollIntoView({ behavior: 'smooth' });
		});
	}

});
