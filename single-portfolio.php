<?php
/**
 * Single portfolio case
 *
 * @package ksenonspb
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id = get_the_ID();
	?>
	<article class="case-page">
		<section class="case-hero">
			<div class="case-hero__container _container">
				<h1 class="case-hero__title title-lg"><?php echo esc_html( (string) ( ksenon_get_post_field( 'hero_title', $post_id ) ?: get_the_title() ) ); ?></h1>
				<?php
				$hero_image = ksenon_get_post_field( 'hero_image', $post_id );
				if ( $hero_image ) {
					echo ksenon_acf_image( $hero_image, 'large', array( 'class' => 'case-hero__img' ) );
				}
				?>
			</div>
		</section>

		<?php if ( ksenon_get_post_field( 'case_description', $post_id ) ) : ?>
			<section class="case-description">
				<div class="case-description__container _container typography-block">
					<?php the_content(); ?>
					<?php echo wp_kses_post( (string) ksenon_get_post_field( 'case_description', $post_id ) ); ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$what_we_did = (array) ksenon_get_post_field( 'what_we_did', $post_id );
		if ( $what_we_did ) :
			?>
			<section class="case-done">
				<div class="case-done__container _container">
					<h2 class="case-done__title title-md"><?php esc_html_e( 'Что мы сделали', 'ksenonspb' ); ?></h2>
					<ul class="case-done__list">
						<?php foreach ( $what_we_did as $row ) : ?>
							<?php if ( ! empty( $row['text'] ) ) : ?>
								<li><?php echo esc_html( $row['text'] ); ?></li>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$process = (array) ksenon_get_post_field( 'work_process', $post_id );
		if ( $process ) :
			?>
			<section class="case-process">
				<div class="case-process__container _container">
					<h2 class="case-process__title title-md"><?php esc_html_e( 'Процесс работы', 'ksenonspb' ); ?></h2>
					<div class="case-process__steps">
						<?php foreach ( $process as $index => $step ) : ?>
							<div class="case-process__step">
								<span class="case-process__num"><?php echo esc_html( (string) ( $index + 1 ) ); ?></span>
								<?php if ( ! empty( $step['title'] ) ) : ?>
									<h3><?php echo esc_html( $step['title'] ); ?></h3>
								<?php endif; ?>
								<?php if ( ! empty( $step['text'] ) ) : ?>
									<p><?php echo nl2br( esc_html( $step['text'] ) ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$components = (array) ksenon_get_post_field( 'components', $post_id );
		if ( $components ) :
			?>
			<section class="case-components">
				<div class="case-components__container _container">
					<h2 class="case-components__title title-md"><?php esc_html_e( 'Использованные комплектующие', 'ksenonspb' ); ?></h2>
					<div class="case-components__grid">
						<?php foreach ( $components as $component ) : ?>
							<div class="case-components__item">
								<?php if ( ! empty( $component['name'] ) ) : ?>
									<h3><?php echo esc_html( $component['name'] ); ?></h3>
								<?php endif; ?>
								<?php if ( ! empty( $component['description'] ) ) : ?>
									<p><?php echo esc_html( $component['description'] ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/blocks/cta-form', null, array( 'variant' => 'same_result' ) ); ?>

		<?php
		$related = ksenon_get_related_portfolio( $post_id, 4 );
		if ( $related->have_posts() ) :
			?>
			<section class="case-related">
				<div class="case-related__container _container">
					<h2 class="case-related__title title-md"><?php esc_html_e( 'Похожие работы', 'ksenonspb' ); ?></h2>
					<div class="case-related__grid">
						<?php
						while ( $related->have_posts() ) :
							$related->the_post();
							get_template_part( 'template-parts/blocks/portfolio-card', null, array( 'post' => get_post() ) );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
