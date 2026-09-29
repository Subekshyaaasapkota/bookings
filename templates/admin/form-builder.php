<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($form);
$form_id = $is_edit ? $form->id : 0;
$title = $is_edit ? $form->title : __('New Booking Form', 'svbookings');
$description = $is_edit ? $form->description : '';
$status = $is_edit ? $form->status : 'active';
$raw_fields = $is_edit ? $form->fields : '[]';
$raw_settings = $is_edit ? $form->settings : '{}';

$fields = json_decode($raw_fields, true) ?: [];
$settings = json_decode($raw_settings, true) ?: [
    'primary_color' => '#4f46e5',
    'accent_color' => '#6366f1',
    'currency_symbol' => '$',
    'slot_duration' => 30,
    'business_hours_start' => '09:00',
    'business_hours_end' => '17:00',
    'available_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    'confirmation_message' => __('Thank you! Your booking request has been received.', 'svbookings'),
    'admin_email_notify' => get_option('admin_email'),
    'enable_client_email' => 1,
    'max_bookings_per_slot' => 1
];
?>

<div class="sv-admin-wrap sv-builder-wrap">
    <!-- Builder Header -->
    <div class="sv-builder-header">
        <div class="sv-builder-header-left">
            <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings')); ?>" class="sv-back-btn" title="<?php esc_attr_e('Back to Forms', 'svbookings'); ?>">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
            </a>
            <div class="sv-form-title-edit-wrap">
                <input type="text" id="sv-builder-form-title" class="sv-title-input" value="<?php echo esc_attr($title); ?>" placeholder="<?php esc_attr_e('Enter Form Name...', 'svbookings'); ?>">
                <span class="sv-builder-badge"><?php echo $is_edit ? sprintf(__('Form #%d', 'svbookings'), $form_id) : __('Draft', 'svbookings'); ?></span>
            </div>
        </div>

        <div class="sv-builder-tabs">
            <button type="button" class="sv-tab-btn active" data-tab="fields">
                <span class="dashicons dashicons-layout"></span>
                <?php esc_html_e('Form Fields', 'svbookings'); ?>
            </button>
            <button type="button" class="sv-tab-btn" data-tab="settings">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e('Booking & Settings', 'svbookings'); ?>
            </button>
        </div>

        <div class="sv-builder-header-actions">
            <?php if ($is_edit): ?>
                <div class="sv-shortcode-chip-sm" data-shortcode="[sv_booking_form id=&quot;<?php echo esc_attr($form_id); ?>&quot;]">
                    <code>[sv_booking_form id="<?php echo esc_attr($form_id); ?>"]</code>
                    <button type="button" class="sv-copy-btn" title="<?php esc_attr_e('Copy Shortcode', 'svbookings'); ?>">
                        <span class="dashicons dashicons-admin-page"></span>
                    </button>
                </div>
            <?php endif; ?>

            <button type="button" id="sv-preview-form-btn" class="sv-btn sv-btn-secondary">
                <span class="dashicons dashicons-visibility"></span>
                <span><?php esc_html_e('Preview', 'svbookings'); ?></span>
            </button>

            <button type="button" id="sv-save-form-btn" class="sv-btn sv-btn-primary">
                <span class="dashicons dashicons-saved"></span>
                <span class="sv-btn-text"><?php echo $is_edit ? esc_html__('Save Changes', 'svbookings') : esc_html__('Publish Form', 'svbookings'); ?></span>
            </button>
        </div>
    </div>

    <!-- Main Builder Workspace -->
    <div class="sv-builder-body">
        
        <!-- TAB 1: FORM FIELDS BUILDER -->
        <div class="sv-tab-content active" id="sv-tab-fields">
            <div class="sv-builder-layout">
                
                <!-- Left: Field Palette -->
                <div class="sv-palette-panel">
                    <div class="sv-panel-header">
                        <h3><?php esc_html_e('Add Custom Fields', 'svbookings'); ?></h3>
                        <p><?php esc_html_e('Click or drag fields to add them to your booking form.', 'svbookings'); ?></p>
                    </div>

                    <div class="sv-palette-group">
                        <div class="sv-group-title"><?php esc_html_e('Booking Specific', 'svbookings'); ?></div>
                        <div class="sv-palette-items">
                            <button type="button" class="sv-palette-item" data-type="service">
                                <span class="dashicons dashicons-cart"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Service / Package', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Options with price & duration', 'svbookings'); ?></span>
                                </div>
                            </button>
                            <button type="button" class="sv-palette-item" data-type="datetime_picker">
                                <span class="dashicons dashicons-calendar"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Date & Time Slot', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Live slot availability picker', 'svbookings'); ?></span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="sv-palette-group">
                        <div class="sv-group-title"><?php esc_html_e('Standard Fields', 'svbookings'); ?></div>
                        <div class="sv-palette-items">
                            <button type="button" class="sv-palette-item" data-type="text">
                                <span class="dashicons dashicons-editor-textcolor"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Text Field', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Single-line text', 'svbookings'); ?></span>
                                </div>
                            </button>
                            <button type="button" class="sv-palette-item" data-type="email">
                                <span class="dashicons dashicons-email"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Email Address', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Email with validation', 'svbookings'); ?></span>
                                </div>
                            </button>
                            <button type="button" class="sv-palette-item" data-type="phone">
                                <span class="dashicons dashicons-phone"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Phone Number', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Telephone / mobile', 'svbookings'); ?></span>
                                </div>
                            </button>
                            <button type="button" class="sv-palette-item" data-type="number">
                                <span class="dashicons dashicons-calculator"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Number', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Numeric input', 'svbookings'); ?></span>
                                </div>
                            </button>
                            <button type="button" class="sv-palette-item" data-type="textarea">
                                <span class="dashicons dashicons-editor-paragraph"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Textarea', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Multi-line notes/details', 'svbookings'); ?></span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <div class="sv-palette-group">
                        <div class="sv-group-title"><?php esc_html_e('Choice & Selection', 'svbookings'); ?></div>
                        <div class="sv-palette-items">
                            <button type="button" class="sv-palette-item" data-type="select">
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Dropdown Select', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Single pick dropdown', 'svbookings'); ?></span>
                                </div>
                            </button>
                            <button type="button" class="sv-palette-item" data-type="radio">
                                <span class="dashicons dashicons-marker"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Radio Buttons', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Single option selection', 'svbookings'); ?></span>
                                </div>
                            </button>
                            <button type="button" class="sv-palette-item" data-type="checkbox_group">
                                <span class="dashicons dashicons-yes"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Checkboxes / Add-ons', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Multiple options with add-on pricing', 'svbookings'); ?></span>
                                </div>
                            </button>
                            <button type="button" class="sv-palette-item" data-type="section">
                                <span class="dashicons dashicons-minus"></span>
                                <div class="sv-palette-item-info">
                                    <span class="sv-palette-item-name"><?php esc_html_e('Section Break / Note', 'svbookings'); ?></span>
                                    <span class="sv-palette-item-desc"><?php esc_html_e('Header & descriptive text', 'svbookings'); ?></span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Center: Visual Canvas -->
                <div class="sv-canvas-panel">
                    <div class="sv-canvas-header">
                        <div class="sv-canvas-title">
                            <h3><?php esc_html_e('Form Structure', 'svbookings'); ?></h3>
                            <span class="sv-canvas-count" id="sv-field-counter">0 fields</span>
                        </div>
                        <div class="sv-canvas-actions">
                            <button type="button" id="sv-clear-canvas-btn" class="sv-btn-link-danger">
                                <span class="dashicons dashicons-trash"></span>
                                <?php esc_html_e('Clear All', 'svbookings'); ?>
                            </button>
                        </div>
                    </div>

                    <div id="sv-form-canvas" class="sv-form-canvas">
                        <!-- Draggable Fields List injected by JS -->
                    </div>

                    <div id="sv-canvas-empty" class="sv-canvas-empty" style="display: none;">
                        <div class="sv-empty-icon">
                            <span class="dashicons dashicons-plus-alt"></span>
                        </div>
                        <h4><?php esc_html_e('Your form is empty', 'svbookings'); ?></h4>
                        <p><?php esc_html_e('Click on any field from the left panel to begin assembling your booking form.', 'svbookings'); ?></p>
                    </div>
                </div>

                <!-- Right: Field Properties Inspector -->
                <div class="sv-inspector-panel" id="sv-field-inspector">
                    <div class="sv-inspector-empty">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <h4><?php esc_html_e('Field Settings', 'svbookings'); ?></h4>
                        <p><?php esc_html_e('Select any field in the center canvas to customize its labels, validation, width, and options.', 'svbookings'); ?></p>
                    </div>

                    <div class="sv-inspector-form" style="display: none;">
                        <div class="sv-panel-header">
                            <h3 id="sv-inspector-title"><?php esc_html_e('Edit Field', 'svbookings'); ?></h3>
                            <span class="sv-pill sv-pill-info" id="sv-inspector-type-badge">Text</span>
                        </div>

                        <div class="sv-inspector-fields">
                            <div class="sv-form-group">
                                <label for="sv-prop-label"><?php esc_html_e('Field Label', 'svbookings'); ?></label>
                                <input type="text" id="sv-prop-label" class="sv-input">
                            </div>

                            <div class="sv-form-group">
                                <label for="sv-prop-name"><?php esc_html_e('Field Name / Key', 'svbookings'); ?></label>
                                <input type="text" id="sv-prop-name" class="sv-input">
                                <small class="sv-help-text"><?php esc_html_e('Unique attribute name used for storing data.', 'svbookings'); ?></small>
                            </div>

                            <div class="sv-form-group" id="sv-prop-placeholder-group">
                                <label for="sv-prop-placeholder"><?php esc_html_e('Placeholder Text', 'svbookings'); ?></label>
                                <input type="text" id="sv-prop-placeholder" class="sv-input">
                            </div>

                            <div class="sv-form-group">
                                <label for="sv-prop-help"><?php esc_html_e('Help / Instruction Text', 'svbookings'); ?></label>
                                <input type="text" id="sv-prop-help" class="sv-input">
                            </div>

                            <div class="sv-form-row">
                                <div class="sv-form-group sv-col-6">
                                    <label for="sv-prop-width"><?php esc_html_e('Field Width', 'svbookings'); ?></label>
                                    <select id="sv-prop-width" class="sv-select">
                                        <option value="100"><?php esc_html_e('100% (Full Width)', 'svbookings'); ?></option>
                                        <option value="50"><?php esc_html_e('50% (Half Width)', 'svbookings'); ?></option>
                                        <option value="33"><?php esc_html_e('33% (One Third)', 'svbookings'); ?></option>
                                    </select>
                                </div>
                                <div class="sv-form-group sv-col-6 sv-checkbox-vertical">
                                    <label class="sv-checkbox-label">
                                        <input type="checkbox" id="sv-prop-required">
                                        <span><?php esc_html_e('Required Field', 'svbookings'); ?></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Options Manager for Select / Radio / Checkbox / Service -->
                            <div class="sv-options-editor" id="sv-options-editor-group" style="display: none;">
                                <div class="sv-options-header">
                                    <label><?php esc_html_e('Field Options & Pricing', 'svbookings'); ?></label>
                                    <button type="button" id="sv-add-option-btn" class="sv-btn-sm sv-btn-secondary">
                                        <span class="dashicons dashicons-plus"></span> <?php esc_html_e('Add Option', 'svbookings'); ?>
                                    </button>
                                </div>
                                <div id="sv-options-list" class="sv-options-list">
                                    <!-- Dynamic Option Rows -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- TAB 2: BOOKING & FORM SETTINGS -->
        <div class="sv-tab-content" id="sv-tab-settings">
            <div class="sv-settings-grid">
                
                <!-- Card 1: Scheduling & Hours -->
                <div class="sv-card">
                    <div class="sv-card-header">
                        <h3><span class="dashicons dashicons-clock"></span> <?php esc_html_e('Working Hours & Slot Settings', 'svbookings'); ?></h3>
                    </div>
                    <div class="sv-card-body">
                        <div class="sv-form-row">
                            <div class="sv-form-group sv-col-6">
                                <label for="sv-set-slot-duration"><?php esc_html_e('Slot Duration (Minutes)', 'svbookings'); ?></label>
                                <select id="sv-set-slot-duration" class="sv-select">
                                    <option value="15" <?php selected($settings['slot_duration'], 15); ?>>15 Minutes</option>
                                    <option value="30" <?php selected($settings['slot_duration'], 30); ?>>30 Minutes</option>
                                    <option value="45" <?php selected($settings['slot_duration'], 45); ?>>45 Minutes</option>
                                    <option value="60" <?php selected($settings['slot_duration'], 60); ?>>60 Minutes (1 Hour)</option>
                                    <option value="90" <?php selected($settings['slot_duration'], 90); ?>>90 Minutes (1.5 Hours)</option>
                                    <option value="120" <?php selected($settings['slot_duration'], 120); ?>>120 Minutes (2 Hours)</option>
                                </select>
                            </div>
                            <div class="sv-form-group sv-col-6">
                                <label for="sv-set-slot-capacity"><?php esc_html_e('Max Bookings per Slot', 'svbookings'); ?></label>
                                <input type="number" id="sv-set-slot-capacity" class="sv-input" min="1" max="100" value="<?php echo esc_attr(isset($settings['max_bookings_per_slot']) ? $settings['max_bookings_per_slot'] : 1); ?>">
                            </div>
                        </div>

                        <div class="sv-form-row">
                            <div class="sv-form-group sv-col-6">
                                <label for="sv-set-hours-start"><?php esc_html_e('Business Start Time', 'svbookings'); ?></label>
                                <input type="time" id="sv-set-hours-start" class="sv-input" value="<?php echo esc_attr(isset($settings['business_hours_start']) ? $settings['business_hours_start'] : '09:00'); ?>">
                            </div>
                            <div class="sv-form-group sv-col-6">
                                <label for="sv-set-hours-end"><?php esc_html_e('Business End Time', 'svbookings'); ?></label>
                                <input type="time" id="sv-set-hours-end" class="sv-input" value="<?php echo esc_attr(isset($settings['business_hours_end']) ? $settings['business_hours_end'] : '17:00'); ?>">
                            </div>
                        </div>

                        <div class="sv-form-group">
                            <label><?php esc_html_e('Available Days of the Week', 'svbookings'); ?></label>
                            <div class="sv-days-selector">
                                <?php 
                                $days = [
                                    'monday' => __('Mon', 'svbookings'),
                                    'tuesday' => __('Tue', 'svbookings'),
                                    'wednesday' => __('Wed', 'svbookings'),
                                    'thursday' => __('Thu', 'svbookings'),
                                    'friday' => __('Fri', 'svbookings'),
                                    'saturday' => __('Sat', 'svbookings'),
                                    'sunday' => __('Sun', 'svbookings'),
                                ];
                                $avail_days = isset($settings['available_days']) ? (array)$settings['available_days'] : ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                                foreach ($days as $k => $label): ?>
                                    <label class="sv-day-chip">
                                        <input type="checkbox" name="sv_days[]" value="<?php echo esc_attr($k); ?>" <?php checked(in_array($k, $avail_days)); ?>>
                                        <span><?php echo esc_html($label); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Styling & Currency -->
                <div class="sv-card">
                    <div class="sv-card-header">
                        <h3><span class="dashicons dashicons-admin-appearance"></span> <?php esc_html_e('Styling & Currency', 'svbookings'); ?></h3>
                    </div>
                    <div class="sv-card-body">
                        <div class="sv-form-row">
                            <div class="sv-form-group sv-col-6">
                                <label for="sv-set-primary-color"><?php esc_html_e('Primary Brand Color', 'svbookings'); ?></label>
                                <div class="sv-color-picker-wrap">
                                    <input type="color" id="sv-set-primary-color" value="<?php echo esc_attr(isset($settings['primary_color']) ? $settings['primary_color'] : '#4f46e5'); ?>">
                                    <input type="text" class="sv-input" id="sv-set-primary-color-text" value="<?php echo esc_attr(isset($settings['primary_color']) ? $settings['primary_color'] : '#4f46e5'); ?>">
                                </div>
                            </div>
                            <div class="sv-form-group sv-col-6">
                                <label for="sv-set-currency"><?php esc_html_e('Currency Symbol', 'svbookings'); ?></label>
                                <input type="text" id="sv-set-currency" class="sv-input" value="<?php echo esc_attr(isset($settings['currency_symbol']) ? $settings['currency_symbol'] : '$'); ?>">
                            </div>
                        </div>

                        <div class="sv-form-group">
                            <label for="sv-set-description"><?php esc_html_e('Form Description / Header Subtitle', 'svbookings'); ?></label>
                            <textarea id="sv-set-description" class="sv-textarea" rows="3"><?php echo esc_textarea($description); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Email & Notifications -->
                <div class="sv-card">
                    <div class="sv-card-header">
                        <h3><span class="dashicons dashicons-email-alt"></span> <?php esc_html_e('Email Notifications', 'svbookings'); ?></h3>
                    </div>
                    <div class="sv-card-body">
                        <div class="sv-form-group">
                            <label for="sv-set-admin-email"><?php esc_html_e('Admin Notification Recipient Email', 'svbookings'); ?></label>
                            <input type="email" id="sv-set-admin-email" class="sv-input" value="<?php echo esc_attr(isset($settings['admin_email_notify']) ? $settings['admin_email_notify'] : get_option('admin_email')); ?>">
                        </div>

                        <div class="sv-form-group">
                            <label class="sv-checkbox-label">
                                <input type="checkbox" id="sv-set-client-email" <?php checked(!empty($settings['enable_client_email'])); ?>>
                                <span><?php esc_html_e('Send instant confirmation email to customer upon booking', 'svbookings'); ?></span>
                            </label>
                        </div>

                        <div class="sv-form-group">
                            <label for="sv-set-confirm-msg"><?php esc_html_e('Custom Confirmation Message', 'svbookings'); ?></label>
                            <textarea id="sv-set-confirm-msg" class="sv-textarea" rows="3"><?php echo esc_textarea(isset($settings['confirmation_message']) ? $settings['confirmation_message'] : __('Thank you! Your booking request has been received.', 'svbookings')); ?></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Hidden inputs storing current JSON state -->
<input type="hidden" id="sv-form-id" value="<?php echo esc_attr($form_id); ?>">
<input type="hidden" id="sv-initial-fields" value="<?php echo esc_attr($raw_fields); ?>">
<input type="hidden" id="sv-initial-settings" value="<?php echo esc_attr($raw_settings); ?>">

<!-- Form Live Preview Modal -->
<div id="sv-preview-modal" class="sv-modal-backdrop" style="display: none;">
    <div class="sv-modal-card sv-modal-card-lg">
        <div class="sv-modal-header">
            <div class="sv-modal-title-wrap">
                <span class="dashicons dashicons-visibility"></span>
                <h3><?php esc_html_e('Live Form Preview', 'svbookings'); ?></h3>
            </div>
            <div class="sv-preview-device-toggle">
                <button type="button" class="sv-device-btn active" data-device="desktop" title="<?php esc_attr_e('Desktop View', 'svbookings'); ?>">
                    <span class="dashicons dashicons-desktop"></span>
                    <span><?php esc_html_e('Desktop', 'svbookings'); ?></span>
                </button>
                <button type="button" class="sv-device-btn" data-device="mobile" title="<?php esc_attr_e('Mobile View', 'svbookings'); ?>">
                    <span class="dashicons dashicons-smartphone"></span>
                    <span><?php esc_html_e('Mobile', 'svbookings'); ?></span>
                </button>
            </div>
            <button type="button" class="sv-modal-close-btn" id="sv-close-preview-btn" title="<?php esc_attr_e('Close Preview', 'svbookings'); ?>">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="sv-modal-body" id="sv-preview-modal-body" style="background: #f8fafc; padding: 24px; max-height: calc(90vh - 75px); overflow-y: auto;">
            <!-- Rendered preview dynamically inserted by JS -->
        </div>
    </div>
</div>

