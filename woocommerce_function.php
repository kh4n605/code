 <?php

    function ibrahim_redirect_checkout_add_cart()
    {
        return wc_get_checkout_url();
    }

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



    // IF CATEGORY IS 'X and it's subcategory' ADD MEASUREMENT UNIT BEFORE 'ADD TO CART' 
    add_action('woocommerce_after_add_to_cart_quantity', 'wc_text_after_quantity', 50);
    function wc_text_after_quantity()
    {
        // Check main Category
        if (is_product() && has_term('letkut', 'product_cat')) { ?>
 <span class="measurementUnit meter">metri /</span>
 <span class="measurementUnit meters"> metri??</span>
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
 <span class="measurementUnit meters"> metri??</span>
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