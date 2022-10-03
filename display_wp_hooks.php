<?php
// Which hooks is in use
$debug_tags = array();
add_action('all', function ($tag) {
    global $debug_tags;
    if (in_array($tag, $debug_tags)) {
        return;
    }
    echo "<pre>" . $tag . "</pre>";
    $debug_tags[] = $tag;
});
// What is in which hooks
add_action('wp_footer', 'actiondebug', 99);
function actiondebug()
{
    global $wp_actions, $wp_filter;
    $var = $wp_filter['wp_footer'];
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}