<?php
/**
 * Cambia la descrizione del prodotto in fattura (pdf) aggiungendo il contenuto dell'editor
 *
 * title: Cambia la descrizione del prodotto in fattura (pdf)
 * layout: snippet
 * collection: invoice/pdf
 * category: invoice, pdf, description
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */

add_filter('wc_el_inv-product_description_pdf_invoice', function ($description, $item) {
    $post = get_post(intval($item['product_id']));
    if($post) {
        $description = $post->post_title . ' ' . $post->post_content;
    }

    return $description;
}, 10, 2);