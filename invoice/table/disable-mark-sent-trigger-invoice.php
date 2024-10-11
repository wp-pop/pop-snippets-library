<?php
/**
 * Remove mark sent action from table and order
 */
add_filter('wc_el_inv-actions_mark_trigger_invoice', '__return_false');