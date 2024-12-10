<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     $attributes (array): The block attributes.
 *     $content (string): The block default content.
 *     $block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

$unique_id = wp_unique_id();

$page_query_var     = "filterable-post-list-page-{$unique_id}";
$category_query_var = "filterable-post-list-category-{$unique_id}";
$tags_query_var     = "filterable-post-list-tags-{$unique_id}";

$current_page = isset( $_GET[ $page_query_var ] ) ? (int) $_GET[ $page_query_var ] : 1;

$args = [
	'post_type'      => 'post',
	'posts_per_page' => 5,
	'orderby'        => 'date',
	'order'          => 'DESC',
	'paged'          => $current_page,
	'tax_query'      => [],
];

if ( isset( $_GET[ $category_query_var ] ) && ! empty( $_GET[ $category_query_var ] ) ) {
	$args['tax_query'][] = [
		'taxonomy' => 'category',
		'terms'    => $_GET[ $category_query_var ],
	];
}

if ( isset( $_GET[ $tags_query_var ] ) && ! empty( $_GET[ $tags_query_var ] ) ) {
	$args['tax_query'][] = [
		'taxonomy' => 'post_tag',
		'terms'    => explode( ',', $_GET[ $tags_query_var ] ),
		'operator' => 'IN',
	];
}

$query = new WP_Query( $args );

$tags = get_tags(
	[
		'hide_empty' => 0,
	]
);

$categories = get_categories();

$selected_category = isset( $_GET[ $category_query_var ] ) ? (int) $_GET[ $category_query_var ] : 0;
$selected_tags     = isset( $_GET[ $tags_query_var ] ) ? array_map( 'absint', explode( ',', $_GET[ $tags_query_var ] ) ) : [];

?>

<div
	<?php echo get_block_wrapper_attributes(); ?>
	data-wp-interactive="create-block"
	<?php
	echo wp_interactivity_data_wp_context(
		[
			'selectedCategory' => $selected_category,
			'selectedTags'     => $selected_tags,
			'categoryQueryVar' => $category_query_var,
			'tagsQueryVar'     => $tags_query_var,
			'pageQueryVar'     => $page_query_var,
		]
	);
	?>
>
	<div class="wp-block-create-block-filterable-post-list-filters">
		<div class="wp-block-create-block-filterable-post-list-filters__category">
			<label><?php esc_html_e( 'Category:', 'filterable-post-list' ); ?></label>
			<select data-wp-on--change="actions.updateCategory">
				<option value="0"><?php esc_html_e( '- All -', 'filterable-post-list' ); ?></option>
				<?php foreach ( $categories as $cat ) { ?>
				<option value="<?php echo $cat->term_id; ?>" <?php selected( $cat->term_id, $selected_category ); ?>><?php echo $cat->name; ?></option>
				<?php } ?>
			</select>
		</div>
		
		<div class="wp-block-create-block-filterable-post-list-filters__tags">
			<label><?php esc_html_e( 'Tags:', 'filterable-post-list' ); ?></label>
			<div class="wp-block-create-block-filterable-post-list-filters__tags-container">
				<?php foreach ( $tags as $tag ) { ?>
				<label class="wp-block-create-block-filterable-post-list-filters__tag-item">
					<input type="checkbox" data-wp-on--change="actions.updateTags" name="tag_id" value="<?php echo $tag->term_id; ?>" <?php checked( in_array( $tag->term_id, $selected_tags ) ); ?> />
					<?php echo $tag->name; ?>
				</label>
				<?php } ?>
			</div>
		</div>
	</div>

	<div
	data-wp-interactive="create-block"
	class="wp-block-create-block-filterable-post-list-posts"
	<?php echo wp_interactivity_data_wp_context( [ 'pageQueryVar' => $page_query_var ] ); ?> 
	data-wp-router-region="filterable-post-list-<?php echo $unique_id; ?>"
	>
		<p><?php printf( __( 'Total results: %s', 'filterable-post-list' ), '<i>' . $query->found_posts . '</i>' ); ?></p>
		<div class="wp-block-create-block-filterable-post-list-posts__container">
			<?php
			if ( $query->have_posts() ) :
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
			<div class="wp-block-create-block-filterable-post-list-posts__item">
				<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
			</div>
					<?php
				endwhile;
			else :
				?>
			<div class="wp-block-create-block-filterable-post-list-posts__item">
				<p><?php esc_html_e( 'No results found.', 'filterable-post-list' ); ?></p>
			</div>
				<?php
			endif;
			wp_reset_postdata();
			?>
		</div>

		<?php if ( $query->have_posts() ) : ?>
		<div class="wp-block-create-block-filterable-post-list-posts__navigation">
			<div class="wp-block-create-block-filterable-post-list-posts__navigation--previous">
			<?php if ( $current_page > 1 ) : ?>
				<a href="<?php echo add_query_arg( [ $page_query_var => $current_page - 1 ] ); ?>" data-wp-on--click="actions.previous"><?php esc_html_e( '&laquo; Previous', 'filterable-post-list' ); ?></a>
			<?php endif; ?>
			</div>

			<div class="wp-block-create-block-filterable-post-list-posts__navigation--next">
			<?php if ( $current_page < $query->max_num_pages ) : ?>
				<a href="<?php echo add_query_arg( [ $page_query_var => $current_page + 1 ] ); ?>" data-wp-on--click="actions.next"><?php esc_html_e( 'Next &raquo;', 'filterable-post-list' ); ?></a>
			<?php endif; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
