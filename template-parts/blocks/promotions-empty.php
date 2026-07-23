<?php

/**
 * Empty state for promotions archive
 *
 * @package ksenonspb
 */

$icons = ksenon_assets_uri('img/icons.svg');
?>
<section class="promotions-empty" aria-labelledby="promotions-empty-title">
	<div class="promotions-empty__inner">
		<div class="promotions-empty__code" aria-hidden="true">
			<span class="promotions-empty__digit">0</span>
			<span class="promotions-empty__digit promotions-empty__digit--accent">%</span>
		</div>

		<div class="promotions-empty__head">
			<h2 id="promotions-empty-title" class="promotions-empty__title title title-md">
				<?php esc_html_e('Сейчас акций нет', 'ksenonspb'); ?>
			</h2>
			<p class="promotions-empty__text text-lead">
				<?php esc_html_e('Скоро появятся новые предложения. А пока загляните в услуги или вернитесь на главную.', 'ksenonspb'); ?>
			</p>
		</div>

		<div class="promotions-empty__actions">
			<a class="btn btn--primary promotions-empty__btn" href="<?php echo esc_url(home_url('/')); ?>">
				<?php esc_html_e('Вернуться на главную', 'ksenonspb'); ?>
				<svg class="btn__icon" width="28" height="28" aria-hidden="true">
					<use href="<?php echo esc_url($icons); ?>#icon-arrow-up-right"></use>
				</svg>
			</a>
			<a class="btn btn--secondary promotions-empty__btn" href="<?php echo esc_url(ksenon_services_archive_url()); ?>">
				<?php esc_html_e('Смотреть услуги', 'ksenonspb'); ?>
				<svg class="btn__icon" width="28" height="28" aria-hidden="true">
					<use href="<?php echo esc_url($icons); ?>#icon-arrow-up-right"></use>
				</svg>
			</a>
		</div>
	</div>
</section>
