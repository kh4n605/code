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