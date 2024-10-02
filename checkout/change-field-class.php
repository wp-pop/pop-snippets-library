<?php
/**
 * Modifica la classe css nei campi del checkout ad esempio per visualizzarli su due colonne
 *
 * title: Modifica la classe css nei del checkout
 * layout: snippet
 * collection: checkout
 * category: checkout, fields
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */

add_filter('wc_el_inv-billing_fields', function ($fields) {
    $fields['billing_choice_type']['class'] = array(
        'wc_el_inv-type-field',
        'form-row-first',
    );

    $fields['billing_invoice_type']['class'] = array(
        'wc_el_inv-type-field',
        'form-row-last',
    );

    return $fields;
});