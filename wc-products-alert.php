<?php
/*
Plugin Name:  WooCommerce products alerts
Plugin URI:   #
Description:  This plugin will send alert email if any product edited
Version:      1.0.0
Author:       Abdalsalaam Halawa
Author URI:
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wcrestapiproxy
Domain Path:  /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    add_action('woocommerce_before_product_object_save', 'product_edited_alert', 10, 3);

    function product_edited_alert(  $product , $data_store ) {
        $product_data = $product->get_data();
        $changes = $product->get_changes();
        $user = wp_get_current_user();

        if(sizeof($changes) > 0){
            $product = wc_get_product($product_data['id']);

            $to = get_bloginfo ( 'admin_email' );
            $subject = 'Product #'.$product_data['id'].' edited. discount alert';

            $body2 = '';
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

            $body2 = $body2 . "<br><br><br>================================<br> Changes Json :<br><br>".json_encode($changes);
            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail( $to, $subject, $body2, $headers );

        }
    }
}