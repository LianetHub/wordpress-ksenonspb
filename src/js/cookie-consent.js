"use strict";

const COOKIE_NAME = "ksenon_cookie_consent";
const COOKIE_MAX_AGE = 180 * 24 * 60 * 60;
const DEFAULT_PREFS = {
	necessary: true,
	analytics: false,
	marketing: false,
};

let currentPrefs = null;
let metrikaLoaded = false;
const changeListeners = [];

function getThemeAjax() {
	return typeof theme_ajax !== "undefined" ? theme_ajax : {};
}

function parseConsentCookie(raw) {
	if (!raw) return null;

	try {
		const data = JSON.parse(decodeURIComponent(raw));
		if (!data || typeof data !== "object") return null;

		return {
			necessary: true,
			analytics: Boolean(data.analytics),
			marketing: Boolean(data.marketing),
		};
	} catch (e) {
		return null;
	}
}

function readCookie(name) {
	const match = document.cookie.match(
		new RegExp(
			"(?:^|; )" +
				name.replace(/([.$?*|{}()[\]\\/+^])/g, "\\$1") +
				"=([^;]*)",
		),
	);
	return match ? match[1] : null;
}

function writeCookie(name, value, maxAge) {
	const secure =
		typeof location !== "undefined" && location.protocol === "https:"
			? "; Secure"
			: "";
	document.cookie =
		name +
		"=" +
		encodeURIComponent(value) +
		"; path=/; max-age=" +
		maxAge +
		"; SameSite=Lax" +
		secure;
}

function notifyChange(prefs) {
	changeListeners.forEach((cb) => {
		try {
			cb(prefs);
		} catch (e) {
			/* ignore listener errors */
		}
	});

	document.dispatchEvent(
		new CustomEvent("ksenon:consent-change", { detail: prefs }),
	);
}

function getRoot() {
	return document.getElementById("cookie-notice");
}

function showPanel(root, name) {
	if (!root) return;

	root.querySelectorAll("[data-cookie-panel]").forEach((panel) => {
		const isActive = panel.getAttribute("data-cookie-panel") === name;
		panel.hidden = !isActive;
	});
}

function syncCheckboxes(root, prefs) {
	if (!root || !prefs) return;

	root.querySelectorAll("[data-cookie-category]").forEach((input) => {
		const category = input.getAttribute("data-cookie-category");
		if (category === "necessary") {
			input.checked = true;
			return;
		}
		input.checked = Boolean(prefs[category]);
	});
}

function readSettingsFromUi(root) {
	const prefs = { ...DEFAULT_PREFS };
	if (!root) return prefs;

	root.querySelectorAll("[data-cookie-category]").forEach((input) => {
		const category = input.getAttribute("data-cookie-category");
		if (category === "necessary") return;
		prefs[category] = Boolean(input.checked);
	});

	return prefs;
}

function hideNotice(root) {
	if (!root) return;

	root.classList.add("cookie-notice--hidden");
	root.setAttribute("hidden", "");
	root.classList.add("cookie-notice--has-consent");
}

function showNotice(root) {
	if (!root) return;

	root.removeAttribute("hidden");
	root.classList.remove("cookie-notice--hidden");
	showPanel(root, "main");
}

function openSettingsUi(root) {
	if (!root) return;

	const prefs = currentPrefs || { ...DEFAULT_PREFS };
	syncCheckboxes(root, prefs);
	root.removeAttribute("hidden");
	root.classList.remove("cookie-notice--hidden");
	showPanel(root, "settings");
}

function setConsent(prefs) {
	const next = {
		necessary: true,
		analytics: Boolean(prefs && prefs.analytics),
		marketing: Boolean(prefs && prefs.marketing),
	};

	currentPrefs = next;
	writeCookie(COOKIE_NAME, JSON.stringify(next), COOKIE_MAX_AGE);
	notifyChange(next);
	maybeLoadMetrika(next);

	return next;
}

function maybeLoadMetrika(prefs) {
	const ajax = getThemeAjax();
	const enabled =
		String(ajax.analytics_enabled) === "1" ||
		ajax.analytics_enabled === true;
	const id = String(ajax.metrika_id || "").trim();

	if (!prefs || !prefs.analytics || !enabled || !id || metrikaLoaded) {
		return;
	}

	metrikaLoaded = true;

	window.ym =
		window.ym ||
		function () {
			(window.ym.a = window.ym.a || []).push(arguments);
		};
	window.ym.l = Date.now();

	const script = document.createElement("script");
	script.async = true;
	script.src = "https://mc.yandex.ru/metrika/tag.js";
	document.head.appendChild(script);

	window.ym(Number(id) || id, "init", {
		clickmap: true,
		trackLinks: true,
		accurateTrackBounce: true,
		webvisor: true,
	});
}

function bindUi(root) {
	if (!root || root.dataset.cookieBound === "1") return;
	root.dataset.cookieBound = "1";

	root.addEventListener("click", (event) => {
		const btn = event.target.closest("[data-cookie-action]");
		if (!btn || !root.contains(btn)) return;

		const action = btn.getAttribute("data-cookie-action");

		if (action === "accept-all") {
			setConsent({ analytics: true, marketing: true });
			hideNotice(root);
			return;
		}

		if (action === "necessary-only") {
			setConsent({ analytics: false, marketing: false });
			hideNotice(root);
			return;
		}

		if (action === "open-settings") {
			openSettingsUi(root);
			return;
		}

		if (action === "back") {
			if (currentPrefs) {
				hideNotice(root);
			} else {
				showPanel(root, "main");
			}
			return;
		}

		if (action === "save-settings") {
			setConsent(readSettingsFromUi(root));
			hideNotice(root);
		}
	});
}

function bindFooterTriggers() {
	document.addEventListener("click", (event) => {
		const trigger = event.target.closest("[data-cookie-settings]");
		if (!trigger) return;

		event.preventDefault();
		const api = window.ksenonConsent;
		if (api && typeof api.openSettings === "function") {
			api.openSettings();
		}
	});
}

export function initCookieConsent() {
	const root = getRoot();
	const stored = parseConsentCookie(readCookie(COOKIE_NAME));

	currentPrefs = stored;

	window.ksenonConsent = {
		get() {
			return currentPrefs ? { ...currentPrefs } : null;
		},
		has(category) {
			if (!currentPrefs) return false;
			if (category === "necessary") return true;
			return Boolean(currentPrefs[category]);
		},
		set(prefs) {
			const next = setConsent(prefs || {});
			const el = getRoot();
			if (el) hideNotice(el);
			return next;
		},
		openSettings() {
			openSettingsUi(getRoot());
		},
		onChange(cb) {
			if (typeof cb === "function") {
				changeListeners.push(cb);
			}
		},
	};

	bindFooterTriggers();

	if (!root) {
		if (stored) maybeLoadMetrika(stored);
		return;
	}

	bindUi(root);

	if (stored) {
		syncCheckboxes(root, stored);
		hideNotice(root);
		maybeLoadMetrika(stored);
		return;
	}

	showNotice(root);
	showPanel(root, "main");
}
