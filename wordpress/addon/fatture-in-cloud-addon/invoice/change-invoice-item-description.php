<?php
/**
 * Cambia la descrizione del prodotto in fattura (fatture in cloud) aggiungendo il contenuto dell'editor
 *
 * title: Cambia la descrizione del prodotto in fattura (fatture in cloud)
 * layout: snippet
 * collection: addon/fatture-in-cloud-addon/invoice
 * category: invoice, description, fattureincloud
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */

add_filter('wfc-invoice-items_list_description', function ($prodShortDesc, $prodDesc, $prodID) {
    $post = get_post($prodID);
    if($post) {
        $prodShortDesc = $post->post_content;
    }
    return $prodShortDesc;
}, 10, 3);
