"use strict";

import { initBlogFeed } from "./blog-feed.js";
import { initYandexMaps } from "./map.js";
import { initTooltips } from "./tooltip.js";
document.addEventListener("DOMContentLoaded", () => {
	initBurger();
	initFancybox();
	initPartnersAutoPopup();
	initBlogTabs();
	initProductTabs();
	initProductTableMore();
	initAccordion();
	initTooltips();
	initHome();
	initDevicesPage();
	initRelatedEquipmentSwiper();
	initPhoneMask();
	initYandexMaps();
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
		toggle.setAttribute("aria-label", isOpen ? "Закрыть меню" : "Открыть меню");
		drawer.setAttribute("aria-hidden", String(!isOpen));
	};

	toggle.addEventListener("click", () => {
		setMenuOpen(!drawer.classList.contains("is-open"));
	});

	backdrop?.addEventListener("click", () => setMenuOpen(false));

	drawer.querySelectorAll(".header-drawer__link, .header-drawer__primary, .header-drawer__logo").forEach((link) => {
		link.addEventListener("click", () => setMenuOpen(false));
	});

	document.addEventListener("keydown", (e) => {
		if (e.key === "Escape" && drawer.classList.contains("is-open")) {
			setMenuOpen(false);
		}
	});
}

function initFancybox() {
	if (typeof Fancybox === "undefined") return;

	Fancybox.bind('[data-fancybox]:not([data-fancybox="pick-video"])', {
		mainClass: "fancybox-popup",
		dragToClose: false,
		placeFocusBack: true,
		autoFocus: true,
		trapFocus: true,
	});

	Fancybox.bind('[data-fancybox="pick-video"]', {
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

function initPartnersAutoPopup() {
	const COOKIE_NAME = "ksenon_partners_popup_seen";
	const DELAY_MS = 30_000;
	const COOKIE_DAYS = 30;

	const getCookie = (name) => {
		const escaped = name.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
		const match = document.cookie.match(new RegExp(`(?:^|; )${escaped}=([^;]*)`));
		return match ? decodeURIComponent(match[1]) : null;
	};

	const setCookie = (name, value) => {
		const expires = new Date(Date.now() + COOKIE_DAYS * 864e5).toUTCString();
		document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
	};

	const hasSeenPopup = () => getCookie(COOKIE_NAME) === "1";

	const markPopupSeen = () => setCookie(COOKIE_NAME, "1");

	const popup = document.getElementById("popup-partners");
	if (!popup || !popup.querySelector(".popup-modal__partners")) return;

	document.addEventListener("click", (e) => {
		const trigger = e.target.closest('[data-fancybox][data-src="#popup-partners"]');
		if (trigger) markPopupSeen();
	});

	if (hasSeenPopup()) return;

	setTimeout(() => {
		if (hasSeenPopup()) return;
		if (typeof Fancybox === "undefined") return;
		if (Fancybox.getInstance()) return;

		markPopupSeen();
		Fancybox.show(
			[{ src: "#popup-partners", type: "inline" }],
			{
				mainClass: "fancybox-popup",
				dragToClose: false,
				placeFocusBack: true,
				autoFocus: true,
				trapFocus: true,
			}
		);
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

function initBlogTabs() {
	const feed = document.querySelector("[data-blog-feed]");
	if (!feed || typeof theme_ajax === "undefined") return;

	initBlogFeed(feed, theme_ajax);
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
		wrap.querySelector(".product-table")?.classList.toggle("product-table--expanded", expanded);
		btn.setAttribute("aria-expanded", String(expanded));
	});
}

function initAccordion() {
	document.querySelectorAll("[data-accordion]").forEach((accordion) => {
		const items = accordion.querySelectorAll(".accordion__item");

		items.forEach((item) => {
			const header = item.querySelector(".accordion__header");

			if (!header) return;

			header.addEventListener("click", () => {
				const isOpen = item.classList.contains("_active");

				items.forEach((other) => {
					other.classList.remove("_active");
					other.querySelector(".accordion__header")?.setAttribute("aria-expanded", "false");
				});

				if (!isOpen) {
					item.classList.add("_active");
					header.setAttribute("aria-expanded", "true");
				}
			});
		});
	});
}

function initHome() {
	initAudiencePills();
	initHomePanels();
	initWhyUsPanels();
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
					other.querySelector(".panels__heading")?.setAttribute("aria-expanded", "false");
				});

				if (!isOpen) {
					item.classList.add("_active");
					head.setAttribute("aria-expanded", "true");
				}
			});
		});
	});
}

function initWhyUsPanels() {
	document.querySelectorAll(".why-us__panels").forEach((wrap) => {
		const items = wrap.querySelectorAll(".why-us__card");
		const detail = wrap.querySelector(".why-us__detail");
		if (!items.length || !detail) return;

		const detailStep = detail.querySelector(".why-us__detail-step");
		const detailTitle = detail.querySelector(".why-us__detail-title");
		const detailText = detail.querySelector(".why-us__detail-text");

		const updateDetail = (item) => {
			const step = item.querySelector(".why-us__card-step");
			const title = item.querySelector(".why-us__card-title");
			const text = item.querySelector(".why-us__card-text");

			if (detailStep) {
				detailStep.textContent = step?.textContent?.trim() || "";
				detailStep.hidden = !detailStep.textContent;
			}
			if (detailTitle) {
				detailTitle.textContent = title?.textContent?.trim() || "";
				detailTitle.hidden = !detailTitle.textContent;
			}
			if (detailText) {
				detailText.innerHTML = text?.innerHTML?.trim() || "";
				detailText.hidden = !detailText.textContent?.trim();
			}
		};

		items.forEach((item) => {
			const head = item.querySelector(".why-us__card-heading");
			if (!head) return;

			head.addEventListener("click", () => {
				items.forEach((other) => other.classList.remove("_active"));
				item.classList.add("_active");
				head.setAttribute("aria-expanded", "true");
				updateDetail(item);
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
					item.setAttribute("aria-selected", isActive ? "true" : "false");
				});

				panels.forEach((panel) => {
					panel.classList.toggle("_active", panel.getAttribute("data-reviews-panel") === target);
				});
			});
		});
	});
}

function initHomeSwipers() {
	if (typeof Swiper === "undefined") return;

	const heroPromoEl = document.querySelector(".hero__promo-slider");
	if (heroPromoEl) {
		new Swiper(heroPromoEl, {
			slidesPerView: 1,
			loop: heroPromoEl.querySelectorAll(".swiper-slide").length > 1,
			navigation: {
				nextEl: ".hero__promo-next",
				prevEl: ".hero__promo-prev",
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
					enabled: portfolioEl.querySelectorAll(".swiper-slide").length > 2,
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

	const slider = document.querySelector(".equipment--related .equipment__slider");
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
