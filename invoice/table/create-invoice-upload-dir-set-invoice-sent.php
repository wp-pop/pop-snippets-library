<?php
/**
 * Set _invoice_sent on invoice created to invoice upload dir
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