<?php

/**
 * @wordpress-plugin
 * Plugin Name:       WF Feedback & Reviews
 * Plugin URI:        https://Johnboy4809@bitbucket.org/pixelpaperdesign/oophp-plugin.git
 * Description:       Plugin to collect client feedback and customer reviews to create a google scheme star rating
 * Version:           1.0.0
 * Author:            Joe Glossop, John Fieldsend
 * Author URI:        httop://www.wilsonfield.co.uk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       reviews
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_VERSION', '1.0.0' );

function activate_reviews() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-reviews-activator.php';
	Reviews_Activator::activate();
}

function deactivate_reviews() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-reviews-deactivator.php';
	Reviews_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_reviews' );
register_deactivation_hook( __FILE__, 'deactivate_reviews' );

require plugin_dir_path( __FILE__ ) . 'includes/class-reviews.php';

function run_reviews() {
	$plugin = new Reviews();
	$plugin->run();
}
run_reviews();
