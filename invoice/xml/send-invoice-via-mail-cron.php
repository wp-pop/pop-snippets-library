<?php
if(! defined('_POP_SEND_XML_TEXT_DOMAIN_')) {
    define('_POP_SEND_XML_TEXT_DOMAIN_', 'pop_send_email_whit_xml');
}

if(! defined('POP_SEND_XML_INVOICE_EMAIL_FROM_NAME')) {
    define('POP_SEND_XML_INVOICE_EMAIL_FROM_NAME', get_bloginfo('name'));
}
if(! defined('POP_SEND_XML_INVOICE_DAY_OF_WEEK')) {
    define('POP_SEND_XML_INVOICE_DAY_OF_WEEK', 5); // Venerdì
}
if(! defined('POP_SEND_XML_INVOICE_TIME_OF_DAY')) {
    define('POP_SEND_XML_INVOICE_TIME_OF_DAY', "23:00:00"); // Ore 23:00:00
}

if(! defined('POP_SEND_XML_INVOICE_EMAIL_FROM_NAME')) {
    define('POP_SEND_XML_INVOICE_EMAIL_FROM_NAME', get_bloginfo('name'));
}
if(! defined('POP_SEND_XML_INVOICE_EMAIL_FROM_EMAIL')) {
    define('POP_SEND_XML_INVOICE_EMAIL_FROM_EMAIL', 'noreplay@woopop.it');
}
if(! defined('POP_SEND_XML_INVOICE_EMAIL_TO')) {
    define('POP_SEND_XML_INVOICE_EMAIL_TO', 'alfio.piccione@gmail.com');
}
if(! defined('POP_SEND_XML_INVOICE_EMAIL_SUBJECT')) {
    define('POP_SEND_XML_INVOICE_EMAIL_SUBJECT', __('Rapporto settimanale delle fatture', _POP_SEND_XML_TEXT_DOMAIN_));
}
if(! defined('POP_SEND_XML_INVOICE_EMAIL_BODY')) {
    define('POP_SEND_XML_INVOICE_EMAIL_BODY', __('Questo è il tuo report settimanale con le fatture.', _POP_SEND_XML_TEXT_DOMAIN_));
}

function custom_wp_mail_from($default) {
    return POP_SEND_XML_INVOICE_EMAIL_FROM_EMAIL; // L'email personalizzata
}

function custom_wp_mail_from_name($default) {
    return POP_SEND_XML_INVOICE_EMAIL_FROM_NAME; // Il nome del mittente personalizzato
}

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
    $day_of_week = POP_SEND_XML_INVOICE_DAY_OF_WEEK; // 1 = Lunedì, 2 = Martedì, ecc...
    $time        = POP_SEND_XML_INVOICE_TIME_OF_DAY; // Orario es: 10:00 AM

    // Recupera il timezone configurato in WordPress
    $timezone = wp_timezone();

    // Ottieni la data e l'ora corrente con il timezone di WordPress
    $now = new DateTime('now', $timezone);
    $current_day_of_week = (int) $now->format('N'); // Giorno della settimana corrente (1 = Lunedì, 7 = Domenica)

    // Calcola la differenza in giorni tra oggi e il prossimo giorno desiderato
    if ($current_day_of_week <= $day_of_week) {
        // Il giorno desiderato è questa settimana
        $days_until_next_event = $day_of_week - $current_day_of_week;
    } else {
        // Il giorno desiderato è la prossima settimana
        $days_until_next_event = 7 - ($current_day_of_week - $day_of_week);
    }

    // Calcola la data del prossimo evento basandosi sul timezone di WordPress
    $scheduled_date = $now->modify("+$days_until_next_event days")->format('Y-m-d') . ' ' . $time;

    // Crea l'oggetto DateTime per il prossimo evento
    $scheduled_time = new DateTime($scheduled_date, $timezone);

    // Converte l'oggetto DateTime in timestamp
    $scheduled_timestamp = $scheduled_time->getTimestamp();

    // Pianifica solo se non è già pianificato
    if (! wp_next_scheduled('sendEmailWithInvoicesByDay')) {
        wp_schedule_event($scheduled_timestamp, 'weekly_custom', 'sendEmailWithInvoicesByDay');
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
    $checkSentInvoice = array();

    if (! empty($orders)) {
        foreach ($orders as $orderID) {
            // it's useless but I'll check
            $checkSent = \WcElectronInvoice\Functions\getPostMeta('_invoice_sent', null, $orderID);
            if ('sent' === $checkSent) {
                $checkSentInvoice[] = 1;
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
        $to      = POP_SEND_XML_INVOICE_EMAIL_TO;
        $subject = POP_SEND_XML_INVOICE_EMAIL_SUBJECT;
        $body    = POP_SEND_XML_INVOICE_EMAIL_BODY;
        $headers = array('Content-Type: text/html; charset=UTF-8');

        if($to && $subject && $body) {
            add_filter('wp_mail_from', 'custom_wp_mail_from');
            add_filter('wp_mail_from_name', 'custom_wp_mail_from_name');

            if($send = wp_mail($to, $subject, $body, $headers, $attachments)) {
                error_log('Email con "' . $subject . '" - inviata');
                error_log('Allegati: ' . PHP_EOL . print_r($attachments, true));
            }

            remove_filter('wp_mail_from', 'custom_wp_mail_from');
            remove_filter('wp_mail_from_name', 'custom_wp_mail_from_name');
        }

    } else {
        if(empty($checkSentInvoice)) {
            error_log('Le fatture sono state già tutte segnate come inviate');
        } else {
            error_log('ATTENZIONE: Email con non inviata, allegati non presenti');
        }
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