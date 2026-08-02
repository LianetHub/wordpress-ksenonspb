"use strict";

import { initCookieConsent } from "./cookie-consent.js";

document.addEventListener("DOMContentLoaded", () => {
	initCookieConsent();
	initBurger();
	initHeaderScroll();
	initHeaderSubmenus();
	initFancybox();

	initCaseSteps();
	initCaseGallery();
	initCaseVideoPosterFallback();
	initAccordion();
	initHome();
	initBrandServicesFilter();
	initPortfolioBrandSearch();
	initPricingPage();
	initPhoneMask();
	initNameFields();
	initCf7();
	initFormUpload();
	initPortfolioCardImages();
	initYandexMap();
});

function initBurger() {
	const header = document.querySelector(".header");
	const drawer = document.querySelector(".header-drawer");
	const toggle = document.querySelector(".header__toggle");
	const backdrop = document.querySelector(".header-drawer__backdrop");

	if (!drawer || !toggle) return;

	const setMenuOpen = (isOpen) => {
		drawer.classList.toggle("is-open", isOpen);
		header?.classList.toggle("open-menu", isOpen);
		document.body.classList.toggle("lock", isOpen);
		toggle.setAttribute("aria-expanded", String(isOpen));
		toggle.setAttribute(
			"aria-label",
			isOpen ? "Закрыть меню" : "Открыть меню",
		);
		drawer.setAttribute("aria-hidden", String(!isOpen));
	};

	toggle.addEventListener("click", () => {
		setMenuOpen(!drawer.classList.contains("is-open"));
	});

	backdrop?.addEventListener("click", () => setMenuOpen(false));

	drawer
		.querySelectorAll(
			".header-drawer__link, .header-drawer__submenu-link, .header-drawer__primary, .header-drawer__logo",
		)
		.forEach((link) => {
			link.addEventListener("click", () => setMenuOpen(false));
		});

	document.addEventListener("keydown", (e) => {
		if (e.key === "Escape" && drawer.classList.contains("is-open")) {
			setMenuOpen(false);
		}
	});
}

function initHeaderSubmenus() {
	const desktopItems = document.querySelectorAll(
		".header__menu-item--has-sub",
	);
	const canHover = window.matchMedia("(any-hover: hover)").matches;

	const setDesktopOpen = (item, isOpen) => {
		item.classList.toggle("is-open", isOpen);
		const link = item.querySelector(":scope > .header__link");
		link?.setAttribute("aria-expanded", String(isOpen));
	};

	const closeAllDesktop = (except = null) => {
		desktopItems.forEach((item) => {
			if (item !== except) setDesktopOpen(item, false);
		});
	};

	if (!canHover) {
		desktopItems.forEach((item) => {
			const link = item.querySelector(":scope > .header__link");
			if (!link) return;

			link.addEventListener("click", (e) => {
				const isOpen = item.classList.contains("is-open");
				if (!isOpen) {
					e.preventDefault();
					closeAllDesktop(item);
					setDesktopOpen(item, true);
				}
			});
		});

		document.addEventListener("click", (e) => {
			if (!e.target.closest(".header__menu-item--has-sub")) {
				closeAllDesktop();
			}
		});

		document.addEventListener("keydown", (e) => {
			if (e.key === "Escape") closeAllDesktop();
		});
	}

	document
		.querySelectorAll(".header-drawer__sub-toggle")
		.forEach((toggle) => {
			toggle.addEventListener("click", () => {
				const item = toggle.closest(
					".header-drawer__menu-item--has-sub",
				);
				if (!item) return;

				const isOpen = item.classList.toggle("is-open");
				toggle.setAttribute("aria-expanded", String(isOpen));
				toggle.setAttribute(
					"aria-label",
					isOpen ? "Закрыть подменю" : "Открыть подменю",
				);
			});
		});
}

function initHeaderScroll() {
	const header = document.querySelector(".header");
	if (!header) return;

	const SCROLL_THRESHOLD = 46;

	const update = () => {
		header.classList.toggle(
			"is-scrolled",
			window.scrollY > SCROLL_THRESHOLD,
		);
	};

	update();
	window.addEventListener("scroll", update, { passive: true });
}

function initFancybox() {
	if (typeof Fancybox === "undefined") return;

	Fancybox.bind(
		'[data-fancybox]:not([data-fancybox="case-video"]):not([data-fancybox="case-gallery"])',
		{
			mainClass: "fancybox-popup",
			dragToClose: false,
			placeFocusBack: true,
			autoFocus: true,
			trapFocus: true,
		},
	);

	Fancybox.bind('[data-fancybox="case-gallery"]', {
		placeFocusBack: true,
		autoFocus: true,
		trapFocus: true,
	});

	Fancybox.bind('[data-fancybox="case-video"]', {
		mainClass: "fancybox-popup fancybox-video",
		closeButton: true,
		dragToClose: false,
		placeFocusBack: true,
		autoFocus: true,
		trapFocus: true,
		Html: {
			preload: false,
			iframeAttr: {
				allow: "autoplay; fullscreen; picture-in-picture; encrypted-media",
				allowfullscreen: "true",
				referrerpolicy: "strict-origin-when-cross-origin",
			},
		},
	});
}

function initCf7() {
	const showErrorPopup = () => {
		if (typeof Fancybox === "undefined") return;

		Fancybox.close();
		Fancybox.show([{ src: "#popup-error", type: "inline" }]);
	};

	const getThanksUrl = () => {
		const ajax = typeof theme_ajax !== "undefined" ? theme_ajax : {};
		const url = typeof ajax.thanks_url === "string" ? ajax.thanks_url.trim() : "";
		return url || "/thanks/";
	};

	const getPhoneDigits = (value) => String(value || "").replace(/\D/g, "");

	const isValidRuPhone = (value) => {
		const digits = getPhoneDigits(value);
		return digits.length === 11 && (digits[0] === "7" || digits[0] === "8");
	};

	const getConsentCheckbox = (form) =>
		form.querySelector(
			'.wpcf7-acceptance input[type="checkbox"], input[name="agree"][type="checkbox"], input[name="agree[]"][type="checkbox"]',
		);

	const getSubmitButton = (form) =>
		form.querySelector(
			'input.wpcf7-submit[type="submit"], input[type="submit"].wpcf7-submit, input[type="submit"]',
		);

	const syncFormSubmitState = (form) => {
		if (!(form instanceof Element)) return;

		const submit = getSubmitButton(form);
		if (!submit) return;

		const consent = getConsentCheckbox(form);
		const phone = form.querySelector('input[type="tel"]');
		const consentOk = !consent || consent.checked;
		const phoneOk = !phone || isValidRuPhone(phone.value);

		submit.disabled = !(consentOk && phoneOk);
	};

	const bindFormGuards = (form) => {
		if (!(form instanceof HTMLFormElement) || form.dataset.ksenonGuardsBound === "1") {
			return;
		}

		form.dataset.ksenonGuardsBound = "1";

		const consent = getConsentCheckbox(form);
		const phone = form.querySelector('input[type="tel"]');

		if (consent) {
			consent.addEventListener("change", () => syncFormSubmitState(form));
		}

		if (phone) {
			["input", "change", "blur", "keyup"].forEach((eventName) => {
				phone.addEventListener(eventName, () => syncFormSubmitState(form));
			});
		}

		form.addEventListener("submit", (event) => {
			const consentEl = getConsentCheckbox(form);
			const phoneEl = form.querySelector('input[type="tel"]');

			if ((consentEl && !consentEl.checked) || (phoneEl && !isValidRuPhone(phoneEl.value))) {
				event.preventDefault();
				event.stopPropagation();
				syncFormSubmitState(form);
			}
		});

		syncFormSubmitState(form);
	};

	const applySubmitLabel = (root) => {
		const label = (root.getAttribute("data-submit-label") || "").trim();
		if (!label) return;

		const submit = root.querySelector(
			'input.wpcf7-submit[type="submit"], input[type="submit"].wpcf7-submit, input[type="submit"]',
		);
		if (submit && submit.value !== label) {
			submit.value = label;
		}
	};

	document.querySelectorAll(".wpcf7-form").forEach((form) => bindFormGuards(form));

	document
		.querySelectorAll("[data-submit-label]")
		.forEach((root) => applySubmitLabel(root));

	["wpcf7invalid", "wpcf7spam", "wpcf7mailfailed", "wpcf7mailsent", "wpcf7reset"].forEach(
		(eventName) => {
			document.addEventListener(eventName, (event) => {
				const form = event.target;
				if (!(form instanceof Element)) return;
				const root = form.closest("[data-submit-label]");
				if (root) applySubmitLabel(root);
				if (form instanceof HTMLFormElement) {
					bindFormGuards(form);
					syncFormSubmitState(form);
				}
			});
		},
	);

	document.addEventListener("wpcf7mailsent", () => {
		window.location.assign(getThanksUrl());
	});
	document.addEventListener("wpcf7mailfailed", () => showErrorPopup());
	document.addEventListener("wpcf7spam", () => showErrorPopup());
}

function initFormUpload() {
	const MAX_FILES = 5;
	const MAX_SIZE = 10 * 1024 * 1024;
	const ALLOWED_EXT = new Set(["jpg", "jpeg", "png", "heic"]);
	const ALLOWED_MIME = new Set([
		"image/jpeg",
		"image/png",
		"image/heic",
		"image/heif",
	]);
	const PREVIEW_MIME = new Set(["image/jpeg", "image/png"]);

	const roots = document.querySelectorAll("[data-form-upload]");
	if (!roots.length) return;

	const getExt = (name) => {
		const parts = String(name || "")
			.toLowerCase()
			.split(".");
		return parts.length > 1 ? parts.pop() : "";
	};

	const isAllowedFile = (file) => {
		const ext = getExt(file.name);
		if (ALLOWED_EXT.has(ext)) return true;
		return ALLOWED_MIME.has(file.type);
	};

	const canPreview = (file) => {
		if (PREVIEW_MIME.has(file.type)) return true;
		const ext = getExt(file.name);
		return ext === "jpg" || ext === "jpeg" || ext === "png";
	};

	const getIconsBase = (root) => {
		const href =
			root
				.querySelector(".form-upload__icon-svg use")
				?.getAttribute("href") ||
			root
				.querySelector(".form-upload__icon-svg use")
				?.getAttribute("xlink:href") ||
			"";
		const hashIndex = href.indexOf("#");
		return hashIndex >= 0 ? href.slice(0, hashIndex) : href;
	};

	const syncInputFiles = (input, files) => {
		const transfer = new DataTransfer();
		files.forEach((file) => transfer.items.add(file));
		input.files = transfer.files;
	};

	roots.forEach((root) => {
		const dropzone = root.querySelector(".form-upload__dropzone");
		const input = root.querySelector('input[type="file"]');
		const list = root.querySelector("[data-form-upload-list]");
		const errorEl = root.querySelector("[data-form-upload-error]");

		if (!input || !list) return;

		const iconsBase = getIconsBase(root);
		let files = [];
		const objectUrls = new Map();

		const setError = (message) => {
			if (!errorEl) return;
			if (message) {
				errorEl.textContent = message;
				errorEl.hidden = false;
			} else {
				errorEl.textContent = "";
				errorEl.hidden = true;
			}
		};

		const revokeAllUrls = () => {
			objectUrls.forEach((url) => URL.revokeObjectURL(url));
			objectUrls.clear();
		};

		const updateFullState = () => {
			root.classList.toggle("is-full", files.length >= MAX_FILES);
		};

		const render = () => {
			revokeAllUrls();
			list.innerHTML = "";

			if (!files.length) {
				list.hidden = true;
				updateFullState();
				return;
			}

			list.hidden = false;
			const fragment = document.createDocumentFragment();

			files.forEach((file, index) => {
				const item = document.createElement("li");
				item.className = "form-upload__item";

				const thumb = document.createElement("div");
				thumb.className = "form-upload__thumb";

				if (canPreview(file)) {
					const img = document.createElement("img");
					img.className = "form-upload__thumb-img";
					img.alt = "";
					const url = URL.createObjectURL(file);
					objectUrls.set(index, url);
					img.src = url;
					thumb.appendChild(img);
				} else {
					const placeholder = document.createElement("div");
					placeholder.className = "form-upload__thumb-placeholder";
					placeholder.setAttribute("aria-hidden", "true");
					placeholder.innerHTML = iconsBase
						? `<svg width="28" height="28"><use href="${iconsBase}#icon-attach-plus"></use></svg>`
						: "";
					thumb.appendChild(placeholder);
				}

				const removeBtn = document.createElement("button");
				removeBtn.type = "button";
				removeBtn.className = "form-upload__remove";
				removeBtn.setAttribute(
					"aria-label",
					`Удалить файл ${file.name}`,
				);
				removeBtn.innerHTML = iconsBase
					? `<svg width="20" height="20" aria-hidden="true"><use href="${iconsBase}#icon-close-circle"></use></svg>`
					: "×";
				removeBtn.addEventListener("click", (event) => {
					event.preventDefault();
					event.stopPropagation();
					files = files.filter((_, i) => i !== index);
					syncInputFiles(input, files);
					setError("");
					render();
				});

				const name = document.createElement("span");
				name.className = "form-upload__name";
				name.textContent = file.name;
				name.title = file.name;

				item.appendChild(thumb);
				item.appendChild(removeBtn);
				item.appendChild(name);
				fragment.appendChild(item);
			});

			list.appendChild(fragment);
			updateFullState();
		};

		const clear = () => {
			files = [];
			syncInputFiles(input, files);
			setError("");
			render();
			input.value = "";
		};

		const addFiles = (incoming) => {
			const messages = [];
			const next = [...files];

			Array.from(incoming).forEach((file) => {
				if (next.length >= MAX_FILES) {
					messages.push(
						`Можно прикрепить не больше ${MAX_FILES} файлов`,
					);
					return;
				}

				if (!isAllowedFile(file)) {
					messages.push(`«${file.name}»: недопустимый формат`);
					return;
				}

				if (file.size > MAX_SIZE) {
					messages.push(`«${file.name}»: больше 10 МБ`);
					return;
				}

				const duplicate = next.some(
					(existing) =>
						existing.name === file.name &&
						existing.size === file.size &&
						existing.lastModified === file.lastModified,
				);
				if (duplicate) return;

				next.push(file);
			});

			files = next.slice(0, MAX_FILES);
			syncInputFiles(input, files);
			setError(messages[0] || "");
			render();
		};

		input.addEventListener("change", () => {
			const selected = Array.from(input.files || []);
			if (!selected.length) return;

			// Сброс value до sync, чтобы повторный выбор тех же файлов сработал;
			// актуальный FileList затем записывается через DataTransfer в addFiles.
			input.value = "";
			addFiles(selected);
		});

		const form = root.closest(".wpcf7-form") || root.closest("form");
		const onCf7Clear = (event) => {
			const targetForm = event.target;
			if (!form || !targetForm) return;
			if (targetForm === form || targetForm.contains(root)) {
				clear();
			}
		};

		document.addEventListener("wpcf7mailsent", onCf7Clear);
		document.addEventListener("wpcf7reset", onCf7Clear);

		if (dropzone) {
			dropzone.addEventListener("dragover", (event) => {
				event.preventDefault();
				if (files.length >= MAX_FILES) return;
				dropzone.classList.add("is-dragover");
			});

			dropzone.addEventListener("dragleave", () => {
				dropzone.classList.remove("is-dragover");
			});

			dropzone.addEventListener("drop", (event) => {
				event.preventDefault();
				dropzone.classList.remove("is-dragover");
				if (files.length >= MAX_FILES) {
					setError(`Можно прикрепить не больше ${MAX_FILES} файлов`);
					return;
				}
				if (event.dataTransfer?.files?.length) {
					addFiles(event.dataTransfer.files);
				}
			});
		}

		updateFullState();
	});
}

function initCaseSteps() {
	const root = document.querySelector("[data-case-steps]");
	if (!root) return;

	const items = Array.from(root.querySelectorAll(".case-done__item"));
	const buttons = Array.from(root.querySelectorAll("[data-case-step]"));
	const shots = Array.from(root.querySelectorAll("[data-case-step-image]"));

	if (!buttons.length) return;

	const setActive = (index) => {
		const activeIndex = Number(index);

		buttons.forEach((button) => {
			const isActive = Number(button.dataset.caseStep) === activeIndex;
			button.classList.toggle("is-active", isActive);
			button.setAttribute("aria-selected", String(isActive));
			button.tabIndex = isActive ? 0 : -1;
		});

		items.forEach((item, i) => {
			const isActive = i === activeIndex;
			item.classList.toggle("is-active", isActive);

			const panel = item.querySelector(".case-done__step-text");
			if (panel) {
				panel.hidden = !isActive;
			}
		});
		shots.forEach((shot) => {
			const shotIndex = Number(shot.dataset.caseStepImage);
			const isActive = shotIndex === activeIndex;
			const isPrev = shotIndex === activeIndex - 1;
			const isNext = shotIndex === activeIndex + 1;

			shot.classList.toggle("is-active", isActive);
			shot.classList.toggle("is-prev", isPrev);
			shot.classList.toggle("is-next", isNext);
		});
	};

	buttons.forEach((button) => {
		button.addEventListener("click", () => {
			setActive(button.dataset.caseStep);
		});

		button.addEventListener("keydown", (event) => {
			const current = Number(button.dataset.caseStep);
			let next = current;

			if (event.key === "ArrowDown" || event.key === "ArrowRight") {
				next = Math.min(current + 1, buttons.length - 1);
			} else if (event.key === "ArrowUp" || event.key === "ArrowLeft") {
				next = Math.max(current - 1, 0);
			} else if (event.key === "Home") {
				next = 0;
			} else if (event.key === "End") {
				next = buttons.length - 1;
			} else {
				return;
			}

			event.preventDefault();
			setActive(next);
			buttons[next]?.focus();
		});
	});

	setActive(0);
}

function initCaseGallery() {
	if (typeof Swiper === "undefined") return;

	document.querySelectorAll(".case-gallery__slider").forEach((sliderEl) => {
		const root = sliderEl.closest(".case-gallery");
		const slides = sliderEl.querySelectorAll(".swiper-slide");
		if (!slides.length) return;

		new Swiper(sliderEl, {
			slidesPerView: 1.15,
			spaceBetween: 16,
			watchOverflow: true,
			loop: slides.length > 2,
			navigation: {
				nextEl: root?.querySelector(".case-gallery__next"),
				prevEl: root?.querySelector(".case-gallery__prev"),
			},
			breakpoints: {
				991.98: {
					slidesPerView: 3,
					spaceBetween: 20,
					loop: slides.length > 3,
				},
			},
		});
	});
}

function initCaseVideoPosterFallback() {
	document.querySelectorAll("[data-yt-fallback]").forEach((img) => {
		img.addEventListener("error", () => {
			const fallback = img.getAttribute("data-yt-fallback");
			if (!fallback) return;
			img.removeAttribute("data-yt-fallback");
			img.src = fallback;
		});
	});
}

function initAccordion() {
	document.querySelectorAll("[data-accordion]").forEach((accordion) => {
		const items = Array.from(
			accordion.querySelectorAll(".accordion__item"),
		);

		items.forEach((item) => {
			const header = item.querySelector(".accordion__header");
			if (!header) return;

			header.addEventListener("click", () => {
				const isOpen = item.classList.contains("_active");

				items.forEach((other) => {
					if (other === item) return;
					other.classList.remove("_active");
					const otherHeader =
						other.querySelector(".accordion__header");
					if (otherHeader) {
						otherHeader.setAttribute("aria-expanded", "false");
					}
				});

				if (isOpen) {
					item.classList.remove("_active");
					header.setAttribute("aria-expanded", "false");
					return;
				}

				item.classList.add("_active");
				header.setAttribute("aria-expanded", "true");
			});
		});
	});
}

function initHome() {
	initHomeSwipers();
	initReviewsTabs();
}

function initReviewsTabs() {
	document.querySelectorAll("[data-reviews]").forEach((root) => {
		const tabs = root.querySelectorAll("[data-reviews-tab]");
		const panels = root.querySelectorAll("[data-reviews-panel]");
		const moreBtn = root.querySelector(".reviews__more");
		if (!tabs.length || !panels.length) return;

		const sourceUrls = {
			yandex: root.getAttribute("data-url-yandex") || "",
			drive2: root.getAttribute("data-url-drive2") || "",
		};
		const sourceLabels = {
			yandex: root.getAttribute("data-label-yandex") || "",
			drive2: root.getAttribute("data-label-drive2") || "",
		};

		const setMoreLink = (source) => {
			if (!moreBtn) return;

			const url = sourceUrls[source];
			if (url) {
				moreBtn.setAttribute("href", url);
			}

			const label = sourceLabels[source];
			if (!label) return;

			const textEl = moreBtn.querySelector(".btn__text");
			if (textEl) {
				textEl.textContent = label;
			} else {
				moreBtn.textContent = label;
			}
		};

		tabs.forEach((tab) => {
			tab.addEventListener("click", () => {
				const target = tab.getAttribute("data-reviews-tab");
				if (!target) return;

				tabs.forEach((item) => {
					const isActive = item === tab;
					item.classList.toggle("_active", isActive);
					item.setAttribute(
						"aria-selected",
						isActive ? "true" : "false",
					);
				});

				panels.forEach((panel) => {
					panel.classList.toggle(
						"_active",
						panel.getAttribute("data-reviews-panel") === target,
					);
				});

				setMoreLink(target);
			});
		});
	});
}

function initPricingPage() {
	initPricingTabs();
	initGiftAmounts();
}

function initPricingTabs() {
	document.querySelectorAll("[data-pricing]").forEach((root) => {
		const tabs = root.querySelectorAll("[data-pricing-tab]");
		const panels = root.querySelectorAll("[data-pricing-panel]");
		if (!tabs.length || !panels.length) return;

		tabs.forEach((tab) => {
			tab.addEventListener("click", () => {
				const target = tab.getAttribute("data-pricing-tab");
				if (!target) return;

				tabs.forEach((item) => {
					const isActive = item === tab;
					item.classList.toggle("_active", isActive);
					item.setAttribute("aria-selected", isActive ? "true" : "false");
				});

				panels.forEach((panel) => {
					const isActive =
						panel.getAttribute("data-pricing-panel") === target;
					panel.classList.toggle("_active", isActive);
					panel.hidden = !isActive;
				});
			});
		});
	});
}

function initGiftAmounts() {
	document.querySelectorAll("[data-gift-amounts]").forEach((root) => {
		const pills = root.querySelectorAll("[data-gift-amount]");
		if (!pills.length) return;

		const getSelected = () => {
			const active = root.querySelector("[data-gift-amount]._active");
			return active?.getAttribute("data-gift-amount") || "";
		};

		const syncCertificateForm = () => {
			const popup = document.querySelector("#popup-certificate");
			if (!popup) return;
			const input = popup.querySelector(
				'input[name="certificate-amount"]',
			);
			if (!input) return;

			const selected = getSelected();
			const isCustom = !selected || selected === "custom";

			if (isCustom) {
				input.value = "";
				input.readOnly = false;
			} else {
				input.value = selected;
				input.readOnly = true;
			}
		};

		pills.forEach((pill) => {
			pill.addEventListener("click", () => {
				pills.forEach((item) => {
					const isActive = item === pill;
					item.classList.toggle("_active", isActive);
					item.setAttribute("aria-pressed", isActive ? "true" : "false");
				});
				syncCertificateForm();
			});
		});

		root.querySelectorAll("[data-gift-open]").forEach((btn) => {
			btn.addEventListener("click", () => {
				syncCertificateForm();
			});
		});

		syncCertificateForm();
	});
}

/**
 * @class ConditionSwiper
 * Desktop-only Swiper: init above breakpoint, CSS grid on mobile.
 *
 * @param {string|Element} slider
 * @param {Object} options
 * @param {number} [minWidth=767.98]
 * @see https://swiperjs.com/get-started
 */
class ConditionSwiper {
	constructor(slider, options, minWidth = 767.98) {
		this.el =
			typeof slider === "string"
				? document.querySelector(slider)
				: slider;
		this.options = options;
		this.minWidth = minWidth;
		this.init = false;
		this.swiper = null;

		if (!this.el || typeof Swiper === "undefined") return;

		this.handleResize();
		window.addEventListener("resize", () => this.handleResize());
	}

	handleResize() {
		if (window.innerWidth > this.minWidth) {
			if (!this.init) {
				this.init = true;
				this.swiper = new Swiper(this.el, this.options);
			}
		} else if (this.init) {
			this.swiper.destroy(true, true);
			this.swiper = null;
			this.init = false;
		}
	}
}

function initHomeSwipers() {
	if (typeof Swiper === "undefined") return;

	const heroPromoEl = document.querySelector(".hero__promo-slider");
	if (heroPromoEl) {
		const heroPromo = heroPromoEl.closest(".hero__promo");
		const heroPromoPrev = heroPromo?.querySelector(".hero__promo-prev");
		const heroPromoNext = heroPromo?.querySelector(".hero__promo-next");

		new Swiper(heroPromoEl, {
			slidesPerView: 1,
			speed: 1000,
			loop: heroPromoEl.querySelectorAll(".swiper-slide").length > 1,
			watchOverflow: true,
			effect: "fade",
			fadeEffect: {
				crossFade: true,
			},
			autoplay: {
				delay: 5000,
				disableOnInteraction: false,
				stopOnLastSlide: false,
			},
			navigation: {
				nextEl: heroPromoNext,
				prevEl: heroPromoPrev,
			},
		});
	}

	const servicesTeaserEl = document.querySelector(
		".services-teaser__slider .swiper",
	);
	if (servicesTeaserEl) {
		const servicesTeaserRoot = servicesTeaserEl.closest(".services-teaser");

		new ConditionSwiper(servicesTeaserEl, {
			slidesPerView: 2,
			spaceBetween: 10,
			watchOverflow: true,
			breakpoints: {
				991.98: {
					slidesPerView: 3,
					spaceBetween: 20,
				},
			},
			navigation: {
				nextEl: servicesTeaserRoot?.querySelector(
					".services-teaser__next",
				),
				prevEl: servicesTeaserRoot?.querySelector(
					".services-teaser__prev",
				),
			},
		});
	}
}

function initNameFields() {
	const nameInputs = document.querySelectorAll('input[name="your-name"]');

	if (!nameInputs.length) return;

	const stripDigits = (value) => String(value || "").replace(/\d/g, "");

	nameInputs.forEach((input) => {
		input.removeAttribute("required");
		input.removeAttribute("aria-required");
		input.classList.remove("wpcf7-validates-as-required");

		input.addEventListener("input", () => {
			const cleaned = stripDigits(input.value);
			if (cleaned !== input.value) {
				const pos = input.selectionStart;
				input.value = cleaned;
				if (typeof pos === "number") {
					const nextPos = Math.max(0, pos - 1);
					input.setSelectionRange(nextPos, nextPos);
				}
			}
		});

		input.addEventListener("paste", (event) => {
			event.preventDefault();
			const pasted = event.clipboardData?.getData("text") || "";
			const cleaned = stripDigits(pasted);
			const start = input.selectionStart ?? input.value.length;
			const end = input.selectionEnd ?? input.value.length;
			const next = stripDigits(
				input.value.slice(0, start) + cleaned + input.value.slice(end),
			);
			input.value = next;
			const caret = start + cleaned.length;
			input.setSelectionRange(caret, caret);
			input.dispatchEvent(new Event("input", { bubbles: true }));
		});
	});
}

function initPhoneMask() {
	const phoneInputs = document.querySelectorAll('input[type="tel"]');

	if (!phoneInputs.length) return;

	const getDigits = (value) => String(value || "").replace(/\D/g, "");

	const normalizeRuDigits = (digits) => {
		let d = getDigits(digits);
		if (!d) return "";

		if (d[0] === "8") {
			d = "7" + d.slice(1);
		} else if (d[0] === "9") {
			d = "7" + d;
		} else if (d[0] !== "7") {
			d = "7" + d;
		}

		return d.slice(0, 11);
	};

	const formatRuPhone = (digits) => {
		const d = normalizeRuDigits(digits);
		if (!d) return "";

		let formatted = "+7";

		if (d.length > 1) {
			formatted += " (" + d.slice(1, Math.min(4, d.length));
		}
		if (d.length >= 5) {
			formatted += ") " + d.slice(4, Math.min(7, d.length));
		}
		if (d.length >= 8) {
			formatted += "-" + d.slice(7, Math.min(9, d.length));
		}
		if (d.length >= 10) {
			formatted += "-" + d.slice(9, 11);
		}

		return formatted;
	};

	const onPhoneInput = (e) => {
		const input = e.target;
		const digits = getDigits(input.value);

		if (!digits) {
			input.value = "";
			return;
		}

		input.value = formatRuPhone(digits);
	};

	const onPhoneFocus = (e) => {
		const input = e.target;
		if (!getDigits(input.value)) {
			input.value = "+7 ";
		}
	};

	const onPhoneBlur = (e) => {
		const input = e.target;
		const digits = getDigits(input.value);
		if (!digits || digits === "7") {
			input.value = "";
		}
	};

	const onPhoneKeyDown = (e) => {
		const input = e.target;
		const digits = getDigits(input.value);

		if (e.key === "Backspace" && digits.length <= 1) {
			e.preventDefault();
			input.value = "";
		}
	};

	const onPhonePaste = (e) => {
		e.preventDefault();
		const input = e.target;
		const pasted = e.clipboardData?.getData("text") || "";
		input.value = formatRuPhone(pasted);
		input.dispatchEvent(new Event("input", { bubbles: true }));
	};

	phoneInputs.forEach((input) => {
		input.setAttribute("inputmode", "tel");
		input.setAttribute("autocomplete", "tel");
		input.setAttribute("maxlength", "18");

		input.addEventListener("focus", onPhoneFocus);
		input.addEventListener("blur", onPhoneBlur);
		input.addEventListener("keydown", onPhoneKeyDown);
		input.addEventListener("input", onPhoneInput);
		input.addEventListener("paste", onPhonePaste);

		if (getDigits(input.value)) {
			input.value = formatRuPhone(input.value);
		}
	});
}

function initBrandServicesFilter() {
	document.querySelectorAll("[data-brand-services]").forEach((root) => {
		const buttons = Array.from(root.querySelectorAll("[data-brand-filter]"));
		const cards = Array.from(root.querySelectorAll(".service-card[data-categories]"));
		if (!buttons.length || !cards.length) return;

		const setActive = (activeBtn) => {
			const filter = activeBtn.getAttribute("data-brand-filter") || "all";

			buttons.forEach((btn) => {
				const isActive = btn === activeBtn;
				btn.classList.toggle("_active", isActive);
				btn.setAttribute("aria-pressed", isActive ? "true" : "false");
			});

			cards.forEach((card) => {
				if (filter === "all") {
					card.hidden = false;
					return;
				}
				const cats = (card.getAttribute("data-categories") || "").split(/\s+/).filter(Boolean);
				card.hidden = !cats.includes(filter);
			});
		};

		buttons.forEach((btn) => {
			btn.addEventListener("click", () => setActive(btn));
		});
	});
}

function initPortfolioBrandSearch() {
	const form = document.querySelector("[data-portfolio-brand-search]");
	if (!form) return;

	const valueInput = form.querySelector("[data-brand-value]");
	const desktopInput = form.querySelector("[data-brand-desktop-input]");
	const sheetSearch = form.querySelector("[data-brand-sheet-search]");
	const clearBtns = Array.from(form.querySelectorAll("[data-brand-clear]"));
	const openBtns = Array.from(form.querySelectorAll("[data-brand-sheet-open]"));
	const closeBtns = Array.from(form.querySelectorAll("[data-brand-sheet-close]"));
	const sheet = form.querySelector("[data-brand-sheet]");
	const brands = form.querySelector("[data-brand-grid]");
	const listbox = form.querySelector(".cpt-archive__brands-grid");
	const empty = form.querySelector(".cpt-archive__brands-empty");
	const triggerLabel = form.querySelector("[data-brand-trigger-label]");
	const placeholderText = desktopInput?.getAttribute("placeholder") || "Выберите марку..";
	const mobileMq = window.matchMedia("(max-width: 767.98px)");

	const options = listbox
		? Array.from(listbox.querySelectorAll(".cpt-archive__brands-option"))
		: [];
	let focusIndex = -1;
	const initialBrand = (valueInput?.value || desktopInput?.value || "").trim();
	const hadBrandOnLoad = initialBrand !== "";
	let committedBrand = initialBrand;
	let sheetOpen = false;

	const normalizeBrand = (value) => (value || "").trim().toLowerCase();

	const isMobile = () => mobileMq.matches;

	const syncClearButtons = () => {
		const hasValue = committedBrand !== "" || (desktopInput?.value || "").trim() !== "";
		clearBtns.forEach((btn) => {
			btn.hidden = !hasValue;
		});
	};

	const syncValueInput = (brand) => {
		if (!valueInput) return;
		if (brand) {
			valueInput.disabled = false;
			valueInput.value = brand;
		} else {
			valueInput.value = "";
			valueInput.disabled = true;
		}
	};

	const syncTriggerLabel = () => {
		if (!triggerLabel) return;
		triggerLabel.textContent = committedBrand || placeholderText;
		triggerLabel.classList.toggle("is-placeholder", committedBrand === "");
	};

	const clearFocus = () => {
		focusIndex = -1;
		desktopInput?.removeAttribute("aria-activedescendant");
		options.forEach((option) => {
			option.classList.remove("_active");
		});
	};

	const showAllOptions = () => {
		options.forEach((option) => {
			const item = option.closest(".cpt-archive__brands-item");
			if (item) {
				item.hidden = false;
			}
		});
		if (empty) {
			empty.hidden = true;
		}
		if (listbox) {
			listbox.hidden = false;
		}
	};

	const filterSheetOptions = (query) => {
		const normalized = normalizeBrand(query);
		let visibleCount = 0;

		options.forEach((option) => {
			const brand = normalizeBrand(option.dataset.brand);
			const match = !normalized || brand.includes(normalized);
			const item = option.closest(".cpt-archive__brands-item");
			if (item) {
				item.hidden = !match;
			}
			if (match) {
				visibleCount += 1;
			}
		});

		if (empty) {
			empty.hidden = visibleCount > 0;
		}
		if (listbox) {
			listbox.hidden = visibleCount === 0;
		}
		clearFocus();
	};

	const syncSelectionClasses = () => {
		const selected = normalizeBrand(committedBrand);
		if (brands) {
			brands.classList.toggle("cpt-archive__brands--active", selected !== "");
		}
		options.forEach((option) => {
			const isSelected = selected !== "" && normalizeBrand(option.dataset.brand) === selected;
			option.classList.toggle("is-active", isSelected);
			option.setAttribute("aria-selected", isSelected ? "true" : "false");
		});
	};

	const setFocus = (index) => {
		const visible = options.filter((option) => !option.closest(".cpt-archive__brands-item")?.hidden);
		clearFocus();
		if (!visible.length || index < 0 || index >= visible.length) {
			return;
		}
		focusIndex = index;
		const option = visible[index];
		option.classList.add("_active");
		desktopInput?.setAttribute("aria-activedescendant", option.id);
		option.scrollIntoView({ block: "nearest" });
	};

	const findExactOption = (query) => {
		const normalized = normalizeBrand(query);
		if (!normalized) return null;
		return options.find((option) => normalizeBrand(option.dataset.brand) === normalized) || null;
	};

	const submitForm = () => {
		if (form.requestSubmit) {
			form.requestSubmit();
		} else {
			form.submit();
		}
	};

	const updateSheetKeyboardOffset = () => {
		if (!sheet || !sheetOpen || !isMobile()) return;
		const panel = sheet.querySelector(".cpt-archive__brand-sheet-panel");
		if (!panel) return;
		const viewport = window.visualViewport;
		const keyboardOffset = viewport
			? Math.max(0, window.innerHeight - viewport.height - viewport.offsetTop)
			: 0;
		panel.style.setProperty("--brand-sheet-keyboard", `${keyboardOffset}px`);
	};

	let sheetCloseTimer = null;

	const closeSheet = () => {
		if (!sheet) return;
		sheetOpen = false;
		sheet.classList.remove("is-open");
		document.body.classList.remove("lock");
		openBtns.forEach((btn) => btn.setAttribute("aria-expanded", "false"));
		if (sheetSearch) {
			sheetSearch.value = "";
		}
		showAllOptions();
		syncSelectionClasses();
		const panel = sheet.querySelector(".cpt-archive__brand-sheet-panel");
		panel?.style.removeProperty("--brand-sheet-keyboard");

		if (sheetCloseTimer) {
			clearTimeout(sheetCloseTimer);
		}
		sheetCloseTimer = window.setTimeout(() => {
			if (!sheetOpen && isMobile()) {
				sheet.hidden = true;
			}
		}, 280);
	};

	const openSheet = () => {
		if (!sheet || !isMobile()) return;
		if (sheetCloseTimer) {
			clearTimeout(sheetCloseTimer);
			sheetCloseTimer = null;
		}
		sheetOpen = true;
		sheet.hidden = false;
		requestAnimationFrame(() => {
			sheet.classList.add("is-open");
		});
		document.body.classList.add("lock");
		openBtns.forEach((btn) => btn.setAttribute("aria-expanded", "true"));
		if (sheetSearch) {
			sheetSearch.value = "";
		}
		showAllOptions();
		syncSelectionClasses();
		updateSheetKeyboardOffset();
	};

	const selectOption = (option) => {
		if (!option) return;

		const brand = (option.dataset.brand || "").trim();
		const isSame = normalizeBrand(brand) === normalizeBrand(committedBrand);

		if (isSame) {
			committedBrand = "";
			if (desktopInput) {
				desktopInput.value = "";
			}
			syncValueInput("");
			syncTriggerLabel();
			syncClearButtons();
			syncSelectionClasses();
			closeSheet();
			submitForm();
			return;
		}

		committedBrand = brand;
		if (desktopInput) {
			desktopInput.value = brand;
		}
		syncValueInput(brand);
		syncTriggerLabel();
		syncClearButtons();
		syncSelectionClasses();
		closeSheet();
		submitForm();
	};

	const clearField = () => {
		const shouldReload = hadBrandOnLoad || committedBrand !== "";
		committedBrand = "";
		if (desktopInput) {
			desktopInput.value = "";
		}
		if (sheetSearch) {
			sheetSearch.value = "";
		}
		syncValueInput("");
		syncTriggerLabel();
		syncClearButtons();
		showAllOptions();
		syncSelectionClasses();
		closeSheet();

		if (shouldReload) {
			submitForm();
			return;
		}

		if (!isMobile()) {
			desktopInput?.focus();
		}
	};

	syncValueInput(committedBrand);
	syncTriggerLabel();
	syncClearButtons();
	syncSelectionClasses();
	showAllOptions();
	desktopInput?.setAttribute("aria-expanded", "true");

	clearBtns.forEach((btn) => {
		btn.addEventListener("mousedown", (event) => {
			event.preventDefault();
		});
		btn.addEventListener("click", (event) => {
			event.preventDefault();
			clearField();
		});
	});

	openBtns.forEach((btn) => {
		btn.addEventListener("click", (event) => {
			event.preventDefault();
			openSheet();
		});
	});

	closeBtns.forEach((btn) => {
		btn.addEventListener("click", (event) => {
			event.preventDefault();
			closeSheet();
		});
	});

	desktopInput?.addEventListener("input", () => {
		const exact = findExactOption(desktopInput.value);
		committedBrand = exact ? (exact.dataset.brand || "").trim() : "";
		syncValueInput(desktopInput.value.trim());
		syncTriggerLabel();
		syncClearButtons();
		showAllOptions();
		syncSelectionClasses();
		clearFocus();
	});

	desktopInput?.addEventListener("keydown", (event) => {
		const visible = options.filter((option) => !option.closest(".cpt-archive__brands-item")?.hidden);

		switch (event.key) {
			case "ArrowDown":
				event.preventDefault();
				setFocus(focusIndex < visible.length - 1 ? focusIndex + 1 : 0);
				break;
			case "ArrowUp":
				event.preventDefault();
				setFocus(focusIndex > 0 ? focusIndex - 1 : visible.length - 1);
				break;
			case "Enter":
				if (focusIndex >= 0 && visible[focusIndex]) {
					event.preventDefault();
					selectOption(visible[focusIndex]);
				} else {
					syncValueInput(desktopInput.value.trim());
				}
				break;
			case "Escape":
				event.preventDefault();
				clearFocus();
				break;
			default:
				break;
		}
	});

	sheetSearch?.addEventListener("input", () => {
		filterSheetOptions(sheetSearch.value);
	});

	sheetSearch?.addEventListener("focus", () => {
		updateSheetKeyboardOffset();
	});

	options.forEach((option) => {
		option.addEventListener("click", () => {
			selectOption(option);
		});
	});

	document.addEventListener("keydown", (event) => {
		if (event.key === "Escape" && sheetOpen) {
			event.preventDefault();
			closeSheet();
		}
	});

	window.visualViewport?.addEventListener("resize", updateSheetKeyboardOffset);
	window.visualViewport?.addEventListener("scroll", updateSheetKeyboardOffset);

	form.addEventListener("submit", () => {
		const typed = (desktopInput?.value || committedBrand || "").trim();
		syncValueInput(typed);
	});

	mobileMq.addEventListener("change", () => {
		if (!isMobile()) {
			sheetOpen = false;
			document.body.classList.remove("lock");
			if (sheet) {
				sheet.hidden = false;
				sheet.classList.remove("is-open");
			}
			showAllOptions();
			syncSelectionClasses();
		} else if (sheet) {
			sheetOpen = false;
			sheet.hidden = true;
			sheet.classList.remove("is-open");
			document.body.classList.remove("lock");
		}
	});

	if (!isMobile() && sheet) {
		sheet.hidden = false;
	}
}

function initPortfolioCardImages() {
	const images = document.querySelectorAll(".portfolio-card__img");
	if (!images.length) return;

	const markLoaded = (img) => {
		if (img.classList.contains("_loaded")) return;
		img.classList.add("_loaded");
		img.closest(".portfolio-card__image")?.classList.add("_loaded");
	};

	images.forEach((img) => {
		if (img.complete) {
			markLoaded(img);
			return;
		}

		img.addEventListener("load", () => markLoaded(img), { once: true });
		img.addEventListener("error", () => markLoaded(img), { once: true });
	});
}

function initYandexMap() {
	const mapContainer = document.getElementById("map");
	if (!mapContainer) return;

	const getIconParams = () => {
		const width = window.innerWidth;
		let size = [62, 70];

		if (width <= 767) {
			size = [47, 53];
		} else if (width <= 1024) {
			size = [40, 45];
		}

		// Сдвиг вниз: острие у «начала» здания, а не в середине
		const tipShiftY = 12;

		return {
			size,
			offset: [-(size[0] / 2), -size[1] + tipShiftY],
		};
	};

	const init = () => {
		const rawCoords = mapContainer.dataset.coords;
		const coords = rawCoords
			? rawCoords.split(",").map((item) => parseFloat(item.trim()))
			: [60.006714, 30.35932];

		if (coords.length < 2 || coords.some((n) => Number.isNaN(n))) {
			return;
		}

		const zoom = parseInt(mapContainer.dataset.zoom, 10) || 16;
		const iconPath = mapContainer.dataset.icon;
		const iconParams = getIconParams();
		const title = (mapContainer.dataset.title || "КБ авто").trim() || "КБ авто";

		const map = new ymaps.Map("map", {
			center: coords,
			zoom,
			controls: ["zoomControl"],
		});

		map.behaviors.disable("scrollZoom");

		const placemarkOptions = {
			hasBalloon: false,
		};

		if (iconPath) {
			Object.assign(placemarkOptions, {
				iconLayout: "default#image",
				iconImageHref: iconPath,
				iconImageSize: iconParams.size,
				iconImageOffset: iconParams.offset,
			});
		} else {
			Object.assign(placemarkOptions, {
				preset: "islands#orangeDotIconWithCaption",
				iconCaption: "КБ\nавто",
			});
		}

		map.geoObjects.add(
			new ymaps.Placemark(
				coords,
				{
					hintContent: title,
				},
				placemarkOptions,
			),
		);
	};

	const loadScript = () => {
		if (typeof ymaps !== "undefined") {
			ymaps.ready(init);
			return;
		}

		const apiKey = mapContainer.dataset.apikey || "";
		const script = document.createElement("script");
		script.src = `https://api-maps.yandex.ru/2.1/?${apiKey ? `apikey=${encodeURIComponent(apiKey)}&` : ""}lang=ru_RU`;
		script.async = true;
		script.onload = () => {
			ymaps.ready(init);
		};
		document.head.appendChild(script);
	};

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					loadScript();
					observer.unobserve(entry.target);
				}
			});
		},
		{ rootMargin: "200px" },
	);

	observer.observe(mapContainer);
}
