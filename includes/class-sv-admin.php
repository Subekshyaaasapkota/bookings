<?php
if (!defined('ABSPATH')) {
    exit;
}

class SV_Bookings_Admin {

    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'register_admin_menus'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_admin_assets'));
    }

    public static function register_admin_menus() {
        $stats = SV_Bookings_Database::get_stats();
        $pending_count = $stats['pending_bookings'];
        $badge = $pending_count > 0 ? sprintf(' <span class="update-plugins count-%d"><span class="plugin-count">%d</span></span>', $pending_count, $pending_count) : '';

        // Main Menu
        add_menu_page(
            __('SV Bookings', 'svbookings'),
            __('Bookings', 'svbookings') . $badge,
            'manage_options',
            'sv-bookings',
            array(__CLASS__, 'render_forms_list_page'),
            'dashicons-calendar-alt',
            26
        );

        // Submenu: Forms List
        add_submenu_page(
            'sv-bookings',
            __('All Booking Forms', 'svbookings'),
            __('All Forms', 'svbookings'),
            'manage_options',
            'sv-bookings',
            array(__CLASS__, 'render_forms_list_page')
        );

        // Submenu: Add New Form (Builder)
        add_submenu_page(
            'sv-bookings',
            __('Create Booking Form', 'svbookings'),
            __('Add New Form', 'svbookings'),
            'manage_options',
            'sv-bookings-form-builder',
            array(__CLASS__, 'render_form_builder_page')
        );

        // Submenu: Bookings / Submissions
        add_submenu_page(
            'sv-bookings',
            __('Bookings List', 'svbookings'),
            __('All Bookings', 'svbookings') . $badge,
            'manage_options',
            'sv-bookings-entries',
            array(__CLASS__, 'render_bookings_list_page')
        );
    }

    public static function enqueue_admin_assets($hook) {
        if (strpos($hook, 'sv-bookings') === false) {
            return;
        }

        // Font Awesome / Dashicons / Google Fonts
        wp_enqueue_style('sv-google-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap', array(), null);
        wp_enqueue_style('sv-admin-css', SV_BOOKINGS_PLUGIN_URL . 'assets/css/admin-style.css', array(), SV_BOOKINGS_VERSION);
        wp_enqueue_style('sv-frontend-css', SV_BOOKINGS_PLUGIN_URL . 'assets/css/frontend-style.css', array(), SV_BOOKINGS_VERSION);

        // Flatpickr for date & time picking in admin
        wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), '4.6.13');
        wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), '4.6.13', true);

        // Sortable JS for drag-and-drop field sorting in builder
        wp_enqueue_script('sortable-js', 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js', array(), '1.15.0', true);

        // Admin Custom JS
        wp_enqueue_script('sv-admin-builder-js', SV_BOOKINGS_PLUGIN_URL . 'assets/js/admin-builder.js', array('jquery', 'sortable-js', 'flatpickr-js'), SV_BOOKINGS_VERSION, true);
        wp_enqueue_script('sv-admin-bookings-js', SV_BOOKINGS_PLUGIN_URL . 'assets/js/admin-bookings.js', array('jquery'), SV_BOOKINGS_VERSION, true);

        wp_localize_script('sv-admin-builder-js', 'svBookingsAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sv_bookings_admin_nonce'),
            'pluginUrl' => SV_BOOKINGS_PLUGIN_URL,
            'strings' => [
                'confirmDeleteForm' => __('Are you sure you want to delete this form and all its booking records? This cannot be undone.', 'svbookings'),
                'confirmDeleteBooking' => __('Are you sure you want to delete this booking entry?', 'svbookings'),
                'formSaved' => __('Form saved successfully!', 'svbookings'),
                'copied' => __('Shortcode copied to clipboard!', 'svbookings'),
                'fieldDeleted' => __('Field removed.', 'svbookings'),
                'unsavedWarning' => __('You have unsaved changes in the form builder. Are you sure you want to leave?', 'svbookings')
            ]
        ]);
    }

    public static function render_forms_list_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        if ($action === 'edit' || $action === 'new') {
            self::render_form_builder_page();
            return;
        }

        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $forms = SV_Bookings_Database::get_forms(['search' => $search, 'status' => $status]);
        $stats = SV_Bookings_Database::get_stats();

        include SV_BOOKINGS_PLUGIN_DIR . 'templates/admin/forms-list.php';
    }

    public static function render_form_builder_page() {
        $form_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $form = null;
        if ($form_id > 0) {
            $form = SV_Bookings_Database::get_form($form_id);
        }

        include SV_BOOKINGS_PLUGIN_DIR . 'templates/admin/form-builder.php';
    }

    public static function render_bookings_list_page() {
        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

        $bookings = SV_Bookings_Database::get_bookings([
            'form_id' => $form_id,
            'status' => $status,
            'search' => $search,
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);

        $all_forms = SV_Bookings_Database::get_forms();
        $stats = SV_Bookings_Database::get_stats();

        include SV_BOOKINGS_PLUGIN_DIR . 'templates/admin/bookings-list.php';
    }
}
