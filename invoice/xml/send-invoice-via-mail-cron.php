<?php

// Funzione per gli allegati
function get_order_xml_attachments($order_id)
{
    // Imposta la directory degli allegati
    $upload_dir  = wp_upload_dir();
    $invoice_dir = $upload_dir['basedir'] . '/woopop-invoice/';

    $attachments = null;
    // Cerca tutti i file XML con l'ID dell'ordine nel nome
    if (is_dir($invoice_dir)) {
        // Apre la directory
        if ($handle = opendir($invoice_dir)) {
            // Leggi i file all'interno della directory
            while (false !== ($file = readdir($handle))) {
                // Controlla se il file contiene l'ID dell'ordine e ha estensione .xml
                if (strpos($file, (string)$order_id) !== false && pathinfo($file, PATHINFO_EXTENSION) === 'xml') {
                    // Aggiungi il percorso completo
                    $attachments = $invoice_dir . $file;
                }
            }
            closedir($handle);
        }
    }

    return $attachments;
}

// Registrazione del custom cron weekly_custom
function pop_custom_cron_schedule($schedules)
{
    $schedules['weekly_custom'] = array(
        'interval' => 7 * 24 * 60 * 60, // Intervallo di 1 settimana
        'display'  => __('Once Weekly on Custom Day'),
    );

    return $schedules;
}

add_filter('cron_schedules', 'pop_custom_cron_schedule');

// Hook init per schedulare l'invio della mail
add_action('init', 'schedule_email_cron');
function schedule_email_cron()
{
    // Scegli il giorno della settimana (1 = Lunedì, 7 = Domenica) e l'orario
    $day_of_week = 2; // 1 = Lunedì, 2 = Martedì, ecc...
    $time        = '10:00:00'; // Orario 10:00 AM

    // Calcola il timestamp del prossimo invio
    $now                 = current_time('timestamp');
    $current_day_of_week = date('N', $now); // Giorno della settimana corrente (1 = Lunedì, 7 = Domenica)

    // Calcola la differenza in giorni tra oggi e il prossimo giorno desiderato
    if ($current_day_of_week <= $day_of_week) {
        // Il giorno desiderato è questa settimana
        $days_until_next_event = $day_of_week - $current_day_of_week;
    } else {
        // Il giorno desiderato è la prossima settimana
        $days_until_next_event = 7 - ($current_day_of_week - $day_of_week);
    }

    // Timestamp per il prossimo evento
    $scheduled_time = strtotime("+$days_until_next_event days $time", $now);

    // Pianifica solo se non è già pianificato
    if (! wp_next_scheduled('sendEmailWithInvoicesByDay')) {
        wp_schedule_event($scheduled_time, 'weekly_custom', 'sendEmailWithInvoicesByDay');
    }
}

// Hook per eseguire la funzione quando il cron job scatta
add_action('sendEmailWithInvoicesByDay', 'send_email_with_invoices');
function send_email_with_invoices()
{
    // Recupera il timestamp dell'ultimo evento cron 'sendEmailWithInvoicesByDay'
    $last_cron_event_timestamp = wp_next_scheduled('sendEmailWithInvoicesByDay', current_time('timestamp'));

    // Se non c'è un evento precedente, imposta una data predefinita (ad esempio, 7 giorni fa)
    if (! $last_cron_event_timestamp) {
        $last_cron_event_timestamp = strtotime('-7 days', current_time('timestamp'));
    }

    // Imposta il timestamp corrente per il nuovo evento cron
    $current_cron_event_timestamp = current_time('timestamp');

    // Imposta i parametri della query
    $args = array(
        'status'         => array('completed'),
        'limit'          => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_key'       => '_invoice_sent',
        'meta_value'     => 'sent',
        'meta_compare'   => '!=',
        'date_completed' => $last_cron_event_timestamp . '...' . $current_cron_event_timestamp,
        'return'         => 'ids',
    );

    // Esegui la query
    $query  = new \WC_Order_Query($args);
    $orders = $query->get_orders();

    $attachments = array();
    if (! empty($orders)) {
        foreach ($orders as $orderID) {
            // it's useless but I'll check
            $checkSent = \WcElectronInvoice\Functions\getPostMeta('_invoice_sent', null, $orderID);
            if ('sent' === $checkSent) {
                continue;
            }

            $xml = get_order_xml_attachments($orderID);
            if ($xml) {
                $attachments[] = $xml;
            }
        }
    }

    if (! empty($_REQUEST['send_email_with_invoices'])) {
        echo '<b>WC_Order_Query args:</b>' . '<br><pre>' . print_r($args, true) . '</pre><br>';
        echo '<b>Orders:</b>' . '<br><pre>' . print_r($orders, true) . '</pre><br>';
        echo '<b>Attachments found:</b>' . '<br><pre>' . print_r($attachments, true) . '</pre><br>';
        die();
    }

    if (! empty($attachments)) {
        $to      = 'example@example.com'; // Destinatario dell'email
        $subject = 'Rapporto settimanale delle fatture';
        $body    = 'Questo è il tuo report settimanale con le fatture.';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($to, $subject, $body, $headers, $attachments);
    }
}

// Funzione che rimuove l'evento alla disattivazione del plugin o tema
function remove_email_cron_schedule()
{
    $timestamp = wp_next_scheduled('sendEmailWithInvoicesByDay');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'sendEmailWithInvoicesByDay');
    }
}

register_deactivation_hook(__FILE__, 'remove_email_cron_schedule');

// Test
add_action('init', function () {
    if (! empty($_REQUEST['send_email_with_invoices'])) {
        send_email_with_invoices();
    }
});