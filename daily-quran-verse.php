<?php
/*
 * Plugin Name:       Daily Quran Verse
 * Plugin URI:        https://github.com/moursalinislambd/daily-quran-verse/releases
 * Description:       Daily Quran Verse - Inspire Your Day with Divine Wisdom | Use Shortcode [daily_quran_verse] anywhere on your site to display the verse. Also Support Elementor Widget & Gutenberg / Sidebar Widget.
 * Version:           1.0.2
 * Author:            Moursalin Islam
 * Author URI:        https://www.facebook.com/morsalinislam.bd
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       daily-quran-verse
 * Domain Path:       /languages
 * Tags: islamic,quran,verse,quran verse, daily quran, Bangladesh
 * Requires at least: 5.0
 * Tested up to:      6.0
 * Requires PHP:      7.0
 * Update URI:        https://mosquesofbangladesh.xyz/post-category/wp-plugin/
 * ------------------------------------------------------------------------
 * This plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this plugin. If not, see <https://www.gnu.org/licenses/>.
 * ------------------------------------------------------------------------
 */

// Load the verses from the JSON file
function load_quran_verses() {
    $file_path = plugin_dir_path(__FILE__) . 'quran-verses.json';
    if (file_exists($file_path)) {
        $json_data = file_get_contents($file_path);
        return json_decode($json_data, true);
    }
    return [];
}

// Get a random verse from the loaded verses
function get_daily_quran_verse() {
    $verses = load_quran_verses();
    if (!empty($verses)) {
        // Use a random index to select a verse
        $index = array_rand($verses); // Get a random index
        return $verses[$index];
    }
    return null;
}

// Shortcode to display the daily verse as a widget in posts or pages
function display_quran_verse_widget_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'title' => '' // Default empty title
        ),
        $atts,
        'quran_verse_widget'
    );

    ob_start();
    if (!empty($atts['title'])) {
        echo '<h3>' . esc_html($atts['title']) . '</h3>';
    }
    the_widget('Daily_Quran_Verse_Widget');
    return ob_get_clean();
}

add_shortcode('quran_verse_widget', 'display_quran_verse_widget_shortcode');

// Widget to display daily Quran verse in the sidebar
class Daily_Quran_Verse_Widget extends WP_Widget {
    
    // Constructor function
    public function __construct() {
        parent::__construct(
            'daily_quran_verse_widget',
            __('Daily Quran Verse', 'text_domain'),
            array('description' => __('Displays a daily Quran verse in the sidebar.', 'text_domain'))
        );
    }

    // Output the widget content on the front-end
    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        echo display_daily_quran_verse();
        echo $args['after_widget'];
    }

    // Widget form in the admin dashboard
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Daily Quran Verse', 'text_domain');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    // Update widget settings
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

// Register the widget
function register_daily_quran_verse_widget() {
    register_widget('Daily_Quran_Verse_Widget');
}
add_action('widgets_init', 'register_daily_quran_verse_widget');

// Shortcode to display the daily verse
if ( ! function_exists( 'display_daily_quran_verse' ) ) {
    function display_daily_quran_verse() {
        $verse_data = get_daily_quran_verse();
        if ($verse_data) {
            $verse_text = esc_html($verse_data['verse']);
            $verse_reference = esc_html($verse_data['reference']);
            $share_url = urlencode(get_permalink());
            $share_text = urlencode($verse_text . ' - ' . $verse_reference);
            return '<div class="dqv-container">
                        <blockquote id="dqv-quran-verse-text">' . $verse_text . '</blockquote>
                        <p><em>' . $verse_reference . '</em></p>
                        <button id="dqv-copy-verse-button" onclick="copyQuranVerse()">Copy</button>
                        <div class="dqv-share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=' . $share_url . '&quote=' . $share_text . '" target="_blank" class="dqv-share-button facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://twitter.com/intent/tweet?text=' . $share_text . '&url=' . $share_url . '" target="_blank" class="dqv-share-button twitter"><i class="fab fa-twitter"></i></a>
                            <a href="https://api.whatsapp.com/send?text=' . $share_text . '%20' . $share_url . '" target="_blank" class="dqv-share-button whatsapp"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>';
        }
        return '<div class="dqv-container">No verses available.</div>';
    }
}
add_shortcode('daily_quran_verse', 'display_daily_quran_verse');

// Gutenberg Block
function enqueue_quran_verse_block_assets() {
    wp_enqueue_script(
        'quran-verse-block',
        plugins_url('block.js', __FILE__),
        array('wp-blocks', 'wp-element'),
        filemtime(plugin_dir_path(__FILE__) . 'block.js')
    );
}
add_action('enqueue_block_editor_assets', 'enqueue_quran_verse_block_assets');

// Include Elementor widget support if Elementor is active
if ( defined( 'ELEMENTOR_VERSION' ) ) {
    include_once plugin_dir_path(__FILE__) . 'elementor-widget.php';
}

add_action('plugins_loaded', function() {
    if ( defined('ELEMENTOR_VERSION') ) {
        include_once plugin_dir_path(__FILE__) . 'elementor-widget.php';
    }
});

// Enqueue the plugin's CSS file
function enqueue_quran_verse_styles() {
    wp_enqueue_style(
        'daily-quran-verse-style', // Unique handle
        plugins_url('assets/css/daily-quran-verse.css', __FILE__), // Path to the CSS file
        array(), // Dependencies
        '1.0.2' // Version number
    );
}
add_action('wp_enqueue_scripts', 'enqueue_quran_verse_styles');

// Font analyzer
function my_plugin_enqueue_fonts() {
    // Enqueue Bensen Handwriting font
    wp_enqueue_style(
        'bensen-handwriting-font', 
        'https://fonts.maateen.me/bensen-handwriting/font.css', 
        [], 
        null 
    );
}
add_action('wp_enqueue_scripts', 'my_plugin_enqueue_fonts');

// Enqueue Font Awesome
function dqv_enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'dqv_enqueue_font_awesome');

// Enqueue plugin styles and scripts
function dqv_enqueue_scripts() {
    wp_enqueue_style('daily-quran-verse-style', plugin_dir_url(__FILE__) . 'assets/css/daily-quran-verse.css');
    wp_enqueue_script('daily-quran-verse-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), null, true);
    wp_enqueue_script('copy-verse-script', plugin_dir_url(__FILE__) . 'assets/js/copy-verse.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'dqv_enqueue_scripts');

// Add custom link to plugin action links
function dqv_add_custom_plugin_links($links) {
    $custom_link = '<a href="https://github.com/moursalinislambd/daily-quran-verse?tab=readme-ov-file#documentation" target="_blank"> Documentation </a>';
    array_push($links, $custom_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'dqv_add_custom_plugin_links');

// Function to check for plugin updates
function check_for_plugin_update() {
    // URL to the JSON file
    $json_url = 'https://mosquesofbangladesh.xyz/upjs/dqv-plugin-update.json';

    // Fetch the JSON file
    $response = wp_remote_get($json_url);

    // Check for errors
    if (is_wp_error($response)) {
        return;
    }

    // Decode the JSON response
    $update_data = json_decode(wp_remote_retrieve_body($response), true);

    // Check if the version is set in the JSON file
    if (isset($update_data['version'])) {
        // Get the current plugin version
        $current_version = '1.0.1'; // Replace with your plugin's current version

        // Compare versions
        if (version_compare($current_version, $update_data['version'], '<')) {
            // Display update notice after the plugin name
            add_action('after_plugin_row_' . plugin_basename(__FILE__), function() use ($update_data) {
                echo '<tr class="plugin-update-tr">';
                echo '<td colspan="3" class="plugin-update colspanchange">';
                echo '<div class="update-message notice inline notice-warning notice-alt">';
                echo '<p>There is a new version of the Daily Quran Verse plugin available. <a href="' . esc_url($update_data['download_url']) . '">Download version ' . esc_html($update_data['version']) . '</a> now.</p>';
                if (isset($update_data['changelog'])) {
                    echo '<p>Changelog: ' . esc_html($update_data['changelog']) . '</p>';
                }
                if (isset($update_data['release_date'])) {
                    echo '<p>Release Date: ' . esc_html($update_data['release_date']) . '</p>';
                }
                if (isset($update_data['author'])) {
                    echo '<p>Author: ' . esc_html($update_data['author']) . '</p>';
                }
                if (isset($update_data['additional_info'])) {
                    echo '<p>Additional Info: ' . esc_html($update_data['additional_info']) . '</p>';
                }
                echo '</div>';
                echo '</td>';
                echo '</tr>';
            });
        }
    }
}
add_action('admin_init', 'check_for_plugin_update');

// WPBakery Page Builder Integration
function dqv_register_wpbakery_element() {
    if (function_exists('vc_map')) {
        vc_map(array(
            'name' => __('Daily Quran Verse', 'text_domain'),
            'base' => 'daily_quran_verse',
            'class' => '',
            'category' => __('Content', 'text_domain'),
            'params' => array(
                array(
                    'type' => 'textfield',
                    'heading' => __('Title', 'text_domain'),
                    'param_name' => 'title',
                    'description' => __('Enter the title for the Quran verse widget.', 'text_domain')
                )
            )
        ));
    }
}
add_action('vc_before_init', 'dqv_register_wpbakery_element');

// Add the dashboard widget
function dqv_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'dqv_dashboard_widget', // Widget slug
        'Daily Quran Verse', // Title
        'dqv_display_dashboard_widget' // Display function
    );
}
add_action('wp_dashboard_setup', 'dqv_add_dashboard_widget');

// Display the dashboard widget content
function dqv_display_dashboard_widget() {
    $verse_data = get_daily_quran_verse();
    if ($verse_data) {
        $verse_text = esc_html($verse_data['verse']);
        $verse_reference = esc_html($verse_data['reference']);
        echo '<div class="dqv-dashboard-widget">';
        echo '<blockquote>' . $verse_text . '</blockquote>';
        echo '<p><em>' . $verse_reference . '</em></p>';
        echo '</div>';
    } else {
        echo '<p>No verses available.</p>';
    }
}

// Add the feedback form to the admin menu
function dqv_add_feedback_menu() {
    add_menu_page(
        'Feedback | Daily Quran Verse Plugin', // Page title
        'Feedback | DVQ', // Menu title
        'manage_options', // Capability
        'dqv-feedback', // Menu slug
        'dqv_display_feedback_form', // Display function
        'dashicons-feedback', // Icon
        100 // Position
    );
}
add_action('admin_menu', 'dqv_add_feedback_menu');

// Display the feedback form
function dqv_display_feedback_form() {
    ?>
    <div class="wrap">
        <h1>Feedback</h1>
        <form method="post" action="options.php">
            <?php settings_fields('dqv_feedback_options_group'); ?>
            <?php do_settings_sections('dqv-feedback'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register the feedback form settings
function dqv_register_feedback_settings() {
    register_setting('dqv_feedback_options_group', 'dqv_feedback_options', 'dqv_feedback_options_validate');
    add_settings_section('dqv_feedback_main', 'Feedback Form', 'dqv_feedback_section_text', 'dqv-feedback');
    add_settings_field('dqv_feedback_email', 'Your Email', 'dqv_feedback_email_input', 'dqv-feedback', 'dqv_feedback_main');
    add_settings_field('dqv_feedback_message', 'Your Feedback', 'dqv_feedback_message_input', 'dqv-feedback', 'dqv_feedback_main');
}
add_action('admin_init', 'dqv_register_feedback_settings');

// Display the feedback section text
function dqv_feedback_section_text() {
    echo '<p>Please provide your feedback below:</p>';
}

// Display the email input field
function dqv_feedback_email_input() {
    $options = get_option('dqv_feedback_options');
    echo '<input id="dqv_feedback_email" name="dqv_feedback_options[email]" type="email" value="' . esc_attr($options['email']) . '" />';
}

// Display the message input field
function dqv_feedback_message_input() {
    $options = get_option('dqv_feedback_options');
    echo '<textarea id="dqv_feedback_message" name="dqv_feedback_options[message]" rows="5" cols="50">' . esc_textarea($options['message']) . '</textarea>';
}

// Validate the feedback form input
function dqv_feedback_options_validate($input) {
    $new_input = array();
    if (isset($input['email'])) {
        $new_input['email'] = sanitize_email($input['email']);
    }
    if (isset($input['message'])) {
        $new_input['message'] = sanitize_textarea_field($input['message']);
    }
    return $new_input;
}

// Handle feedback form submission
function dqv_handle_feedback_submission() {
    if (isset($_POST['dqv_feedback_options'])) {
        $options = get_option('dqv_feedback_options');
        $email = sanitize_email($_POST['dqv_feedback_options']['email']);
        $message = sanitize_textarea_field($_POST['dqv_feedback_options']['message']);

        // Send the feedback email
        $to = 'dev.onexusdev@gmail.com'; // Replace with your email address
        $subject = 'New Feedback from Daily Quran Verse Plugin';
        $body = 'Email: ' . $email . "\n\n" . 'Message: ' . $message;
        $headers = array('Content-Type: text/plain; charset=UTF-8');

        wp_mail($to, $subject, $body, $headers);

        // Display a success message
        add_settings_error('dqv_feedback_options', 'dqv_feedback_message', 'Thank you for your feedback!', 'updated');
    }
}
add_action('admin_notices', 'dqv_handle_feedback_submission');

// Enqueue admin styles
function dqv_enqueue_admin_styles() {
    wp_enqueue_style('dqv-admin-styles', plugins_url('assets/css/admin-styles.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'dqv_enqueue_admin_styles');
?>
