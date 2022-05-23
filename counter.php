<?php

/**
 * Plugin Name:       Plugin Word Counter
 * Plugin URI:        https://andrevega.com
 * Description:       Shows the number of words, characters and reader time in each post.
 * Version:           0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Andrés Vega
 * Author URI:        https://andrevega.com
 * Text Domain:       wcpdomain
 * Domain Path:       /languages
 */


define('WORD_COUNTER_PATH', plugin_dir_path((__FILE__)));
require_once WORD_COUNTER_PATH . '/admin/admin-index.php';
