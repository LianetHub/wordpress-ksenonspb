<?php

/**
 * FAQ section
 *
 * @package ksenonspb
 *
 * @var array $args {
 *     @type string $title Section title.
 *     @type string $intro Sidebar intro.
 *     @type array  $items FAQ items with question, answer, answer_after, prices, is_open keys.
 * }
 */

$args = wp_parse_args(
	isset($args) && is_array($args) ? $args : array(),
	array(
		'title' => '',
		'intro' => '',
		'items' => array(),
	)
);

$title = (string) $args['title'];
$intro = (string) $args['intro'];
$items = (array) $args['items'];

$faq_items = array();

foreach ($items as $item) {
	if (! is_array($item)) {
		continue;
	}

	$question = trim((string) ($item['question'] ?? ''));
	if ('' === $question) {
		continue;
	}

	$prices = array();
	if (! empty($item['prices']) && is_array($item['prices'])) {
		foreach ($item['prices'] as $row) {
			if (! is_array($row)) {
				continue;
			}
			$label = trim((string) ($row['label'] ?? ''));
			$value = trim((string) ($row['value'] ?? ''));
			if ('' === $label && '' === $value) {
				continue;
			}
			$prices[] = array(
				'label' => $label,
				'value' => $value,
			);
		}
	}

	$faq_items[] = array(
		'question'     => $question,
		'answer'       => (string) ($item['answer'] ?? ''),
		'answer_after' => (string) ($item['answer_after'] ?? ''),
		'prices'       => $prices,
		'is_open'      => ! empty($item['is_open']),
	);
}

if (! $faq_items) {
	return;
}

if (! $intro) {
	$intro = __('Собрали ответы на то, что чаще всего спрашивают перед заказом. Не нашли свой вопрос — напишите нам.', 'ksenonspb');
}

if (! $title) {
	$title = __('Частые вопросы', 'ksenonspb');
}

$render_faq_item = static function ($item) {
	$is_open      = ! empty($item['is_open']);
	$answer       = trim((string) ($item['answer'] ?? ''));
	$answer_after = trim((string) ($item['answer_after'] ?? ''));
	$prices       = (array) ($item['prices'] ?? array());
?>
	<div class="accordion__item<?php echo $is_open ? ' _active' : ''; ?>">
		<button class="accordion__header" type="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
			<span class="accordion__question"><?php echo esc_html($item['question'] ?? ''); ?></span>
			<span class="accordion__toggle" aria-hidden="true">
				<?php ksenon_icon('icon-faq-plus', 18, 19, 'accordion__toggle-icon'); ?>
			</span>
		</button>
		<div class="accordion__body">
			<div class="accordion__inner">
				<div class="accordion__answer">
					<?php if ($answer) : ?>
						<?php echo wp_kses_post(wpautop($answer)); ?>
					<?php endif; ?>
					<?php if ($prices) : ?>
						<ul class="faq-prices">
							<?php foreach ($prices as $row) : ?>
								<li class="faq-prices__row">
									<span class="faq-prices__label"><?php echo esc_html($row['label'] ?? ''); ?></span>
									<span class="faq-prices__value"><?php echo esc_html($row['value'] ?? ''); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ($answer_after) : ?>
						<?php echo wp_kses_post(wpautop($answer_after)); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
<?php
};
?>
<section class="faq">
	<div class="faq__container container">
		<div class="faq__layout">
			<aside class="faq__sidebar">
				<p class="faq__label <?php echo ksenon_anim_class('fade-up'); ?>"><?php esc_html_e('FAQ', 'ksenonspb'); ?></p>
				<h2 class="faq__title title-md <?php echo ksenon_anim_class('fade-up'); ?>"><?php echo ksenon_faq_title_html($title); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																							?></h2>
				<?php if ($intro) : ?>
					<p class="faq__intro"><?php echo nl2br(esc_html($intro)); ?></p>
				<?php endif; ?>
				<div class="faq__help">
					<div class="faq__help-inner">
						<div class="faq__help-copy">
							<p class="faq__help-title"><?php esc_html_e('Остались вопросы?', 'ksenonspb'); ?></p>
							<p class="faq__help-text"><?php esc_html_e('Ответим в мессенджере в течение 15 минут в рабочее время.', 'ksenonspb'); ?></p>
						</div>
						<?php ksenon_render_messenger_links('faq__messengers messenger-links', true); ?>
					</div>
				</div>
			</aside>

			<div class="faq__content accordion" data-accordion="">
				<?php
				foreach ($faq_items as $item) {
					$render_faq_item($item);
				}
				?>
			</div>
		</div>
	</div>
</section>