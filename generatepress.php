
============== Menu =================

add_shortcode( 'gp_nav', 'tct_gp_nav' );
function tct_gp_nav( $atts ) {
    ob_start();
    generate_navigation_position();
    return ob_get_clean();
}