<?php
/*
Plugin Name: Accordion Tables
PluginURI: http://www.renesejling.dk
Description: Display tables as Accordions using shortcode [accordion_tables]
Author: René Sejling
Version: 1.0
*/

class AccordionTables {

	public $version = '1.0.0';

	public function __construct() {
		add_action('wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_shortcode('accordion_tables', [ $this, 'accordion_tables_shortcode' ] );
		add_action('init', [ $this, 'register_post_type' ] );
		add_action( 'add_meta_boxes', [ $this, 'accordion_labels_meta_box' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		add_action( 'save_post', [ $this, 'save_accordion_labels_meta_box_data' ] );
	}

	public function accordion_labels_meta_box() {
		add_meta_box(
			'accordion-labels',
			'Accordion Labels',
			[ $this, 'accordion_labels_meta_box_callback' ],
			'accordion_tables',
			'side'
		);
	}

	public function save_accordion_labels_meta_box_data( $post_id ) {
		if( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
		if( ! wp_verify_nonce( $_POST['accordion_tables_nonce'], 'accordion_tables_nonce' ) ) return $post_id;
		if( empty( $_POST['post_type'] ) || $_POST['post_type'] !== 'accordion_tables' ) return $post_id;
		if( defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
		$properties = ['','','',''];
		for( $i = 0; $i < 5; $i++ ) {
			if( !empty( $_POST['properties'][$i]['type'] ) && $_POST['properties'][$i]['type'] == 'custom_value' ) {
				$properties[$i] = $_POST['properties'][$i]['text'];
			} else if( !empty( $_POST['properties'][$i]['type'] ) ) {
				$properties[$i] = $_POST['properties'][$i]['type'];
			}
		}
		$order = !empty( $_POST['list_order'] ) ? $_POST['list_order'] : 0;

		update_post_meta( $post_id, '_accordion_labels', $properties );
		update_post_meta( $post_id, '_list_order', $order );
	}

	public function accordion_labels_meta_box_callback( $post ) {
		wp_enqueue_style( 'admin_accordion_tables' );
		ob_start();
		wp_nonce_field( 'accordion_tables_nonce', 'accordion_tables_nonce' );
		$properties = get_post_meta( $post->ID, '_accordion_labels', true );
		$order = get_post_meta( $post->ID, '_list_order', true );
?>
<div id="accordion_tables_meta_box">

	<label>
		List Order
		<input type="number" name="list_order" min="0" value="<?php echo !empty( $order ) ? $order : '0'; ?>">
	</label>

	<h3>Dynamics 365 Business Central Essen al Basic</h3>
	<label>
		<input type="radio" name="properties[0][type]" value="" <?php echo empty( $properties[0] ) ? 'checked' : ''; ?>/> Empty
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[0][type]" value="check" <?php echo ( !empty( $properties[0] ) && $properties[0] == 'check' ) ? 'checked' : ''; ?>/>
		<img class="accordion_table_icon" src="<?php echo plugins_url( '/accordion-tables/images/check-green.svg' ); ?>"/>
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[0][type]" value="phone" <?php echo ( !empty( $properties[0] ) && $properties[0] == 'phone' ) ? 'checked' : ''; ?>/>
		<img class="accordion_table_icon" src="<?php echo plugins_url( '/accordion-tables/images/phone-green.svg' ); ?>"/>
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[0]" value="custom_value" <?php echo ( !empty( $properties[0] ) && $properties[0] !== 'phone' && $properties[0] !== 'check' ) ? 'checked' : ''; ?>/>
		<input class="custom_value" type="text" name="properties[0][text]" placeholder="Custom Value" value="<?php echo ( !empty( $properties[0] ) && $properties[0] !== 'phone' && $properties[0] !== 'check' ) ? $properties[0] : ''; ?>"/>
	</label>

	<h3>Dynamics 365 Business Central Essen al</h3>
	<label>
		<input type="radio" name="properties[1][type]" value="" <?php echo empty( $properties[1] ) ? 'checked' : ''; ?>/> Empty
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[1][type]" value="check" <?php echo ( !empty( $properties[1] ) && $properties[1] == 'check' ) ? 'checked' : ''; ?>/>
		<img class="accordion_table_icon" src="<?php echo plugins_url( '/accordion-tables/images/check-green.svg' ); ?>"/>
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[1][type]" value="phone" <?php echo ( !empty( $properties[1] ) && $properties[1] == 'phone' ) ? 'checked' : ''; ?>/>
		<img class="accordion_table_icon" src="<?php echo plugins_url( '/accordion-tables/images/phone-green.svg' ); ?>"/>
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[1][type]" value="custom_value" <?php echo ( !empty( $properties[1] ) && $properties[1] !== 'phone' && $properties[1] !== 'check' ) ? 'checked' : ''; ?>/>
		<input class="custom_value" type="text" name="properties[1][text]" placeholder="Custom Value" value="<?php echo ( !empty( $properties[1] ) && $properties[1] !== 'phone' && $properties[1] !== 'check' ) ? $properties[1] : ''; ?>"/>
	</label>

	<h3>Dynamics 365 Byg & Anlæg</h3>
	<small>Op  l 10 administra ve brugere</small><br/>
	<label>
		<input type="radio" name="properties[2][type]" value="" <?php echo empty( $properties[2] ) ? 'checked' : ''; ?>/> Empty
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[2][type]" value="check" <?php echo ( !empty( $properties[2] ) && $properties[2] == 'check' ) ? 'checked' : ''; ?>/>
		<img class="accordion_table_icon" src="<?php echo plugins_url( '/accordion-tables/images/check-green.svg' ); ?>"/>
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[2][type]" value="phone" <?php echo ( !empty( $properties[2] ) && $properties[2] == 'phone' ) ? 'checked' : ''; ?>/>
		<img class="accordion_table_icon" src="<?php echo plugins_url( '/accordion-tables/images/phone-green.svg' ); ?>"/>
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[2][type]" value="custom_value" <?php echo ( !empty( $properties[2] ) && $properties[2] !== 'phone' && $properties[2] !== 'check' ) ? 'checked' : ''; ?>/>
		<input class="custom_value" type="text" name="properties[2][text]" placeholder="Custom Value" value="<?php echo ( !empty( $properties[2] ) && $properties[2] !== 'phone' && $properties[2] !== 'check' ) ? $properties[2] : ''?>"/>
	</label>

	<h3>Dynamics NAV Byg & Anlæg</h3>
	<small>+ 10 administra ve brugere</small><br/>
	<label>
		<input type="radio" name="properties[3][type]" value="" <?php echo empty( $properties[3] ) ? 'checked' : ''; ?>/> Empty
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[3][type]" value="check" <?php echo ( !empty( $properties[3] ) && $properties[3] == 'check' ) ? 'checked' : ''; ?>/>
		<img class="accordion_table_icon" src="<?php echo plugins_url( '/accordion-tables/images/check-green.svg' ); ?>"/>
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[3][type]" value="phone" <?php echo ( !empty( $properties[3] ) && $properties[3] == 'phone' ) ? 'checked' : ''; ?>/>
		<img class="accordion_table_icon" src="<?php echo plugins_url( '/accordion-tables/images/phone-green.svg' ); ?>"/>
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[3][type]" value="custom_value" <?php echo ( !empty( $properties[3] ) && $properties[3] !== 'phone' && $properties[3] !== 'check' ) ? 'checked' : ''; ?>/>
		<input class="custom_value" type="text" name="properties[3][text]" placeholder="Custom Value" value="<?php echo ( !empty( $properties[3] ) && $properties[3] !== 'phone' && $properties[3] !== 'check' ) ? $properties[3] : ''; ?>"/>
	</label>

</div>
<?php
		echo ob_get_clean();
	}

	public function enqueue_scripts() {
		wp_register_script(
			'accordion_tables',
			plugins_url('/accordion-tables/js/script.js'),
			[ 'jquery-ui-accordion', 'jquery' ],
			$this->version
		);
		wp_register_style(
			'accordion_tables',
			plugins_url('/accordion-tables/css/style.css'),
			[],
			$this->version
		);
	}

	public function register_post_type() {
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
			'menu_icon'          => 'dashicons-feedback',
			'supports'           => array( 'title', 'editor' ),
			'register_meta_box_cb' => [ $this, 'accordion_labels_meta_box' ]
		);

		register_post_type( 'accordion_tables', $args );

	}

	public function accordion_tables_shortcode() {
		wp_enqueue_script('accordion_tables');
		wp_enqueue_style('accordion_tables');
		$accordion_items = new WP_Query([
			'post_type' => 'accordion_tables'
		]);
		if( !$accordion_items->have_posts() ) return;
		ob_start();
?>
<div id="accordion_tables">
	<div class="header">
		<div class="one-fifth">
			<p>&nbsp;</p>
		</div>
		<div class="one-fifth">
			<h4>Dynamics 365 Business Central Essen al Basic</h4>
		</div>
		<div class="one-fifth">
			<h4>Dynamics 365 Business Central Essen al</h4>
		</div>
		<div class="one-fifth">
			<h4>Dynamics 365 Byg & Anlæg</h4>
			<small>Op  l 10 administra ve brugere</small>
		</div>
		<div class="one-fifth">
			<h4>Dynamics 365 Byg & Anlæg</h4>
			<small>+ 10 administra ve brugere</small>
		</div>
	</div>
	<div class="accordion_container">
<?php while( $accordion_items->have_posts() ): $accordion_items->the_post(); ?>
<?php $property = get_post_meta( get_the_ID(), '_accordion_labels', true ); ?>
		<h3>
			<div>
				<div class="one-fifth"><?php the_title(); ?></div>
				<div class="one-fifth">
					<?php if( empty( $property[0] ) ) : ?>
						<p>&nbsp;</p>
					<?php elseif( $property[0] == 'check' ) : ?>
						<img class="accordion-icon" src="<?php echo plugins_url('/accordion-tables/images/check-green.svg'); ?>"/>
					<?php elseif( $property[0] == 'phone' ) : ?>
						<img class="accordion-icon" src="<?php echo plugins_url('/accordion-tables/images/phone-green.svg'); ?>"/>
					<?php else: ?>
						<p><?php echo $property[0]; ?></p>
					<?php endif; ?>
				</div>
				<div class="one-fifth">
					<?php if( empty( $property[1] ) ) : ?>
						<p>&nbsp;</p>
					<?php elseif( $property[1] == 'check' ) : ?>
						<img class="accordion-icon" src="<?php echo plugins_url('/accordion-tables/images/check-green.svg'); ?>"/>
					<?php elseif( $property[1] == 'phone' ) : ?>
						<img class="accordion-icon" src="<?php echo plugins_url('/accordion-tables/images/phone-green.svg'); ?>"/>
					<?php else: ?>
						<p><?php echo $property[1]; ?></p>
					<?php endif; ?>
				</div>
				<div class="one-fifth">
					<?php if( empty( $property[2] ) ) : ?>
						<p>&nbsp;</p>
					<?php elseif( $property[2] == 'check' ) : ?>
						<img class="accordion-icon" src="<?php echo plugins_url('/accordion-tables/images/check-green.svg'); ?>"/>
					<?php elseif( $property[2] == 'phone' ) : ?>
						<img class="accordion-icon" src="<?php echo plugins_url('/accordion-tables/images/phone-green.svg'); ?>"/>
					<?php else: ?>
						<p><?php echo $property[2]; ?></p>
					<?php endif; ?>
				</div>
				<div class="one-fifth">
					<?php if( empty( $property[3] ) ) : ?>
						<p>&nbsp;</p>
					<?php elseif( $property[3] == 'check' ) : ?>
						<img class="accordion-icon" src="<?php echo plugins_url('/accordion-tables/images/check-green.svg'); ?>"/>
					<?php elseif( $property[3] == 'phone' ) : ?>
						<img class="accordion-icon" src="<?php echo plugins_url('/accordion-tables/images/phone-green.svg'); ?>"/>
					<?php else: ?>
						<p><?php echo $property[3]; ?></p>
					<?php endif; ?>
				</div>
			</div>
		</h3>
		<div>
			<div class="one-fifth"><p>&nbsp;</p></div>
			<div class="four-fifth">
				<?php the_content(); ?>
			</div>
		</div>
<?php endwhile; ?>
	</div>
</div>
<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	public function admin_enqueue_scripts() {
		wp_register_style(
			'admin_accordion_tables', 
			plugins_url( '/accordion-tables/css/admin.css' ),
			false,
			$this->version
		);

	}

}

$accordionTables = new AccordionTables();
