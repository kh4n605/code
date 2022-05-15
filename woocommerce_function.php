 <?php

    function ibrahim_redirect_checkout_add_cart()
    {
        return wc_get_checkout_url();
    }

    add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20);
    add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);