<?php
/**
 * Elimino il watermark dalla fattura (pdf)
 *
 * title: Elimino il watermark dalla fattura (pdf)
 * layout: snippet
 * collection: invoice/pdf
 * category: invoice, pdf, watermark
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */

add_filter('wc_el_inv-disable_watermark', function() {
    return 'yes';
});