<?php

/**
 * Pricing page
 *
 * @package ksenonspb
 */

$hero_title = function_exists('get_field') ? (string) get_field('hero_title') : '';
if ('' === $hero_title) {
	$hero_title = get_the_title();
}

$hero_notice = function_exists('get_field')
	? (string) get_field('hero_notice')
	: '';
if ('' === $hero_notice) {
	$hero_notice = __('Цены ориентировочные — точная стоимость рассчитывается после бесплатного осмотра', 'ksenonspb');
}

$col_work     = function_exists('get_field') && get_field('table_col_work') ? (string) get_field('table_col_work') : __('Вид работы', 'ksenonspb');
$col_price    = function_exists('get_field') && get_field('table_col_price') ? (string) get_field('table_col_price') : __('Цена', 'ksenonspb');
$col_duration = function_exists('get_field') && get_field('table_col_duration') ? (string) get_field('table_col_duration') : __('Срок', 'ksenonspb');

$pricing_tabs = array(
	array(
		'key'   => 'remont',
		'label' => __('Ремонт', 'ksenonspb'),
		'tax'   => array(
			'taxonomy'         => 'service_category',
			'field'            => 'term_id',
			'terms'            => array(18),
			'include_children' => true,
		),
	),
	array(
		'key'   => 'tyuning',
		'label' => __('Тюнинг', 'ksenonspb'),
		'tax'   => array(
			'taxonomy'         => 'service_category',
			'field'            => 'term_id',
			'terms'            => array(17),
			'include_children' => true,
		),
	),
	array(
		'key'   => 'complex',
		'label' => __('Сложные работы', 'ksenonspb'),
		'tax'   => array(
			'taxonomy'         => 'service_category',
			'field'            => 'term_id',
			'terms'            => array(17, 18),
			'operator'         => 'NOT IN',
			'include_children' => true,
		),
	),
);

$tab_panels = array();
foreach ($pricing_tabs as $tab) {
	$query = ksenon_query_services(
		array(
			'posts_per_page'         => -1,
			'update_post_meta_cache' => true,
			'tax_query'              => array($tab['tax']),
		)
	);

	$rows = array();
	if ($query instanceof WP_Query) {
		while ($query->have_posts()) {
			$query->the_post();
			$service_id = get_the_ID();
			$rows[]     = array(
				'title'    => get_the_title(),
				'note'     => (string) ksenon_get_post_field('price_note', $service_id),
				'price'    => ksenon_get_post_field('price_from', $service_id),
				'duration' => (string) ksenon_get_post_field('duration', $service_id),
			);
		}
		wp_reset_postdata();
	}

	$tab_panels[] = array(
		'key'   => $tab['key'],
		'label' => $tab['label'],
		'rows'  => $rows,
	);
}

$why_title = function_exists('get_field') && get_field('why_prices_title')
	? (string) get_field('why_prices_title')
	: __('Почему цены разные?', 'ksenonspb');
$why_cards = function_exists('get_field') ? (array) get_field('why_prices_cards') : array();
$why_cards = array_values(
	array_filter(
		$why_cards,
		static function ($card) {
			return is_array($card) && ('' !== trim((string) ($card['title'] ?? '')) || '' !== trim((string) ($card['text'] ?? '')));
		}
	)
);

$installment_title = function_exists('get_field') ? (string) get_field('installment_title') : '';
$installment_text  = function_exists('get_field') ? (string) get_field('installment_text') : '';
$installment_rows  = function_exists('get_field') ? (array) get_field('installment_rows') : array();
$installment_rows  = array_values(
	array_filter(
		$installment_rows,
		static function ($row) {
			return is_array($row) && ('' !== trim((string) ($row['label'] ?? '')) || '' !== trim((string) ($row['value'] ?? '')));
		}
	)
);
$installment_btn = function_exists('get_field') && get_field('installment_btn')
	? (string) get_field('installment_btn')
	: __('Взять рассрочку', 'ksenonspb');

$gift_title = function_exists('get_field') ? (string) get_field('gift_title') : '';
$gift_text  = function_exists('get_field') ? (string) get_field('gift_text') : '';
$gift_amounts = function_exists('get_field') ? (array) get_field('gift_amounts') : array();
$gift_amounts = array_values(
	array_filter(
		$gift_amounts,
		static function ($row) {
			return is_array($row) && '' !== trim((string) ($row['amount'] ?? ''));
		}
	)
);
$gift_custom_label = function_exists('get_field') && get_field('gift_custom_label')
	? (string) get_field('gift_custom_label')
	: __('Своя сумма', 'ksenonspb');
$gift_btn = function_exists('get_field') && get_field('gift_btn')
	? (string) get_field('gift_btn')
	: __('Взять сертификат', 'ksenonspb');

$icons = ksenon_assets_uri('img/icons.svg');
$nbsp  = "\xc2\xa0";
?>
<section class="pricing-page" data-pricing>
	<div class="pricing-page__container container">
		<h1 class="pricing-page__title title-lg"><?php echo esc_html($hero_title); ?></h1>

		<?php if ($hero_notice) : ?>
			<p class="pricing-page__notice">
				<svg class="pricing-page__notice-icon icon" width="15" height="15" aria-hidden="true">
					<use href="<?php echo esc_url($icons); ?>#icon-notification"></use>
				</svg>
				<span class="pricing-page__notice-text"><?php echo esc_html($hero_notice); ?></span>
			</p>
		<?php endif; ?>

		<?php if ($tab_panels) : ?>
			<div class="pricing-page__tabs" role="tablist" aria-label="<?php esc_attr_e('Категории услуг', 'ksenonspb'); ?>">
				<?php foreach ($tab_panels as $index => $panel) : ?>
					<?php
					$tab_key   = (string) $panel['key'];
					$tab_id    = 'pricing-tab-' . $tab_key;
					$panel_id  = 'pricing-panel-' . $tab_key;
					$is_active = 0 === $index;
					?>
					<button
						type="button"
						class="pricing-page__tab<?php echo $is_active ? ' _active' : ''; ?>"
						id="<?php echo esc_attr($tab_id); ?>"
						role="tab"
						aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr($panel_id); ?>"
						data-pricing-tab="<?php echo esc_attr($tab_key); ?>"
					>
						<?php echo esc_html((string) $panel['label']); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="pricing-page__table-head" aria-hidden="true">
				<span class="pricing-page__col-label pricing-page__col-label--work"><?php echo esc_html($col_work); ?></span>
				<span class="pricing-page__col-labels">
					<span class="pricing-page__col-label"><?php echo esc_html($col_price); ?></span>
					<span class="pricing-page__col-label"><?php echo esc_html($col_duration); ?></span>
				</span>
			</div>

			<?php foreach ($tab_panels as $index => $panel) : ?>
				<?php
				$tab_key   = (string) $panel['key'];
				$panel_id  = 'pricing-panel-' . $tab_key;
				$tab_id    = 'pricing-tab-' . $tab_key;
				$is_active = 0 === $index;
				?>
				<div
					class="pricing-page__panel<?php echo $is_active ? ' _active' : ''; ?>"
					id="<?php echo esc_attr($panel_id); ?>"
					role="tabpanel"
					aria-labelledby="<?php echo esc_attr($tab_id); ?>"
					data-pricing-panel="<?php echo esc_attr($tab_key); ?>"
					<?php echo $is_active ? '' : ' hidden'; ?>
				>
					<?php if ($panel['rows']) : ?>
						<ul class="pricing-page__list">
							<?php foreach ($panel['rows'] as $row) : ?>
								<li class="pricing-page__row">
									<div class="pricing-page__work">
										<span class="pricing-page__work-name"><?php echo esc_html($row['title']); ?></span>
										<?php if ($row['note']) : ?>
											<span class="pricing-page__work-note"><?php echo esc_html('(' . $row['note'] . ')'); ?></span>
										<?php endif; ?>
									</div>
									<div class="pricing-page__meta">
										<span class="pricing-page__price"><?php echo esc_html(ksenon_format_price_table($row['price'])); ?></span>
										<span class="pricing-page__duration"><?php echo esc_html($row['duration']); ?></span>
									</div>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="pricing-page__empty"><?php esc_html_e('В этой категории пока нет услуг.', 'ksenonspb'); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</section>

<?php if ($why_cards) : ?>
	<section class="pricing-why">
		<div class="pricing-why__container container">
			<h2 class="pricing-why__title title-md"><?php echo esc_html($why_title); ?></h2>
			<ul class="pricing-why__grid">
				<?php foreach ($why_cards as $card) : ?>
					<li class="pricing-why__card">
						<?php if (! empty($card['title'])) : ?>
							<h3 class="pricing-why__card-title"><?php echo esc_html((string) $card['title']); ?></h3>
						<?php endif; ?>
						<?php if (! empty($card['text'])) : ?>
							<p class="pricing-why__card-text"><?php echo esc_html((string) $card['text']); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
<?php endif; ?>

<?php if ($installment_title || $gift_title) : ?>
	<section class="pricing-extra">
		<div class="pricing-extra__container container">
			<div class="pricing-extra__inner">
				<?php if ($installment_title || $installment_rows) : ?>
					<div class="pricing-extra__col">
						<article class="pricing-extra__card">
							<?php if ($installment_title) : ?>
								<h2 class="pricing-extra__title"><?php echo esc_html($installment_title); ?></h2>
							<?php endif; ?>
							<?php if ($installment_text) : ?>
								<p class="pricing-extra__lead"><?php echo esc_html($installment_text); ?></p>
							<?php endif; ?>
							<?php if ($installment_rows) : ?>
								<ul class="pricing-extra__rows">
									<?php foreach ($installment_rows as $row) : ?>
										<li class="pricing-extra__row">
											<span class="pricing-extra__row-label"><?php echo esc_html((string) ($row['label'] ?? '')); ?></span>
											<span class="pricing-extra__row-value"><?php echo esc_html((string) ($row['value'] ?? '')); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</article>
						<button
							type="button"
							class="btn btn--primary btn--large pricing-extra__btn"
							data-fancybox
							data-src="#popup-installment"
						>
							<span class="btn__text"><?php echo esc_html($installment_btn); ?></span>
						</button>
					</div>
				<?php endif; ?>

				<?php if ($gift_title || $gift_amounts) : ?>
					<div class="pricing-extra__col" data-gift-amounts>
						<article class="pricing-extra__card">
							<?php if ($gift_title) : ?>
								<h2 class="pricing-extra__title"><?php echo esc_html($gift_title); ?></h2>
							<?php endif; ?>
							<?php if ($gift_text) : ?>
								<p class="pricing-extra__lead"><?php echo esc_html($gift_text); ?></p>
							<?php endif; ?>
							<?php if ($gift_amounts || $gift_custom_label) : ?>
								<div class="pricing-extra__amounts">
									<?php foreach ($gift_amounts as $index => $amount_row) : ?>
										<?php
										$amount      = (int) ($amount_row['amount'] ?? 0);
										$amount_label = number_format($amount, 0, '', $nbsp) . $nbsp . '₽';
										$is_default   = 1 === $index || (1 === count($gift_amounts) && 0 === $index);
										?>
										<button
											type="button"
											class="pricing-extra__amount<?php echo $is_default ? ' _active' : ''; ?>"
											data-gift-amount="<?php echo esc_attr((string) $amount); ?>"
											aria-pressed="<?php echo $is_default ? 'true' : 'false'; ?>"
										>
											<?php echo esc_html($amount_label); ?>
										</button>
									<?php endforeach; ?>
									<?php if ($gift_custom_label) : ?>
										<button
											type="button"
											class="pricing-extra__amount pricing-extra__amount--custom"
											data-gift-amount="custom"
											aria-pressed="false"
										>
											<?php echo esc_html($gift_custom_label); ?>
										</button>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</article>
						<button
							type="button"
							class="btn btn--white btn--large pricing-extra__btn"
							data-fancybox
							data-src="#popup-certificate"
							data-gift-open
						>
							<span class="btn__text"><?php echo esc_html($gift_btn); ?></span>
						</button>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
<?php endif; ?>
