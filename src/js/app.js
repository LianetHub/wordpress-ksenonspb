"use strict";


import { initTooltips } from "./tooltip.js";
document.addEventListener("DOMContentLoaded", () => {
	initBurger();
	initHeaderScroll();
	initHeaderSubmenus();
	initFancybox();
	initPartnersAutoPopup();
	initProductTabs();
	initCaseSteps();
	initProductTableMore();
	initAccordion();
	initTooltips();
	initHome();
	initDevicesPage();
	initRelatedEquipmentSwiper();
	initCptArchiveFilters();
	initPhoneMask();
	initCf7();
});

function initBurger() {
	const drawer = document.querySelector(".header-drawer");
	const toggle = document.querySelector(".header__toggle");
	const backdrop = document.querySelector(".header-drawer__backdrop");

	if (!drawer || !toggle) return;

	const setMenuOpen = (isOpen) => {
		drawer.classList.toggle("is-open", isOpen);
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
		'[data-fancybox]:not([data-fancybox="pick-video"]):not([data-fancybox="case-video"])',
		{
			mainClass: "fancybox-popup",
			dragToClose: false,
			placeFocusBack: true,
			autoFocus: true,
			trapFocus: true,
		},
	);

	Fancybox.bind(
		'[data-fancybox="pick-video"], [data-fancybox="case-video"]',
		{
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
		},
	);
}

function initPartnersAutoPopup() {
	const COOKIE_NAME = "ksenon_partners_popup_seen";
	const DELAY_MS = 30_000;
	const COOKIE_DAYS = 30;

	const getCookie = (name) => {
		const escaped = name.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
		const match = document.cookie.match(
			new RegExp(`(?:^|; )${escaped}=([^;]*)`),
		);
		return match ? decodeURIComponent(match[1]) : null;
	};

	const setCookie = (name, value) => {
		const expires = new Date(
			Date.now() + COOKIE_DAYS * 864e5,
		).toUTCString();
		document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
	};

	const hasSeenPopup = () => getCookie(COOKIE_NAME) === "1";

	const markPopupSeen = () => setCookie(COOKIE_NAME, "1");

	const popup = document.getElementById("popup-partners");
	if (!popup || !popup.querySelector(".popup-modal__partners")) return;

	document.addEventListener("click", (e) => {
		const trigger = e.target.closest(
			'[data-fancybox][data-src="#popup-partners"]',
		);
		if (trigger) markPopupSeen();
	});

	if (hasSeenPopup()) return;

	setTimeout(() => {
		if (hasSeenPopup()) return;
		if (typeof Fancybox === "undefined") return;
		if (Fancybox.getInstance()) return;

		markPopupSeen();
		Fancybox.show([{ src: "#popup-partners", type: "inline" }], {
			mainClass: "fancybox-popup",
			dragToClose: false,
			placeFocusBack: true,
			autoFocus: true,
			trapFocus: true,
		});
	}, DELAY_MS);
}

function initCf7() {
	const showStatusPopup = (isError) => {
		if (typeof Fancybox === "undefined") return;

		const target = isError ? "#popup-error" : "#popup-success";
		Fancybox.close();
		Fancybox.show([{ src: target, type: "inline" }]);
	};

	document.addEventListener("wpcf7mailsent", () => showStatusPopup(false));
	document.addEventListener("wpcf7mailfailed", () => showStatusPopup(true));
	document.addEventListener("wpcf7spam", () => showStatusPopup(true));
}


function initProductTabs() {
	const tabs = document.querySelectorAll("[data-product-tab]");
	const panels = document.querySelectorAll("[data-product-panel]");

	if (!tabs.length || !panels.length) return;

	tabs.forEach((tab) => {
		tab.addEventListener("click", () => {
			const target = tab.dataset.productTab;

			tabs.forEach((item) => {
				const isActive = item === tab;
				item.classList.toggle("_active", isActive);
				item.setAttribute("aria-selected", String(isActive));
			});

			panels.forEach((panel) => {
				const isActive = panel.dataset.productPanel === target;
				panel.classList.toggle("_active", isActive);
				panel.hidden = !isActive;
			});
		});
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

function initProductTableMore() {
	const wrap = document.querySelector("[data-product-table]");
	const btn = document.querySelector("[data-product-table-more]");

	if (!wrap || !btn) return;

	const rows = wrap.querySelectorAll(".product-table tbody tr");

	if (rows.length <= 10) {
		btn.closest(".product-table__foot")?.remove();
		return;
	}

	btn.addEventListener("click", () => {
		const expanded = wrap.classList.toggle("product-table-wrap--expanded");
		wrap.querySelector(".product-table")?.classList.toggle(
			"product-table--expanded",
			expanded,
		);
		btn.setAttribute("aria-expanded", String(expanded));
	});
}

function initAccordion() {
	if (typeof jQuery === "undefined") {
		return;
	}

	const $ = jQuery;
	const duration = 400;

	$("[data-accordion]").each(function () {
		const $accordion = $(this);
		const $items = $accordion.find(".accordion__item");

		$items.each(function () {
			const $item = $(this);
			const $body = $item.children(".accordion__body");

			if (!$item.hasClass("_active")) {
				$body.hide();
			}
		});

		$items.find(".accordion__header").on("click", function () {
			const $header = $(this);
			const $item = $header.closest(".accordion__item");
			const $body = $item.children(".accordion__body");
			const isOpen = $item.hasClass("_active");

			if (isOpen) {
				$body.stop(true, true).slideToggle(duration, function () {
					$item.removeClass("_active");
				});
				$header.attr("aria-expanded", "false");
				return;
			}

			$items.not($item).each(function () {
				const $other = $(this);

				if (!$other.hasClass("_active")) {
					return;
				}

				$other
					.children(".accordion__body")
					.stop(true, true)
					.slideUp(duration, function () {
						$other.removeClass("_active");
					});
				$other
					.find(".accordion__header")
					.attr("aria-expanded", "false");
			});

			$item.addClass("_active");
			$body.stop(true, true).slideToggle(duration);
			$header.attr("aria-expanded", "true");
		});
	});
}

function initHome() {
	initAudiencePills();
	initHomePanels();
	initHomeSwipers();
	initReviewsTabs();
}

function initAudiencePills() {
	const root = document.querySelector("[data-audience]");
	if (!root) return;

	const cards = root.querySelectorAll("[data-audience-card]");

	cards.forEach((card) => {
		card.addEventListener("click", () => {
			const isOpen = card.classList.contains("_active");

			cards.forEach((item) => {
				item.classList.remove("_active");
				item.setAttribute("aria-expanded", "false");
			});

			if (!isOpen) {
				card.classList.add("_active");
				card.setAttribute("aria-expanded", "true");
			}
		});
	});
}

function initHomePanels() {
	document.querySelectorAll("[data-panels]").forEach((wrap) => {
		const items = wrap.querySelectorAll(".panels__item");

		items.forEach((item) => {
			const head = item.querySelector(".panels__heading");
			if (!head) return;

			head.addEventListener("click", () => {
				const isOpen = item.classList.contains("_active");

				items.forEach((other) => {
					other.classList.remove("_active");
					other
						.querySelector(".panels__heading")
						?.setAttribute("aria-expanded", "false");
				});

				if (!isOpen) {
					item.classList.add("_active");
					head.setAttribute("aria-expanded", "true");
				}
			});
		});
	});
}

function initReviewsTabs() {
	document.querySelectorAll("[data-reviews]").forEach((root) => {
		const tabs = root.querySelectorAll("[data-reviews-tab]");
		const panels = root.querySelectorAll("[data-reviews-panel]");
		if (!tabs.length || !panels.length) return;

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
			});
		});
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
			typeof slider === "string" ? document.querySelector(slider) : slider;
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

	const portfolioEl = document.querySelector(".portfolio-teaser__slider");
	if (portfolioEl) {
		new Swiper(portfolioEl, {
			slidesPerView: 1,
			spaceBetween: 20,
			breakpoints: {
				991.98: {
					slidesPerView: 2,
					spaceBetween: 20,
					enabled:
						portfolioEl.querySelectorAll(".swiper-slide").length >
						2,
				},
			},
			navigation: {
				nextEl: ".portfolio-teaser__next",
				prevEl: ".portfolio-teaser__prev",
			},
		});
	}

	const chooseEl = document.querySelector(".choose__slider");
	if (chooseEl) {
		new Swiper(chooseEl, {
			slidesPerView: 1,
			spaceBetween: 10,
			breakpoints: {
				991: {
					slidesPerView: 2,
					spaceBetween: 20,
				},
			},
			navigation: {
				nextEl: ".choose__arrow--next",
				prevEl: ".choose__arrow--prev",
			},
		});
	}

	const processEl = document.querySelector(".process__slider");
	if (processEl) {
		new Swiper(processEl, {
			slidesPerView: "auto",
			spaceBetween: 10,
			breakpoints: {
				991: {
					enabled: false,
				},
			},
		});
	}

	const partnersEl = document.querySelector(".partners__slider");
	if (partnersEl) {
		new Swiper(partnersEl, {
			slidesPerView: "auto",
			spaceBetween: 12,
			breakpoints: {
				575.98: {
					slidesPerView: 3,
					spaceBetween: 12,
				},
				991.98: {
					slidesPerView: 4,
					spaceBetween: 16,
				},
				1199.98: {
					slidesPerView: 5,
					spaceBetween: 20,
				},
			},
		});
	}

	const docsEl = document.querySelector(".docs__slider");
	if (docsEl) {
		new Swiper(docsEl, {
			slidesPerView: "auto",
			spaceBetween: 10,
			loop: true,
			autoplay: {
				delay: 5000,
				disableOnInteraction: false,
				stopOnLastSlide: false,
			},
			breakpoints: {
				767.98: {
					slidesPerView: 2,
					spaceBetween: 15,
				},
				991.98: {
					slidesPerView: 3,
					spaceBetween: 20,
				},
				1199.98: {
					slidesPerView: 4,
					spaceBetween: 20,
				},
			},
			pagination: {
				el: ".docs__slider-pagination",
				clickable: true,
			},
			navigation: {
				nextEl: ".docs__arrow--next",
				prevEl: ".docs__arrow--prev",
			},
		});
	}
}

function initPhoneMask() {
	const phoneInputs = document.querySelectorAll('input[type="tel"]');

	if (!phoneInputs.length) return;

	const getInputNumbersValue = (input) => input.value.replace(/\D/g, "");

	const onPhoneInput = (e) => {
		const input = e.target;
		let inputNumbersValue = getInputNumbersValue(input);
		const selectionStart = input.selectionStart;
		let formattedInputValue = "";

		if (!inputNumbersValue) {
			input.value = "";
			return;
		}

		if (input.value.length !== selectionStart) {
			if (e.data && /\D/.test(e.data)) {
				input.value = inputNumbersValue;
			}
			return;
		}

		if (["7", "8", "9"].includes(inputNumbersValue[0])) {
			if (inputNumbersValue[0] === "9") {
				inputNumbersValue = "7" + inputNumbersValue;
			}

			const firstSymbols = inputNumbersValue[0] === "8" ? "8" : "+7";
			formattedInputValue = firstSymbols + " ";

			if (inputNumbersValue.length > 1) {
				formattedInputValue += "(" + inputNumbersValue.substring(1, 4);
			}
			if (inputNumbersValue.length >= 5) {
				formattedInputValue += ") " + inputNumbersValue.substring(4, 7);
			}
			if (inputNumbersValue.length >= 8) {
				formattedInputValue += "-" + inputNumbersValue.substring(7, 9);
			}
			if (inputNumbersValue.length >= 10) {
				formattedInputValue += "-" + inputNumbersValue.substring(9, 11);
			}
		} else {
			formattedInputValue = "+" + inputNumbersValue.substring(0, 16);
		}

		input.value = formattedInputValue;
	};

	const onPhoneKeyDown = (e) => {
		const inputValue = e.target.value.replace(/\D/g, "");

		if (e.key === "Backspace" && inputValue.length === 1) {
			e.target.value = "";
		}
	};

	const onPhonePaste = (e) => {
		const input = e.target;
		const inputNumbersValue = getInputNumbersValue(input);
		const pastedText = e.clipboardData?.getData("text");

		if (pastedText && /\D/.test(pastedText)) {
			input.value = inputNumbersValue;
		}
	};

	phoneInputs.forEach((input) => {
		input.addEventListener("keydown", onPhoneKeyDown);
		input.addEventListener("input", onPhoneInput);
		input.addEventListener("paste", onPhonePaste);
	});
}

function initDevicesPage() {
	initDevicesWorkMore();
	initDevicesBenefitsSwiper();
}

function initDevicesWorkMore() {
	const root = document.querySelector("[data-devices-work-text]");
	const btn = document.querySelector("[data-devices-work-more]");

	if (!root || !btn) return;

	const labelMore = btn.dataset.labelMore || "Еще";
	const labelLess = btn.dataset.labelLess || "Свернуть";

	btn.addEventListener("click", () => {
		const expanded = root.classList.toggle("_expanded");
		btn.setAttribute("aria-expanded", String(expanded));
		btn.textContent = expanded ? labelLess : labelMore;
	});
}

function initDevicesBenefitsSwiper() {
	if (typeof Swiper === "undefined") return;

	const slider = document.querySelector(".devices-benefits__slider");
	if (!slider) return;

	new Swiper(slider, {
		slidesPerView: "auto",
		spaceBetween: 10,
		breakpoints: {
			767.98: {
				slidesPerView: 2,
				spaceBetween: 20,
				allowTouchMove: false,
			},
			1199.98: {
				slidesPerView: 3,
				spaceBetween: 20,
				allowTouchMove: false,
			},
		},
		navigation: {
			nextEl: ".devices-benefits__arrow--next",
			prevEl: ".devices-benefits__arrow--prev",
		},
	});
}

function initRelatedEquipmentSwiper() {
	if (typeof Swiper === "undefined") return;

	const slider = document.querySelector(
		".equipment--related .equipment__slider",
	);
	if (!slider) return;

	new Swiper(slider, {
		slidesPerView: 1.15,
		spaceBetween: 10,
		breakpoints: {
			575.98: {
				slidesPerView: 2,
				spaceBetween: 10,
			},
			991.98: {
				slidesPerView: 3,
				spaceBetween: 20,
			},
			1199.98: {
				slidesPerView: 4,
				spaceBetween: 20,
			},
		},
		navigation: {
			nextEl: ".equipment--related .equipment__arrow--next",
			prevEl: ".equipment--related .equipment__arrow--prev",
		},
	});
}

function initCptArchiveFilters() {
	if (typeof Swiper === "undefined") return;

	document
		.querySelectorAll(".cpt-archive__filters, .cpt-archive__subfilters")
		.forEach((el) => {
			new Swiper(el, {
				slidesPerView: "auto",
				spaceBetween: 20,
				freeMode: true,
			});
		});
}
