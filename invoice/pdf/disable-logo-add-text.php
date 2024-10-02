<?php
/**
 * Rimuove il logo dalla fattura PDF di cortesia per aggiungere un semplice testo
 *
 * title: Rimuove il logo dalla fattura PDF e aggiunge testo
 * layout: snippet
 * collection: invoice/pdf
 * category: invoice, pdf, logo
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */


add_filter('wc_el_inv-invoice_pdf_logo_url', '__return_false');

add_filter('wc_el_inv-invoice_pdf_logo_text', function ($text){
    return 'Logo Name';
});