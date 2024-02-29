<?php
/*
Plugin Name: Custom scripts
Description: Custom scripts goes here
Version: 1.0.0
Author: Vladimir
Author URI: slapiosnosys.lt
*/


/*Disable shipping calculator in cart*/

function disable_shipping_calc_on_cart( $show_shipping ) {
    if( is_cart() ) {
        return false;
    }
    return $show_shipping;
}

add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'disable_shipping_calc_on_cart', 99 );

/**
 * Add the shipping class to the bottom of each item in the cart
 */
add_filter( 'woocommerce_cart_item_name', 'shipping_class_in_item_name', 20, 3);
function shipping_class_in_item_name( $item_name, $cart_item, $cart_item_key ) {

	// If the page is NOT the Shopping Cart or the Checkout, then return the product title (otherwise continue...)
	if( ! ( is_cart() || is_checkout() ) ) {
		return $item_name;
	}

	$product = $cart_item['data']; // Get the WC_Product object instance
	$shipping_class_id = $product->get_shipping_class_id(); // Shipping class ID
	$shipping_class_term = get_term( $shipping_class_id, 'product_shipping_class' );

	// Return default product title (in case of no Shipping Class)
	if( empty( $shipping_class_id ) ) {
		return $item_name; 
	}

	// If the Shipping Class slug is either of these, then add a prefix and suffix to the output
	if ( ( $shipping_class_term->slug == 'flat-1995-per' ) || ( $shipping_class_term->slug == 'flat-4999-per' ) ) {
		$prefix = '$';
		$suffix = 'each';
	}

	$label = __( 'Prekė sandėliuojama', 'woocommerce' );

	// Output the Product Title and the new code which wraps the Shipping Class name
	return $item_name . '<br>
		<p class="item-shipping_class" style="margin:0.25em 0 0; font-size: 0.875em;">
		<em>' .$label . ': </em>' . $prefix . $shipping_class_term->name . ' ' . $suffix . '</p>';

}

add_filter( 'woocommerce_cart_shipping_method_full_label', 'filter_woocommerce_cart_shipping_method_full_label', 10, 2 ); 
function filter_woocommerce_cart_shipping_method_full_label( $label, $method ) { 
   // Use the condition here with $method to apply the image to a specific method.      

   if( $method->method_id === "flat_rate" ) {
       $label = "<img src='https://slapiosnosys.lt/wp-content/uploads/2021/11/delivery1.png' style='height: 30px; float: right; margin-right: 35px;' />".$label;
   } 
   return $label; 
}


/** Order status on hold */
add_action('woocommerce_thankyou', 'custom_woocommerce_auto_complete_paid_order', 10, 1);
function custom_woocommerce_auto_complete_paid_order($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    if ($order->is_paid()) {
        $order->update_status('on-hold');
    }
}


function my_cost_tracking_display_order_item_meta($item_id, $item, $product) {
    if ($product) {
        $product_id = $product->get_id();
        $variation_id = $item->get_variation_id();

        echo '<p>';
        echo '<strong>' . __('Product Cost:', 'my-cost-tracking-plugin') . '</strong> ';

        if ($variation_id > 0) {
            $variation_cost = get_post_meta($variation_id, '_variation_cost', true);
            echo !empty($variation_cost) ? wc_price($variation_cost) : __('Not set', 'my-cost-tracking-plugin');
        } else {
            $product_cost = get_post_meta($product_id, '_product_cost', true);
            echo !empty($product_cost) ? wc_price($product_cost) : __('Not set', 'my-cost-tracking-plugin');
        }

        echo '</p>';
    }
}
add_action('woocommerce_after_order_itemmeta', 'my_cost_tracking_display_order_item_meta', 10, 3);


