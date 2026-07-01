<?php

/**
 * Reviews block (global from theme settings)
 *
 * @package ksenonspb
 *
 * @var array $args { @type string $title }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'title' => __('Отзывы клиентов', 'ksenonspb'),
	)
);

$reviews = ksenon_get_reviews();
if (! $reviews) {
	return;
}

$grouped = array(
	'yandex' => array(),
	'drive2' => array(),
);

foreach ($reviews as $review) {
	$source = sanitize_key((string) ($review['source'] ?? 'yandex'));
	if (! isset($grouped[$source])) {
		$source = 'yandex';
	}
	$grouped[$source][] = $review;
}

$tabs = array();
if ($grouped['yandex']) {
	$tabs['yandex'] = __('Яндекс', 'ksenonspb');
}
if ($grouped['drive2']) {
	$tabs['drive2'] = 'Drive2';
}
if (! $tabs) {
	$tabs['yandex'] = __('Отзывы', 'ksenonspb');
	$grouped['yandex'] = $reviews;
}

$active_tab = array_key_first($tabs);
?>
<section class="reviews" data-reviews>
	<div class="reviews__container container">
		<div class="section-head section-head--row reviews__head">
			<h2 class="section-head__title title-md reviews__title"><?php echo esc_html($args['title']); ?></h2>
			<a class="section-head__more reviews__more" href="#contacts">
				<span class="section-head__more-text"><?php esc_html_e('Все отзывы', 'ksenonspb'); ?></span>
				<?php ksenon_render_home_arrow(); ?>
			</a>
		</div>

		<?php if (count($tabs) > 1) : ?>
			<div class="reviews__tabs" role="tablist">
				<?php foreach ($tabs as $key => $label) : ?>
					<button
						class="reviews__tab<?php echo $key === $active_tab ? ' _active' : ''; ?>"
						type="button"
						role="tab"
						data-reviews-tab="<?php echo esc_attr($key); ?>"
						aria-selected="<?php echo $key === $active_tab ? 'true' : 'false'; ?>">
						<?php echo esc_html($label); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php foreach ($tabs as $key => $label) : ?>
			<div class="reviews__panel<?php echo $key === $active_tab ? ' _active' : ''; ?>" data-reviews-panel="<?php echo esc_attr($key); ?>">
				<div class="reviews__grid">
					<?php foreach ($grouped[$key] as $review) : ?>
						<article class="reviews__card">
							<div class="reviews__card-head">
								<div class="reviews__author">
									<?php if (! empty($review['photo'])) : ?>
										<div class="reviews__photo">
											<?php echo ksenon_acf_image($review['photo'], 'thumbnail', array('class' => 'reviews__photo-img cover-image')); ?>
										</div>
									<?php endif; ?>
									<div class="reviews__meta">
										<?php if (! empty($review['name'])) : ?>
											<div class="reviews__name"><?php echo esc_html($review['name']); ?></div>
										<?php endif; ?>
										<?php if (! empty($review['date_label'])) : ?>
											<div class="reviews__date"><?php echo esc_html($review['date_label']); ?></div>
										<?php endif; ?>
									</div>
								</div>
								<?php if (! empty($review['verified'])) : ?>
									<div class="reviews__verified"><?php esc_html_e('Проверено DRIVE2', 'ksenonspb'); ?></div>
								<?php endif; ?>
							</div>
							<?php if (! empty($review['text'])) : ?>
								<p class="reviews__text"><?php echo nl2br(esc_html($review['text'])); ?></p>
							<?php endif; ?>
							<?php if (! empty($review['story_title']) || ! empty($review['story_url']) || ! empty($review['car_model'])) : ?>
								<div class="reviews__story">
									<?php if (! empty($review['story_title'])) : ?>
										<div class="reviews__story-label"><?php esc_html_e('Подробный рассказ с фото', 'ksenonspb'); ?></div>
										<?php if (! empty($review['story_url'])) : ?>
											<a class="reviews__story-title" href="<?php echo esc_url($review['story_url']); ?>" target="_blank" rel="noopener noreferrer">
												<?php echo esc_html($review['story_title']); ?>
											</a>
										<?php else : ?>
											<div class="reviews__story-title"><?php echo esc_html($review['story_title']); ?></div>
										<?php endif; ?>
									<?php endif; ?>
									<?php if (! empty($review['car_model'])) : ?>
										<div class="reviews__car"><?php echo esc_html($review['car_model']); ?></div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>