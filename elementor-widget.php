<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Check if Elementor is loaded and register the widget
function register_quran_verse_elementor_widget() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        return; // Ensure Elementor is loaded before registering the widget
    }

    class Elementor_Quran_Verse_Widget extends \Elementor\Widget_Base {
        
        // Define the widget name, title, and category
        public function get_name() {
            return 'daily_quran_verse';
        }

        public function get_title() {
            return __('Daily Quran Verse', 'text-domain');
        }

       // The icon for the widget
    public function get_icon() {
        return 'fa fa-book';  // This is an example of Font Awesome icon
        // OR
        // return 'dashicons-book'; // This would use Dashicons
    }

        public function get_categories() {
            return ['general']; // General category in the Elementor panel
        }

        // Render the widget content on the frontend
        protected function render() {
            echo do_shortcode('[quran_verse_widget]');
        }
    }

    // Register the widget with Elementor
    \Elementor\Plugin::instance()->widgets_manager->register( new \Elementor_Quran_Verse_Widget() );
}

// Hook the widget registration function after Elementor is loaded
add_action('elementor/widgets/register', 'register_quran_verse_elementor_widget');
