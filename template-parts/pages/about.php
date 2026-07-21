<?php

/**
 * About page
 *
 * @package ksenonspb
 */

$cards = function_exists('get_field') ? (array) get_field('company_cards') : array();
?>
<section class="about-page">
	<div class="about-page__container container">
		<h1 class="about-page__title title-lg"><?php the_title(); ?></h1>
		<?php if ($cards) : ?>
			<ul class="about-page__cards">
				<?php foreach ($cards as $card) : ?>
					<?php
					$card_title  = ! empty($card['title']) ? (string) $card['title'] : '';
					$card_text   = ! empty($card['text']) ? (string) $card['text'] : '';
					$card_button = ! empty($card['button']) && is_array($card['button']) ? $card['button'] : null;
					$card_image  = ! empty($card['image']) ? $card['image'] : null;
					$btn_url     = $card_button ? ksenon_acf_link_url($card_button, '') : '';
					$btn_title   = $card_button ? ksenon_acf_link_title($card_button) : '';
					$btn_target  = $card_button ? ksenon_acf_link_target($card_button) : '';
					?>
					<li class="about-page__card">
						<div class="about-page__card-body">
							<div class="about-page__card-top">
								<?php if ($card_title) : ?>
									<h2 class="about-page__card-title"><?php echo esc_html($card_title); ?></h2>
								<?php endif; ?>
								<?php if ($card_text) : ?>
									<p class="about-page__card-text"><?php echo nl2br(esc_html($card_text)); ?></p>
								<?php endif; ?>
							</div>
							<?php if ($btn_title && $btn_url) : ?>
								<a
									class="about-page__card-btn"
									href="<?php echo esc_url($btn_url); ?>"
									<?php echo $btn_target ? ' target="' . esc_attr($btn_target) . '" rel="noopener noreferrer"' : ''; ?>>
									<?php echo esc_html($btn_title); ?>
								</a>
							<?php endif; ?>
						</div>
						<?php if ($card_image) : ?>
							<div class="about-page__card-media">
								<?php echo ksenon_acf_image($card_image, 'large', array('class' => 'about-page__card-img')); ?>
							</div>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>

<?php
$advantages = function_exists('get_field') ? (array) get_field('advantages') : array();
if ($advantages) {
	get_template_part(
		'template-parts/blocks/advantages',
		null,
		array(
			'title' => __('Преимущества', 'ksenonspb'),
			'items' => $advantages,
		)
	);
}

get_template_part('template-parts/blocks/cta-form', null, array('variant' => 'free_inspection'));
