<?php
if (!defined('ABSPATH')) {
    exit;
}

$form_id = $form->id;
$primary_color = !empty($settings['primary_color']) ? $settings['primary_color'] : '#4f46e5';
$currency = !empty($settings['currency_symbol']) ? $settings['currency_symbol'] : '$';
?>

<div class="sv-booking-container" id="sv-booking-app-<?php echo esc_attr($form_id); ?>" style="--sv-primary: <?php echo esc_attr($primary_color); ?>;">
    
    <!-- Header -->
    <div class="sv-form-header">
        <div class="sv-badge-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <h2 class="sv-form-title"><?php echo esc_html($form->title); ?></h2>
        <?php if (!empty($form->description)): ?>
            <p class="sv-form-desc"><?php echo esc_html($form->description); ?></p>
        <?php endif; ?>
    </div>

    <!-- Main Booking Form -->
    <form class="sv-client-booking-form" data-form-id="<?php echo esc_attr($form_id); ?>" data-currency="<?php echo esc_attr($currency); ?>">
        <input type="hidden" name="form_id" value="<?php echo esc_attr($form_id); ?>">
        <input type="hidden" name="service_price" id="sv_service_price_<?php echo esc_attr($form_id); ?>" value="0">
        <input type="hidden" name="booking_time_end" id="sv_time_end_<?php echo esc_attr($form_id); ?>" value="">

        <div class="sv-form-grid">
            <?php foreach ($fields as $field): 
                $f_type = $field['type'];
                $f_name = esc_attr($field['name']);
                $f_id = esc_attr($field['id']);
                $f_label = esc_html($field['label']);
                $f_placeholder = isset($field['placeholder']) ? esc_attr($field['placeholder']) : '';
                $f_req = !empty($field['required']);
                $f_width = isset($field['width']) ? $field['width'] : '100';
                $f_help = !empty($field['help_text']) ? esc_html($field['help_text']) : '';
                $col_class = 'sv-col-12';
                if ($f_width === '50') $col_class = 'sv-col-6';
                if ($f_width === '33') $col_class = 'sv-col-4';
            ?>

                <?php if ($f_type === 'section'): ?>
                    <div class="sv-col-12 sv-section-header-wrap">
                        <h3 class="sv-section-title"><?php echo $f_label; ?></h3>
                        <?php if ($f_help): ?><p class="sv-section-desc"><?php echo $f_help; ?></p><?php endif; ?>
                    </div>

                <?php elseif ($f_type === 'service'): ?>
                    <div class="sv-col-12 sv-service-group-wrap">
                        <label class="sv-label">
                            <?php echo $f_label; ?>
                            <?php if ($f_req): ?><span class="sv-required">*</span><?php endif; ?>
                        </label>
                        <?php if ($f_help): ?><div class="sv-field-help"><?php echo $f_help; ?></div><?php endif; ?>
                        
                        <div class="sv-services-cards">
                            <?php if (!empty($field['options'])): 
                                foreach ($field['options'] as $idx => $opt): 
                                    $opt_label = esc_html($opt['label']);
                                    $opt_val = esc_attr($opt['value']);
                                    $opt_price = isset($opt['price']) ? floatval($opt['price']) : 0;
                                    $opt_duration = isset($opt['duration']) ? intval($opt['duration']) : 30;
                            ?>
                                <label class="sv-service-card <?php echo $idx === 0 ? 'selected' : ''; ?>">
                                    <input type="radio" name="service_name" value="<?php echo $opt_label; ?>" data-price="<?php echo $opt_price; ?>" data-duration="<?php echo $opt_duration; ?>" <?php checked($idx, 0); ?> <?php echo $f_req ? 'required' : ''; ?>>
                                    <div class="sv-service-card-body">
                                        <div class="sv-service-card-top">
                                            <span class="sv-service-name"><?php echo $opt_label; ?></span>
                                            <span class="sv-service-price"><?php echo $currency . number_format($opt_price, 2); ?></span>
                                        </div>
                                        <div class="sv-service-meta">
                                            <span class="sv-service-duration">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                <?php echo $opt_duration; ?> mins
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>

                <?php elseif ($f_type === 'datetime_picker'): ?>
                    <div class="sv-col-12 sv-scheduler-box">
                        <label class="sv-label">
                            <?php echo $f_label; ?>
                            <?php if ($f_req): ?><span class="sv-required">*</span><?php endif; ?>
                        </label>
                        <?php if ($f_help): ?><div class="sv-field-help"><?php echo $f_help; ?></div><?php endif; ?>

                        <div class="sv-scheduler-grid">
                            <!-- Date Selection -->
                            <div class="sv-date-picker-column">
                                <label class="sv-sublabel"><?php esc_html_e('1. Select Date', 'svbookings'); ?></label>
                                <div class="sv-input-icon-wrap">
                                    <svg class="sv-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    <input type="text" name="booking_date" class="sv-input sv-booking-date-input" placeholder="<?php esc_attr_e('Pick an available date...', 'svbookings'); ?>" <?php echo $f_req ? 'required' : ''; ?> readonly>
                                </div>
                            </div>

                            <!-- Time Slots Selection -->
                            <div class="sv-time-slots-column">
                                <label class="sv-sublabel"><?php esc_html_e('2. Select Available Time Slot', 'svbookings'); ?></label>
                                <input type="hidden" name="booking_time_start" class="sv-selected-time-start" value="">
                                
                                <div class="sv-slots-wrapper">
                                    <div class="sv-slots-notice">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                        <span><?php esc_html_e('Please pick a date on the left to see available slots.', 'svbookings'); ?></span>
                                    </div>
                                    <div class="sv-slots-grid" style="display: none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php elseif ($f_type === 'text' || $f_type === 'email' || $f_type === 'phone' || $f_type === 'number'): ?>
                    <div class="<?php echo esc_attr($col_class); ?>">
                        <label class="sv-label" for="<?php echo $f_id; ?>">
                            <?php echo $f_label; ?>
                            <?php if ($f_req): ?><span class="sv-required">*</span><?php endif; ?>
                        </label>
                        <div class="sv-input-wrap">
                            <input type="<?php echo $f_type === 'phone' ? 'tel' : esc_attr($f_type); ?>" 
                                   id="<?php echo $f_id; ?>" 
                                   name="<?php echo $f_name; ?>" 
                                   class="sv-input" 
                                   placeholder="<?php echo $f_placeholder; ?>" 
                                   <?php echo $f_req ? 'required' : ''; ?>>
                        </div>
                        <?php if ($f_help): ?><small class="sv-field-help"><?php echo $f_help; ?></small><?php endif; ?>
                    </div>

                <?php elseif ($f_type === 'textarea'): ?>
                    <div class="<?php echo esc_attr($col_class); ?>">
                        <label class="sv-label" for="<?php echo $f_id; ?>">
                            <?php echo $f_label; ?>
                            <?php if ($f_req): ?><span class="sv-required">*</span><?php endif; ?>
                        </label>
                        <textarea id="<?php echo $f_id; ?>" name="<?php echo $f_name; ?>" class="sv-textarea" rows="4" placeholder="<?php echo $f_placeholder; ?>" <?php echo $f_req ? 'required' : ''; ?>></textarea>
                        <?php if ($f_help): ?><small class="sv-field-help"><?php echo $f_help; ?></small><?php endif; ?>
                    </div>

                <?php elseif ($f_type === 'select'): ?>
                    <div class="<?php echo esc_attr($col_class); ?>">
                        <label class="sv-label" for="<?php echo $f_id; ?>">
                            <?php echo $f_label; ?>
                            <?php if ($f_req): ?><span class="sv-required">*</span><?php endif; ?>
                        </label>
                        <select id="<?php echo $f_id; ?>" name="<?php echo $f_name; ?>" class="sv-select" <?php echo $f_req ? 'required' : ''; ?>>
                            <?php if ($f_placeholder): ?><option value=""><?php echo $f_placeholder; ?></option><?php endif; ?>
                            <?php if (!empty($field['options'])): 
                                foreach ($field['options'] as $opt): ?>
                                    <option value="<?php echo esc_attr($opt['value']); ?>"><?php echo esc_html($opt['label']); ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                        <?php if ($f_help): ?><small class="sv-field-help"><?php echo $f_help; ?></small><?php endif; ?>
                    </div>

                <?php elseif ($f_type === 'radio'): ?>
                    <div class="<?php echo esc_attr($col_class); ?>">
                        <label class="sv-label">
                            <?php echo $f_label; ?>
                            <?php if ($f_req): ?><span class="sv-required">*</span><?php endif; ?>
                        </label>
                        <div class="sv-choice-group">
                            <?php if (!empty($field['options'])): 
                                foreach ($field['options'] as $idx => $opt): ?>
                                    <label class="sv-radio-option">
                                        <input type="radio" name="<?php echo $f_name; ?>" value="<?php echo esc_attr($opt['value']); ?>" <?php checked($idx, 0); ?> <?php echo $f_req ? 'required' : ''; ?>>
                                        <span><?php echo esc_html($opt['label']); ?></span>
                                    </label>
                            <?php endforeach; endif; ?>
                        </div>
                        <?php if ($f_help): ?><small class="sv-field-help"><?php echo $f_help; ?></small><?php endif; ?>
                    </div>

                <?php elseif ($f_type === 'checkbox_group'): ?>
                    <div class="<?php echo esc_attr($col_class); ?>">
                        <label class="sv-label">
                            <?php echo $f_label; ?>
                            <?php if ($f_req): ?><span class="sv-required">*</span><?php endif; ?>
                        </label>
                        <div class="sv-choice-group">
                            <?php if (!empty($field['options'])): 
                                foreach ($field['options'] as $opt): 
                                    $price_addon = isset($opt['price']) && floatval($opt['price']) > 0 ? ' (+' . $currency . number_format($opt['price'], 2) . ')' : '';
                            ?>
                                <label class="sv-checkbox-option">
                                    <input type="checkbox" name="<?php echo $f_name; ?>[]" value="<?php echo esc_attr($opt['value']); ?>" data-addon-price="<?php echo isset($opt['price']) ? floatval($opt['price']) : 0; ?>" class="sv-addon-checkbox">
                                    <span><?php echo esc_html($opt['label']) . $price_addon; ?></span>
                                </label>
                            <?php endforeach; endif; ?>
                        </div>
                        <?php if ($f_help): ?><small class="sv-field-help"><?php echo $f_help; ?></small><?php endif; ?>
                    </div>

                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Booking Summary & Submission Bar -->
        <div class="sv-booking-summary-bar">
            <div class="sv-summary-left">
                <div class="sv-summary-price-label"><?php esc_html_e('Estimated Total:', 'svbookings'); ?></div>
                <div class="sv-summary-price-value" id="sv-live-total-<?php echo esc_attr($form_id); ?>"><?php echo esc_html($currency); ?>0.00</div>
            </div>
            <div class="sv-summary-right">
                <button type="submit" class="sv-submit-btn" id="sv-btn-submit-<?php echo esc_attr($form_id); ?>">
                    <span class="sv-btn-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </span>
                    <span class="sv-btn-label"><?php esc_html_e('Confirm & Complete Booking', 'svbookings'); ?></span>
                </button>
            </div>
        </div>
    </form>

    <!-- Success Screen (Animated Confirmation) -->
    <div class="sv-booking-success-view" style="display: none;">
        <div class="sv-success-icon-wrap">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <h3 class="sv-success-title"><?php esc_html_e('Booking Confirmed!', 'svbookings'); ?></h3>
        <p class="sv-success-msg" id="sv-success-msg-text"><?php echo esc_html(!empty($settings['confirmation_message']) ? $settings['confirmation_message'] : __('Thank you for booking with us! We have sent a confirmation email with all details.', 'svbookings')); ?></p>

        <div class="sv-receipt-card">
            <div class="sv-receipt-header">
                <span class="sv-receipt-title"><?php esc_html_e('Booking Summary', 'svbookings'); ?></span>
                <span class="sv-receipt-ref" id="sv-receipt-ref-code">SVB-XXXXXX</span>
            </div>
            <div class="sv-receipt-body">
                <div class="sv-receipt-row">
                    <span class="sv-receipt-label"><?php esc_html_e('Customer:', 'svbookings'); ?></span>
                    <span class="sv-receipt-val" id="sv-receipt-customer">--</span>
                </div>
                <div class="sv-receipt-row">
                    <span class="sv-receipt-label"><?php esc_html_e('Service:', 'svbookings'); ?></span>
                    <span class="sv-receipt-val" id="sv-receipt-service">--</span>
                </div>
                <div class="sv-receipt-row">
                    <span class="sv-receipt-label"><?php esc_html_e('Date & Time:', 'svbookings'); ?></span>
                    <span class="sv-receipt-val" id="sv-receipt-datetime">--</span>
                </div>
                <div class="sv-receipt-row sv-receipt-total">
                    <span class="sv-receipt-label"><?php esc_html_e('Total Amount:', 'svbookings'); ?></span>
                    <span class="sv-receipt-val" id="sv-receipt-price">--</span>
                </div>
            </div>
        </div>

        <div class="sv-success-actions">
            <button type="button" class="sv-btn sv-btn-outline sv-new-booking-btn"><?php esc_html_e('Book Another Appointment', 'svbookings'); ?></button>
        </div>
    </div>

</div>
