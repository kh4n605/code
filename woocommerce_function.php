 <?php
    //  Disable AJAX add to cart buttons 
    function ibrahim_redirect_checkout_add_cart()
    {
        return wc_get_checkout_url();
    }
    // Add "Add to Cart" buttons in Divi shop pages
    add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20);
    add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);

    //  PRODUCT THUMNAIL IN CHECKOUT PAGE START

    add_filter('woocommerce_cart_item_name', 'ts_product_image_on_checkout', 10, 3);

    function ts_product_image_on_checkout($name, $cart_item, $cart_item_key)
    {

        /* Return if not checkout page */
        if (!is_checkout()) {
            return $name;
        }

        /* Get product object */
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

        /* Get product thumbnail */
        $thumbnail = $_product->get_image();

        /* Add wrapper to image and add some css */
        $image = '<div class="ts-product-image" style="width: 52px; height: 45px; display: inline-block; padding-right: 7px; vertical-align: middle;">'
            . $thumbnail .
            '</div>';

        /* Prepend image to name and return it */
        return $image . $name;
    }
    //  PRODUCT THUMNAIL IN CHECKOUT PAGE END


    /**
     * Remove default woocommerce functions
     */
    function remove_woocommerce_default_functions()
    {
        //Remove default woocommerce breadcrumb
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
        //Remove default woocommerce result count
        remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
        //Remove default woocommerce result count
        remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
        //Remove default woocommerce pagination
        remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);



        //remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );

    }
    add_action('init', 'remove_woocommerce_default_functions');

    /**
     * Customized woocommerce functions after removed
     */
    function custom_woocommerce_breadcrumb()
    {
        return array(
            'delimiter' => ' ',
            'wrap_before' => '<ol class="breadcrumb justify-content-md-end bg-transparent p-0 m-0">',
            'wrap_after' => '</ol>',
            'before' => '<li class="breadcrumb-item">',
            'after' => '</li>',
            'home' => _x('Home', 'breadcrumb', 'woocommerce')

        );
    }
    add_filter('woocommerce_breadcrumb_defaults', 'custom_woocommerce_breadcrumb');

    /**
     * Change number or products per row to 3
     */
    add_filter('loop_shop_columns', 'loop_columns');
    if (!function_exists('loop_columns')) {
        function loop_columns()
        {
            return 3; // 3 products per row
        }
    }

    /**
     * Change the default state and country on the checkout page
     */
    add_filter('default_checkout_billing_country', 'change_default_checkout_country');
    add_filter('default_checkout_billing_state', 'change_default_checkout_state');

    function change_default_checkout_country()
    {
        return 'XX'; // country code
    }

    function change_default_checkout_state()
    {
        return 'XX'; // state code, leave it empty if you don't want to set default state.
    }

    // IF CATEGORY IS 'X and it's subcategory' ADD MEASUREMENT UNIT BEFORE 'ADD TO CART' 
    add_action('woocommerce_after_add_to_cart_quantity', 'wc_text_after_quantity', 50);
    function wc_text_after_quantity()
    {
        // Check main Category
        if (is_product() && has_term('letkut', 'product_cat')) { ?>
 <span class="measurementUnit meter">metri /</span>
 <span class="measurementUnit meters"> metriä</span>
 <?php } else {
            // Check Subcategory of main category
            global $post;
            $terms = get_the_terms($post->ID, 'product_cat');
            foreach ($terms as $term) {
                $product_cat_id = $term->term_id;
                if ($product_cat_id) {
                    if ($product_cat_id == 26) {
            ?>
 <span class="measurementUnit meter">metri /</span>
 <span class="measurementUnit meters"> metriä</span>
 <?php
                    }
                }
                break;
            }
        }
    }
    // IF CATEGORY IS 'X and it's subcategory' ADD MEASUREMENT UNIT BEFORE 'ADD TO CART'  END 

    /*  ======	REPLACE 'ADD TO CART' TEXT ====== */

    add_filter('woocommerce_product_add_to_cart_text', 'custom_woocommerce_product_add_to_cart_text');
    /**
     * custom_woocommerce_template_loop_add_to_cart
     */
    function custom_woocommerce_product_add_to_cart_text()
    {
        global $product;

        $product_type = $product->product_type;

        switch ($product_type) {
            case 'external':
                return __('Buy product', 'woocommerce');
                break;
            case 'grouped':
                return __('View products', 'woocommerce');
                break;
            case 'simple':
                return __('Add to cart', 'woocommerce');
                break;
            case 'variable':
                return __('Select options', 'woocommerce');
                break;
            default:
                return __('Read more', 'woocommerce');
        }
    }

    /* CHECKOUT PAGE TRANSLATION */
    function lan_chng_btn()
    {
        echo '<form action="" method="post"><label class="ml-3">Change Language to :</label><input type="submit" name="lan-chng" class="btn btn-success" value="Italian"></form>';
    }
    add_action('woocommerce_before_checkout_form', 'lan_chng_btn', 30);

    $italian = $_POST["lan-chng"];
    if (isset($italian)) {
        function success($fields)
        {
            unset($fields["billing"]["billing_company"]);
            $fields["billing"]["billing_first_name"]["label"] = 'NOME';
            $fields["billing"]["billing_last_name"]["label"] = 'COGNOME';
            $fields["billing"]["billing_country"]["label"] = 'NAGIONE';
            return $fields;
        }
        add_filter('woocommerce_checkout_fields', 'success', 20, 1);
    }
    /* CHECKOUT PAGE TRANSLATION END*/

    // Thank you pages based on the chosen payment method

    add_action('woocommerce_thankyou', 'ibrahim_redir_based_on_payment_method');
    function ibrahim_redir_based_on_payment_method()
    {

        /* do nothing if we are not on the appropriate page */
        if (!is_wc_endpoint_url('order-received') || empty($_GET['key'])) {
            return;
        }
        $order_id = wc_get_order_id_by_order_key($_GET['key']);
        $order = wc_get_order($order_id);

        if ('bacs' == $order->get_payment_method()) { /* WC 3.0+ */
            wp_redirect(site_url('/bacs'));
            exit;
        }
        if ('cod' == $order->get_payment_method()) { /* WC 3.0+ */
            wp_redirect(site_url('/cod'));
            exit;
        }
    }

    // Save Country and Phone Field in My Account Edit details 
    /**
     * Step 1. Add your field
     */
    add_action('hook_phone', 'ibrahim_add_phone_field_edit_account_form');
    function ibrahim_add_phone_field_edit_account_form()
    {

        woocommerce_form_field(
            'billing_phone',
            array(
                'type'        => 'text',
                'required'    => true,
                'label'       => 'Phone',
                'class'          => array('woocommerce-input'),
                'default'      => '83838'
            ),
            get_user_meta(get_current_user_id(), 'billing_phone', true)
        );
    }

    /**
     * Step 2. Save field value
     */
    add_action('woocommerce_save_account_details', 'ibrahim_save_billing_phone');
    function ibrahim_save_billing_phone($user_id)
    {

        update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
    }
    /**
     * Step 3. Make it required
     */
    add_filter('woocommerce_save_account_details_required_fields', 'ibrahim_make_phone_field_required');
    function ibrahim_make_phone_field_required($required_fields)
    {

        $required_fields['billing_phone'] = 'Phone';
        return $required_fields;
    }

    /**
     * Step 1. Add your field
     */
    add_action('hook_country', 'ibrahim_add_field_edit_account_form');
    function ibrahim_add_field_edit_account_form()
    {

        woocommerce_form_field(
            'billing_country',
            array(
                'type'        => 'select',
                'required'    => true,
                'label'       => 'Country',
                'class'          => array('woocommerce-Input'),
                'options'       => array(
                    'Kuwait'     => __('Kuwait'),
                    'Saudi Arabia'     => __('Saudi Arabia'),
                ),
            ),
            get_user_meta(get_current_user_id(), 'billing_country', true)
        );
    }

    /**
     * Step 2. Save field value
     */
    add_action('woocommerce_save_account_details', 'ibrahim_save_account_details');
    function ibrahim_save_account_details($user_id)
    {

        update_user_meta($user_id, 'billing_country', sanitize_text_field($_POST['billing_country']));
    }
    /**
     * Step 3. Make it required
     */
    add_filter('woocommerce_save_account_details_required_fields', 'ibrahim_make_field_required');
    function ibrahim_make_field_required($required_fields)
    {

        $required_fields['billing_country'] = 'Country';
        return $required_fields;
    }

    // Remove Fields from My Account Edit Address and Re-order 
    add_filter('woocommerce_billing_fields', 'custom_override_billing_fields');

    function custom_override_billing_fields($fields)
    {

        unset($fields['billing_postcode']);
        unset($fields['billing_company']);
        unset($fields['billing_state']);
        unset($fields['billing_city']);
        unset($fields['billing_address_1']);
        unset($fields['billing_address_2']);

        $fields['billing_email'] = array(
            'label'     => __('Email', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-first'),
            'priority'  => 30,
        );

        $fields['billing_phone'] = array(
            'label'     => __('Phone', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-last'),
            'clear'     => true,
            'priority'  => 31,
        );


        return $fields;
    }


    add_filter('woocommerce_shipping_fields', 'custom_override_shipping_fields');

    function custom_override_shipping_fields($fields)
    {

        unset($fields['shipping_postcode']);
        unset($fields['shipping_company']);
        unset($fields['shipping_state']);
        unset($fields['shipping_city']);
        unset($fields['shipping_address_1']);
        unset($fields['shipping_address_2']);

        $fields['shipping_email'] = array(
            'label'     => __('Email', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-first'),
            'priority'  => 30,
        );

        $fields['shipping_phone'] = array(
            'label'     => __('Phone', 'woocommerce'),
            'required'  => true,
            'class'     => array('form-row-last'),
            'clear'     => true,
            'priority'  => 31,
        );


        return $fields;
    }

    // FEDEX WEIGHT BASED SHIPPING JERZY 4MYPET 

    // Display the cart item weight in cart and checkout pages
    add_filter('woocommerce_get_item_data', 'display_custom_item_data', 10, 2);
    function display_custom_item_data($cart_item_data, $cart_item)
    {
        if ($cart_item['data']->get_weight() > 0) {
            $cart_item_data[] = array(
                'name' => __('Weight', 'woocommerce'),
                'value' =>  $cart_item['data']->get_weight()  . ' ' . get_option('woocommerce_weight_unit')
            );
        }
        return $cart_item_data;
    }
    add_filter('flexible-shipping/condition/contents_weight', 'change_flexible_total_weight', 10, 1);
    function change_flexible_total_weight($contents_weight)
    {
        $items = array();
        foreach (WC()->cart->get_cart() as $cart_item) {
            // gets the product object
            $product            = $cart_item['data'];
            $product_weight_str = $product->get_weight() . " ";
            array_push($items, $product_weight_str);
        }
        $total_weight = WC()->cart->get_cart_contents_weight();
        global $coefficient;
        $heaviest_product =  max($items);
        $contents_weight = $heaviest_product + ($total_weight - $heaviest_product) * $coefficient;
        return $contents_weight;
    }
    $coefficient = 0.35;

    // CREATE POPUP ON PRODUCT PAGE WITH POPUP-MAKER AND ACF
    function ibrahim_sizing_chart()
    {
        if (get_field('sizing_chart')) { ?>
 <button class="<?php the_field('sizing_chart'); ?> chart">Sizing Guide</button><br />
 <?php }
    }
    add_action('woocommerce_before_variations_form', 'ibrahim_sizing_chart', 20);


    // SEND CANCELLED AND FAILED ORDER EMAIL TO CUSTOMERS
    add_action('woocommerce_order_status_changed', 'send_custom_email_notifications', 10, 4);
    function send_custom_email_notifications($order_id, $old_status, $new_status, $order)
    {
        if ($new_status == 'cancelled' || $new_status == 'failed') {
            $wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
            $customer_email = $order->get_billing_email(); // The customer email
        }

        if ($new_status == 'cancelled') {
            // change the recipient of this instance
            $wc_emails['WC_Email_Cancelled_Order']->recipient = $customer_email;
            // Sending the email from this instance
            $wc_emails['WC_Email_Cancelled_Order']->trigger($order_id);
        } elseif ($new_status == 'failed') {
            // change the recipient of this instance
            $wc_emails['WC_Email_Failed_Order']->recipient = $customer_email;
            // Sending the email from this instance
            $wc_emails['WC_Email_Failed_Order']->trigger($order_id);
        }
    }

    // ARTWORKS - THEO PROJECT WITH FRONTEND UPLOAD 


    add_filter('woocommerce_add_to_cart_redirect', 'ibrahim_redirect_checkout_add_cart');

    function ibrahim_redirect_checkout_add_cart()
    {
        return wc_get_checkout_url();
    }

    function ibrahim_notification_form()
    { ?>
 <h2 style="text-align:center;">
     <br />
     <strong> Upload images and submit the form below </strong>
 </h2>
 <?php
        echo do_shortcode('[forminator_form id="116"]'); ?>
 <?php
    }
    add_action('woocommerce_account_content', 'ibrahim_notification_form', 20);


    // SAMPLE ARTWORKS DOWNLOAD
    function ibrahim_preview_images()
    {
        $user_id = get_current_user_id();
        $preview_images = get_field('preview_artwork', 'user_' . $user_id);
        $final_artwork = get_field('final_artwork', 'user_' . $user_id);
        if ($preview_images) { ?>
 <div style="display:flex;justify-content:center;">
     <br /><a href="<?php echo $preview_images ?>" class="preview_artworks" target="_blank"><strong>Download Sample
             Artworks</strong></a><?php
                                            if ($final_artwork) { ?>
     <br /><a href="<?php echo $final_artwork ?>" class="preview_artworks" target="_blank"><strong>Confirm
             Order</strong></a>
     <?php }
                ?>
 </div>
 <?php }
    }
    add_action('woocommerce_account_content', 'ibrahim_preview_images', 30);
    // USER FILE MANAGER
    function ibrahim_file_manager()
    {
        echo do_shortcode('[ffmwp]');
    }
    add_action('woocommerce_account_content', 'ibrahim_file_manager', 40);

    /**
     * Display Custom Attribute on Shop Loop 
     */
    global $product;
    echo wc_display_product_attributes($product);


    /**
     * Display Attribute in Shop Loop with add to Cart
     **/

    /**
     * Replace add to cart button in the loop.
     */
    function iconic_change_loop_add_to_cart()
    {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        add_action('woocommerce_after_shop_loop_item', 'iconic_template_loop_add_to_cart', 10);
    }

    add_action('init', 'iconic_change_loop_add_to_cart', 10);

    /**
     * Use single add to cart button for variable products.
     */
    function iconic_template_loop_add_to_cart()
    {
        global $product;

        if (!$product->is_type('variable')) {
            woocommerce_template_loop_add_to_cart();
            return;
        }

        remove_action('woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20);
        add_action('woocommerce_single_variation', 'iconic_loop_variation_add_to_cart_button', 20);

        woocommerce_template_single_add_to_cart();
    }

    /**
     * Customise variable add to cart button for loop.
     *
     * Remove qty selector and simplify.
     */
    function iconic_loop_variation_add_to_cart_button()
    {
        global $product;

        ?>
 <div class="woocommerce-variation-add-to-cart variations_button">
     <button type="submit"
         class="custom_add_to_cart single_add_to_cart_button button"><?php echo esc_html($product->single_add_to_cart_text()); ?></button>
     <input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>" />
     <input type="hidden" name="product_id" value="<?php echo absint($product->get_id()); ?>" />
     <input type="hidden" name="variation_id" class="variation_id" value="0" />
 </div>
 <?php
    }
    function iconic_add_to_cart_form_action($redirect)
    {
        if (!is_archive()) {
            return $redirect;
        }

        return '';
    }
    add_filter('woocommerce_add_to_cart_form_action', 'iconic_add_to_cart_form_action');
    add_action('wp_footer', 'myScript');


    function myScript()
    {
    ?>
 <script>
jQuery(document).ready(function($) {
    "use strict";

    $('.custom_add_to_cart').click(function(e) {
        e.preventDefault();
        var id = $(this).next().next().next().attr('value');
        var data = {
            product_id: id,
            quantity: 1
        };
        $(this).parent().addClass('loading');
        $.post(wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
            data,
            function(response) {

                if (!response) {
                    return;
                }
                if (response.error) {
                    alert("Custom Massage ");
                    $('.custom_add_to_cart').parent().removeClass('loading');
                    return;
                }
                if (response) {

                    var url = woocommerce_params.wc_ajax_url;
                    url = url.replace("%%endpoint%%", "get_refreshed_fragments");
                    $.post(url, function(data, status) {
                        $(".woocommerce.widget_shopping_cart").html(data.fragments[
                            "div.widget_shopping_cart_content"]);
                        if (data.fragments) {
                            jQuery.each(data.fragments, function(key, value) {

                                jQuery(key).replaceWith(value);
                            });
                        }
                        jQuery("body").trigger("wc_fragments_refreshed");
                    });
                    $('.custom_add_to_cart').parent().removeClass('loading');

                }

            });

    });
});
 </script>
 <?php
    }


    // Remove additional information tab

    add_filter('woocommerce_product_tabs', function ($tabs) {
        unset($tabs['additional_information']);
        return $tabs;
    }, 98);

    // Add additional Field 
    add_action('woocommerce_product_thumbnails', 'ibrahim_product_description');
    function ibrahim_product_description()
    { // additional field function
        global $product;
        if ($product && ($product->has_attributes() || apply_filters('wc_product_enable_dimensions_display', $product->has_weight() || $product->has_dimensions()))) {
            wc_get_template('single-product/tabs/additional-information.php');
        }
    }

    /* Custom Thank You page redirect */
    function wc_custom_thank_you_page($order_id)
    {
        $order = wc_get_order($order_id);

        if ($order->get_billing_email()) {
            wp_redirect('http://www.example.com/thank-you/');
            exit;
        }
    }
    add_action('woocommerce_thankyou', 'wc_custom_thank_you_page');

    // Add to Cart button Icon to WooCommerce shop loop
    add_action('woocommerce_after_shop_loop_item', 'add_add_to_cart_button_to_loop', 9);
    function add_add_to_cart_button_to_loop()
    {
        global $product;

        echo '<a href="' . $product->add_to_cart_url() . '" class="button add_to_cart_button et-pb-icon">&#xe013;</a>';
    }

    // Returning Customer - Enable Guest Checkout
    add_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);

    // Add a collapsible coupon field to the checkout page
    add_action('woocommerce_checkout_before_customer_details', 'add_collapsible_coupon_field_to_checkout', 15);
    function add_collapsible_coupon_field_to_checkout()
    {
        echo '<div id="coupon-div"><h3>' . __('Have a coupon?') . '</h3><a class="showcoupon" href="#">' . __('Click here to enter your code') . '</a><form method="post"><div class="coupon-form" style="display:none"><input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="' . __('Enter coupon code') . '" /><button type="submit" class="button" name="apply_coupon" value="' . __('Apply Coupon') . '">' . __('Apply Coupon') . '</button></div></form></div>';
    }

    // Apply the coupon code entered by the customer
    add_action('woocommerce_applied_coupon', 'apply_coupon_code');
    function apply_coupon_code()
    {
        global $woocommerce;
        $woocommerce->cart->apply_coupon($_POST['coupon_code']);
    }

    add_action('wp_footer', 'collapseable_coupon_checkout');
    function collapseable_coupon_checkout()
    { ?>
 // Toggle the visibility of the coupon form
 <script>
jQuery(document).ready(function($) {
    $('.showcoupon').click(function() {
        $('.coupon-form').slideToggle(200);
        return false;
    });
});
 </script>
 <?php }

 /* ===== ADD CLASS TO PRODUCT TITLE ===== */
 add_filter( 'woocommerce_product_loop_title_classes', 'custom_woocommerce_product_loop_title_classes' );

function custom_woocommerce_product_loop_title_classes( $class ) {
	return $class . ' notranslate'; // set your additional class(es) here.
}

 /* ===== ADD CUSTOM TAXONOMY AND DISPLAY ON FRONTEND ===== */

function custom_taxonomy_author() {
    $labels = array(
        'name'              => _x( 'Authors', 'taxonomy general name' ),
        'singular_name'     => _x( 'Author', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Authors' ),
        'all_items'         => __( 'All Authors' ),
        'parent_item'       => __( 'Parent Author' ),
        'parent_item_colon' => __( 'Parent Author:' ),
        'edit_item'         => __( 'Edit Author' ),
        'update_item'       => __( 'Update Author' ),
        'add_new_item'      => __( 'Add New Author' ),
        'new_item_name'     => __( 'New Author Name' ),
        'menu_name'         => __( 'Authors' ),
    );

    $args = array(
        'hierarchical'      => true, // Set to false if you want a flat taxonomy like categories
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'product_author' ), // You can change the slug
    );

    register_taxonomy( 'product_author', 'product', $args );
}

add_action( 'init', 'custom_taxonomy_author', 0 );
// Display Author on Product Loop
function display_product_author_on_loop() {
    $author_terms = wp_get_post_terms( get_the_ID(), 'product_author' );

    if ( ! empty( $author_terms ) && ! is_wp_error( $author_terms ) ) {
        echo '<div class="product-author">';
        foreach ( $author_terms as $author_term ) {
            echo '<span class="author-name">' . esc_html( $author_term->name ) . '</span>';
        }
        echo '</div>';
    }
}

add_action( 'astra_woo_shop_title_after', 'display_product_author_on_loop', 5 );

// Display Author on Cart Page
add_filter('woocommerce_cart_item_name', 'display_custom_taxonomy_term', 10, 3);
function display_custom_taxonomy_term($product_name, $cart_item, $cart_item_key) {
    $product_id = $cart_item['product_id'];
    $taxonomy = 'product_author'; // Replace with your custom taxonomy name

    // Get the terms
    $terms = get_the_terms($product_id, $taxonomy);

    if ($terms && !is_wp_error($terms)) {
        $term_names = array_map(function ($term) {
            return $term->name;
        }, $terms);

        $term_string = implode(', ', $term_names);
        $product_name .= '<span class="custom-taxonomy"><br>' . $term_string . '</span>';
    }

    return $product_name;
}

// Display Author on Home Page
function ibrahim_display_author() {
    $authors = get_terms( array(
        'taxonomy'   => 'product_author',
        'hide_empty' => false,
    ) );

    if ( ! empty( $authors ) && ! is_wp_error( $authors ) ) {
        $output = '<ul class="author-list">';
        foreach ( $authors as $author ) {
            $author_link = get_term_link( $author );
            $output .= '<li><a href="' . esc_url( $author_link ) . '">' . esc_html( $author->name ) . '</a></li>';
        }
        $output .= '</ul>';
    }

    return $output;
}
add_shortcode( 'ibrahim_author_list', 'ibrahim_display_author' );
// Register custom taxonomy
function custom_publisher_taxonomy() {
    $labels = array(
        'name'                       => _x( 'Publishers', 'taxonomy general name', 'textdomain' ),
        'singular_name'              => _x( 'Publisher', 'taxonomy singular name', 'textdomain' ),
        'search_items'               => __( 'Search Publishers', 'textdomain' ),
        'popular_items'              => __( 'Popular Publishers', 'textdomain' ),
        'all_items'                  => __( 'All Publishers', 'textdomain' ),
        'edit_item'                  => __( 'Edit Publisher', 'textdomain' ),
        'update_item'                => __( 'Update Publisher', 'textdomain' ),
        'add_new_item'               => __( 'Add New Publisher', 'textdomain' ),
        'new_item_name'              => __( 'New Publisher Name', 'textdomain' ),
        'separate_items_with_commas' => __( 'Separate publishers with commas', 'textdomain' ),
        'add_or_remove_items'        => __( 'Add or remove publishers', 'textdomain' ),
        'choose_from_most_used'      => __( 'Choose from the most used publishers', 'textdomain' ),
        'not_found'                  => __( 'No publishers found', 'textdomain' ),
        'menu_name'                  => __( 'Publishers', 'textdomain' ),
    );

    $args = array(
        'hierarchical'          => true,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'publisher' ),
    );

    register_taxonomy( 'publisher', 'product', $args );
}
add_action( 'init', 'custom_publisher_taxonomy', 0 );
function custom_publisher_list_shortcode() {
    $publishers = get_terms( array(
        'taxonomy' => 'publisher',
        'hide_empty' => false,
    ) );

    if ( ! empty( $publishers ) && ! is_wp_error( $publishers ) ) {
        $output = '<ul class="publisher-list">';
        foreach ( $publishers as $publisher ) {
            $publisher_link = get_term_link( $publisher );
            $output .= '<li><a href="' . esc_url( $publisher_link ) . '">' . esc_html( $publisher->name ) . '</a></li>';
        }
        $output .= '</ul>';
    }

    return $output;
}
add_shortcode( 'ibrahim_publisher_list', 'custom_publisher_list_shortcode' );

// === Custom Order Details === //

add_filter( 'woocommerce_localisation_address_formats', 'custom_address_formats', 20 );

function custom_address_formats( $formats ) {
	$formats[ 'default' ]  = "{first_name} {last_name} \n Street: {address_1} {address_2}\n City: {city}\n ZIP: {postcode}\n Region: {state}\n";
	// default will be replaced with Country code "BD"
	return $formats;
}

// === Make it for All Countries if default don't work == //

function custom_address_formats( $formats ) {
    $countryCodes = array(
        'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AO', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AW', 'AX', 'AZ',
        'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BR', 'BS',
        'BT', 'BV', 'BW', 'BY', 'BZ', 'CA', 'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN',
        'CO', 'CR', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ', 'DE', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EC', 'EE',
        'EG', 'EH', 'ER', 'ES', 'ET', 'FI', 'FJ', 'FK', 'FM', 'FO', 'FR', 'GA', 'GB', 'GD', 'GE', 'GF',
        'GG', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HM',
        'HN', 'HR', 'HT', 'HU', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IR', 'IS', 'IT', 'JE', 'JM',
        'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN', 'KP', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC',
        'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH', 'MK',
        'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA',
        'NC', 'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG',
        'PH', 'PK', 'PL', 'PM', 'PN', 'PR', 'PS', 'PT', 'PW', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW',
        'SA', 'SB', 'SC', 'SD', 'SE', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'SS',
        'ST', 'SV', 'SX', 'SY', 'SZ', 'TC', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO',
        'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'UM', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI',
        'VN', 'VU', 'WF', 'WS', 'YE', 'YT', 'ZA', 'ZM', 'ZW'
    );
    
    // Now $countryCodes contains all ISO 3166-1 alpha-2 country codes
    
    
    // Loop through each country code and assign a default format
    foreach ($countryCodes as $countryCode) {
        // Default format with placeholders for each component
        $defaultFormat = "{first_name} {last_name} \n Street: {address_1} {address_2}\n City: {city}\n ZIP: {postcode}\n Region: {state}\n";
    
        // Assign the default format to the country code
        $formats[$countryCode] = $defaultFormat;
        
    }
    return $formats;
    }
    
    add_filter( 'woocommerce_localisation_address_formats', 'custom_address_formats', 20 );

===== Redirect Logged Out user and Logged in From specific country to WhatsApp =====

    function custom_redirect_logic() {
        // Check if the user is logged in
        if ( is_user_logged_in() ) {
            // Check if the user is from France based on IP address
            $user_ip = $_SERVER['REMOTE_ADDR'];
            $ip_details = json_decode(file_get_contents("https://ipinfo.io/{$user_ip}/json"));
    
            if (isset($ip_details->country) && strtoupper($ip_details->country) === 'FR') {
                // Redirect logged-in users from France to a different link
                wp_redirect('https://wa.me/+8801772812413');
                exit();
            }
        } else {
            // Redirect logged-out users to the WhatsApp link
            wp_redirect('https://wa.me/+8801772812413');
            exit();
        }
    }
    
    add_action('template_redirect', 'custom_redirect_logic');

===== Refresh Main page when clicked on iframe button =====

    jQuery(document).ready(function ($) {
        $('#iframe .single_add_to_cart_button').on('click', function () {
            // Communicate with the parent page
            parent.postMessage('refresh', '*');
        });
    });
   window.addEventListener('message', function (event) {
    if (event.data === 'refresh') {
      location.reload();
    }
  });