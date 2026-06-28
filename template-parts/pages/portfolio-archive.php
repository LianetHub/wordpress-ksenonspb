<?php
/**
 * Portfolio archive page
 *
 * @package ksenonspb
 */
?>
<section class="portfolio-archive">
	<div class="portfolio-archive__container _container">
		<h1 class="portfolio-archive__title title-lg"><?php echo esc_html( post_type_archive_title( '', false ) ?: __( 'Портфолио', 'ksenonspb' ) ); ?></h1>
		<div class="portfolio-archive__grid">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/blocks/portfolio-card', null, array( 'post' => get_post() ) );
				endwhile;
			endif;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	</div>
</section>
<?php get_template_part( 'template-parts/blocks/cta-form', null, array( 'variant' => 'same_result' ) ); ?>
