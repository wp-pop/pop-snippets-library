<?php
/**
 * Rimuove il bottone "segna come inviato" nella tabella e dentro l'ordine
 *
 * title: Rimuove il bottone "segna come inviato"
 * layout: snippet
 * collection: invoice
 * category: invoice
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */

add_filter('wc_el_inv-actions_mark_trigger_invoice', '__return_false');