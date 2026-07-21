<?php

/**
 * Page template: 404
 *
 * @package ksenonspb
 */

$title    = ksenon_get_option('404_title', __('Такой страницы не существует', 'ksenonspb'));
$subtitle = ksenon_get_option('404_subtitle', __('Воспользуйтесь меню, чтобы перейти на другие страницы', 'ksenonspb'));
$icons    = ksenon_assets_uri('img/icons.svg');
?>
<section class="not-found" aria-labelledby="not-found-title">
	<div class="not-found__container container">
		<div class="not-found__code" aria-hidden="true">
			<span class="not-found__digit">4</span>
			<span class="not-found__digit not-found__digit--accent">0</span>
			<span class="not-found__digit">4</span>
		</div>

		<div class="not-found__head">
			<?php if ($title) : ?>
				<h1 id="not-found-title" class="not-found__title title title-md"><?php echo esc_html($title); ?></h1>
			<?php endif; ?>
			<?php if ($subtitle) : ?>
				<p class="not-found__text text-lead"><?php echo esc_html($subtitle); ?></p>
			<?php endif; ?>
		</div>

		<div class="not-found__actions">
			<a class="btn btn--primary not-found__btn" href="<?php echo esc_url(home_url('/')); ?>">
				<?php esc_html_e('Вернуться на главную', 'ksenonspb'); ?>
				<svg class="btn__icon" width="28" height="28" aria-hidden="true">
					<use href="<?php echo esc_url($icons); ?>#icon-arrow-up-right"></use>
				</svg>
			</a>
			<a class="btn btn--secondary not-found__btn" href="<?php echo esc_url(ksenon_services_archive_url()); ?>">
				<?php esc_html_e('Смотреть услуги', 'ksenonspb'); ?>
				<svg class="btn__icon" width="28" height="28" aria-hidden="true">
					<use href="<?php echo esc_url($icons); ?>#icon-arrow-up-right"></use>
				</svg>
			</a>
		</div>
	</div>
</section>