<?php
if (!defined('ABSPATH')) {
    exit;
}

class SV_Bookings_Frontend {

    public static function init() {
        add_shortcode('sv_booking_form', array(__CLASS__, 'render_booking_form_shortcode'));
        add_shortcode('sv_bookings', array(__CLASS__, 'render_booking_form_shortcode')); // Alias
        add_shortcode('booking_form', array(__CLASS__, 'render_booking_form_shortcode')); // Alias
        add_shortcode('sv_booking_form_builder', array(__CLASS__, 'render_client_builder_shortcode'));
        add_shortcode('sv_my_bookings', array(__CLASS__, 'render_client_bookings_shortcode'));

        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_frontend_assets'));
    }

    public static function enqueue_frontend_assets() {
        // Enqueue fonts & styles
        wp_enqueue_style('sv-google-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', array(), null);
        wp_enqueue_style('sv-frontend-css', SV_BOOKINGS_PLUGIN_URL . 'assets/css/frontend-style.css', array(), SV_BOOKINGS_VERSION);

        // Flatpickr for booking dates
        wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), '4.6.13');
        wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), '4.6.13', true);

        // Sortable JS if builder is loaded on frontend
        wp_enqueue_script('sortable-js', 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js', array(), '1.15.0', true);

        // Frontend JS
        wp_enqueue_script('sv-frontend-form-js', SV_BOOKINGS_PLUGIN_URL . 'assets/js/frontend-form.js', array('jquery', 'flatpickr-js'), SV_BOOKINGS_VERSION, true);
        wp_enqueue_script('sv-frontend-builder-js', SV_BOOKINGS_PLUGIN_URL . 'assets/js/frontend-builder.js', array('jquery', 'sortable-js'), SV_BOOKINGS_VERSION, true);

        wp_localize_script('sv-frontend-form-js', 'svBookingsFrontend', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sv_bookings_frontend_nonce'),
            'strings' => [
                'loadingSlots' => __('Checking available time slots...', 'svbookings'),
                'selectDateFirst' => __('Please select a date to view available appointment slots.', 'svbookings'),
                'noSlots' => __('No available slots found for this date. Please choose another day.', 'svbookings'),
                'submitting' => __('Processing your booking...', 'svbookings'),
                'requiredMissing' => __('Please complete all required fields.', 'svbookings'),
                'selectSlot' => __('Please select a time slot to continue.', 'svbookings'),
                'copied' => __('Copied to clipboard!', 'svbookings')
            ]
        ]);
    }

    public static function render_booking_form_shortcode($atts) {
        $atts = shortcode_atts([
            'id' => 0,
            'theme' => 'light'
        ], $atts, 'sv_booking_form');

        $form_id = intval($atts['id']);
        $form = null;

        if ($form_id > 0) {
            $form = SV_Bookings_Database::get_form($form_id);
        }

        // If ID wasn't provided or specific ID wasn't found, fallback to first active form
        if (!$form) {
            $forms = SV_Bookings_Database::get_forms(['status' => 'active']);
            if (!empty($forms)) {
                $form = $forms[0];
            } else {
                $all_forms = SV_Bookings_Database::get_forms();
                if (!empty($all_forms)) {
                    $form = $all_forms[0];
                }
            }
        }

        if (!$form) {
            return '<div class="sv-booking-container" style="text-align:center;padding:40px 20px;"><h3 style="margin-bottom:8px;">' . esc_html__('No Booking Form Found', 'svbookings') . '</h3><p style="color:#64748b;">' . esc_html__('Please create a form in WP Admin &gt; Bookings &gt; Add New Form.', 'svbookings') . '</p></div>';
        }

        $fields = json_decode($form->fields, true) ?: [];
        $settings = json_decode($form->settings, true) ?: [];

        ob_start();
        include SV_BOOKINGS_PLUGIN_DIR . 'templates/frontend/booking-form.php';
        return ob_get_clean();
    }

    public static function render_client_builder_shortcode($atts) {
        $atts = shortcode_atts([
            'allow_guest' => 'yes',
        ], $atts, 'sv_booking_form_builder');

        ob_start();
        include SV_BOOKINGS_PLUGIN_DIR . 'templates/frontend/client-form-builder.php';
        return ob_get_clean();
    }

    public static function render_client_bookings_shortcode($atts) {
        ob_start();
        include SV_BOOKINGS_PLUGIN_DIR . 'templates/frontend/client-booking-history.php';
        return ob_get_clean();
    }
}
