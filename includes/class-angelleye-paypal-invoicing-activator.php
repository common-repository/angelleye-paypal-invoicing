<?php

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    AngellEYE_PayPal_Invoicing
 * @subpackage AngellEYE_PayPal_Invoicing/includes
 * @author     Angell EYE <service@angelleye.com>
 */
class AngellEYE_PayPal_Invoicing_Activator {

    /**
     * @since    1.0.0
     */
    public static function activate() {
        $apifw_setting = get_option('apifw_setting');
        $sync_paypal_invoice_history_interval = isset($apifw_setting['sync_paypal_invoice_history_interval']) ? $apifw_setting['sync_paypal_invoice_history_interval'] : 'daily';
        $enable_sync_paypal_invoice_history = isset($apifw_setting['enable_sync_paypal_invoice_history']) ? $apifw_setting['enable_sync_paypal_invoice_history'] : '';
        self::create_files();
        if($enable_sync_paypal_invoice_history == 'on') {
            if (wp_next_scheduled('angelleye_paypal_invoicing_sync_with_paypal')) {
                $timestamp = wp_next_scheduled('angelleye_paypal_invoicing_sync_with_paypal');
                wp_unschedule_event($timestamp, 'angelleye_paypal_invoicing_sync_with_paypal');
            }
            wp_clear_scheduled_hook('angelleye_paypal_invoicing_sync_event');
            if (!wp_next_scheduled('angelleye_paypal_invoicing_sync_with_paypal')) {
                wp_schedule_event(time(), $sync_paypal_invoice_history_interval, 'angelleye_paypal_invoicing_sync_event');
            }
        } else {
            if (wp_next_scheduled('angelleye_paypal_invoicing_sync_with_paypal')) {
                $timestamp = wp_next_scheduled('angelleye_paypal_invoicing_sync_with_paypal');
                wp_unschedule_event($timestamp, 'angelleye_paypal_invoicing_sync_with_paypal');
            }
            wp_clear_scheduled_hook('angelleye_paypal_invoicing_sync_event');
        }
        $webhook_id = get_option('webhook_id', false);
        if( $webhook_id == false) {
            self::angelleye_paypal_invoicing_create_web_hook();
        }
        //self::angelleye_paypal_invoicing_synce_paypal_invoiceing_data_to_wp();
    }

    private static function create_files() {
        $files = array(
            array(
                'base' => ANGELLEYE_PAYPAL_INVOICING_LOG_DIR,
                'file' => '.htaccess',
                'content' => 'deny from all',
            ),
            array(
                'base' => ANGELLEYE_PAYPAL_INVOICING_LOG_DIR,
                'file' => 'index.html',
                'content' => '',
            ),
        );
        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
                $file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w');
                if ($file_handle) {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }

    private static function angelleye_paypal_invoicing_synce_paypal_invoiceing_data_to_wp() {
        try {
            include_once(ANGELLEYE_PAYPAL_INVOICING_PLUGIN_DIR . '/admin/class-angelleye-paypal-invoicing-request.php');
            $request = new AngellEYE_PayPal_Invoicing_Request(null, null);
            $request->angelleye_paypal_invoicing_sync_invoicing_with_wp();
        } catch (Exception $ex) {
            
        }
    }
    
    private static function angelleye_paypal_invoicing_create_web_hook() {
        try {
            include_once(ANGELLEYE_PAYPAL_INVOICING_PLUGIN_DIR . '/admin/class-angelleye-paypal-invoicing-request.php');
            $request = new AngellEYE_PayPal_Invoicing_Request(null, null);
            $request->angelleye_paypal_invoicing_create_web_hook_request();
        } catch (Exception $ex) {

        }
    }

}
