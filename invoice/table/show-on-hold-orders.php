<?php
/**
 * Mostra anche gli ordini in stato "in attesa" (on-hold) nella tabella fatture
 *
 * title: Mostra gli ordini in stato "in attesa"
 * layout: snippet
 * collection: invoice
 * category: invoice
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */

add_filter( 'wc_el_inv-xml_list_query_args', function( $args ){
	$args['status'][] = 'on-hold';

	return $args;
} );
