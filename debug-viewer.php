<?php
/**
 * Plugin Name: Debug Viewer
 * Plugin URI: https://github.com/iftekharbhuiyan/wp-debug-viewer
 * Description: This plugin allows site Administrators to see debug log content from Dashboard.
 * Author: Iftekhar Bhuiyan
 * Version: 1.0.0
 * Requires PHP: 8.0
 * Author URI: https://github.com/iftekharbhuiyan/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Update URI: https://github.com/iftekharbhuiyan/wp-debug-viewer
 */

// disable direct loading
if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!class_exists('BSFT_Debug_Viewer')) :

    class BSFT_Debug_Viewer {

        private function __construct() {}

        // add widget
        public static function hcb_add_dashboard_widget() {
            if (is_admin() && current_user_can('manage_options')) {
                wp_add_dashboard_widget(
                    'debug_viewer_widget',
                    'Debug Log',
                    array(__CLASS__, 'hcb_set_viewer'),
                    null,
                    null,
                    'side',
                    'core'
                );
            }
        }

        // render viewer
        public static function hcb_set_viewer() {
            // get the file location
            $debug_file = ABSPATH . 'wp-content/debug.log';
            $file_mb_format = '0.00';
            // form check
            if (isset($_POST['debug_nonce']) && wp_verify_nonce($_POST['debug_nonce'], basename(__FILE__))) {
                wp_delete_file($debug_file);
                echo '<meta http-equiv="refresh" content="0">';
            }
            // rendering content
            $data = '<div class="main">';
            $data .= '<textarea id="debugLog" class="large-text" style="width: 100%; height: 15em;" readonly>';
            // read the log data
            if (file_exists($debug_file)) {
                // calculate file size
                $file_size = wp_filesize($debug_file);
                $file_mb = ($file_size / (1024 * 1024));
                $file_mb_format = number_format($file_mb, 2, '.', ' MB');
                $data .= file_get_contents($debug_file, false, null, 0, null);
            } else {
                $data .= 'File does not exist!';
            }
            $data .= '</textarea>';
            $data .= '<div style="display: flex; margin: 10px -12px -12px -12px; padding: 10px; ';
            $data .= 'border-top: 1px solid #dcdcde; background-color: #f6f7f7;">';
            $data .= '<div style="flex: 1 1 50%;">';
            if (file_exists($debug_file)) :
            $data .= '<form action="'.admin_url( 'index.php' ).'" method="post">';
            $data .= '<input type="hidden" id="debug_nonce" name="debug_nonce" value="'.wp_create_nonce(basename(__FILE__)).'">';
            $data .= '<button type="submit" class="button button-primary">Delete Log</button>';
            $data .= '</form>';
            endif;
            $data .= '</div>';
            $data .= '<div style="flex: 1 1 50%; align-self: center; text-align: right; ">';
            $data .= '<strong>'.$file_mb_format.' MB</strong></div>';
            $data .= '</div>';
            $data .= '</div>';
            echo $data;
        }

        // activate
        public static function activate() {
            return;
        }
        // deactivate
        public static function deactivate() {
            return;
        }

        // init
        public static function init() {
            // run on activation
            register_activation_hook(__FILE__, array(__CLASS__, 'activate'));
            // run on deactivation
            register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivate'));
            // add dashboard widget
            add_action('wp_dashboard_setup', array(__CLASS__, 'hcb_add_dashboard_widget'));
        }
    }

    // loaded
    add_action('plugins_loaded', array('BSFT_Debug_Viewer', 'init'));

endif;