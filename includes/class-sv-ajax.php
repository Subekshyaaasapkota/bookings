<?php
if (!defined('ABSPATH')) {
    exit;
}

class SV_Bookings_Ajax {

    public static function init() {
        // Admin & Builder AJAX
        add_action('wp_ajax_sv_save_form', array(__CLASS__, 'save_form'));
        add_action('wp_ajax_nopriv_sv_save_form', array(__CLASS__, 'save_form_client')); // For client interface form builder if allowed
        add_action('wp_ajax_sv_delete_form', array(__CLASS__, 'delete_form'));
        add_action('wp_ajax_sv_duplicate_form', array(__CLASS__, 'duplicate_form'));
        add_action('wp_ajax_sv_get_form', array(__CLASS__, 'get_form'));
        add_action('wp_ajax_nopriv_sv_get_form', array(__CLASS__, 'get_form'));

        // Booking submission & slots
        add_action('wp_ajax_sv_get_available_slots', array(__CLASS__, 'get_available_slots'));
        add_action('wp_ajax_nopriv_sv_get_available_slots', array(__CLASS__, 'get_available_slots'));
        add_action('wp_ajax_sv_submit_booking', array(__CLASS__, 'submit_booking'));
        add_action('wp_ajax_nopriv_sv_submit_booking', array(__CLASS__, 'submit_booking'));

        // Admin bookings actions
        add_action('wp_ajax_sv_update_booking_status', array(__CLASS__, 'update_booking_status'));
        add_action('wp_ajax_sv_update_booking_appointment', array(__CLASS__, 'update_booking_appointment'));
        add_action('wp_ajax_sv_delete_booking', array(__CLASS__, 'delete_booking'));
        add_action('wp_ajax_sv_get_booking_details', array(__CLASS__, 'get_booking_details'));
        add_action('wp_ajax_sv_export_bookings_csv', array(__CLASS__, 'export_bookings_csv'));
    }

    public static function save_form() {
        check_ajax_referer('sv_bookings_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'svbookings')]);
        }

        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'active';
        $settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '{}';
        $fields = isset($_POST['fields']) ? wp_unslash($_POST['fields']) : '[]';

        if (empty($title)) {
            wp_send_json_error(['message' => __('Form title is required.', 'svbookings')]);
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'settings' => $settings,
            'fields' => $fields,
        ];

        $saved_id = SV_Bookings_Database::save_form($data, $form_id);

        if ($saved_id) {
            wp_send_json_success([
                'message' => __('Form saved successfully!', 'svbookings'),
                'form_id' => $saved_id,
                'redirect' => admin_url('admin.php?page=sv-bookings-forms&action=edit&id=' . $saved_id)
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to save form. Please try again.', 'svbookings')]);
        }
    }

    public static function save_form_client() {
        check_ajax_referer('sv_bookings_frontend_nonce', 'nonce');

        // Allow client/guest form builder submissions if enabled or authenticated
        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
        $status = 'active';
        $settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '{}';
        $fields = isset($_POST['fields']) ? wp_unslash($_POST['fields']) : '[]';

        if (empty($title)) {
            wp_send_json_error(['message' => __('Form title is required.', 'svbookings')]);
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'settings' => $settings,
            'fields' => $fields,
        ];

        // If not logged in as admin, only create new form
        if (!current_user_can('manage_options')) {
            $form_id = 0;
        }

        $saved_id = SV_Bookings_Database::save_form($data, $form_id);

        if ($saved_id) {
            wp_send_json_success([
                'message' => __('Form created successfully!', 'svbookings'),
                'form_id' => $saved_id,
                'shortcode' => '[sv_booking_form id="' . $saved_id . '"]'
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to create form.', 'svbookings')]);
        }
    }

    public static function delete_form() {
        check_ajax_referer('sv_bookings_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'svbookings')]);
        }

        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        if ($form_id <= 0) {
            wp_send_json_error(['message' => __('Invalid form ID.', 'svbookings')]);
        }

        SV_Bookings_Database::delete_form($form_id);
        wp_send_json_success(['message' => __('Form deleted successfully.', 'svbookings')]);
    }

    public static function duplicate_form() {
        check_ajax_referer('sv_bookings_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'svbookings')]);
        }

        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        $new_id = SV_Bookings_Database::duplicate_form($form_id);

        if ($new_id) {
            wp_send_json_success([
                'message' => __('Form duplicated successfully.', 'svbookings'),
                'new_id' => $new_id
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to duplicate form.', 'svbookings')]);
        }
    }

    public static function get_form() {
        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
        if (!$form_id) {
            wp_send_json_error(['message' => 'Form ID is required']);
        }

        $form = SV_Bookings_Database::get_form($form_id);
        if (!$form) {
            wp_send_json_error(['message' => 'Form not found']);
        }

        $form->settings = json_decode($form->settings, true);
        $form->fields = json_decode($form->fields, true);

        wp_send_json_success($form);
    }

    public static function get_available_slots() {
        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
        $date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';

        if (!$form_id || !$date) {
            wp_send_json_error(['message' => __('Invalid parameters', 'svbookings')]);
        }

        $form = SV_Bookings_Database::get_form($form_id);
        if (!$form) {
            wp_send_json_error(['message' => __('Form not found', 'svbookings')]);
        }

        $settings = json_decode($form->settings, true) ?: [];
        $day_of_week = strtolower(date('l', strtotime($date)));
        $available_days = isset($settings['available_days']) ? (array)$settings['available_days'] : ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        if (!in_array($day_of_week, $available_days)) {
            wp_send_json_success([
                'available' => false,
                'message' => __('No appointments available on this day of the week.', 'svbookings'),
                'slots' => []
            ]);
        }

        $start_time = !empty($settings['business_hours_start']) ? $settings['business_hours_start'] : '09:00';
        $end_time = !empty($settings['business_hours_end']) ? $settings['business_hours_end'] : '17:00';
        $duration = !empty($settings['slot_duration']) ? intval($settings['slot_duration']) : 30;
        $max_bookings_per_slot = !empty($settings['max_bookings_per_slot']) ? intval($settings['max_bookings_per_slot']) : 1;

        // Fetch booked slots
        $booked_records = SV_Bookings_Database::get_booked_slots($form_id, $date);
        $booked_counts = [];
        foreach ($booked_records as $record) {
            $time_key = substr($record['booking_time_start'], 0, 5);
            $booked_counts[$time_key] = intval($record['booked_count']);
        }

        $slots = [];
        $current = strtotime($date . ' ' . $start_time);
        $end = strtotime($date . ' ' . $end_time);

        $is_today = ($date === date('Y-m-d'));
        $now = current_time('timestamp');

        while ($current + ($duration * 60) <= $end) {
            $slot_start = date('H:i', $current);
            $slot_end = date('H:i', $current + ($duration * 60));
            $formatted_label = date('g:i A', $current) . ' - ' . date('g:i A', $current + ($duration * 60));

            $booked_so_far = isset($booked_counts[$slot_start]) ? $booked_counts[$slot_start] : 0;
            $is_full = ($booked_so_far >= $max_bookings_per_slot);

            // If today and time has passed
            $slot_timestamp = $current;
            $is_past = ($is_today && $slot_timestamp <= $now);

            $slots[] = [
                'start' => $slot_start,
                'end' => $slot_end,
                'formatted' => $formatted_label,
                'available' => (!$is_full && !$is_past),
                'booked' => $booked_so_far,
                'capacity' => $max_bookings_per_slot
            ];

            $current += ($duration * 60);
        }

        wp_send_json_success([
            'available' => true,
            'slots' => $slots
        ]);
    }

    public static function submit_booking() {
        check_ajax_referer('sv_bookings_frontend_nonce', 'nonce');

        $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
        if (!$form_id) {
            wp_send_json_error(['message' => __('Invalid form submission.', 'svbookings')]);
        }

        $form = SV_Bookings_Database::get_form($form_id);
        if (!$form) {
            wp_send_json_error(['message' => __('Booking form does not exist.', 'svbookings')]);
        }

        $form_fields = json_decode($form->fields, true) ?: [];
        $form_settings = json_decode($form->settings, true) ?: [];

        $customer_name = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '';
        $customer_email = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '';
        $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '';
        $booking_date = isset($_POST['booking_date']) ? sanitize_text_field($_POST['booking_date']) : '';
        $booking_time_start = isset($_POST['booking_time_start']) ? sanitize_text_field($_POST['booking_time_start']) : '';
        $booking_time_end = isset($_POST['booking_time_end']) ? sanitize_text_field($_POST['booking_time_end']) : '';
        $service_name = isset($_POST['service_name']) ? sanitize_text_field($_POST['service_name']) : '';
        $service_price = isset($_POST['service_price']) ? floatval($_POST['service_price']) : 0.00;

        // Validation
        if (empty($customer_name)) {
            wp_send_json_error(['message' => __('Customer name is required.', 'svbookings')]);
        }
        if (empty($customer_email) || !is_email($customer_email)) {
            wp_send_json_error(['message' => __('A valid email address is required.', 'svbookings')]);
        }
        if (empty($booking_date)) {
            wp_send_json_error(['message' => __('Booking date is required.', 'svbookings')]);
        }

        // Calculate pricing add-ons if selected in custom fields
        $total_price = $service_price;
        foreach ($form_fields as $field) {
            $fname = $field['name'];
            if ($field['type'] === 'checkbox_group' && !empty($field['options']) && isset($_POST[$fname])) {
                $selected_vals = (array)$_POST[$fname];
                foreach ($field['options'] as $opt) {
                    if (in_array($opt['value'], $selected_vals) && !empty($opt['price'])) {
                        $total_price += floatval($opt['price']);
                    }
                }
            }
        }

        // Collect and humanize custom field responses
        $custom_data = [];
        $currency_sym = isset($form_settings['currency_symbol']) ? $form_settings['currency_symbol'] : '$';

        foreach ($form_fields as $field) {
            $fname = $field['name'];
            $flabel = !empty($field['label']) ? $field['label'] : $fname;

            // Skip top-level core columns and section divider labels
            if (in_array($fname, ['customer_name', 'customer_email', 'customer_phone', 'booking_schedule', 'service_type'], true) || $field['type'] === 'section') {
                continue;
            }

            if (isset($_POST[$fname]) && $_POST[$fname] !== '') {
                $raw_val = $_POST[$fname];

                if (is_array($raw_val)) {
                    $sanitized = array_map('sanitize_text_field', $raw_val);
                    $readable_vals = [];
                    foreach ($sanitized as $sv) {
                        $opt_text = $sv;
                        if (!empty($field['options'])) {
                            foreach ($field['options'] as $opt) {
                                if ((string)$opt['value'] === (string)$sv || (string)$opt['label'] === (string)$sv) {
                                    $price_suffix = (!empty($opt['price']) && floatval($opt['price']) > 0) ? ' (+' . $currency_sym . number_format(floatval($opt['price']), 2) . ')' : '';
                                    $opt_text = $opt['label'] . $price_suffix;
                                    break;
                                }
                            }
                        }
                        $readable_vals[] = $opt_text;
                    }
                    $custom_data[$flabel] = $readable_vals;
                } else {
                    $sanitized = sanitize_textarea_field($raw_val);
                    $readable_val = $sanitized;
                    if (!empty($field['options'])) {
                        foreach ($field['options'] as $opt) {
                            if ((string)$opt['value'] === (string)$sanitized || (string)$opt['label'] === (string)$sanitized) {
                                $price_suffix = (!empty($opt['price']) && floatval($opt['price']) > 0) ? ' (+' . $currency_sym . number_format(floatval($opt['price']), 2) . ')' : '';
                                $readable_val = $opt['label'] . $price_suffix;
                                break;
                            }
                        }
                    }
                    $custom_data[$flabel] = $readable_val;
                }
            }
        }

        $booking_payload = [
            'form_id' => $form_id,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'booking_date' => $booking_date,
            'booking_time_start' => $booking_time_start,
            'booking_time_end' => $booking_time_end,
            'service_name' => $service_name,
            'service_price' => $total_price,
            'status' => 'pending',
            'custom_data' => $custom_data,
            'admin_notes' => ''
        ];

        $created = SV_Bookings_Database::create_booking($booking_payload);

        if (!$created) {
            wp_send_json_error(['message' => __('Unable to process booking. Please try again.', 'svbookings')]);
        }

        // Send Email Notifications
        self::send_booking_emails($created['id'], $created['booking_ref'], $booking_payload, $form, $form_settings);

        $currency = isset($form_settings['currency_symbol']) ? $form_settings['currency_symbol'] : '$';
        $confirmation_message = !empty($form_settings['confirmation_message']) 
            ? $form_settings['confirmation_message'] 
            : __('Thank you! Your booking has been placed successfully.', 'svbookings');

        wp_send_json_success([
            'message' => $confirmation_message,
            'booking_ref' => $created['booking_ref'],
            'booking_id' => $created['id'],
            'details' => [
                'ref' => $created['booking_ref'],
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'service' => $service_name,
                'date' => date('F j, Y', strtotime($booking_date)),
                'time' => $booking_time_start ? date('g:i A', strtotime($booking_time_start)) . ' - ' . date('g:i A', strtotime($booking_time_end)) : 'All day',
                'price' => $currency . number_format($total_price, 2)
            ]
        ]);
    }

    private static function send_booking_emails($booking_id, $booking_ref, $data, $form, $settings) {
        $site_title = get_bloginfo('name');
        $admin_email = !empty($settings['admin_email_notify']) ? $settings['admin_email_notify'] : get_option('admin_email');
        $currency = isset($settings['currency_symbol']) ? $settings['currency_symbol'] : '$';

        $time_formatted = $data['booking_time_start'] ? date('g:i A', strtotime($data['booking_time_start'])) . ' - ' . date('g:i A', strtotime($data['booking_time_end'])) : 'Full Day';
        $date_formatted = date('F j, Y', strtotime($data['booking_date']));

        // Admin Email
        $admin_subject = sprintf(__('[%s] New Booking Received: #%s', 'svbookings'), $site_title, $booking_ref);
        $admin_body = "<h2>New Booking Request Received</h2>";
        $admin_body .= "<p><strong>Reference:</strong> " . esc_html($booking_ref) . "</p>";
        $admin_body .= "<p><strong>Form:</strong> " . esc_html($form->title) . "</p>";
        $admin_body .= "<p><strong>Customer:</strong> " . esc_html($data['customer_name']) . " (" . esc_html($data['customer_email']) . ")</p>";
        if (!empty($data['customer_phone'])) {
            $admin_body .= "<p><strong>Phone:</strong> " . esc_html($data['customer_phone']) . "</p>";
        }
        $admin_body .= "<p><strong>Date & Time:</strong> " . esc_html($date_formatted) . " at " . esc_html($time_formatted) . "</p>";
        if (!empty($data['service_name'])) {
            $admin_body .= "<p><strong>Service:</strong> " . esc_html($data['service_name']) . "</p>";
        }
        $admin_body .= "<p><strong>Total Price:</strong> " . esc_html($currency . number_format($data['service_price'], 2)) . "</p>";

        if (!empty($data['custom_data'])) {
            $admin_body .= "<h3>Selected Options & Custom Fields:</h3><ul>";
            foreach ($data['custom_data'] as $k => $v) {
                $val_display = is_array($v) ? implode(', ', $v) : $v;
                $admin_body .= "<li><strong>" . esc_html($k) . ":</strong> " . esc_html($val_display) . "</li>";
            }
            $admin_body .= "</ul>";
        }

        $admin_body .= "<p><a href='" . esc_url(admin_url('admin.php?page=sv-bookings-entries')) . "'>Manage this booking in Admin Dashboard</a></p>";

        $headers = array('Content-Type: text/html; charset=UTF-8');
        @wp_mail($admin_email, $admin_subject, $admin_body, $headers);

        // Customer Confirmation Email
        if (!empty($settings['enable_client_email'])) {
            $client_subject = sprintf(__('Booking Confirmation: #%s - %s', 'svbookings'), $booking_ref, $site_title);
            $client_body = "<h2>Hello " . esc_html($data['customer_name']) . ",</h2>";
            $client_body .= "<p>Thank you for booking with us. We have received your booking request:</p>";
            $client_body .= "<table style='width:100%;max-width:500px;border-collapse:collapse;'>";
            $client_body .= "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>Booking Ref:</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . esc_html($booking_ref) . "</td></tr>";
            $client_body .= "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>Service:</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . esc_html($data['service_name']) . "</td></tr>";
            $client_body .= "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>Date:</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . esc_html($date_formatted) . "</td></tr>";
            $client_body .= "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>Time:</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . esc_html($time_formatted) . "</td></tr>";
            $client_body .= "<tr><td style='padding:8px;border:1px solid #ddd;'><strong>Amount:</strong></td><td style='padding:8px;border:1px solid #ddd;'>" . esc_html($currency . number_format($data['service_price'], 2)) . "</td></tr>";
            $client_body .= "</table>";
            $client_body .= "<p>We will contact you shortly if any further details are required.</p>";

            @wp_mail($data['customer_email'], $client_subject, $client_body, $headers);
        }
    }

    public static function update_booking_appointment() {
        check_ajax_referer('sv_bookings_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'svbookings')]);
        }

        $booking_id        = isset($_POST['booking_id'])        ? intval($_POST['booking_id'])                    : 0;
        $booking_date      = isset($_POST['booking_date'])      ? sanitize_text_field($_POST['booking_date'])      : '';
        $booking_time_start = isset($_POST['booking_time_start']) ? sanitize_text_field($_POST['booking_time_start']) : '';
        $booking_time_end   = isset($_POST['booking_time_end'])   ? sanitize_text_field($_POST['booking_time_end'])   : '';

        if (!$booking_id || !$booking_date) {
            wp_send_json_error(['message' => __('Invalid parameters.', 'svbookings')]);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'sv_bookings';

        $result = $wpdb->update(
            $table,
            [
                'booking_date'       => $booking_date,
                'booking_time_start' => $booking_time_start ?: null,
                'booking_time_end'   => $booking_time_end   ?: null,
            ],
            ['id' => $booking_id],
            ['%s', '%s', '%s'],
            ['%d']
        );

        if ($result !== false) {
            wp_send_json_success([
                'message'      => __('Appointment updated successfully.', 'svbookings'),
                'booking_date' => $booking_date,
                'time_start'   => $booking_time_start,
                'time_end'     => $booking_time_end,
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to update appointment.', 'svbookings')]);
        }
    }

    public static function update_booking_status() {
        check_ajax_referer('sv_bookings_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'svbookings')]);
        }

        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'pending';
        $notes = isset($_POST['admin_notes']) ? sanitize_textarea_field($_POST['admin_notes']) : null;

        $updated = SV_Bookings_Database::update_booking_status($booking_id, $status, $notes);

        if ($updated !== false) {
            wp_send_json_success(['message' => __('Booking status updated successfully.', 'svbookings')]);
        } else {
            wp_send_json_error(['message' => __('Failed to update status.', 'svbookings')]);
        }
    }

    public static function delete_booking() {
        check_ajax_referer('sv_bookings_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'svbookings')]);
        }

        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        SV_Bookings_Database::delete_booking($booking_id);

        wp_send_json_success(['message' => __('Booking removed successfully.', 'svbookings')]);
    }

    public static function get_booking_details() {
        check_ajax_referer('sv_bookings_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'svbookings')]);
        }

        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        $booking = SV_Bookings_Database::get_booking($booking_id);

        if (!$booking) {
            wp_send_json_error(['message' => __('Booking not found.', 'svbookings')]);
        }

        $booking->custom_data = json_decode($booking->custom_data, true) ?: [];
        $booking->form_settings = json_decode($booking->form_settings, true) ?: [];

        // Resolve friendly field names and selected option labels for older or raw entries
        $form = SV_Bookings_Database::get_form($booking->form_id);
        $fields_map = [];
        $currency_sym = isset($booking->form_settings['currency_symbol']) ? $booking->form_settings['currency_symbol'] : '$';

        if ($form && !empty($form->fields)) {
            $form_fields = json_decode($form->fields, true) ?: [];
            foreach ($form_fields as $f) {
                if (!empty($f['name'])) {
                    $fields_map[$f['name']] = $f;
                }
                if (!empty($f['label'])) {
                    $fields_map[$f['label']] = $f;
                }
            }
        }

        $formatted_custom_data = [];
        foreach ($booking->custom_data as $k => $v) {
            $field_def = isset($fields_map[$k]) ? $fields_map[$k] : null;
            $display_key = ($field_def && !empty($field_def['label'])) ? $field_def['label'] : $k;

            if (is_array($v)) {
                $resolved_vals = [];
                foreach ($v as $sub_v) {
                    $resolved = $sub_v;
                    if ($field_def && !empty($field_def['options'])) {
                        foreach ($field_def['options'] as $opt) {
                            if ((string)$opt['value'] === (string)$sub_v || (string)$opt['label'] === (string)$sub_v) {
                                $price_suffix = (!empty($opt['price']) && floatval($opt['price']) > 0) ? ' (+' . $currency_sym . number_format(floatval($opt['price']), 2) . ')' : '';
                                $resolved = $opt['label'] . $price_suffix;
                                break;
                            }
                        }
                    }
                    $resolved_vals[] = $resolved;
                }
                $formatted_custom_data[$display_key] = $resolved_vals;
            } else {
                $resolved = $v;
                if ($field_def && !empty($field_def['options'])) {
                    foreach ($field_def['options'] as $opt) {
                        if ((string)$opt['value'] === (string)$v || (string)$opt['label'] === (string)$v) {
                            $price_suffix = (!empty($opt['price']) && floatval($opt['price']) > 0) ? ' (+' . $currency_sym . number_format(floatval($opt['price']), 2) . ')' : '';
                            $resolved = $opt['label'] . $price_suffix;
                            break;
                        }
                    }
                }
                $formatted_custom_data[$display_key] = $resolved;
            }
        }
        $booking->custom_data = $formatted_custom_data;

        // Format dates
        $booking->formatted_date = date('F j, Y', strtotime($booking->booking_date));
        $booking->formatted_time = $booking->booking_time_start ? date('g:i A', strtotime($booking->booking_time_start)) . ' - ' . date('g:i A', strtotime($booking->booking_time_end)) : 'N/A';
        $booking->formatted_created = date('M j, Y g:i A', strtotime($booking->created_at));

        wp_send_json_success($booking);
    }

    public static function export_bookings_csv() {
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied.');
        }

        $form_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

        $bookings = SV_Bookings_Database::get_bookings([
            'form_id' => $form_id,
            'status' => $status,
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bookings-export-' . date('Y-m-d-His') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Booking Ref', 'Form', 'Customer Name', 'Customer Email', 'Customer Phone', 'Date', 'Time Start', 'Time End', 'Service', 'Price', 'Status', 'Custom Fields', 'Created At']);

        foreach ($bookings as $b) {
            fputcsv($output, [
                $b->booking_ref,
                $b->form_title,
                $b->customer_name,
                $b->customer_email,
                $b->customer_phone,
                $b->booking_date,
                $b->booking_time_start,
                $b->booking_time_end,
                $b->service_name,
                $b->service_price,
                $b->status,
                $b->custom_data,
                $b->created_at
            ]);
        }

        fclose($output);
        exit;
    }
}
