<?php

add_filter( 'pre_option_wc_el_inv-secret-api-key', 'woopop_secret_api_key', 10, 1 );
add_filter( 'pre_option_wc_el_inv-license_check', 'woopop_license_check', 10, 1 );

/**
 * Secret API Key
 *
 * @param $value
 *
 * @return string
 */
function woopop_secret_api_key( $value ) {

    return '1234567890';
}

/**
 * License Check
 *
 * @return object[]
 */
function woopop_license_check() {

    return [
        (object) [
            get_site_url() => (object) [
                'license_expired_timestamp' => time() + ( 300 * DAY_IN_SECONDS ),
            ],
            'NULL'         => (object) [
                'license_expired_timestamp' => time() + ( 300 * DAY_IN_SECONDS ),
            ]
        ]
    ];

}