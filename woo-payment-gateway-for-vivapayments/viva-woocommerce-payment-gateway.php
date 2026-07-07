<?php

/*
Plugin Name: Viva Payments - Viva Wallet WooCommerce Payment Gateway
Plugin URI: https://www.papaki.com
Description: Viva Payments - Viva Wallet Payment Gateway allows you to accept payment through various channels such as Maestro, Mastercard, AMex cards, Diners  and Visa cards On your Woocommerce Powered Site.
Version: 1.5.0.0
Author: Papaki
Author URI: https://www.papaki.com
License:           GPL-3.0+
License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
WC tested up to: 6.2.1
Text Domain: woo-payment-gateway-for-vivapayments
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'plugins_loaded', function () {
    if ( ! class_exists( '\WC_Payment_Gateway' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo __( 'Viva Payments - Viva Wallet WooCommerce Payment Gateway requires WooCommerce to be installed and active.', 'woo-payment-gateway-for-vivapayments' );
            echo '</p></div>';
        } );
        return;
    }

    spl_autoload_register( function ( $class ) {
        $prefix   = 'Papaki\\VivaPayments\\WooCommerce\\';
        $base_dir = plugin_dir_path( __FILE__ ) . 'classes/';

        $len = strlen( $prefix );
        if ( strncmp( $prefix, $class, $len ) !== 0 ) {
            return;
        }

        $relative_class = substr( $class, $len );
        $file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

        if ( file_exists( $file ) ) {
            require $file;
        }
    } );

    new \Papaki\VivaPayments\WooCommerce\Application( plugin_basename( __FILE__ ) );
}, 0 );
