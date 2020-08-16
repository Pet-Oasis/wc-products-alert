<?php
/*
Plugin Name:  WC Products Edits Alert
Plugin URI:   #
Description:  This plugin will send alert email if any product edited.
Version:      1.0.0
Author:       Abdalsalaam Halawa
Tags: woocommerce, products, email, alert
Author URI:
License:      GPL3
License URI:  http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    register_activation_hook( __FILE__, 'alert_plugin_activation' );
    function alert_plugin_activation() {
        if ( ! wp_next_scheduled( 'products_alert_queue' ) ) {
            wp_schedule_event( time(), 'hourly', 'products_alert_queue' );
        }
    }

    /*
     * Products alert Queue Check
     */
    function products_alert_queue() {
        $alert_queue = get_option('products_alert_queue');
        if(!empty($alert_queue) || $alert_queue!= ''){
            $body = '';

            foreach ($alert_queue as $alert){
                $body =  $body.strval($alert);
            }

            $to = get_bloginfo ( 'admin_email' );
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $subject = 'Products edited alert';
            $mail = wp_mail( $to, $subject, $body, $headers );
            if($mail)
            update_option("products_alert_queue",array());
        }
    }
    add_filter( 'products_alert_queue', 'products_alert_queue' );


    add_action('woocommerce_before_product_object_save', 'product_edited_alert', 10, 3);

    function product_edited_alert(  $product , $data_store ) {
        $product_data = $product->get_data();
        $changes = $product->get_changes();
        $user = wp_get_current_user();

        if(sizeof($changes) > 0){
            $product = wc_get_product($product_data['id']);
            $body2 = "<br><br>================================<br><br>";
            $body2 = $body2 .'Product #'.$product_data['id'].' edited.'."<br>";
            if(isset($changes['regular_price'])){
                $body2 = $body2."Old price : ".$product->get_regular_price();
                $body2 = $body2."<br>New price : ".$changes['regular_price'];
            }
            if(isset($changes['sale_price'])){
                $body2 = $body2."<br><br>Old sale price : ".$product->get_sale_price();
                $body2 = $body2."<br>New sale price : ".$changes['sale_price'];
            }

            $body2 = $body2."<br><br>User : ".$user->user_login;

            if(!isset($changes['regular_price'])) $price = $product->get_regular_price();
            else $price = $changes['regular_price'];

            if(!isset($changes['sale_price'])) $sale_price = $product->get_regular_price();
            else $sale_price = $changes['sale_price'];

            if((floatval(($price - $sale_price)/ $price) > 0.15) || (isset($changes['regular_price']) || isset($changes['sale_price']))){
                $body2 = $body2."<br>Discoint alert : ".floatval(($price - $sale_price)/ $price);
            }

            $body2 = $body2 . "<br><br><br>****************************<br> Changes Json :<br><br>".json_encode($changes);

            $products_alert_queue = get_option('products_alert_queue');
            if( $products_alert_queue ==""){
                $products_alert_queue = array();
            }
            array_push($products_alert_queue,$body2);
            update_option("products_alert_queue",$products_alert_queue);
        }
    }
}