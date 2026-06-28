<?php
/**
 * Services archive page
 *
 * @package ksenonspb
 */

$query = ksenon_query_services();
?>
<section class="services-archive">
	<div class="services-archive__container _container">
		<h1 class="services-archive__title title-lg"><?php echo esc_html( get_the_title() ?: __( 'Наши услуги', 'ksenonspb' ) ); ?></h1>
		<div class="services-archive__grid">
			<?php
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) :
					$query->the_post();
					get_template_part( 'template-parts/blocks/service-card', null, array( 'post' => get_post() ) );
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
	</div>
</section>
<?php get_template_part( 'template-parts/blocks/cta-form', null, array( 'variant' => 'service_not_found' ) ); ?>
