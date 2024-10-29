<?php
/**
 * Imposta la fattura come inviata quando viene creata nella dir uploads
 *
 * title: Imposta la fattura come inviata
 * layout: snippet
 * collection: invoice/table
 * category: invoice
 *
 * You can add this recipe to your site by creating a custom plugin
 * or using the Code Snippets plugin available for free in the WordPress repository.
 * Read this companion article for step-by-step directions on either method.
 * https://wp-pop.com/create-a-plugin-for-pop-customizations/
 */

add_filter('wc_el_inv-invoice_create_xml_by_order_status_file', function (
    $file,
    $uploadDir,
    $docTypeCode,
    $docType,
    $orderID,
    $fileName
) {
    // Set invoice sent
    $order = wc_get_order(intval($orderID));
    $order->update_meta_data('_invoice_sent', 'sent');
    // Save
    $order->save();

    return $file;
}, 10, 6);