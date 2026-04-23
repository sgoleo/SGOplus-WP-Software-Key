<?php
define('ABSPATH', './');
define('SGOPLUS_SWK_PATH', './');
define('SGOPLUS_SWK_URL', 'http://example.com/');
define('WPINC', 'wp-includes');

function add_action($tag, $callback) {}
function add_filter($tag, $callback) {}
function register_activation_hook($file, $callback) {}
function plugin_dir_path($file) { return './'; }
function plugin_dir_url($file) { return 'http://example.com/'; }

require_once 'sgoplus-software-key.php';

echo "Autoloader registered.\n";

try {
    $engine = new SGOplus\SoftwareKey\Migration_Engine();
    echo "Migration_Engine loaded.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
