<?php
/**
 * Drag Drop Switch - Pause drag and drop for meta boxes on demand.
 *
 * @copyright Copyright (C) 2025, Furkan OZTURK - me@furkanozturk.dev
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Drag Drop Switch
 * Version: 1.0.1
 * Plugin URI: https://github.com/itsjjfurki/drag-drop-switch
 * Description: Adds a toggle switch under Screen Options to disable or enable drag-and-drop functionality for meta boxes if Classic Editor plugin is installed and active.
 * Author: Furkan OZTURK
 * Author URI:  https://furkanozturk.dev/about
 * Text Domain: drag-drop-switch
 * Domain Path: /languages/
 * License: GPL v3
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Network: true
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! function_exists('ddsw1tch_activate') ) {
    function ddsw1tch_activate() {
        if ( ! class_exists( 'Classic_Editor' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            $error_message = __( '"Drag Drop Switch" plugin requires the "Classic Editor" plugin to be installed and activated. Please install and activate the Classic Editor plugin before activating this plugin', 'drag-drop-switch' );
            $return_label = __( 'Return to Plugins', 'drag-drop-switch' );
            wp_die(
                esc_html($error_message) . '<br><br><a href="' . esc_html(admin_url( 'plugins.php' )) . '">' . esc_html($return_label) . '</a>'
            );
        }
    }
}

register_activation_hook( __FILE__, 'ddsw1tch_activate' );

if( ! function_exists('ddsw1tch_enqueue_scripts') ) {
    function ddsw1tch_enqueue_scripts($hook) {
        if ($hook === 'post.php') {
            wp_enqueue_script(
                'drag-drop-switch',
                plugin_dir_url(__FILE__) . 'drag-drop-switch.js',
                array('jquery'),
                '1.0.1',
                true
            );

            wp_localize_script( 'drag-drop-switch', 'DragDropSwitch', array(
                'disabled' => get_user_meta(get_current_user_id(), 'ddsw1tch_state', true) === '1' ? 'true' : 'false',
                'nonce' => wp_create_nonce( 'ddsw1tch_update_meta_nonce' ),
            ));
        }
    }
}

if( ! function_exists('ddsw1tch_add_screen_option') ) {
    function ddsw1tch_add_screen_option($screen_settings, $screen) {
        if ( isset( $screen->id ) && ( $screen->id === 'post' || $screen->id === 'page' ) ) {
            $legend_label = __( "Enable / Disable drag and drop for meta boxes", 'drag-drop-switch' );
            $switch_label = __( "Check to disable drag and drop", 'drag-drop-switch' );
            $toggle_container = '<fieldset class="ddsw1tch-toggle-container"><legend>' . $legend_label . '</legend><label><input type="checkbox" id="ddsw1tch-toggle">' . $switch_label . '</label></fieldset>';
            return $screen_settings . $toggle_container;
        }

        return $screen_settings;
    }
}

if( ! function_exists('ddsw1tch_update_user_meta') ) {
    function ddsw1tch_update_user_meta() {
        $nonce = isset( $_POST['ddsw1tch_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['ddsw1tch_nonce'] ) ) : '';
        $state = isset( $_POST['ddsw1tch_state'] ) ? sanitize_text_field( wp_unslash( $_POST['ddsw1tch_state'] ) ) : null;

        if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'ddsw1tch_update_meta_nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Invalid nonce '.$nonce ] );
            return;
        }

        if ( ! is_null( $state ) ) {
            update_user_meta( get_current_user_id(), 'ddsw1tch_state', sanitize_text_field( $state ) );
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }
}

if ( class_exists( 'Classic_Editor' ) ) {
    add_action( 'admin_enqueue_scripts', 'ddsw1tch_enqueue_scripts' );
    add_filter( 'screen_settings', 'ddsw1tch_add_screen_option', 10, 2 );
    add_action( 'wp_ajax_ddsw1tch_update_user_meta', 'ddsw1tch_update_user_meta' );
}
