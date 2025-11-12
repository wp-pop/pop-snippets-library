<?php
/**
 * Disabilita l'assegnazione del numero progressivo in base ad un metodo di pagamento specifico
 *
 * title: Disabilita l'assegnazione del numero progressivo
 * layout: snippet
 * collection: invoice
 * category: invoice, number
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */

add_filter('wc_el_inv-disabled_set_invoice_number', function ($bool, $order, $payment_method) {
    if('cod' === $payment_method) {
        return true;
    }

    return $bool;
}, 10, 3);