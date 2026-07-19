<?php

/**
 * Single portfolio case
 *
 * @package ksenonspb
 */

get_header();

while (have_posts()) :
	the_post();
	$post_id = get_the_ID();

	$hero_title = (string) (ksenon_get_post_field('hero_title', $post_id) ?: get_the_title());
	$price      = trim((string) ksenon_get_post_field('price', $post_id));
	$duration   = trim((string) ksenon_get_post_field('duration', $post_id));
	$video      = trim((string) ksenon_get_post_field('video', $post_id));

	$before = ksenon_get_post_field('before_image', $post_id);
	$after  = ksenon_get_post_field('after_image', $post_id);

	if (! $before) {
		$before = ksenon_get_post_field('hero_image', $post_id);
	}
	if (! $after && has_post_thumbnail($post_id)) {
		$after = get_post_thumbnail_id($post_id);
	}

	$task_description = (string) ksenon_get_post_field('task_description', $post_id);
	$process          = array_values(array_filter(
		(array) ksenon_get_post_field('work_process', $post_id),
		static function ($step) {
			return is_array($step) && (! empty($step['title']) || ! empty($step['text']) || ! empty($step['image']));
		}
	));

	$video_poster = '';
	if ($video) {
		$video_id = ksenon_youtube_id($video);
		if ($video_id) {
			$video_poster = 'https://i.ytimg.com/vi/' . rawurlencode($video_id) . '/hqdefault.jpg';
		}
	}
	if (! $video_poster && $after) {
		$video_poster = ksenon_acf_image_url($after, 'large');
	}
	if (! $video_poster && ! empty($process[0]['image'])) {
		$video_poster = ksenon_acf_image_url($process[0]['image'], 'large');
	}
?>
	<article class="case-page">
		<section class="case-hero">
			<div class="case-hero__container container">
				<h1 class="case-hero__title title-lg"><?php echo esc_html($hero_title); ?></h1>
				<?php if ($price || $duration) : ?>
					<p class="case-hero__meta">
						<?php if ($price) : ?>
							<span class="case-hero__price"><?php echo esc_html($price); ?></span>
						<?php endif; ?>
						<?php if ($price && $duration) : ?>
							<span class="case-hero__sep" aria-hidden="true"> / </span>
						<?php endif; ?>
						<?php if ($duration) : ?>
							<span class="case-hero__duration"><?php echo esc_html($duration); ?></span>
						<?php endif; ?>
					</p>
				<?php endif; ?>
			</div>
		</section>

		<?php if ($before || $after) : ?>
			<section class="case-before-after">
				<div class="case-before-after__container container">
					<div class="case-before-after__grid">
						<?php if ($before) : ?>
							<figure class="case-before-after__item">
								<?php echo ksenon_acf_image($before, 'large', array('class' => 'case-before-after__img cover-image')); ?>
								<figcaption class="case-before-after__badge"><?php esc_html_e('До', 'ksenonspb'); ?></figcaption>
							</figure>
						<?php endif; ?>
						<?php if ($after) : ?>
							<figure class="case-before-after__item">
								<?php echo ksenon_acf_image($after, 'large', array('class' => 'case-before-after__img cover-image')); ?>
								<figcaption class="case-before-after__badge"><?php esc_html_e('После', 'ksenonspb'); ?></figcaption>
							</figure>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ($task_description) : ?>
			<section class="case-task">
				<div class="case-task__container container typography-block">
					<h2 class="case-task__title title-md"><?php esc_html_e('Описание задачи', 'ksenonspb'); ?></h2>
					<div class="case-task__text">
						<?php echo wp_kses_post($task_description); ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ($process) : ?>
			<section class="case-done" data-case-steps>
				<div class="case-done__container container container--large">
					<div class="case-done__inner">
						<h2 class="case-done__title title-md">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %s: accented word */
									__('Что мы %s', 'ksenonspb'),
									'<span class="case-done__title-accent">' . esc_html__('сделали?', 'ksenonspb') . '</span>'
								),
								array('span' => array('class' => true))
							);
							?>
						</h2>
						<div class="case-done__layout">
							<ol class="case-done__list" role="tablist" aria-label="<?php esc_attr_e('Шаги работы', 'ksenonspb'); ?>">
								<?php foreach ($process as $index => $step) : ?>
									<?php
									$is_active = 0 === $index;
									$step_id   = 'case-step-' . ($index + 1);
									$panel_id  = 'case-step-panel-' . ($index + 1);
									$title     = (string) ($step['title'] ?? '');
									$text      = (string) ($step['text'] ?? '');
									?>
									<li class="case-done__item<?php echo $is_active ? ' is-active' : ''; ?>">
										<button
											type="button"
											class="case-done__step"
											id="<?php echo esc_attr($step_id); ?>"
											role="tab"
											aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
											aria-controls="<?php echo esc_attr($panel_id); ?>"
											data-case-step="<?php echo esc_attr((string) $index); ?>"
											tabindex="<?php echo $is_active ? '0' : '-1'; ?>">
											<span class="case-done__step-head">
												<span class="case-done__step-num"><?php echo esc_html((string) ($index + 1)); ?>.</span>
												<span class="case-done__step-title"><?php echo esc_html($title); ?></span>
												<span class="case-done__step-arrow" aria-hidden="true">→</span>
											</span>
										</button>
										<?php if ($text) : ?>
											<div
												class="case-done__step-text"
												id="<?php echo esc_attr($panel_id); ?>"
												role="tabpanel"
												aria-labelledby="<?php echo esc_attr($step_id); ?>"
												<?php echo $is_active ? '' : ' hidden'; ?>><?php echo esc_html($text); ?></div>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ol>
							<?php
							$process_has_images = false;
							foreach ($process as $step) {
								if (! empty($step['image'])) {
									$process_has_images = true;
									break;
								}
							}
							?>
							<?php if ($process_has_images) : ?>
								<div class="case-done__media" aria-hidden="true">
									<div class="case-done__stack">
										<?php foreach ($process as $index => $step) : ?>
											<?php if (empty($step['image'])) : ?>
												<?php continue; ?>
											<?php endif; ?>
											<div
												class="case-done__shot<?php echo 0 === $index ? ' is-active' : ''; ?>"
												data-case-step-image="<?php echo esc_attr((string) $index); ?>">
												<?php echo ksenon_acf_image($step['image'], 'large', array('class' => 'case-done__img cover-image')); ?>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ($video) : ?>
			<section class="case-video">
				<div class="case-video__container container">
					<h2 class="case-video__title title-md"><?php esc_html_e('Процесс работы', 'ksenonspb'); ?></h2>
					<a
						class="case-video__link"
						href="<?php echo esc_url($video); ?>"
						data-fancybox="case-video"
						data-type="video"
						aria-label="<?php esc_attr_e('Смотреть видео процесса работы', 'ksenonspb'); ?>">
						<?php if ($video_poster) : ?>
							<img
								class="case-video__poster cover-image"
								src="<?php echo esc_url($video_poster); ?>"
								alt=""
								loading="lazy"
								decoding="async">
						<?php endif; ?>
						<span class="case-video__play">
							<?php ksenon_icon('icon-play', 91, 91, 'case-video__play-icon'); ?>
						</span>
					</a>
				</div>
			</section>
		<?php endif; ?>

		<?php get_template_part('template-parts/blocks/cta-form', null, array('variant' => 'same_result')); ?>

		<?php
		$related = ksenon_get_related_portfolio($post_id, 4);
		if ($related->have_posts()) :
		?>
			<section class="case-related">
				<div class="case-related__container container container--large">
					<div class="case-related__inner">
						<h2 class="case-related__title title-md"><?php esc_html_e('Похожие работы', 'ksenonspb'); ?></h2>
						<div class="case-related__grid">
							<?php
							while ($related->have_posts()) :
								$related->the_post();
								get_template_part('template-parts/blocks/portfolio-card', null, array('post' => get_post()));
							endwhile;
							wp_reset_postdata();
							?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</article>
<?php
endwhile;

get_footer();
