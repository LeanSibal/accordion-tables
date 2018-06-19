<?php
/*
Plugin Name: Accordion Tables
PluginURI: http://www.renesejling.dk
Description: Display tables as Accordions using shortcode [accordion_tables]
Author: René Sejling
Version: 1.0.1
*/

class AccordionTables {

	public $version = '1.0.0';

	public function __construct() {
		add_action('wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_shortcode('accordion_tables', [ $this, 'accordion_tables_shortcode' ] );
		add_action( 'init', [ $this, 'register_taxonomy' ] );
		add_action( 'row_headers_add_form_fields', [ $this, 'row_headers_add_form_fields' ] );
		add_action( 'row_headers_edit_form_fields', [ $this, 'row_headers_add_form_fields' ] );
		add_action( 'create_row_headers', [ $this, 'save_row_headers' ] );
		add_action( 'edit_row_headers', [ $this, 'save_row_headers' ] );
		add_action( 'init', [ $this, 'register_post_type' ] );
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
		$row_headers = get_terms([
			'taxonomy' => 'row_headers',
			'hide_empty' => false,
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'meta_key' => 'tax-order'
		]);
		$properties = [];
		foreach( $row_headers as $row_header ) {
			if( !empty( $_POST['properties'][$row_header->slug]['type'] ) && $_POST['properties'][$row_header->slug]['type'] == 'custom_value' ) {
				$properties[$row_header->slug] = $_POST['properties'][$row_header->slug]['text'];
			} else if( !empty( $_POST['properties'][$row_header->slug]['type'] ) ) {
				$properties[$row_header->slug] = $_POST['properties'][$row_header->slug]['type'];
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
		$row_headers = get_terms([
			'taxonomy' => 'row_headers',
			'hide_empty' => false,
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'meta_key' => 'tax-order'
		]);
?>
<div id="accordion_tables_meta_box">

	<label>
		List Order
		<input type="number" name="list_order" min="0" value="<?php echo !empty( $order ) ? $order : '0'; ?>">
	</label>

<?php foreach( $row_headers as $row_header ): ?>

	<h3><?php echo $row_header->name; ?></h3>
	<small><?php echo $row_header->description; ?></small><br/>
	<label>
		<input type="radio" name="properties[<?php echo $row_header->slug; ?>][type]" value="" <?php echo empty( $properties[$row_header->slug] ) ? 'checked' : ''; ?>/> Empty
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[<?php echo $row_header->slug; ?>][type]" value="check" <?php echo ( !empty( $properties[$row_header->slug] ) && $properties[$row_header->slug] == 'check' ) ? 'checked' : ''; ?>/>
		<img class="accordion_table_icon" src="<?php echo plugins_url( '/accordion-tables/images/check-green.svg' ); ?>"/>
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[<?php echo $row_header->slug; ?>][type]" value="phone" <?php echo ( !empty( $properties[$row_header->slug] ) && $properties[$row_header->slug] == 'phone' ) ? 'checked' : ''; ?>/>
		<img class="accordion_table_icon" src="<?php echo plugins_url( '/accordion-tables/images/phone-green.svg' ); ?>"/>
	</label>
	<br/>
	<label>
		<input type="radio" name="properties[<?php echo $row_header->slug; ?>][type]" value="custom_value" <?php echo ( !empty( $properties[$row_header->slug] ) && $properties[$row_header->slug] !== 'phone' && $properties[$row_header->slug] !== 'check' ) ? 'checked' : ''; ?>/>
		<input class="custom_value" type="text" name="properties[<?php echo $row_header->slug; ?>][text]" placeholder="Custom Value" value="<?php echo ( !empty( $properties[$row_header->slug] ) && $properties[$row_header->slug] !== 'phone' && $properties[$row_header->slug] !== 'check' ) ? $properties[$row_header->slug] : ''; ?>"/>
	</label>
<?php endforeach; ?>

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
		wp_register_style(
			'fontawesome',
			'https://use.fontawesome.com/releases/v5.0.13/css/all.css',
			[],
			'5.0.13'
		);

	}

	public function register_taxonomy() {
		$labels = array(
			'name'              => _x( 'Row Headers', 'taxonomy general name', 'accordion_tables' ),
			'singular_name'     => _x( 'Row Header', 'taxonomy singular name', 'accordion_tables' ),
			'search_items'      => __( 'Search Row Headers', 'accordion_tables' ),
			'all_items'         => __( 'All Row Headers', 'accordion_tables' ),
			'parent_item'       => __( 'Parent Row Header', 'accordion_tables' ),
			'parent_item_colon' => __( 'Parent Row Header:', 'accordion_tables' ),
			'edit_item'         => __( 'Edit Row Header', 'accordion_tables' ),
			'update_item'       => __( 'Update Row Header', 'accordion_tables' ),
			'add_new_item'      => __( 'Add New Row Header', 'accordion_tables' ),
			'new_item_name'     => __( 'New Row Header Name', 'accordion_tables' ),
			'menu_name'         => __( 'Row Header', 'accordion_tables' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'sort'              => true,
			'rewrite'           => array( 'slug' => 'headers' ),
		);

		register_taxonomy( 'row_headers', array( 'accordion_tables' ), $args );
	}
	public function row_headers_add_form_fields( $term ) {
		$tax_order = "0";
		if( !empty( $term->term_id ) ) {
			$tax_order = get_term_meta( $term->term_id, 'tax-order', true );
		}
		?>
 
	<tr class="form-field">
		<th scope="row" valign="top">
			<label for="tax-order">List Order</label>
		</th>
		<td>
			<input name="tax-order" id="tax-order" type="number" value="<?php echo $tax_order; ?>" size="40" aria-required="true" />
			<p class="description">Determines the order in which the row header is displayed.</p>
		</td>
	</tr>
		<?php
	}

	public function save_row_headers( $term_id ) {
		if( isset( $_POST['tax-order'] ) ) {
			update_term_meta( $term_id, 'tax-order', $_POST['tax-order'] );
		}
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
			'taxonomies'         => array( 'row_headers' ),
			'register_meta_box_cb' => [ $this, 'accordion_labels_meta_box' ]
		);

		register_post_type( 'accordion_tables', $args );
		add_submenu_page(
			'edit.php?post_type=accordion_tables',
			__('Table Shortcodes','accordion_tables'),
			__('Table Shortcodes','accordion_tables'),
			'manage_options',
			'table_shortcodes_page',
			[ $this, 'table_shortcodes_page' ]
		);
	}

	public function table_shortcodes_page(){
		wp_enqueue_style('admin_accordion_tables');
		wp_enqueue_script( 'admin_accordion_tables' );
		wp_enqueue_style( 'lou_multi_select' );
		$accordion_items = new WP_Query([
			'post_type' => 'accordion_tables',
			'order' => 'ASC',
			'orderby' => 'meta_value_num',
			'posts_per_page' => -1,
			'meta_key' => '_list_order'
		]);
		$row_headers = get_terms([
			'taxonomy' => 'row_headers',
			'hide_empty' => false,
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'meta_key' => 'tax-order'
		]);
		$accordion_tables = get_option( 'accordion_tables' );
		$current = [
			'shortcode_name' => '',
			'post_ids' => [],
			'category_ids' => []
		];
		ob_start();
		if( !empty( $_POST ) && !empty( $_POST['admin_accordion_tables_save'] ) && wp_verify_nonce( $_POST['admin_accordion_tables_save'], 'admin_accordion_tables_save' ) ) {
			// save function
			$post_ids = ( !empty( $_POST['post_ids'] ) ? $_POST['post_ids'] : [] );
			$category_ids = ( !empty( $_POST['category_ids'] ) ? $_POST['category_ids'] : [] );
			$current = [
				'post_ids' => ( !empty( $_POST['post_ids'] ) ? $_POST['post_ids'] : [] ),
				'category_ids' => ( !empty( $_POST['category_ids'] ) ? $_POST['category_ids'] : [] )
			];
			if( !empty( $_POST['shortcode_name'] ) ) {
				$accordion_tables[ $_POST['shortcode_name'] ] = $current;
				update_option('accordion_tables', $accordion_tables );
				$current['shortcode_name'] = $_POST['shortcode_name'];
			} else {
				$current['shortcode_name'] = '';
			}
		} else if ( !empty( $_POST ) && !empty( $_POST['admin_accordion_tables_edit'] ) && wp_verify_nonce( $_POST['admin_accordion_tables_edit'], 'admin_accordion_tables_edit' ) && !empty( $accordion_tables[ $_POST['shortcode_name'] ] ) ) {
			$current = $accordion_tables[ $_POST['shortcode_name'] ];
			$current['shortcode_name'] = $_POST['shortcode_name'];
		} else if ( !empty( $_POST ) && !empty( $_POST['admin_accordion_tables_delete'] ) && wp_verify_nonce( $_POST['admin_accordion_tables_delete'], 'admin_accordion_tables_delete' ) && !empty( $accordion_tables[ $_POST['shortcode_name'] ] ) ) {
			unset( $accordion_tables[ $_POST['shortcode_name'] ] );
			update_option( 'accordion_tables', $accordion_tables );
		}
?>
<div class="wrap">
	<form method="post">
		<?php wp_nonce_field( 'admin_accordion_tables_save', 'admin_accordion_tables_save' ); ?>
		<h1>Table Shortcodes Generator</h1>
		<hr class="wp-header-end">
		<h2>Create New Shortcode</h2>
		<table class="form-table center-align-table">
			<thead>
				<tr>
					<th colspan="2">
					<p>[accordion_tables id="<?php echo $current['shortcode_name']; ?>"]</p>
					</th>
				</tr>
				<tr>
					<th>
						<label for="shortcode_name">
							Shortcode Name
							<input name="shortcode_name" type="text" class="regular-text ltr" value="<?php echo $current['shortcode_name']; ?>">
						</label>
					</th>
					<th>
						<?php submit_button(); ?>
					</th>
				</tr>
				<tr>
					<th>
						Accordion Items
					</th>
					<th>
						Headers
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<select name="post_ids[]" class="searchable" multiple="multiple">
							<?php while( $accordion_items->have_posts() ): $accordion_items->the_post(); ?>
								<option value="<?php echo get_the_ID(); ?>" <?php echo ( !empty( $current['post_ids'] ) && in_array( get_the_ID(), $current['post_ids'] ) ) ? 'selected' : ''; ?>><?php echo get_the_title(); ?></option>
							<?php endwhile; ?>
						</select>
					</td>
					<td>
						<select name="category_ids[]" class="searchable" multiple="multiple">
							<?php foreach( $row_headers as $row_header ): ?>
								<option value="<?php echo $row_header->term_id; ?>" <?php echo ( !empty( $current['category_ids'] ) && in_array( $row_header->term_id, $current['category_ids'] ) ) ? 'selected' : ''; ?>><?php echo $row_header->name; ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<table class="wp-list-table widefat fixed striped posts">
		<tbody id="the-list">
			<?php foreach( $accordion_tables as $accordion_table_name => $fields ): ?>
				<tr>
					<td class="title column-title has-row-actions column-primary">
						<?php echo $accordion_table_name; ?>
					</td>
					<td style="width:35px">
						<?php if( $accordion_table_name != $current['shortcode_name'] ): ?>
						<form method="post">
							<input type="hidden" name="shortcode_name" value="<?php echo $accordion_table_name; ?>"/>
							<?php wp_nonce_field( 'admin_accordion_tables_edit', 'admin_accordion_tables_edit' ); ?>
							<button type="submit">
								<span class="dashicons-before dashicons-edit"></span>
							</button>
						</form>
						<?php endif; ?>
					</td>
					<td style="width:35px">
						<form method="post">
							<input type="hidden" name="shortcode_name" value="<?php echo $accordion_table_name; ?>"/>
							<?php wp_nonce_field( 'admin_accordion_tables_delete', 'admin_accordion_tables_delete' ); ?>
							<button type="submit">
								<span class="dashicons-before dashicons-trash"></span>
							</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php
		echo ob_get_clean();
	}

	private function floorp($val, $precision) {
		    $mult = pow(10, $precision);
			return floor($val * $mult) / $mult;
	}

	public function accordion_tables_shortcode() {
		wp_enqueue_script('accordion_tables');
		wp_enqueue_style('accordion_tables');
		wp_enqueue_style('fontawesome');
		$accordion_items = new WP_Query([
			'post_type' => 'accordion_tables',
			'order' => 'ASC',
			'orderby' => 'meta_value_num',
			'posts_per_page' => -1,
			'meta_key' => '_list_order'
		]);
		$row_headers = get_terms([
			'taxonomy' => 'row_headers',
			'hide_empty' => false,
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'meta_key' => 'tax-order'
		]);
		$row_width = $this->floorp( 100 / ( count( $row_headers ) + 1 ), 2 );
		if( !$accordion_items->have_posts() ) return;
		ob_start();
?>
<div id="accordion_tables">
	<div class="header">
		<div class="one-fifth nostyle">
			<p>&nbsp;</p>
		</div>
		<div class="four-fifth flex">
		<?php foreach( $row_headers as $row_header ): ?>
			<div class="flex-grow" style="width:<?php echo $row_width; ?>%">
					<div class="spacer">
						<h4><?php echo $row_header->name; ?></h4>
						<span><?php echo $row_header->description; ?></span>
					</div>
				</div>
		<?php endforeach; ?>
		</div>
	</div>
	<div class="accordion_container">
<?php while( $accordion_items->have_posts() ): $accordion_items->the_post(); ?>
<?php $property = get_post_meta( get_the_ID(), '_accordion_labels', true ); ?>
		<h3>
				<div class="one-fifth align-right">
					<span class="accordion_title"><?php the_title(); ?></span><i class="fas fa-plus-circle"></i>
				</div>
				<div class="four-fifth flex">
				<?php foreach( $row_headers as $row_header ): ?>
					<div class="flex-grow" style="max-width:<?php echo $row_width; ?>%">
						<?php if( empty( $property[ $row_header->slug ] ) ) : ?>
							<p>&nbsp;</p>
						<?php elseif( $property[ $row_header->slug ] == 'check' ) : ?>
							<img class="accordion-icon" src="<?php echo plugins_url('/accordion-tables/images/check-green.svg'); ?>"/>
						<?php elseif( $property[ $row_header->slug ] == 'phone' ) : ?>
							<img class="accordion-icon" src="<?php echo plugins_url('/accordion-tables/images/phone-green.svg'); ?>"/>
						<?php else: ?>
							<p><?php echo $property[ $row_header->slug ]; ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
				</div>
		</h3>
		<div class="content-container">
			<div class="one-fifth"><p>&nbsp;</p></div>
			<div class="four-fifth accordion-content">
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
		wp_register_script(
			'admin_accordion_tables',
			plugins_url( '/accordion-tables/js/admin.js' ),
			[ 'jquery', 'lou_multi_select' ],
			$this->version
		);
		wp_register_style(
			'lou_multi_select',
			plugins_url( '/accordion-tables/css/multi-select.css' ),
			false,
			'0.9.12'
		);
		wp_register_script(
			'lou_multi_select',
			plugins_url( '/accordion-tables/js/jquery.multi-select.js' ),
			[ 'jquery', 'jquery.quicksearch' ],
			'0.9.12'
		);
		wp_register_script(
			'jquery.quicksearch',
			plugins_url( '/accordion-tables/js/jquery.quicksearch.js' ),
			[ 'jquery' ],
			'0.9.12'
		);

	}

}

$accordionTables = new AccordionTables();
