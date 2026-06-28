<?php
/**
 * FAQ section
 *
 * @package ksenonspb
 *
 * @var array $args {
 *     @type string $tag   Section tag.
 *     @type string $title Section title.
 *     @type string $intro Sidebar intro.
 *     @type array  $items FAQ items with question, answer, is_open keys.
 * }
 */

$args = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'tag'   => '',
		'title' => '',
		'intro' => '',
		'items' => array(),
	)
);

$tag   = (string) $args['tag'];
$title = (string) $args['title'];
$intro = (string) $args['intro'];
$items = (array) $args['items'];

$faq_items = array();

foreach ( $items as $item ) {
	if ( ! is_array( $item ) ) {
		continue;
	}

	$question = trim( (string) ( $item['question'] ?? '' ) );
	if ( '' === $question ) {
		continue;
	}

	$faq_items[] = array(
		'question' => $question,
		'answer'   => (string) ( $item['answer'] ?? '' ),
		'is_open'  => ! empty( $item['is_open'] ),
	);
}

if ( ! $faq_items ) {
	return;
}

if ( ! $intro ) {
	$intro = __( 'Собрали ответы на то, что чаще всего спрашивают перед заказом. Не нашли свой вопрос — напишите нам.', 'ksenonspb' );
}

if ( ! $title ) {
	$title = __( 'Частые вопросы', 'ksenonspb' );
}

$icons = ksenon_assets_uri( 'img/icons.svg' );

$render_faq_item = static function ( $item ) use ( $icons ) {
	$is_open = ! empty( $item['is_open'] );
	?>
	<div class="accordion__item<?php echo $is_open ? ' _active' : ''; ?>">
		<button class="accordion__header" type="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>">
			<span class="accordion__question"><?php echo esc_html( $item['question'] ?? '' ); ?></span>
			<svg class="accordion__chevron icon" width="32" height="32" aria-hidden="true">
				<use href="<?php echo esc_url( $icons ); ?>#icon-panels-chevron"></use>
			</svg>
		</button>
		<div class="accordion__body">
			<div class="accordion__inner">
				<div class="accordion__answer"><?php echo wp_kses_post( wpautop( $item['answer'] ?? '' ) ); ?></div>
			</div>
		</div>
	</div>
	<?php
};
?>
<section class="faq">
	<div class="faq__container _container">
		<div class="faq__layout">
			<aside class="faq__sidebar">
				<?php if ( $tag ) : ?>
					<span class="faq__tag tag <?php echo ksenon_anim_class( 'bounce-up' ); ?>"><?php echo esc_html( $tag ); ?></span>
				<?php endif; ?>
				<?php if ( $title ) : ?>
					<h2 class="faq__title title-md <?php echo ksenon_anim_class( 'fade-up' ); ?>"><?php echo nl2br( esc_html( $title ) ); ?></h2>
				<?php endif; ?>
				<?php if ( $intro ) : ?>
					<p class="faq__intro"><?php echo nl2br( esc_html( $intro ) ); ?></p>
				<?php endif; ?>
				<div class="faq__help">
					<p class="faq__help-title"><?php esc_html_e( 'Остались вопросы?', 'ksenonspb' ); ?></p>
					<p class="faq__help-text"><?php esc_html_e( 'Ответим в мессенджере в течение 15 минут в рабочее время.', 'ksenonspb' ); ?></p>
					<?php ksenon_render_messenger_links( 'faq__messengers' ); ?>
				</div>
			</aside>

			<div class="faq__content accordion" data-accordion="">
				<?php
				foreach ( $faq_items as $item ) {
					$render_faq_item( $item );
				}
				?>
			</div>
		</div>
	</div>
</section>
