const VIEWPORT_PADDING = 8;
const TRIGGER_GAP = 10;

let tooltipLayer = null;
let activeTrigger = null;
let hideTimer = null;

const canHover = () => window.matchMedia("(hover: hover) and (pointer: fine)").matches;

function getTooltipLayer() {
	if (!tooltipLayer) {
		tooltipLayer = document.createElement("div");
		tooltipLayer.className = "tooltip-float";
		tooltipLayer.setAttribute("role", "tooltip");
		tooltipLayer.id = "site-tooltip";
		document.body.appendChild(tooltipLayer);
	}
	return tooltipLayer;
}

function setTriggerState(trigger, isOpen) {
	trigger.classList.toggle("_active", isOpen);
	trigger.setAttribute("aria-expanded", String(isOpen));

	if (isOpen) {
		const layer = getTooltipLayer();
		trigger.setAttribute("aria-describedby", layer.id);
	} else {
		trigger.removeAttribute("aria-describedby");
	}
}

function positionTooltip(trigger) {
	const layer = getTooltipLayer();
	const text = trigger.dataset.tooltip;

	if (!text) return;

	const isBenefits = Boolean(trigger.closest(".benefits"));
	const useMobileBenefitsPlacement = isBenefits && !canHover();

	layer.textContent = text;
	layer.classList.toggle("tooltip-float--mint", isBenefits);
	layer.classList.toggle("tooltip-float--mobile-left", useMobileBenefitsPlacement);
	layer.classList.add("_visible");

	const triggerRect = trigger.getBoundingClientRect();
	const layerRect = layer.getBoundingClientRect();

	if (useMobileBenefitsPlacement) {
		let left = triggerRect.left - layerRect.width - TRIGGER_GAP;
		let top = triggerRect.top + (triggerRect.height - layerRect.height) / 2;

		left = Math.max(VIEWPORT_PADDING, left);
		top = Math.max(
			VIEWPORT_PADDING,
			Math.min(top, window.innerHeight - layerRect.height - VIEWPORT_PADDING)
		);

		layer.style.left = `${Math.round(left)}px`;
		layer.style.top = `${Math.round(top)}px`;
		return;
	}

	const maxLeft = window.innerWidth - layerRect.width - VIEWPORT_PADDING;
	let left = triggerRect.left + triggerRect.width / 2 - layerRect.width / 2;

	left = Math.max(VIEWPORT_PADDING, Math.min(left, maxLeft));

	let top = triggerRect.bottom + TRIGGER_GAP;
	const fitsBelow = top + layerRect.height <= window.innerHeight - VIEWPORT_PADDING;

	if (!fitsBelow) {
		top = triggerRect.top - layerRect.height - TRIGGER_GAP;
	}

	top = Math.max(VIEWPORT_PADDING, Math.min(top, window.innerHeight - layerRect.height - VIEWPORT_PADDING));

	layer.style.left = `${Math.round(left)}px`;
	layer.style.top = `${Math.round(top)}px`;
}

function showTooltip(trigger) {
	if (!trigger?.dataset.tooltip) return;

	window.clearTimeout(hideTimer);
	activeTrigger = trigger;
	setTriggerState(trigger, true);
	positionTooltip(trigger);
}

function hideTooltip() {
	window.clearTimeout(hideTimer);

	if (activeTrigger) {
		setTriggerState(activeTrigger, false);
		activeTrigger = null;
	}

	if (tooltipLayer) {
		tooltipLayer.classList.remove("_visible", "tooltip-float--mint", "tooltip-float--mobile-left");
		tooltipLayer.textContent = "";
		tooltipLayer.style.left = "";
		tooltipLayer.style.top = "";
	}
}

function scheduleHide() {
	hideTimer = window.setTimeout(hideTooltip, 80);
}

function onDocumentPointerDown(event) {
	if (!activeTrigger || canHover()) return;

	const target = event.target;

	if (activeTrigger.contains(target) || tooltipLayer?.contains(target)) return;

	hideTooltip();
}

function onViewportChange() {
	if (activeTrigger) {
		positionTooltip(activeTrigger);
	}
}

export function initTooltips() {
	const triggers = document.querySelectorAll("[data-tooltip]");

	if (!triggers.length) return;

	getTooltipLayer();

	triggers.forEach((trigger) => {
		if (canHover()) {
			trigger.addEventListener("mouseenter", () => showTooltip(trigger));
			trigger.addEventListener("mouseleave", scheduleHide);
			trigger.addEventListener("focus", () => showTooltip(trigger));
			trigger.addEventListener("blur", scheduleHide);
		} else {
			trigger.addEventListener("click", (event) => {
				event.preventDefault();
				event.stopPropagation();

				if (activeTrigger === trigger) {
					hideTooltip();
					return;
				}

				hideTooltip();
				showTooltip(trigger);
			});
		}
	});

	document.addEventListener("pointerdown", onDocumentPointerDown);
	document.addEventListener("keydown", (event) => {
		if (event.key === "Escape") hideTooltip();
	});
	window.addEventListener("scroll", hideTooltip, { passive: true });
	window.addEventListener("resize", onViewportChange);
}
