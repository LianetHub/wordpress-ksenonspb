<?php

/**
 * Sticky CTA panel (mobile only) — service & pricing pages.
 *
 * @package ksenonspb
 */

if (! ksenon_should_show_sticky_cta()) {
	return;
}

$phones      = ksenon_get_phones();
$phone       = $phones[0] ?? '';
$phone_href  = $phone ? 'tel:+' . ksenon_phone_clean($phone) : '';
$messengers  = array();

foreach (ksenon_get_messenger_links() as $link) {
	if (! empty($link['network']) && ! empty($link['url'])) {
		$messengers[$link['network']] = $link;
	}
}

$telegram = $messengers['telegram'] ?? null;
$whatsapp = $messengers['whatsapp'] ?? null;
?>
<div class="sticky-cta" data-sticky-cta>
	<div class="sticky-cta__inner">
		<button
			type="button"
			class="btn btn--primary sticky-cta__btn"
			data-fancybox
			data-src="#popup-order"
		>
			<span class="btn__text"><?php esc_html_e('Оценить ремонт', 'ksenonspb'); ?></span>
		</button>

		<?php if ($phone_href) : ?>
			<a
				class="sticky-cta__icon sticky-cta__icon--tel"
				href="<?php echo esc_url($phone_href); ?>"
				aria-label="<?php esc_attr_e('Позвонить', 'ksenonspb'); ?>"
			>
				<?php ksenon_icon('icon-phone-outline', 22, 22, 'sticky-cta__icon-svg'); ?>
			</a>
		<?php endif; ?>

		<?php if ($telegram) : ?>
			<a
				class="sticky-cta__icon sticky-cta__icon--telegram"
				href="<?php echo esc_url($telegram['url']); ?>"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e('Telegram', 'ksenonspb'); ?>"
			>
				<?php ksenon_icon('icon-telegram', 18, 16, 'sticky-cta__icon-svg sticky-cta__icon-svg--telegram'); ?>
			</a>
		<?php endif; ?>

		<?php if ($whatsapp) : ?>
			<a
				class="sticky-cta__icon sticky-cta__icon--whatsapp"
				href="<?php echo esc_url($whatsapp['url']); ?>"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php esc_attr_e('WhatsApp', 'ksenonspb'); ?>"
			>
				<?php ksenon_icon('icon-whatsapp', 20, 20, 'sticky-cta__icon-svg sticky-cta__icon-svg--whatsapp'); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
