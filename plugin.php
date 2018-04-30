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
	}

	public function enqueue_scripts() {
		wp_register_script(
			'accordion_tables',
			plugins_url('/accordion-tables/js/script.js'),
			[ 'jquery-ui-accordion', 'jquery' ]
		);
	}

	public function accordion_tables() {
		wp_enqueue_script('accordion_tables');
		ob_start();
		return ob_get_clean();
	}

}

$accordionTables = new AccordionTables();
