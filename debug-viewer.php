<?php
/**
 * Plugin Name: Debug Viewer
 * Description: A Plugin that allows site Administrators to see debug log content from Dashboard.
 * Author: Iftekhar Bhuiyan
 * Version: 1.0.0
 * Requires PHP: 8.0
 * Author URI: https://profiles.wordpress.org/iftekharbhuiyan/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

// disable direct loading
if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!class_exists('BSFT_Debug_Viewer')) :

    class BSFT_Debug_Viewer {

        private $class_file;
        private $debug_file;

        public function __construct() {
            $this->class_file = dirname(__FILE__) . '/debug-viewer.php';
            $this->debug_file = (defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : ABSPATH . 'wp-content') . '/debug.log';
        }

        // add widget callback
        public function add_widget() {
            if (is_admin() && current_user_can('manage_options')) {
                // dashboard widget
                wp_add_dashboard_widget(
                    'debug_viewer_widget',
                    'Debug Viewer',
                    array($this, 'get_viewer'),
                    null,
                    null,
                    'side',
                    'core'
                );
            }
        }

        // render viewer
        public function get_viewer() {
            // form submission
            if (isset($_POST['debug_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['debug_nonce'])), basename(__FILE__))) {
                if (current_user_can('manage_options')) {
                    if (file_exists($this->debug_file)) {
                        if (!function_exists('wp_delete_file')) {
                            require_once ABSPATH . 'wp-admin/includes/file.php';
                        }
                        wp_delete_file($this->debug_file);
                    }
                }
            }
            // rendering content
            $data = '<div class="main">';
            $data .= '<textarea class="small-text" style="width: 100%; height: 15em;" readonly>';
            // read the log data
            if (file_exists($this->debug_file)) {
                $data .= file_get_contents($this->debug_file);
            } else {
                $data .= esc_html('Debug log does not exist!');
            }
            $data .= '</textarea>';
            // delete button
            if (file_exists($this->debug_file)) {
                $data .= '<div style="margin: 10px -12px -12px -12px; padding: 10px; ';
                $data .= 'border-top: 1px solid #dcdcde; background-color: #f6f7f7;">';
                $data .= '<form action="'.admin_url('index.php').'" method="post">';
                $data .= '<input type="hidden" id="debug_nonce" name="debug_nonce" value="'.wp_create_nonce(basename(__FILE__)).'">';
                $data .= '<button type="submit" class="button button-primary">Delete Log</button>';
                $data .= '</form>';
                $data .= '</div>';
            }
            $data .= '</div>';
            $data .= '</div>';
            echo wp_kses($data, array(
                'div' => array(
                    'class' => true,
                    'style' => true,
                    'readonly' => true,
                ),
                'textarea' => array(
                    'class' => true,
                    'style' => true,
                    'readonly' => true,
                ),
                'form' => array(
                    'action' => true,
                    'method' => true,
                ),
                'input' => array(
                    'type' => true,
                    'id' => true,
                    'name' => true,
                    'value' => true,
                ),
                'button' => array(
                    'type' => true,
                    'class' => true,
                ),
                'strong' => array(),
            ));
        }

        // activate
        public function do_activate() {
            return;
        }

        // deactivate
        public function do_deactivate() {
            return;
        }

        // init
        public function init() {
            // on activation
            register_activation_hook($this->class_file, array($this, 'do_activate'));

            // on deactivation
            register_deactivation_hook($this->class_file, array($this, 'do_deactivate'));

            // add dashboard widget
            add_action('wp_dashboard_setup', array($this, 'add_widget'));
        }
    }

    // instance
    $bsft_debug_viewer = new BSFT_Debug_Viewer();

    // loaded
    add_action('plugins_loaded', array($bsft_debug_viewer, 'init'));

endif;