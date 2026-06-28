"use strict";

const IMMEDIATE_ANIMATION_ROOTS = [".hero", ".heading", ".product-hero", ".not-found"];
const IMMEDIATE_ANIMATION_DELAY = 450;
const SCROLL_ANIMATION_INIT_DELAY = 350;
const ANIM_DURATION_SCALE = 1.15;

const MOTION_GLOBAL = {
	pointerEase: 0.08,
};

const MOTION_DEFAULTS = {
	scrollParallaxY: 0.14,
	scrollParallaxX: 0.05,
	pointerX: 16,
	pointerY: 10,
	floatSpeed: 0.85,
	floatAmp: 7,
	rotateSpeed: 0.55,
	rotateAmp: 1.1,
	phase: 0,
	reverse: false,
};

const MOTION_PRESETS = {
	"catalog-teaser": {
		scroll: 0.05,
		pointerX: 12,
		pointerY: 7,
		floatSpeed: 0.7,
		floatAmp: 5,
		rotateSpeed: 0.45,
		rotateAmp: 0.8,
		phase: 0.2,
		reverse: true,
	},
	news: {
		scroll: 0.08,
		pointerX: 18,
		pointerY: 11,
		floatSpeed: 0.9,
		floatAmp: 8,
		rotateSpeed: 0.6,
		rotateAmp: 1.3,
		phase: 1.1,
	},
	request: {
		scroll: 0.06,
		pointerX: 10,
		pointerY: 12,
		floatSpeed: 0.75,
		floatAmp: 6.5,
		rotateSpeed: 0.5,
		rotateAmp: 1,
		phase: 2.4,
		reverse: true,
	},
	equipment: {
		scroll: 0.09,
		pointerX: 15,
		pointerY: 9,
		floatSpeed: 0.82,
		floatAmp: 7.5,
		rotateSpeed: 0.58,
		rotateAmp: 1.2,
		phase: 0.8,
	},
	contacts: {
		scroll: 0.07,
		pointerX: 13,
		pointerY: 10,
		floatSpeed: 0.88,
		floatAmp: 6,
		rotateSpeed: 0.52,
		rotateAmp: 0.95,
		phase: 3,
		reverse: true,
	},
	"contacts-info": {
		scroll: 0.065,
		pointerX: 11,
		pointerY: 8,
		floatSpeed: 0.68,
		floatAmp: 5.5,
		rotateSpeed: 0.42,
		rotateAmp: 0.85,
		phase: 1.7,
		reverse: true,
	},
	policy: {
		scroll: 0.04,
		pointerX: 9,
		pointerY: 6,
		floatSpeed: 0.62,
		floatAmp: 4.5,
		rotateSpeed: 0.38,
		rotateAmp: 0.7,
		phase: 4.2,
		reverse: true,
	},
	"catalog-gallery": {
		scroll: 0.075,
		pointerX: 17,
		pointerY: 10,
		floatSpeed: 0.78,
		floatAmp: 7,
		rotateSpeed: 0.53,
		rotateAmp: 1.05,
		phase: 2,
	},
	"devices-benefits": {
		scroll: 0.085,
		pointerX: 14,
		pointerY: 11,
		floatSpeed: 0.92,
		floatAmp: 8.5,
		rotateSpeed: 0.62,
		rotateAmp: 1.25,
		phase: 0.5,
		reverse: true,
	},
	audience: {
		scroll: 0.07,
		pointerX: 16,
		pointerY: 10,
		floatSpeed: 0.85,
		floatAmp: 7,
		rotateSpeed: 0.55,
		rotateAmp: 1.1,
		phase: 1.5,
	},
};

function resolveMotionConfig(scene) {
	const presetKey = scene.dataset.motion;
	const preset = presetKey ? MOTION_PRESETS[presetKey] : null;
	const config = { ...MOTION_DEFAULTS, ...preset };

	if (preset?.scroll != null && preset.scrollParallaxY == null) {
		config.scrollParallaxY = preset.scroll * 2;
	}

	if (scene.dataset.motionScrollY) {
		config.scrollParallaxY = Number(scene.dataset.motionScrollY);
	}

	if (scene.dataset.motionScrollX) {
		config.scrollParallaxX = Number(scene.dataset.motionScrollX);
	}

	if (scene.dataset.motionReverse !== undefined) {
		config.reverse = scene.dataset.motionReverse !== "false";
	}

	delete config.scroll;

	return config;
}

let animItems = [];
let animTicking = false;
let scrollInitialized = false;
const pendingArticleItems = new WeakSet();

export function initAnimation() {
	initRipple();
	initImmediateAnimation();
	initArticleBodyAnimations();
	initScrollAnimation();
	initMotionAnimation();
}

export function refreshScrollAnimations(root = document, { runScroll = true } = {}) {
	const scope = root instanceof Element ? root : document;
	const newItems = scope.querySelectorAll("._anim-items");

	newItems.forEach((item) => {
		if (!animItems.includes(item)) {
			animItems.push(item);
		}
	});

	if (runScroll) {
		animOnScroll();
	}
}

function initRipple() {
	document.querySelectorAll(".a-ripple").forEach((el) => {
		el.addEventListener("mouseenter", (e) => {
			if (window.innerWidth < 1200) return;

			const point = e.touches ? e.touches[0] : e;
			const rect = el.getBoundingClientRect();
			const diameter = Math.sqrt(rect.width ** 2 + rect.height ** 2) * 2;

			el.style.cssText = "--s: 0; --o: 1;";
			el.offsetTop;
			el.style.cssText = `--t: 1; --o: 0; --d: ${diameter}; --x:${point.clientX - rect.left}; --y:${point.clientY - rect.top};`;
		});
	});
}

function initScrollAnimation() {
	animItems = [...document.querySelectorAll("._anim-items")];

	if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
		animItems.forEach((item) => item.classList.add("_active"));
		return;
	}

	if (!scrollInitialized) {
		window.addEventListener("scroll", handleAnimScroll, { passive: true });
		scrollInitialized = true;
	}

	if (!animItems.length) {
		return;
	}

	setTimeout(animOnScroll, SCROLL_ANIMATION_INIT_DELAY);
}

const ARTICLE_MEDIA_SELECTOR = [
	"figure",
	".wp-block-image",
	".wp-block-gallery",
	".gallery",
	".article__gallery",
].join(", ");

function isImageOnlyParagraph(element) {
	if (!(element instanceof Element) || element.tagName !== "P") {
		return false;
	}

	const img = element.querySelector(":scope > img");

	if (!img || element.children.length !== 1) {
		return false;
	}

	return element.textContent.trim() === "";
}

function isArticleMediaElement(element) {
	if (!(element instanceof Element)) {
		return false;
	}

	if (isImageOnlyParagraph(element)) {
		return false;
	}

	if (element.matches(ARTICLE_MEDIA_SELECTOR) || element.tagName === "IMG") {
		return true;
	}

	return Boolean(element.querySelector(`${ARTICLE_MEDIA_SELECTOR}, img`));
}

function getArticleAnimBlock(item) {
	const body = item.closest(".article__body.typography-block");

	if (!body) {
		return item;
	}

	let block = item;

	while (block.parentElement && block.parentElement !== body) {
		block = block.parentElement;
	}

	return block;
}

function isArticleItemInView(item) {
	const rect = item.getBoundingClientRect();
	const isArticleBody = Boolean(item.closest(".article__body.typography-block"));
	const threshold = isArticleBody ? window.innerHeight + 80 : window.innerHeight * 0.95;

	return rect.top < threshold && rect.bottom > 0;
}

function isScrollAnimItem(item) {
	return item instanceof Element && !item.closest(".article__body.typography-block");
}

function runItemCounters(item) {
	item.querySelectorAll("[data-counter], [data-num]").forEach((num) => {
		if (num.dataset.counter !== undefined) {
			startCounter(num);
		} else {
			animateNumber(num);
		}
	});
}

function activateScrollAnimItem(item) {
	if (!(item instanceof Element) || item.classList.contains("_active")) {
		return;
	}

	item.classList.add("_active", "_anim-no-hide");
	runItemCounters(item);
}

function runArticleItemTransition(item) {
	if (!(item instanceof Element) || item.classList.contains("_active")) {
		return;
	}

	item.removeAttribute("data-article-activating");
	item.classList.add("_anim-no-hide");

	if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
		item.classList.add("_active");
		return;
	}

	if (pendingArticleItems.has(item)) {
		return;
	}

	pendingArticleItems.add(item);

	requestAnimationFrame(() => {
		item.classList.remove("_active");
		void item.offsetHeight;

		requestAnimationFrame(() => {
			item.classList.add("_active");
		});
	});
}

function activateArticleItem(item, { chainNext = true } = {}) {
	if (!(item instanceof Element) || item.classList.contains("_active") || pendingArticleItems.has(item)) {
		return;
	}

	const body = item.closest(".article__body.typography-block");

	runArticleItemTransition(item);

	if (!body || !chainNext) {
		return;
	}

	const block = getArticleAnimBlock(item);
	const next = block.nextElementSibling;

	if (!(next instanceof Element)) {
		return;
	}

	const nextItem = next.classList.contains("_anim-items")
		? next
		: next.querySelector("._anim-items");

	if (nextItem instanceof Element && !nextItem.classList.contains("_active")) {
		const delay = window.matchMedia("(prefers-reduced-motion: reduce)").matches ? 0 : 80;

		setTimeout(() => activateArticleItem(nextItem, { chainNext: false }), delay);
	}
}

function prepareArticleBody(body) {
	if (!(body instanceof Element) || body.dataset.articleAnimPrepared === "true") {
		return;
	}

	body.dataset.articleAnimPrepared = "true";

	Array.from(body.children).forEach((element) => {
		if (!(element instanceof Element) || element.classList.contains("_anim-items")) {
			return;
		}

		if (isImageOnlyParagraph(element)) {
			element.classList.add("article__image-paragraph");
			const img = element.querySelector(":scope > img");

			if (img instanceof Element) {
				img.classList.remove("_active");
				img.removeAttribute("data-article-activating");
				img.classList.add("_anim-items", "a-fade-up");
			}

			return;
		}

		if (isArticleMediaElement(element)) {
			element.classList.remove("_active");
			element.removeAttribute("data-article-activating");
			element.classList.add("_anim-items", "a-fade-up");
			return;
		}

		element.classList.remove("_active");
		element.removeAttribute("data-article-activating");
		element.classList.add("_anim-items", "a-fade-up");
	});

	refreshScrollAnimations(body, { runScroll: false });
}

function activateVisibleArticleItems(body) {
	const visibleItems = Array.from(body.children)
		.map((block) =>
			block.classList.contains("_anim-items") ? block : block.querySelector("._anim-items"),
		)
		.filter((item) => item instanceof Element && isArticleItemInView(item));

	const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

	visibleItems.forEach((item, index) => {
		const delay = reducedMotion ? 0 : index * 80;

		setTimeout(() => activateArticleItem(item, { chainNext: false }), delay);
	});
}

function watchArticleImages(body) {
	body.querySelectorAll("img").forEach((img) => {
		if (img.complete) {
			return;
		}

		img.addEventListener("load", () => activateVisibleArticleItems(body), { once: true });
	});
}

function initArticleBodyAnimations(root = document) {
	const scope = root instanceof Element ? root : document;
	const bodies = scope.querySelectorAll(".article__body.typography-block");

	if (!bodies.length) {
		return;
	}

	if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
		bodies.forEach((body) => {
			prepareArticleBody(body);
			body.querySelectorAll("._anim-items").forEach((item) => item.classList.add("_active"));
		});
		return;
	}

	bodies.forEach((body) => {
		prepareArticleBody(body);
		watchArticleImages(body);

		requestAnimationFrame(() => {
			requestAnimationFrame(() => activateVisibleArticleItems(body));
		});
	});
}

function handleAnimScroll() {
	if (!animTicking) {
		requestAnimationFrame(() => {
			animOnScroll();
			animTicking = false;
		});
		animTicking = true;
	}
}

function animOnScroll() {
	const articleItems = [];

	animItems.forEach((item) => {
		if (item.closest(".article__body.typography-block")) {
			articleItems.push(item);
			return;
		}

		if (!isScrollAnimItem(item) || item.closest("[hidden]")) {
			return;
		}

		const rect = item.getBoundingClientRect();
		const passed = rect.bottom < 0;
		const inView = isArticleItemInView(item);

		if (passed || inView) {
			activateScrollAnimItem(item);
		} else if (!item.classList.contains("_anim-no-hide")) {
			item.classList.remove("_active");
			resetItemAnimations(item);
		}
	});

	articleItems.forEach((item) => {
		if (!isArticleItemInView(item) || item.classList.contains("_active")) {
			return;
		}

		activateArticleItem(item);
	});
}

function resetItemAnimations(container) {
	container.querySelectorAll("[data-num]").forEach((num) => {
		num.textContent = "0";
	});
}

function animateNumber(el, duration = Math.round(700 * ANIM_DURATION_SCALE)) {
	const end = parseInt(el.dataset.num, 10);
	if (Number.isNaN(end)) return;

	let startTime = null;

	const step = (timestamp) => {
		if (!startTime) startTime = timestamp;

		const progress = Math.min((timestamp - startTime) / duration, 1);
		el.textContent = String(Math.floor(end * progress));

		if (progress < 1) {
			requestAnimationFrame(step);
		} else {
			el.textContent = String(end);
		}
	};

	requestAnimationFrame(step);
}

function startCounter(el) {
	if (el.dataset.counterStarted === "true") return;
	el.dataset.counterStarted = "true";

	const originalText = el.textContent.trim();
	const targetNumber = parseInt(originalText.replace(/\D/g, ""), 10);
	const suffix = originalText.replace(/[0-9\s\u00A0\u202F]/g, "");
	const startNumber = Math.floor(targetNumber * 0.8);
	const startTime = performance.now();
	const animationDuration = Math.round(1500 * ANIM_DURATION_SCALE);

	const formatNumber = (num) => num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, "\u00A0");

	const updateCounter = (currentTime) => {
		const elapsedTime = currentTime - startTime;
		const progress = Math.min(elapsedTime / animationDuration, 1);
		const currentCount = Math.floor(startNumber + progress * (targetNumber - startNumber));

		el.textContent = formatNumber(currentCount) + (suffix ? ` ${suffix}` : "");

		if (progress < 1) {
			requestAnimationFrame(updateCounter);
		} else {
			el.textContent = formatNumber(targetNumber) + (suffix ? ` ${suffix}` : "");
		}
	};

	requestAnimationFrame(updateCounter);
}

function initImmediateAnimation() {
	const selector = IMMEDIATE_ANIMATION_ROOTS.map((root) => `${root} ._anim-items`).join(", ");
	const items = document.querySelectorAll(selector);
	if (!items.length) return;

	if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
		items.forEach((el) => {
			el.classList.add("_active", "_anim-no-hide");
		});
		return;
	}

	setTimeout(() => {
		requestAnimationFrame(() => {
			items.forEach((el) => {
				el.classList.remove("_active");
				el.classList.add("_anim-no-hide");
			});

			requestAnimationFrame(() => {
				items.forEach((el) => {
					el.classList.add("_active");
				});
			});
		});
	}, IMMEDIATE_ANIMATION_DELAY);
}

function initMotionAnimation() {
	const sceneElements = document.querySelectorAll("[data-motion]");
	if (!sceneElements.length) return;

	const motionQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
	const sceneConfigs = [];
	const sceneConfigMap = new Map();
	const activeScenes = new Set();

	let pointerNormX = 0;
	let pointerNormY = 0;
	let targetPointerNormX = 0;
	let targetPointerNormY = 0;

	sceneElements.forEach((scene) => {
		const targetSelector = scene.dataset.motionTarget;
		const target = targetSelector ? scene.querySelector(targetSelector) : scene;
		if (!target) return;

		const config = {
			scene,
			target,
			showOnVisible: Boolean(targetSelector),
			...resolveMotionConfig(scene),
		};

		sceneConfigs.push(config);
		sceneConfigMap.set(scene, config);
	});

	const resetTarget = (target) => {
		target.style.removeProperty("--mx");
		target.style.removeProperty("--my");
		target.style.removeProperty("--mrotate");
		target.style.removeProperty("--scroll-x");
		target.style.removeProperty("--scroll-y");
	};

	const showStatic = () => {
		sceneConfigs.forEach(({ target, showOnVisible }) => {
			if (showOnVisible) target.classList.add("is-visible");
			resetTarget(target);
		});
	};

	if (motionQuery.matches) {
		showStatic();
		return;
	}

	const getScrollParallax = (scene, parallaxX, parallaxY) => {
		const rect = scene.getBoundingClientRect();
		const viewportHeight = window.innerHeight;

		if (rect.bottom < 0 || rect.top > viewportHeight) {
			return { x: 0, y: 0 };
		}

		const sectionCenter = rect.top + rect.height / 2;
		const normalized = (sectionCenter - viewportHeight / 2) / viewportHeight;

		return {
			x: normalized * parallaxX * viewportHeight,
			y: normalized * parallaxY * viewportHeight,
		};
	};

	const updateMotion = () => {
		pointerNormX += (targetPointerNormX - pointerNormX) * MOTION_GLOBAL.pointerEase;
		pointerNormY += (targetPointerNormY - pointerNormY) * MOTION_GLOBAL.pointerEase;

		const time = performance.now() * 0.001;

		activeScenes.forEach(
			({
				scene,
				target,
				scrollParallaxX,
				scrollParallaxY,
				pointerX,
				pointerY,
				floatSpeed,
				floatAmp,
				rotateSpeed,
				rotateAmp,
				phase,
				reverse,
			}) => {
				const direction = reverse ? -1 : 1;
				const floatY = Math.sin(time * floatSpeed + phase) * floatAmp;
				const floatRotate = Math.sin(time * rotateSpeed + phase * 0.7) * rotateAmp;
				const parallax = getScrollParallax(scene, scrollParallaxX, scrollParallaxY);
				const mx = pointerNormX * pointerX * direction;
				const my = (pointerNormY * pointerY + floatY) * direction;

				target.style.setProperty("--mx", `${mx.toFixed(2)}px`);
				target.style.setProperty("--my", `${my.toFixed(2)}px`);
				target.style.setProperty("--scroll-x", `${(parallax.x * direction).toFixed(2)}px`);
				target.style.setProperty("--scroll-y", `${(parallax.y * direction).toFixed(2)}px`);
				target.style.setProperty("--mrotate", `${(floatRotate * direction).toFixed(2)}deg`);
			},
		);
	};

	const loop = () => {
		if (activeScenes.size) updateMotion();
		requestAnimationFrame(loop);
	};

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				const config = sceneConfigMap.get(entry.target);
				if (!config) return;

				if (entry.isIntersecting) {
					activeScenes.add(config);
					if (config.showOnVisible) config.target.classList.add("is-visible");
				} else {
					activeScenes.delete(config);
					resetTarget(config.target);
				}
			});
		},
		{ threshold: 0.08 },
	);

	sceneConfigs.forEach(({ scene }) => observer.observe(scene));

	document.addEventListener(
		"mousemove",
		(event) => {
			targetPointerNormX = event.clientX / window.innerWidth - 0.5;
			targetPointerNormY = event.clientY / window.innerHeight - 0.5;
		},
		{ passive: true },
	);

	document.documentElement.addEventListener("mouseleave", () => {
		targetPointerNormX = 0;
		targetPointerNormY = 0;
	});

	motionQuery.addEventListener("change", (event) => {
		if (event.matches) {
			activeScenes.clear();
			showStatic();
		} else {
			sceneConfigs.forEach((config) => {
				const rect = config.scene.getBoundingClientRect();

				if (rect.bottom > 0 && rect.top < window.innerHeight) {
					activeScenes.add(config);
					if (config.showOnVisible) config.target.classList.add("is-visible");
				}
			});
		}
	});

	requestAnimationFrame(loop);
}
