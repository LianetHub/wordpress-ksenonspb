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

$video_url = function_exists('get_field') ? (string) get_field('video_url') : '';
$poster    = function_exists('get_field') ? get_field('video_poster') : null;
if ($video_url) :
?>
	<section class="about-video">
		<div class="about-video__container container">
			<h2 class="about-video__title title-md"><?php esc_html_e('Видео о студии', 'ksenonspb'); ?></h2>
			<div class="about-video__player">
				<?php if ($poster) : ?>
					<?php echo ksenon_acf_image($poster, 'large', array('class' => 'about-video__poster')); ?>
				<?php endif; ?>
				<a class="about-video__link" href="<?php echo esc_url($video_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Смотреть видео', 'ksenonspb'); ?></a>
			</div>
		</div>
	</section>
<?php
endif;

$certificates = function_exists('get_field') ? (array) get_field('certificates') : array();
if ($certificates) :
?>
	<section class="about-certificates">
		<div class="about-certificates__container container">
			<h2 class="about-certificates__title title-md"><?php esc_html_e('Сертификаты', 'ksenonspb'); ?></h2>
			<div class="about-certificates__grid">
				<?php foreach ($certificates as $image) : ?>
					<?php echo ksenon_acf_image($image, 'medium', array('class' => 'about-certificates__img')); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php
endif;

get_template_part('template-parts/blocks/cta-form', null, array('variant' => 'free_inspection'));
