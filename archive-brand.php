<?php
/**
 * Brand archive
 *
 * @package ksenonspb
 */

get_header();
?>
<section class="brands-archive">
	<div class="brands-archive__container _container">
		<h1 class="brands-archive__title title-lg"><?php post_type_archive_title(); ?></h1>
		<div class="brands-archive__grid">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/blocks/brand-card', null, array( 'post' => get_post() ) );
				endwhile;
			endif;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	</div>
</section>
<?php
get_footer();
