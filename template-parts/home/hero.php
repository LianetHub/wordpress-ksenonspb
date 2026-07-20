<?php

/**
 * Home: Hero
 *
 * @package ksenonspb
 */

$title           = (string) ksenon_home_get('title');
$text            = (string) ksenon_home_get('text');
$btn             = ksenon_home_get('btn');
$slides          = ksenon_home_rows('slides');
$portfolio_count = ksenon_count_cpt('portfolio');
$portfolio_url   = ksenon_portfolio_archive_url();
?>
<section class="hero">
	<div class="hero__wrapper">
		<?php if ($slides) : ?>
			<div class="hero__promo">
				<div class="hero__promo-slider swiper">
					<div class="swiper-wrapper">
						<?php foreach ($slides as $slide) : ?>
							<?php
							$slide_title = trim((string) ($slide['title'] ?? ''));
							$slide_btn   = $slide['btn'] ?? null;
							?>
							<div class="hero__promo-slide swiper-slide">
								<?php if ($slide_title) : ?>
									<div class="hero__promo-title"><?php echo nl2br(ksenon_kses_inline($slide_title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																	?></div>
								<?php endif; ?>
								<?php if (is_array($slide_btn) && ! empty($slide_btn['url'])) : ?>
									<a
										class="hero__promo-btn btn btn--primary"
										href="<?php echo esc_url(ksenon_acf_link_url($slide_btn)); ?>"
										<?php echo ksenon_acf_link_target($slide_btn) ? ' target="' . esc_attr(ksenon_acf_link_target($slide_btn)) . '"' : ''; ?>>
										<?php echo esc_html(ksenon_acf_link_title($slide_btn, __('Подробнее', 'ksenonspb'))); ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="hero__promo-controls">
					<button
						type="button"
						class="hero__promo-prev swiper-button-prev"
						aria-label="<?php esc_attr_e('Предыдущий слайд', 'ksenonspb'); ?>">
						<?php ksenon_icon('icon-chevron-left', 7, 11, 'hero__promo-control-icon'); ?>
					</button>
					<button
						type="button"
						class="hero__promo-next swiper-button-next"
						aria-label="<?php esc_attr_e('Следующий слайд', 'ksenonspb'); ?>">
						<?php ksenon_icon('icon-chevron-right', 7, 11, 'hero__promo-control-icon'); ?>
					</button>
				</div>
			</div>
		<?php endif; ?>

		<div class="hero__main">
			<?php if ($title) : ?>
				<h1 class="hero__title title-lg"><?php echo nl2br(ksenon_kses_inline($title)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
													?></h1>
			<?php endif; ?>
			<?php if ($text) : ?>
				<p class="hero__text"><?php echo nl2br(esc_html($text)); ?></p>
			<?php endif; ?>
			<div class="hero__actions">
				<?php if (is_array($btn) && ! empty($btn['url'])) : ?>
					<?php ksenon_render_btn_arrow($btn, 'btn btn--primary btn--small hero__btn', __('Оценить ремонт', 'ksenonspb')); ?>
				<?php endif; ?>
				<?php if ($portfolio_count > 0 && $portfolio_url) : ?>
					<a class="hero__btn-secondary btn btn--white" href="<?php echo esc_url($portfolio_url); ?>">
						<?php echo esc_html(ksenon_portfolio_works_label($portfolio_count)); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>