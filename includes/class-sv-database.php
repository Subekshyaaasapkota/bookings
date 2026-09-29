<?php
if (!defined('ABSPATH')) {
    exit;
}

class SV_Bookings_Database {

    public static function init() {
        // Ensure tables exist and initial sample form is seeded
        self::create_tables();
        self::insert_sample_data();
    }

    public static function get_forms_table() {
        global $wpdb;
        return $wpdb->prefix . 'sv_booking_forms';
    }

    public static function get_bookings_table() {
        global $wpdb;
        return $wpdb->prefix . 'sv_bookings';
    }

    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $forms_table = self::get_forms_table();
        $bookings_table = self::get_bookings_table();

        $sql_forms = "CREATE TABLE IF NOT EXISTS $forms_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text DEFAULT '',
            status varchar(50) DEFAULT 'active',
            settings longtext DEFAULT NULL,
            fields longtext DEFAULT NULL,
            created_by bigint(20) unsigned DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY status (status)
        ) $charset_collate;";

        $sql_bookings = "CREATE TABLE IF NOT EXISTS $bookings_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_ref varchar(64) NOT NULL,
            form_id bigint(20) unsigned NOT NULL,
            customer_name varchar(255) NOT NULL,
            customer_email varchar(255) NOT NULL,
            customer_phone varchar(100) DEFAULT '',
            booking_date date NOT NULL,
            booking_time_start time DEFAULT NULL,
            booking_time_end time DEFAULT NULL,
            service_name varchar(255) DEFAULT '',
            service_price decimal(10,2) DEFAULT '0.00',
            status varchar(50) DEFAULT 'pending',
            custom_data longtext DEFAULT NULL,
            admin_notes text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY booking_ref (booking_ref),
            KEY form_id (form_id),
            KEY booking_date (booking_date),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_forms);
        dbDelta($sql_bookings);
    }

    public static function insert_sample_data() {
        global $wpdb;
        $forms_table = self::get_forms_table();
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $forms_table");

        if ($count == 0) {
            $sample_fields = [
                [
                    'id' => 'field_' . time() . '_1',
                    'type' => 'service',
                    'label' => 'Select Service',
                    'name' => 'service_type',
                    'placeholder' => 'Choose a service',
                    'required' => true,
                    'width' => '100',
                    'help_text' => 'Select the primary service for your booking.',
                    'options' => [
                        ['label' => 'Standard Consultation (30 min)', 'value' => 'consultation', 'price' => 50, 'duration' => 30],
                        ['label' => 'Comprehensive Strategy Session (60 min)', 'value' => 'strategy', 'price' => 120, 'duration' => 60],
                        ['label' => 'VIP Full Assessment (90 min)', 'value' => 'vip_assessment', 'price' => 180, 'duration' => 90]
                    ]
                ],
                [
                    'id' => 'field_' . time() . '_2',
                    'type' => 'datetime_picker',
                    'label' => 'Preferred Date & Time',
                    'name' => 'booking_schedule',
                    'placeholder' => 'Pick your slot',
                    'required' => true,
                    'width' => '100',
                    'help_text' => 'Choose your preferred date and available time slot.'
                ],
                [
                    'id' => 'field_' . time() . '_3',
                    'type' => 'text',
                    'label' => 'Full Name',
                    'name' => 'customer_name',
                    'placeholder' => 'John Doe',
                    'required' => true,
                    'width' => '50',
                    'help_text' => ''
                ],
                [
                    'id' => 'field_' . time() . '_4',
                    'type' => 'email',
                    'label' => 'Email Address',
                    'name' => 'customer_email',
                    'placeholder' => 'john@example.com',
                    'required' => true,
                    'width' => '50',
                    'help_text' => 'We will send your confirmation details here.'
                ],
                [
                    'id' => 'field_' . time() . '_5',
                    'type' => 'phone',
                    'label' => 'Phone Number',
                    'name' => 'customer_phone',
                    'placeholder' => '+1 (555) 000-0000',
                    'required' => true,
                    'width' => '50',
                    'help_text' => ''
                ],
                [
                    'id' => 'field_' . time() . '_6',
                    'type' => 'select',
                    'label' => 'Meeting Location Preference',
                    'name' => 'meeting_mode',
                    'placeholder' => 'Select preference',
                    'required' => false,
                    'width' => '50',
                    'options' => [
                        ['label' => 'Zoom / Google Meet (Online)', 'value' => 'online'],
                        ['label' => 'In-Person (Office Headquarters)', 'value' => 'office'],
                        ['label' => 'Phone Call', 'value' => 'phone']
                    ]
                ],
                [
                    'id' => 'field_' . time() . '_7',
                    'type' => 'textarea',
                    'label' => 'Project Details & Goals',
                    'name' => 'project_notes',
                    'placeholder' => 'Tell us briefly about what you would like to discuss...',
                    'required' => false,
                    'width' => '100',
                    'help_text' => 'Any specific agenda or details.'
                ],
                [
                    'id' => 'field_' . time() . '_8',
                    'type' => 'checkbox_group',
                    'label' => 'Add-on Options',
                    'name' => 'addons',
                    'required' => false,
                    'width' => '100',
                    'options' => [
                        ['label' => 'Priority Express Delivery (+$30)', 'value' => 'priority_express', 'price' => 30],
                        ['label' => 'Include Recorded Video Summary (+$20)', 'value' => 'video_recording', 'price' => 20],
                        ['label' => 'Dedicated Follow-up Checklist (+$15)', 'value' => 'followup_checklist', 'price' => 15]
                    ]
                ]
            ];

            $sample_settings = [
                'primary_color' => '#4f46e5',
                'accent_color' => '#6366f1',
                'currency_symbol' => '$',
                'slot_duration' => 30,
                'business_hours_start' => '09:00',
                'business_hours_end' => '17:00',
                'available_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                'confirmation_message' => 'Thank you! Your booking request has been received successfully. We look forward to meeting you.',
                'admin_email_notify' => get_option('admin_email'),
                'enable_client_email' => 1,
                'allow_client_edit' => 1,
                'max_bookings_per_slot' => 1
            ];

            $wpdb->insert(
                $forms_table,
                [
                    'title' => 'Consultation & Appointment Booking',
                    'slug' => 'consultation-appointment-booking',
                    'description' => 'Comprehensive client booking form with dynamic service options, custom fields, and real-time scheduling.',
                    'status' => 'active',
                    'settings' => json_encode($sample_settings),
                    'fields' => json_encode($sample_fields),
                    'created_by' => get_current_user_id() ? get_current_user_id() : 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ]
            );

            $form_id = $wpdb->insert_id;

            // Insert a couple of realistic demo bookings
            $bookings_table = self::get_bookings_table();
            $demo_bookings = [
                [
                    'booking_ref' => 'SVB-' . strtoupper(wp_generate_password(8, false)),
                    'form_id' => $form_id,
                    'customer_name' => 'Sophia Martinez',
                    'customer_email' => 'sophia.martinez@example.com',
                    'customer_phone' => '+1 (555) 234-5678',
                    'booking_date' => date('Y-m-d', strtotime('+2 days')),
                    'booking_time_start' => '10:00:00',
                    'booking_time_end' => '11:00:00',
                    'service_name' => 'Comprehensive Strategy Session (60 min)',
                    'service_price' => 120.00,
                    'status' => 'confirmed',
                    'custom_data' => json_encode([
                        'meeting_mode' => 'online',
                        'project_notes' => 'Looking to revamp our company portal and integrate online booking.',
                        'addons' => ['video_recording']
                    ]),
                    'admin_notes' => 'Client verified. Meeting link sent via Zoom.',
                    'created_at' => current_time('mysql')
                ],
                [
                    'booking_ref' => 'SVB-' . strtoupper(wp_generate_password(8, false)),
                    'form_id' => $form_id,
                    'customer_name' => 'Alexander Wright',
                    'customer_email' => 'alex.wright@example.com',
                    'customer_phone' => '+1 (555) 876-5432',
                    'booking_date' => date('Y-m-d', strtotime('+3 days')),
                    'booking_time_start' => '14:30:00',
                    'booking_time_end' => '15:00:00',
                    'service_name' => 'Standard Consultation (30 min)',
                    'service_price' => 50.00,
                    'status' => 'pending',
                    'custom_data' => json_encode([
                        'meeting_mode' => 'office',
                        'project_notes' => 'Brief introductory discussion for quarterly planning.',
                        'addons' => []
                    ]),
                    'admin_notes' => '',
                    'created_at' => current_time('mysql')
                ],
                [
                    'booking_ref' => 'SVB-' . strtoupper(wp_generate_password(8, false)),
                    'form_id' => $form_id,
                    'customer_name' => 'Elena Rostova',
                    'customer_email' => 'elena.rostova@example.com',
                    'customer_phone' => '+1 (555) 432-1098',
                    'booking_date' => date('Y-m-d', strtotime('+5 days')),
                    'booking_time_start' => '11:00:00',
                    'booking_time_end' => '12:30:00',
                    'service_name' => 'VIP Full Assessment (90 min)',
                    'service_price' => 210.00,
                    'status' => 'confirmed',
                    'custom_data' => json_encode([
                        'meeting_mode' => 'online',
                        'project_notes' => 'Detailed enterprise architectural review.',
                        'addons' => ['priority_express']
                    ]),
                    'admin_notes' => 'Prepared onboarding documents.',
                    'created_at' => current_time('mysql')
                ]
            ];

            foreach ($demo_bookings as $booking) {
                $wpdb->insert($bookings_table, $booking);
            }
        }
    }

    public static function get_forms($args = []) {
        global $wpdb;
        $table = self::get_forms_table();
        $bookings_table = self::get_bookings_table();

        $where = ['1=1'];
        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare("f.status = %s", $args['status']);
        }
        if (!empty($args['search'])) {
            $like = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = $wpdb->prepare("(f.title LIKE %s OR f.description LIKE %s)", $like, $like);
        }

        $where_str = implode(' AND ', $where);
        $order_by = !empty($args['orderby']) ? sanitize_sql_orderby($args['orderby']) : 'f.created_at DESC';

        $query = "SELECT f.*, COUNT(b.id) as total_bookings 
                  FROM $table f 
                  LEFT JOIN $bookings_table b ON f.id = b.form_id 
                  WHERE $where_str 
                  GROUP BY f.id 
                  ORDER BY $order_by";

        return $wpdb->get_results($query);
    }

    public static function get_form($id) {
        global $wpdb;
        $table = self::get_forms_table();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public static function save_form($data, $id = 0) {
        global $wpdb;
        $table = self::get_forms_table();

        $form_data = [
            'title' => sanitize_text_field($data['title']),
            'slug' => sanitize_title($data['title']),
            'description' => sanitize_textarea_field(isset($data['description']) ? $data['description'] : ''),
            'status' => isset($data['status']) ? sanitize_text_field($data['status']) : 'active',
            'settings' => is_string($data['settings']) ? $data['settings'] : json_encode($data['settings']),
            'fields' => is_string($data['fields']) ? $data['fields'] : json_encode($data['fields']),
            'updated_at' => current_time('mysql')
        ];

        if ($id > 0) {
            $wpdb->update($table, $form_data, ['id' => $id]);
            return $id;
        } else {
            $form_data['created_by'] = get_current_user_id();
            $form_data['created_at'] = current_time('mysql');
            $wpdb->insert($table, $form_data);
            return $wpdb->insert_id;
        }
    }

    public static function delete_form($id) {
        global $wpdb;
        $forms_table = self::get_forms_table();
        $bookings_table = self::get_bookings_table();

        $wpdb->delete($forms_table, ['id' => $id]);
        $wpdb->delete($bookings_table, ['form_id' => $id]);
        return true;
    }

    public static function duplicate_form($id) {
        global $wpdb;
        $table = self::get_forms_table();
        $original = self::get_form($id);
        if (!$original) return false;

        $new_data = [
            'title' => $original->title . ' (Copy)',
            'slug' => sanitize_title($original->title . '-copy-' . time()),
            'description' => $original->description,
            'status' => 'active',
            'settings' => $original->settings,
            'fields' => $original->fields,
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];

        $wpdb->insert($table, $new_data);
        return $wpdb->insert_id;
    }

    public static function get_bookings($args = []) {
        global $wpdb;
        $table = self::get_bookings_table();
        $forms_table = self::get_forms_table();

        $where = ['1=1'];
        if (!empty($args['form_id'])) {
            $where[] = $wpdb->prepare("b.form_id = %d", $args['form_id']);
        }
        if (!empty($args['status']) && $args['status'] !== 'all') {
            $where[] = $wpdb->prepare("b.status = %s", $args['status']);
        }
        if (!empty($args['date_from'])) {
            $where[] = $wpdb->prepare("b.booking_date >= %s", $args['date_from']);
        }
        if (!empty($args['date_to'])) {
            $where[] = $wpdb->prepare("b.booking_date <= %s", $args['date_to']);
        }
        if (!empty($args['search'])) {
            $like = '%' . $wpdb->esc_like($args['search']) . '%';
            $where[] = $wpdb->prepare("(b.customer_name LIKE %s OR b.customer_email LIKE %s OR b.customer_phone LIKE %s OR b.booking_ref LIKE %s)", $like, $like, $like, $like);
        }

        $where_str = implode(' AND ', $where);
        $order_by = !empty($args['orderby']) ? sanitize_sql_orderby($args['orderby']) : 'b.booking_date DESC, b.booking_time_start ASC';

        $query = "SELECT b.*, f.title as form_title 
                  FROM $table b 
                  LEFT JOIN $forms_table f ON b.form_id = f.id 
                  WHERE $where_str 
                  ORDER BY $order_by";

        return $wpdb->get_results($query);
    }

    public static function get_booking($id) {
        global $wpdb;
        $table = self::get_bookings_table();
        $forms_table = self::get_forms_table();

        $query = $wpdb->prepare(
            "SELECT b.*, f.title as form_title, f.settings as form_settings 
             FROM $table b 
             LEFT JOIN $forms_table f ON b.form_id = f.id 
             WHERE b.id = %d", 
            $id
        );

        return $wpdb->get_row($query);
    }

    public static function get_booking_by_ref($ref) {
        global $wpdb;
        $table = self::get_bookings_table();
        $forms_table = self::get_forms_table();

        $query = $wpdb->prepare(
            "SELECT b.*, f.title as form_title 
             FROM $table b 
             LEFT JOIN $forms_table f ON b.form_id = f.id 
             WHERE b.booking_ref = %s", 
            $ref
        );

        return $wpdb->get_row($query);
    }

    public static function create_booking($data) {
        global $wpdb;
        $table = self::get_bookings_table();

        $booking_ref = 'SVB-' . strtoupper(wp_generate_password(8, false));

        $booking_data = [
            'booking_ref' => $booking_ref,
            'form_id' => intval($data['form_id']),
            'customer_name' => sanitize_text_field($data['customer_name']),
            'customer_email' => sanitize_email($data['customer_email']),
            'customer_phone' => isset($data['customer_phone']) ? sanitize_text_field($data['customer_phone']) : '',
            'booking_date' => sanitize_text_field($data['booking_date']),
            'booking_time_start' => isset($data['booking_time_start']) ? sanitize_text_field($data['booking_time_start']) : null,
            'booking_time_end' => isset($data['booking_time_end']) ? sanitize_text_field($data['booking_time_end']) : null,
            'service_name' => isset($data['service_name']) ? sanitize_text_field($data['service_name']) : '',
            'service_price' => isset($data['service_price']) ? floatval($data['service_price']) : 0.00,
            'status' => isset($data['status']) ? sanitize_text_field($data['status']) : 'pending',
            'custom_data' => is_string($data['custom_data']) ? $data['custom_data'] : json_encode($data['custom_data']),
            'admin_notes' => isset($data['admin_notes']) ? sanitize_textarea_field($data['admin_notes']) : '',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ];

        $result = $wpdb->insert($table, $booking_data);
        if ($result === false) {
            return false;
        }

        return [
            'id' => $wpdb->insert_id,
            'booking_ref' => $booking_ref
        ];
    }

    public static function update_booking_status($id, $status, $notes = null) {
        global $wpdb;
        $table = self::get_bookings_table();

        $data = ['status' => sanitize_text_field($status), 'updated_at' => current_time('mysql')];
        if ($notes !== null) {
            $data['admin_notes'] = sanitize_textarea_field($notes);
        }

        return $wpdb->update($table, $data, ['id' => $id]);
    }

    public static function delete_booking($id) {
        global $wpdb;
        $table = self::get_bookings_table();
        return $wpdb->delete($table, ['id' => $id]);
    }

    public static function get_booked_slots($form_id, $date) {
        global $wpdb;
        $table = self::get_bookings_table();
        $query = $wpdb->prepare(
            "SELECT booking_time_start, booking_time_end, COUNT(*) as booked_count 
             FROM $table 
             WHERE form_id = %d AND booking_date = %s AND status != 'cancelled' 
             GROUP BY booking_time_start",
            $form_id,
            $date
        );
        return $wpdb->get_results($query, ARRAY_A);
    }

    public static function get_stats() {
        global $wpdb;
        $bookings_table = self::get_bookings_table();
        $forms_table = self::get_forms_table();

        $total_bookings = (int) $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table");
        $pending_bookings = (int) $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table WHERE status = 'pending'");
        $confirmed_bookings = (int) $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table WHERE status = 'confirmed'");
        $total_forms = (int) $wpdb->get_var("SELECT COUNT(*) FROM $forms_table WHERE status = 'active'");
        $revenue = (float) $wpdb->get_var("SELECT SUM(service_price) FROM $bookings_table WHERE status IN ('confirmed', 'completed')");

        return [
            'total_bookings' => $total_bookings,
            'pending_bookings' => $pending_bookings,
            'confirmed_bookings' => $confirmed_bookings,
            'total_forms' => $total_forms,
            'total_revenue' => $revenue
        ];
    }
}
