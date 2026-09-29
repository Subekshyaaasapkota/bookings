<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="sv-client-builder-wrap">
    <div class="sv-client-builder-header">
        <div class="sv-cb-badge">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
        </div>
        <div>
            <h2 class="sv-cb-title"><?php esc_html_e('Client Booking Form Creator', 'svbookings'); ?></h2>
            <p class="sv-cb-subtitle"><?php esc_html_e('Design and generate custom booking forms directly from this page.', 'svbookings'); ?></p>
        </div>
    </div>

    <div class="sv-cb-container">
        <!-- Step 1: Form Information -->
        <div class="sv-cb-card">
            <h3 class="sv-cb-card-title"><?php esc_html_e('1. Basic Form Info & Details', 'svbookings'); ?></h3>
            <div class="sv-form-grid">
                <div class="sv-col-12">
                    <label class="sv-label"><?php esc_html_e('Form Title *', 'svbookings'); ?></label>
                    <input type="text" id="sv-cf-title" class="sv-input" placeholder="e.g. Wellness & Therapy Session Booking">
                </div>
                <div class="sv-col-12">
                    <label class="sv-label"><?php esc_html_e('Description / Instructions', 'svbookings'); ?></label>
                    <textarea id="sv-cf-desc" class="sv-textarea" rows="2" placeholder="Brief note to describe the booking flow to your customers..."></textarea>
                </div>
            </div>
        </div>

        <!-- Step 2: Custom Fields Builder -->
        <div class="sv-cb-card">
            <div class="sv-cb-card-header-flex">
                <h3 class="sv-cb-card-title"><?php esc_html_e('2. Form Fields & Options', 'svbookings'); ?></h3>
                <div class="sv-cb-add-menu">
                    <button type="button" class="sv-btn sv-btn-sm sv-btn-secondary sv-cf-add-field-btn" data-type="text">+ Text</button>
                    <button type="button" class="sv-btn sv-btn-sm sv-btn-secondary sv-cf-add-field-btn" data-type="email">+ Email</button>
                    <button type="button" class="sv-btn sv-btn-sm sv-btn-secondary sv-cf-add-field-btn" data-type="phone">+ Phone</button>
                    <button type="button" class="sv-btn sv-btn-sm sv-btn-secondary sv-cf-add-field-btn" data-type="service">+ Service Card</button>
                    <button type="button" class="sv-btn sv-btn-sm sv-btn-secondary sv-cf-add-field-btn" data-type="datetime_picker">+ Scheduler</button>
                    <button type="button" class="sv-btn sv-btn-sm sv-btn-secondary sv-cf-add-field-btn" data-type="select">+ Select</button>
                    <button type="button" class="sv-btn sv-btn-sm sv-btn-secondary sv-cf-add-field-btn" data-type="checkbox_group">+ Add-ons</button>
                </div>
            </div>

            <div id="sv-cf-fields-list" class="sv-cf-fields-list">
                <!-- Fields rendered by client-builder.js -->
            </div>
        </div>

        <!-- Step 3: Timing & Notification Settings -->
        <div class="sv-cb-card">
            <h3 class="sv-cb-card-title"><?php esc_html_e('3. Schedule & Color Preferences', 'svbookings'); ?></h3>
            <div class="sv-form-grid">
                <div class="sv-col-6">
                    <label class="sv-label"><?php esc_html_e('Slot Duration', 'svbookings'); ?></label>
                    <select id="sv-cf-slot-duration" class="sv-select">
                        <option value="15">15 Minutes</option>
                        <option value="30" selected>30 Minutes</option>
                        <option value="45">45 Minutes</option>
                        <option value="60">60 Minutes (1 Hour)</option>
                    </select>
                </div>
                <div class="sv-col-6">
                    <label class="sv-label"><?php esc_html_e('Currency Symbol', 'svbookings'); ?></label>
                    <input type="text" id="sv-cf-currency" class="sv-input" value="$">
                </div>
                <div class="sv-col-6">
                    <label class="sv-label"><?php esc_html_e('Primary Accent Color', 'svbookings'); ?></label>
                    <input type="color" id="sv-cf-color" class="sv-input" value="#4f46e5" style="height:44px;padding:2px 4px;">
                </div>
                <div class="sv-col-6">
                    <label class="sv-label"><?php esc_html_e('Notification Email', 'svbookings'); ?></label>
                    <input type="email" id="sv-cf-email" class="sv-input" value="<?php echo esc_attr(get_option('admin_email')); ?>">
                </div>
            </div>
        </div>

        <!-- Action Submit -->
        <div class="sv-cb-submit-wrap">
            <button type="button" id="sv-cf-save-btn" class="sv-submit-btn">
                <span><?php esc_html_e('Create & Publish Booking Form', 'svbookings'); ?></span>
            </button>
        </div>

        <!-- Success Generator Result -->
        <div id="sv-cf-result" class="sv-cf-result-card" style="display: none;">
            <div class="sv-cf-result-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <h4><?php esc_html_e('Your Booking Form is Ready!', 'svbookings'); ?></h4>
            <p><?php esc_html_e('Copy and paste this shortcode anywhere on your site to display your newly generated booking form:', 'svbookings'); ?></p>
            <div class="sv-code-box">
                <code id="sv-cf-shortcode-output">[sv_booking_form id="1"]</code>
                <button type="button" class="sv-copy-btn" id="sv-cf-copy-shortcode-btn"><?php esc_html_e('Copy Shortcode', 'svbookings'); ?></button>
            </div>
        </div>
    </div>
</div>
