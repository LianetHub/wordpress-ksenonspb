<?php
/**
 * Search results
 *
 * @package ksenonspb
 */

get_header();
?>
	<div class="_container">
		<h1 class="title title-lg">
			<?php
			/* translators: %s: search query */
			printf( esc_html__( 'Результаты поиска: %s', 'ksenonspb' ), esc_html( get_search_query() ) );
			?>
		</h1>
		<?php if ( have_posts() ) : ?>
			<ul>
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endwhile; ?>
			</ul>
		<?php else : ?>
			<p><?php esc_html_e( 'Ничего не найдено.', 'ksenonspb' ); ?></p>
		<?php endif; ?>
	</div>
<?php
get_footer();
