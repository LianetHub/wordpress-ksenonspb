<?php

/**
 * Home: Why us
 *
 * @package ksenonspb
 */

$cards = array();

for ($i = 1; $i <= 4; $i++) {
	$card = ksenon_home_get("card_{$i}");
	if (! is_array($card)) {
		$card = array();
	}

	$cards[] = array(
		'title'    => trim((string) ($card['title'] ?? '')),
		'subtitle' => trim((string) ($card['subtitle'] ?? '')),
		'image'    => $card['image'] ?? null,
		'step'     => sprintf('%02d / %02d', $i, 4),
		'has_bg'   => $i <= 3,
	);
}

$has_cards = false;
foreach ($cards as $card) {
	if ($card['title'] || $card['subtitle'] || ($card['has_bg'] && ! empty($card['image']))) {
		$has_cards = true;
		break;
	}
}

if (! $has_cards && ! ksenon_home_get('title')) {
	return;
}

$title      = (string) ksenon_home_get('title', __('Почему <span class="color-accent">мы?</span>', 'ksenonspb'));
$title_html = function_exists('ksenon_title_accent_html')
	? ksenon_title_accent_html($title)
	: esc_html($title);
?>
<section class="why-us">
	<div class="why-us__container container">
		<div class="why-us__grid">
			<h2 class="why-us__title title-md title-md--panels">
				<?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</h2>

			<?php foreach ($cards as $index => $card) : ?>
				<?php $modifier = 'why-us__card--' . ($index + 1); ?>
				<article class="why-us__card <?php echo esc_attr($modifier); ?><?php echo $card['has_bg'] ? ' why-us__card--media' : ' why-us__card--accent'; ?>">
					<div class="why-us__card-step"><?php echo esc_html($card['step']); ?></div>

					<div class="why-us__card-body">
						<?php if ($card['title']) : ?>
							<h3 class="why-us__card-title"><?php echo esc_html($card['title']); ?></h3>
						<?php endif; ?>
						<?php if ($card['subtitle']) : ?>
							<p class="why-us__card-text"><?php echo nl2br(esc_html($card['subtitle'])); ?></p>
						<?php endif; ?>
					</div>

					<?php if ($card['has_bg'] && ! empty($card['image']) && function_exists('ksenon_acf_image')) : ?>
						<div class="why-us__card-media">
							<?php echo ksenon_acf_image($card['image'], 'medium_large', array('class' => 'why-us__card-img cover-image')); ?>
						</div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
