<?php
 /*
 * Plugin Name:       Daily Quran Verse
 * Plugin URI:        https://bdislamicqa.xyz/wordpress-plugin/
 * Description:       The Daily Quran Verse plugin brings the timeless wisdom of the Quran right to your WordPress website. Use the [daily_quran_verse] shortcode in your pages, posts, or widgets to display the verse.

 * Version:           1.0
 * Author:            Moursalin islam 
 * Author URI:        https://www.facebook.com/morsalinislam.bd
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://bdislamicqa.xyz/wordpress-plugin/
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

// Get a daily verse based on the current date
function get_daily_quran_verse() {
    $verses = load_quran_verses();
    if (!empty($verses)) {
        $day_of_year = date('z'); // Day of the year (0-365)
        $index = $day_of_year % count($verses);
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
function display_daily_quran_verse() {
    $verse_data = get_daily_quran_verse();
    if ($verse_data) {
        return '<div class="daily-quran-verse">
                    <blockquote>"' . esc_html($verse_data['verse']) . '"</blockquote>
                    <p><em>' . esc_html($verse_data['reference']) . '</em></p>
                </div>';
    }
    return '<div class="daily-quran-verse">No verses available.</div>';
}

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
        plugins_url('style.css', __FILE__), // Path to the CSS file
        array(), // Dependencies
        '1.0.0' // Version number
    );
}
add_action('wp_enqueue_scripts', 'enqueue_quran_verse_styles');
