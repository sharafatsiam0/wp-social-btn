<?php
/**
 * Plugin Name: Social Share Buttons
 * Description: Add social share buttons to single posts
 * Version: 1.0.1
 * Author: Your Sharafat Siam
 * Author URI: https://sharafatsiam.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: social-share-buttons
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
    }

// Define plugin constants
define('SSB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SSB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SSB_VERSION', '1.0.1');


class Social_Share_Buttons {
    private static $instance = null;
    private $buttons_added = false;
    
    function __construct(){
        
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('the_content', [$this, 'display_share_buttons'], 99);
        
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        register_activation_hook(__FILE__,[$this, 'ssb_activate']);
    }
   
    public function enqueue_assets(){
        if(!is_singular('post')){
            return;
        }

         // Enqueue CSS
        wp_enqueue_style(
            'ssb-styles',
            SSB_PLUGIN_URL . 'css/style.css',
            array(),
            SSB_VERSION
        );

        // Enqueue JavaScript
        wp_enqueue_script(
            'ssb-script',
            SSB_PLUGIN_URL . 'js/script.js',
            array(),
            SSB_VERSION,
            true
        );
    }


     public function display_share_buttons($content) {
        if (!is_singular('post')) {
            return $content;
        }


        if ($this->buttons_added) {
            return $content;
        }

        // Mark that we've added buttons
        $this->buttons_added = true;

        // Get plugin settings
        $settings = get_option('ssb_settings');
        
        if (!is_array($settings)) {
            $settings = array(
                'position' => 'bottom',
                'platforms' => array('facebook', 'twitter', 'linkedin', 'whatsapp', 'email', 'copy')
            );
        }

        $position = isset($settings['position']) ? sanitize_text_field($settings['position']) : 'bottom';
        $platforms = isset($settings['platforms']) && is_array($settings['platforms']) ? $settings['platforms'] : array('facebook', 'twitter', 'linkedin', 'whatsapp', 'email', 'copy');

        // Prevent empty platforms
        if (empty($platforms)) {
            return $content;
        }

        // Generate share buttons HTML
        $buttons_html = $this->generate_buttons_html($platforms);

        // Add buttons to content based on position
        switch ($position) {
            case 'top':
                $content = $buttons_html . $content;
                break;
            case 'both':
                $content = $buttons_html . $content . $buttons_html;
                break;
            case 'bottom':
            default:
                $content = $content . $buttons_html;
                break;
        }

        return $content;
    }

     private function generate_buttons_html($platforms) {
        $post_id = get_the_ID();
        $post_url = urlencode(get_permalink($post_id));
        $post_title = urlencode(get_the_title($post_id));
        $post_excerpt = urlencode(wp_trim_words(get_the_excerpt($post_id), 20));

        // Share URLs for each platform
        $share_urls = array(
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$post_url}",
            'twitter' => "https://twitter.com/intent/tweet?url={$post_url}&text={$post_title}",
            'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$post_url}",
            'pinterest' => "https://pinterest.com/pin/create/button/?url={$post_url}&description={$post_title}",
            'whatsapp' => "https://wa.me/?text={$post_title}%20{$post_url}",
            'email' => "mailto:?subject={$post_title}&body={$post_excerpt}%0A%0A{$post_url}",
        );

        // Icons for each platform
        $icons = array(
            'facebook' => '🌐',
            'twitter' => '𝕏',
            'linkedin' => 'in',
            'pinterest' => '📌',
            'whatsapp' => '💬',
            'email' => '✉️',
            'copy' => '📋'
        );

        // Build HTML
        $html = '<div class="ssb-container">';
        $html .= '<div class="ssb-label">Share this post:</div>';
        $html .= '<div class="ssb-buttons">';

        foreach ($platforms as $platform) {
            $platform = sanitize_text_field($platform);
            
            if ($platform === 'copy') {
                // Copy link button
                $html .= '<button class="ssb-button ssb-copy" title="Copy Link" data-url="' . esc_attr(get_permalink($post_id)) . '">';
                $html .= isset($icons[$platform]) ? $icons[$platform] : '📋';
                $html .= '</button>';
            } else {
                // Social share links
                $url = isset($share_urls[$platform]) ? $share_urls[$platform] : '#';
                $icon = isset($icons[$platform]) ? $icons[$platform] : '';
                
                if (!empty($url) && $url !== '#') {
                    $html .= '<a href="' . esc_url($url) . '" class="ssb-button ssb-' . esc_attr($platform) . '" target="_blank" rel="noopener noreferrer" title="' . ucfirst(esc_attr($platform)) . '">';
                    $html .= $icon;
                    $html .= '</a>';
                }
            }
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }


     public function add_admin_menu() {
        add_options_page(
            'Social Share Buttons Settings',
            'Social Share Buttons',
            'manage_options',
            'ssb-settings',
            array($this, 'render_settings_page')
        );
    }
 public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }

        $settings = get_option('ssb_settings');
        
        if (!is_array($settings)) {
            $settings = array(
                'position' => 'bottom',
                'platforms' => array('facebook', 'twitter', 'linkedin', 'whatsapp', 'email', 'copy')
            );
        }

        $position = isset($settings['position']) ? $settings['position'] : 'bottom';
        $platforms = isset($settings['platforms']) && is_array($settings['platforms']) ? $settings['platforms'] : array();

        $available_platforms = array(
            'facebook' => 'Facebook',
            'twitter' => 'Twitter/X',
            'linkedin' => 'LinkedIn',
            'pinterest' => 'Pinterest',
            'whatsapp' => 'WhatsApp',
            'email' => 'Email',
            'copy' => 'Copy Link'
        );

        ?>
        <div class="wrap">
            <h1><?php echo esc_html('Social Share Buttons Settings'); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('ssb_group'); ?>

                <table class="form-table">
                    <!-- Button Position Setting -->
                    <tr>
                        <th scope="row"><label for="ssb_position">Button Position</label></th>
                        <td>
                            <select id="ssb_position" name="ssb_settings[position]">
                                <option value="top" <?php selected($position, 'top'); ?>>Before Content</option>
                                <option value="bottom" <?php selected($position, 'bottom'); ?>>After Content</option>
                                <option value="both" <?php selected($position, 'both'); ?>>Before & After Content</option>
                            </select>
                            <p class="description">Where to display the share buttons on your posts</p>
                        </td>
                    </tr>

                    <!-- Platform Selection -->
                    <tr>
                        <th scope="row"><label>Select Platforms</label></th>
                        <td>
                            <?php foreach ($available_platforms as $key => $label) : ?>
                                <label style="display: block; margin-bottom: 8px;">
                                    <input type="checkbox" name="ssb_settings[platforms][]" value="<?php echo esc_attr($key); ?>" 
                                        <?php echo in_array($key, $platforms, true) ? 'checked' : ''; ?>>
                                    <?php echo esc_html($label); ?>
                                </label>
                            <?php endforeach; ?>
                            <p class="description">Choose which platforms to display</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }


      public function register_settings() {
        register_setting('ssb_group', 'ssb_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));
    }

     public function sanitize_settings($input) {
        if (!is_array($input)) {
            return array();
        }

        $sanitized = array();
        
        if (isset($input['position'])) {
            $sanitized['position'] = sanitize_text_field($input['position']);
        }
        
        if (isset($input['platforms']) && is_array($input['platforms'])) {
            $sanitized['platforms'] = array_map('sanitize_text_field', $input['platforms']);
        }
        
        return $sanitized;
    }

    function ssb_activate() {
    $default_settings = array(
        'position' => 'bottom',
        'platforms' => array('facebook', 'twitter', 'linkedin', 'whatsapp', 'email', 'copy')
    );
    
    if (!get_option('ssb_settings')) {
        add_option('ssb_settings', $default_settings);
    }
}
}


new Social_Share_Buttons();
