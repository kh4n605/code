<?php
// DISPLAY NAVBAR
wp_nav_menu(array(
    'menu'         => 'primary',
    'menu_class'   => 'navbar-nav',
    'li_class'     => 'nav-item',
    'a_class'      => 'nav-link',
    'active_class' => 'active',
));

// SET NAVBAR li CLASS
function add_additional_class_on_li($classes, $item, $args)
{
    if (isset($args->li_class)) {
        $classes[] = $args->li_class;
    }
    return $classes;
}
add_filter('nav_menu_css_class', 'add_additional_class_on_li', 1, 3);

// SET NAVBAR a CLASS
function add_menu_link_class($atts, $item, $args)
{
    if (property_exists($args, 'a_class')) {
        $atts['class'] = $args->a_class;
    }
    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_menu_link_class', 1, 3);

// REGISTER WIDGETS
function ibrahim_khan_widget_init()
{
    register_sidebar(array(
        'name'          => __('Footer 1', 'ibrahim_khan'),
        'id'            => 'khan_ft_1',
        'description'   => __('Widgets in this area will be shown on Footer 1.', 'textdomain'),
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget'  => '</li>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'ibrahim_khan_widget_init');

// DISPLAY WIDGETS
dynamic_sidebar('khan_ft_1');

// SEE ALL METHODS OF A CLASS 
print_r(get_class_methods(WC()->cart)); // WC()->cart is a class