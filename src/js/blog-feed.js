"use strict";

import { refreshScrollAnimations } from "./animation.js";

export function buildFeedUrl(baseUrl, tab, page, origin = "https://example.com") {
	const resolvedBase = baseUrl || "/stati-po-allergologii/";

	if (tab === "news") {
		const url = new URL(resolvedBase, origin);

		url.searchParams.set("tab", "news");

		if (page > 1) {
			url.searchParams.set("news_page", String(page));
		} else {
			url.searchParams.delete("news_page");
		}

		return `${url.pathname}${url.search}${url.hash}`;
	}

	let articlesBase = resolvedBase;

	try {
		articlesBase = new URL(resolvedBase, origin).pathname;
	} catch {
		articlesBase = resolvedBase;
	}

	const normalizedBase = articlesBase.replace(/\/+$/, "");

	if (page <= 1) {
		return `${normalizedBase}/`;
	}

	return `${normalizedBase}/page/${page}/`;
}

export function getRequestPayload(feedEl, tab, page, themeAjax) {
	const payload = new URLSearchParams({
		action: "filter_blogs",
		nonce: themeAjax.nonce,
		tab,
		page: String(page),
		base_url: feedEl.dataset.baseUrl || "",
	});

	const archiveTermId = feedEl.dataset.archiveTermId;
	if (archiveTermId && archiveTermId !== "0") {
		payload.set("archive_term_id", archiveTermId);
	}

	const tagId = feedEl.dataset.tagId;
	if (tagId && tagId !== "0") {
		payload.set("tag_id", tagId);
	}

	return payload;
}

export function applyFeedResponse({ grid, paginationSlot, data }) {
	if (!grid || !data) {
		return;
	}

	grid.innerHTML = data.html || "";
	refreshScrollAnimations(grid);

	if (!paginationSlot) {
		return;
	}

	const hasPagination = Boolean(data.pagination);
	const shouldKeepPrevious = !hasPagination && Number(data.max_pages) > 1;

	if (!shouldKeepPrevious) {
		paginationSlot.innerHTML = data.pagination || "";
	}

	refreshScrollAnimations(paginationSlot);
}

export function handlePaginationClick(event, { currentPage, activeTab, loadFeed }) {
	const link = event.target.closest("[data-blog-page]");
	if (!link) {
		return false;
	}

	event.preventDefault();

	const page = Number.parseInt(link.dataset.blogPage || "1", 10) || 1;
	if (page === currentPage) {
		return true;
	}

	loadFeed(activeTab, page, { scroll: true });
	return true;
}

export function initBlogFeed(feed, themeAjax) {
	const grid = feed.querySelector("[data-blog-grid]");
	const panel = feed.querySelector("[data-blog-panel]");
	const paginationSlot = feed.querySelector("[data-blog-pagination-slot]");
	const tabs = feed.querySelectorAll("[data-blog-tab]");

	if (!grid || !tabs.length) {
		return;
	}

	let activeTab = feed.dataset.activeTab || "articles";
	let currentPage = Number.parseInt(feed.dataset.currentPage || "1", 10) || 1;
	let requestId = 0;

	const setLoading = (isLoading) => {
		feed.classList.toggle("_loading", isLoading);
		feed.setAttribute("aria-busy", String(isLoading));
	};

	const updateTabs = (tab) => {
		tabs.forEach((item) => {
			const isActive = item.dataset.blogTab === tab;
			item.classList.toggle("_active", isActive);
			item.setAttribute("aria-selected", String(isActive));
		});

		if (panel) {
			panel.setAttribute("aria-labelledby", `blog-tab-${tab}`);
		}
	};

	const updateFeed = (tab, page, { pushState = true, scroll = false } = {}) => {
		activeTab = tab;
		currentPage = page;
		feed.dataset.activeTab = tab;
		feed.dataset.currentPage = String(page);
		updateTabs(tab);

		if (pushState) {
			const nextUrl = buildFeedUrl(feed.dataset.baseUrl, tab, page, window.location.origin);
			window.history.pushState({ blogTab: tab, blogPage: page }, "", nextUrl);
		}

		if (scroll) {
			feed.scrollIntoView({ behavior: "smooth", block: "start" });
		}
	};

	const fetchFeed = (tab, page) => {
		const currentRequest = ++requestId;

		setLoading(true);

		return fetch(themeAjax.ajax_url, {
			method: "POST",
			body: getRequestPayload(feed, tab, page, themeAjax),
		})
			.then((response) => response.json())
			.then((data) => {
				if (currentRequest !== requestId || !data.success || !data.data) {
					return;
				}

				applyFeedResponse({
					grid,
					paginationSlot,
					data: data.data,
				});
			})
			.catch((error) => console.error(error))
			.finally(() => {
				if (currentRequest === requestId) {
					setLoading(false);
				}
			});
	};

	const loadFeed = (tab, page, options = {}) => {
		updateFeed(tab, page, options);
		return fetchFeed(tab, page);
	};

	tabs.forEach((tab) => {
		tab.addEventListener("click", (event) => {
			event.preventDefault();

			const target = tab.dataset.blogTab;
			if (!target || target === activeTab) {
				return;
			}

			loadFeed(target, 1, { scroll: false });
		});
	});

	paginationSlot?.addEventListener("click", (event) => {
		handlePaginationClick(event, {
			currentPage,
			activeTab,
			loadFeed,
		});
	});

	window.addEventListener("popstate", (event) => {
		const state = event.state;
		if (!state || (!state.blogTab && !state.blogPage)) {
			return;
		}

		const tab = state.blogTab || activeTab;
		const page = state.blogPage || 1;

		loadFeed(tab, page, { pushState: false });
	});

	window.history.replaceState(
		{ blogTab: activeTab, blogPage: currentPage },
		"",
		window.location.href
	);

	return { loadFeed, getState: () => ({ activeTab, currentPage }) };
}
