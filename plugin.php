<?php
/*
Plugin Name: Accordion Tables
PluginURI: http://www.renesejling.dk
Description: Display tables as Accordions using shortcode [accordion_tables]
Author: René Sejling
Version: 1.0
*/

class AccordionTables {

	public function __construct() {
		add_action('wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_shortcode('accordion_tables', [ $this, 'accordion_tables' ] );
		add_action('init', [ $this, 'init' ] );
	}

	public function init() {
		self::register_post_type();
	}

	public function enqueue_scripts() {
		wp_register_script(
			'accordion_tables',
			plugins_url('/accordion-tables/js/script.js'),
			[ 'jquery-ui-accordion', 'jquery' ]
		);
	}

	private function register_post_type() {
		$labels = array(
			'name'               => _x( 'Accordion Tables', 'post type general name', 'accordion_tables' ),
			'singular_name'      => _x( 'Accordion Table', 'post type singular name', 'accordion_tables' ),
			'menu_name'          => _x( 'Accordion Tables', 'admin menu', 'accordion_tables' ),
			'name_admin_bar'     => _x( 'Accordion Table', 'add new on admin bar', 'accordion_tables' ),
			'add_new'            => _x( 'Add New', 'Accordion Table', 'accordion_tables' ),
			'add_new_item'       => __( 'Add New Accordion Table', 'accordion_tables' ),
			'new_item'           => __( 'New Accordion Table', 'accordion_tables' ),
			'edit_item'          => __( 'Edit Accordion Table', 'accordion_tables' ),
			'view_item'          => __( 'View Accordion Table', 'accordion_tables' ),
			'all_items'          => __( 'All Accordion Tables', 'accordion_tables' ),
			'search_items'       => __( 'Search Accordion Tables', 'accordion_tables' ),
			'parent_item_colon'  => __( 'Parent Accordion Tables:', 'accordion_tables' ),
			'not_found'          => __( 'No Accordion Tables found.', 'accordion_tables' ),
			'not_found_in_trash' => __( 'No Accordion Tables found in Trash.', 'accordion_tables' )
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'accordion_tables' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'accordion_table' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => true,
			'supports'           => array( 'title', 'editor' )
		);

		register_post_type( 'accordion_tables', $args );

	}

	public function accordion_tables() {
		wp_enqueue_script('accordion_tables');
		$accordion_items = new WP_Query([
			'post_type' => 'accordion_tables'
		]);
		if( !$accordion_items->have_posts() ) return;
		ob_start();
?>
<div id="accordion_tables">
<?php while( $accordion_items->have_posts() ): $accordion_items->the_post(); ?>
	<h3><?php the_title(); ?></h3>
	<div>
		<?php the_content(); ?>
	</div>
<?php endwhile; ?>
</div>
<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

}

$accordionTables = new AccordionTables();
