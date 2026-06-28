<?php
/**
 * FAQ section
 *
 * @package ksenonspb
 *
 * @var array $args {
 *     @type string $tag   Section tag.
 *     @type string $title Section title.
 *     @type array  $items FAQ items with question, answer, is_open keys.
 * }
 */

$args = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'tag'   => '',
		'title' => '',
		'items' => array(),
	)
);

$tag   = (string) $args['tag'];
$title = (string) $args['title'];
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

$faq_col1 = array();
$faq_col2 = array();

foreach ( $faq_items as $index => $item ) {
	if ( ( $index + 1 ) % 2 === 0 ) {
		$faq_col2[] = $item;
	} else {
		$faq_col1[] = $item;
	}
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
				<p class="accordion__answer"><?php echo nl2br( esc_html( $item['answer'] ?? '' ) ); ?></p>
			</div>
		</div>
	</div>
	<?php
};
?>
<section class="faq">
	<div class="faq__container _container">
		<div class="faq__head">
			<?php if ( $tag ) : ?>
				<span class="faq__tag tag <?php echo ksenon_anim_class( 'bounce-up' ); ?>"><?php echo esc_html( $tag ); ?></span>
			<?php endif; ?>
			<?php if ( $title ) : ?>
				<h2 class="faq__title title-md <?php echo ksenon_anim_class( 'fade-up' ); ?>"><?php echo nl2br( esc_html( $title ) ); ?></h2>
			<?php endif; ?>
		</div>
		<div class="faq__columns accordion" data-accordion="">
			<?php if ( $faq_col1 ) : ?>
				<div class="faq__col <?php echo ksenon_anim_class( 'stagger' ); ?>">
					<?php
					foreach ( $faq_col1 as $item ) {
						$render_faq_item( $item );
					}
					?>
				</div>
			<?php endif; ?>
			<?php if ( $faq_col2 ) : ?>
				<div class="faq__col <?php echo ksenon_anim_class( 'stagger' ); ?>">
					<?php
					foreach ( $faq_col2 as $item ) {
						$render_faq_item( $item );
					}
					?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
