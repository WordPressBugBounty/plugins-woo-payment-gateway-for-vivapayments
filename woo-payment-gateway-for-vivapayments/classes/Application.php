<?php

namespace Papaki\VivaPayments\WooCommerce;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Application {
    public const PLUGIN_TITLE = 'VivaPayments Gateway';
    public const TEXT_DOMAIN  = 'woo-payment-gateway-for-vivapayments';

    private $entrypoint_path;

    public function __construct( $entrypoint ) {
        $this->entrypoint_path = $entrypoint;

        $this->init();

        add_action( 'init', [ $this, 'load_languages' ] );
        add_action( 'before_woocommerce_init', [ $this, 'declare_transactions' ] );

        $checkout_block = new Checkout_Block( $entrypoint );
        $checkout_block->init();
    }

    public function init() {
        add_action( 'wp', [ $this, 'papaki_vivapayments_message' ] );
        add_filter( 'woocommerce_payment_gateways', [ $this, 'woocommerce_add_vivapayments_gateway' ] );
        add_filter( 'plugin_action_links', [ $this, 'papaki_vivapayments_plugin_action_links' ], 10, 2 );
    }

    public function load_languages() {
        load_plugin_textdomain( static::TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );
    }

    public function declare_transactions() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->entrypoint_path, true );
        }
    }

    public function papaki_vivapayments_message() {
        $order_id = absint( get_query_var( 'order-received' ) );
        $order    = new \WC_Order( $order_id );
        if ( method_exists( $order, 'get_payment_method' ) ) {
            $payment_method = $order->get_payment_method();
        } else {
            $payment_method = $order->payment_method;
        }

        if ( is_order_received_page() && ( 'papaki_vivapayments_gateway' == $payment_method ) ) {

            $vivapayments_message = '';
            if ( method_exists( $order, 'get_meta' ) ) {
                $vivapayments_message = $order->get_meta( '_papaki_vivapayments_message', true );
            } else {
                $vivapayments_message = get_post_meta( $order_id, '_papaki_vivapayments_message', true );
            }
            if ( ! empty( $vivapayments_message ) ) {
                $message      = $vivapayments_message['message'];
                $message_type = $vivapayments_message['message_type'];

                //delete_post_meta($order_id, '_papaki_vivapayments_message');
                if ( method_exists( $order, 'delete_meta_data' ) ) {
                    $order->delete_meta_data( '_papaki_vivapayments_message' );
                    $order->save_meta_data();
                } else {
                    delete_post_meta( $order_id, '_papaki_vivapayments_message' );
                }

                wc_add_notice( $message, $message_type );
            }
        }
    }

    /**
     * Add Vivapayments Gateway to WC
     *
     * @param array $methods
     * @return array
     * */
    public function woocommerce_add_vivapayments_gateway( $methods ) {
        $methods[] = '\Papaki\VivaPayments\WooCommerce\WC_Papaki_Vivapayments_Gateway';
        return $methods;
    }

    /**
     * @param $links
     * @param $file
     * @return mixed
     */
    public function papaki_vivapayments_plugin_action_links( $links, $file ) {
        static $this_plugin;

        if ( ! $this_plugin ) {
            $this_plugin = $this->entrypoint_path;
        }

        if ( $file == $this_plugin ) {
            $settings_url  = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=papaki_vivapayments_gateway' );
            $settings_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', static::TEXT_DOMAIN ) . '</a>';
            array_unshift( $links, $settings_link );
        }
        return $links;
    }
}
