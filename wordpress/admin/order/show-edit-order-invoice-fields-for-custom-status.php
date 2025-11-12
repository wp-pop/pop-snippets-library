<?php
/**
 * Abilita la visualizzazione e modifica dei dati fattura dentro l'ordine sulla base di un stato ordine custom
 *
 * title: Abilita la visualizzazione e modifica dei dati fattura
 * layout: snippet
 * collection: admin/order
 * category: admin, order, fields
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */

add_filter('wc_el_inv-edit_order_disabled_invoice_fields', function ($view, $order) {
    if('your_status' === $order->get_status()) {
        return false;
    }

    return true;
}, 10, 2);