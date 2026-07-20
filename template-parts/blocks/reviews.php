<?php

/**
 * Reviews block (CPT `review`)
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

$source_urls = ksenon_get_reviews_source_urls();

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
if ($grouped['drive2']) {
	$tabs['drive2'] = 'Drive2';
}
if ($grouped['yandex']) {
	$tabs['yandex'] = __('Яндекс', 'ksenonspb');
}
if (! $tabs) {
	$tabs['drive2'] = 'Drive2';
	$grouped['drive2'] = $reviews;
}

$active_tab = isset($tabs['drive2']) ? 'drive2' : array_key_first($tabs);
$more_label = __('Все отзывы', 'ksenonspb');
$more_url   = $source_urls[$active_tab] ?? ($source_urls['yandex'] ?? '');
$more_link  = array(
	'url'    => $more_url,
	'title'  => $more_label,
	'target' => '_blank',
);
$title_html = function_exists('ksenon_title_accent_html')
	? ksenon_title_accent_html((string) $args['title'])
	: esc_html((string) $args['title']);
?>
<section
	class="reviews"
	data-reviews
	data-url-yandex="<?php echo esc_url($source_urls['yandex']); ?>"
	data-url-drive2="<?php echo esc_url($source_urls['drive2']); ?>">
	<div class="reviews__container container">
		<div class="section-head section-head--row reviews__head">
			<h2 class="section-head__title title-md reviews__title">
				<?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				?>
			</h2>
			<?php if ($more_url) : ?>
				<?php ksenon_render_btn_arrow($more_link, 'btn btn--primary btn--large reviews__more', $more_label); ?>
			<?php endif; ?>
		</div>

		<?php if (count($tabs) > 1) : ?>
			<div class="reviews__tabs" role="tablist">
				<?php foreach ($tabs as $key => $label) : ?>
					<button
						class="reviews__tab reviews__tab--<?php echo esc_attr($key); ?><?php echo $key === $active_tab ? ' _active' : ''; ?>"
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
				<ul class="reviews__grid">
					<?php foreach ($grouped[$key] as $review) : ?>
						<?php
						$rating     = (int) ($review['rating'] ?? 5);
						$satisfied  = $rating >= 4;
						$car_model  = (string) ($review['car_model'] ?? '');
						$car_parts  = preg_split('/\s*·\s*/u', $car_model, 2);
						$car_name   = trim((string) ($car_parts[0] ?? ''));
						$car_meta   = trim((string) ($car_parts[1] ?? ''));
						$story_url  = trim((string) ($review['story_url'] ?? ''));
						$has_link   = '' !== $story_url;
						$has_story  = ! empty($review['story_title']) || $has_link || $car_model || ! empty($review['story_image']);
						$card_class = 'reviews__card' . ($has_link ? ' reviews__card--link' : '');
						?>
						<li class="reviews__item">
							<?php if ($has_link) : ?>
								<a
									class="<?php echo esc_attr($card_class); ?>"
									href="<?php echo esc_url($story_url); ?>"
									target="_blank"
									rel="noopener noreferrer">
								<?php else : ?>
									<div class="<?php echo esc_attr($card_class); ?>">
									<?php endif; ?>
									<div class="reviews__card-top">
										<div class="reviews__card-head">
											<div class="reviews__author">
												<?php if (! empty($review['photo'])) : ?>
													<div class="reviews__photo">
														<?php echo ksenon_acf_image($review['photo'], 'thumbnail', array('class' => 'reviews__photo-img cover-image')); ?>
													</div>
												<?php endif; ?>
												<div class="reviews__meta">
													<div class="reviews__name-row">
														<?php if (! empty($review['name'])) : ?>
															<div class="reviews__name"><?php echo esc_html($review['name']); ?></div>
														<?php endif; ?>
														<?php if ($satisfied) : ?>
															<span class="reviews__satisfied">
																<?php ksenon_icon('icon-review-thumbs', 12, 12, 'reviews__satisfied-icon'); ?>
																<span class="reviews__satisfied-text"><?php esc_html_e('доволен', 'ksenonspb'); ?></span>
															</span>
														<?php endif; ?>
													</div>
													<?php if (! empty($review['date_label'])) : ?>
														<div class="reviews__date"><?php echo esc_html($review['date_label']); ?></div>
													<?php endif; ?>
												</div>
											</div>
											<?php if (! empty($review['verified'])) : ?>
												<div class="reviews__verified">
													<?php ksenon_icon('icon-review-verified', 19, 19, 'reviews__verified-icon'); ?>
													<span class="reviews__verified-text"><?php esc_html_e('Проверено DRIVE2', 'ksenonspb'); ?></span>
												</div>
											<?php endif; ?>
										</div>
										<?php if (! empty($review['text'])) : ?>
											<p class="reviews__text"><?php echo nl2br(esc_html($review['text'])); ?></p>
										<?php endif; ?>
									</div>
									<?php if ($has_story) : ?>
										<div class="reviews__story">
											<div class="reviews__story-body">
												<?php if (! empty($review['story_title']) || $has_link) : ?>
													<div class="reviews__story-label"><?php esc_html_e('Подробный рассказ с фото', 'ksenonspb'); ?></div>
													<?php if (! empty($review['story_title'])) : ?>
														<div class="reviews__story-title"><?php echo esc_html($review['story_title']); ?></div>
													<?php endif; ?>
												<?php endif; ?>
												<?php if ($car_name) : ?>
													<div class="reviews__car">
														<span class="reviews__car-name"><?php echo esc_html($car_name); ?></span>
														<?php if ($car_meta) : ?>
															<span class="reviews__car-meta"> · <?php echo esc_html($car_meta); ?></span>
														<?php endif; ?>
													</div>
												<?php endif; ?>
											</div>
											<?php if (! empty($review['story_image'])) : ?>
												<div class="reviews__story-image">
													<?php echo ksenon_acf_image($review['story_image'], 'medium', array('class' => 'reviews__story-img')); ?>
												</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
									<?php if ($has_link) : ?>
								</a>
							<?php else : ?>
			</div>
		<?php endif; ?>
		</li>
	<?php endforeach; ?>
	</ul>
	</div>
<?php endforeach; ?>
</div>
</section>