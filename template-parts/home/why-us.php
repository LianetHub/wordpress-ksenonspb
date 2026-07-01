<?php

/**
 * Home: Why us
 *
 * @package ksenonspb
 */

$items = ksenon_home_rows('items');
$items = array_values(
	array_filter(
		$items,
		static function ($item) {
			return is_array($item) && (! empty($item['title']) || ! empty($item['text']));
		}
	)
);

if (! $items && ! ksenon_home_get('title')) {
	return;
}

$active_index = 0;
foreach ($items as $index => $item) {
	if (! empty($item['step']) && str_contains((string) $item['step'], '04')) {
		$active_index = $index;
		break;
	}
}
?>
<section class="why-us">
	<div class="why-us__container container">
		<?php if (ksenon_home_get('tag')) : ?>
			<span class="why-us__tag tag"><?php echo esc_html((string) ksenon_home_get('tag')); ?></span>
		<?php endif; ?>
		<h2 class="why-us__title title-md"><?php echo esc_html((string) ksenon_home_get('title', __('Почему мы?', 'ksenonspb'))); ?></h2>

		<?php if ($items) : ?>
			<div class="why-us__panels" data-panels>
				<div class="why-us__cards">
					<?php foreach ($items as $index => $item) : ?>
						<?php
						$is_active = $index === $active_index;
						$step      = trim((string) ($item['step'] ?? ''));
						if (! $step) {
							$step = sprintf('%02d / %02d', $index + 1, count($items));
						}
						?>
						<article class="why-us__card panels__item<?php echo $is_active ? ' _active' : ''; ?>" data-panel-index="<?php echo esc_attr((string) $index); ?>">
							<?php if (! empty($item['image'])) : ?>
								<div class="why-us__card-media">
									<?php echo ksenon_acf_image($item['image'], 'medium_large', array('class' => 'why-us__card-img cover-image')); ?>
								</div>
							<?php endif; ?>
							<button class="why-us__card-heading panels__heading" type="button" aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>">
								<?php if ($step) : ?>
									<span class="why-us__card-step"><?php echo esc_html($step); ?></span>
								<?php endif; ?>
								<?php if (! empty($item['title'])) : ?>
									<h3 class="why-us__card-title"><?php echo esc_html($item['title']); ?></h3>
								<?php endif; ?>
							</button>
							<?php if (! empty($item['text'])) : ?>
								<div class="why-us__card-body panels__body">
									<p class="why-us__card-text"><?php echo nl2br(esc_html($item['text'])); ?></p>
								</div>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>

				<?php $active = $items[$active_index] ?? $items[0]; ?>
				<div class="why-us__detail">
					<?php if (! empty($active['step'])) : ?>
						<div class="why-us__detail-step"><?php echo esc_html($active['step']); ?></div>
					<?php endif; ?>
					<?php if (! empty($active['title'])) : ?>
						<h3 class="why-us__detail-title"><?php echo esc_html($active['title']); ?></h3>
					<?php endif; ?>
					<?php if (! empty($active['text'])) : ?>
						<p class="why-us__detail-text"><?php echo nl2br(esc_html($active['text'])); ?></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>