<?php

namespace Papaki\VivaPayments\WooCommerce;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( class_exists( '\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {

    final class WC_Vivapayments_Gateway_Checkout_Block extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
        private $gateway;
        protected $name = 'papaki_vivapayments_gateway';

        public function initialize() {
            $gateways      = WC()->payment_gateways()->payment_gateways();
            $this->gateway = isset( $gateways[ $this->name ] ) ? $gateways[ $this->name ] : null;
        }

        public function is_active() {
            return $this->gateway instanceof \WC_Payment_Gateway && $this->gateway->is_available();
        }

        public function get_payment_method_script_handles() {
            $handle = $this->name . '_gc-blocks-integration';

            wp_register_script(
                $handle,
                plugins_url( 'assets/js/blocks/checkout.js', dirname( __FILE__ ) ),
                [
                    'wc-blocks-registry',
                    'wc-settings',
                    'wp-element',
                    'wp-html-entities',
                    'wp-i18n',
                ],
                null,
                true
            );

            if ( function_exists( 'wp_set_script_translations' ) ) {
                wp_set_script_translations( $handle, Application::TEXT_DOMAIN, dirname( __DIR__ ) . '/languages' );
            }
            return [ $handle ];
        }

        public function get_payment_method_data() {
            $data = [
                'title'       => $this->gateway->title,
                'description' => $this->gateway->description,
            ];

            if ( ! empty( $this->gateway->icon ) ) {
                $data['icon'] = $this->gateway->icon;
            }

            return $data;
        }
    }

} else {

    final class WC_Vivapayments_Gateway_Checkout_Block {
        public function __construct() {
            // Do nothing - blocks not supported
        }
    }

}
