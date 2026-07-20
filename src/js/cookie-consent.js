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
let initialized = false;
const changeListeners = [];

function getThemeAjax() {
	return typeof theme_ajax !== "undefined" ? theme_ajax : {};
}

function parseConsentCookie(raw) {
	if (!raw) return null;

	try {
		let value = raw;
		try {
			value = decodeURIComponent(raw);
		} catch (e) {
			value = raw;
		}

		const data = JSON.parse(value);
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
			/* ignore */
		}
	});

	try {
		document.dispatchEvent(
			new CustomEvent("ksenon:consent-change", { detail: prefs }),
		);
	} catch (e) {
		/* ignore */
	}
}

function getRoot() {
	return document.getElementById("cookie-notice");
}

function showPanel(root, name) {
	if (!root) return;

	root.querySelectorAll("[data-cookie-panel]").forEach((panel) => {
		panel.hidden = panel.getAttribute("data-cookie-panel") !== name;
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
	const prefs = {
		necessary: true,
		analytics: false,
		marketing: false,
	};
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
	root.classList.add("cookie-notice--has-consent");

	window.setTimeout(() => {
		if (!root.classList.contains("cookie-notice--hidden")) return;
		root.setAttribute("hidden", "");
	}, 320);
}

function showNotice(root) {
	if (!root) return;

	root.removeAttribute("hidden");
	showPanel(root, "main");
	// Force reflow so opacity/transform transition runs.
	void root.offsetWidth;
	root.classList.remove("cookie-notice--hidden");
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

function handleAction(action, root) {
	if (!root || !action) return;

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
}

function onDocumentClick(event) {
	const settingsTrigger = event.target.closest("[data-cookie-settings]");
	if (settingsTrigger) {
		event.preventDefault();
		openSettingsUi(getRoot());
		return;
	}

	const btn = event.target.closest("[data-cookie-action]");
	if (!btn) return;

	const root = getRoot();
	if (!root || !root.contains(btn)) return;

	event.preventDefault();
	handleAction(btn.getAttribute("data-cookie-action"), root);
}

export function initCookieConsent() {
	if (initialized) return;
	initialized = true;

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

	document.addEventListener("click", onDocumentClick);

	if (!root) {
		if (stored) maybeLoadMetrika(stored);
		return;
	}

	if (stored) {
		syncCheckboxes(root, stored);
		root.classList.add(
			"cookie-notice--hidden",
			"cookie-notice--has-consent",
		);
		root.setAttribute("hidden", "");
		maybeLoadMetrika(stored);
		return;
	}

	root.classList.add("cookie-notice--hidden");
	showNotice(root);
	showPanel(root, "main");
}

if (document.readyState === "loading") {
	document.addEventListener("DOMContentLoaded", initCookieConsent);
} else {
	initCookieConsent();
}
