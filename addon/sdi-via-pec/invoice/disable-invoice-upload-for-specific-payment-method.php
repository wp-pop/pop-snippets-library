<?php
/**
 * Disabilita l'invio della fattura allo sdi sulla base di uno specifico metodo di pagamento (MP01 = contanti)
 *
 * title: Disabilita l'invio della fattura allo sdi
 * layout: snippet
 * collection: addon/sdi-via-pec/invoice
 * category: invoice, sdi-via-pec
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */

add_filter('woopopsdi-upload-invoice_exclude_payment_method_code', function ($bool, $payCode, $payMethod){
    if('MP01' === $payCode) {
        $bool = true;
    }

    return $bool;
}, 10, 3);
