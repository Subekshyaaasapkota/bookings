<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="sv-admin-wrap">
    <!-- Header -->
    <div class="sv-header">
        <div class="sv-header-left">
            <div class="sv-logo-badge" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <span class="dashicons dashicons-clipboard"></span>
            </div>
            <div>
                <h1 class="sv-title"><?php esc_html_e('All Bookings & Appointments', 'svbookings'); ?></h1>
                <p class="sv-subtitle"><?php esc_html_e('Review incoming client bookings, manage statuses, and inspect custom field details.', 'svbookings'); ?></p>
            </div>
        </div>
        <div class="sv-header-actions">
            <a href="<?php echo esc_url(admin_url('admin-ajax.php?action=sv_export_bookings_csv&form_id=' . $form_id . '&status=' . $status . '&date_from=' . $date_from . '&date_to=' . $date_to)); ?>" class="sv-btn sv-btn-secondary" id="sv-export-csv-btn">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e('Export CSV', 'svbookings'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-form-builder')); ?>" class="sv-btn sv-btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e('Create New Form', 'svbookings'); ?>
            </a>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="sv-card">
        <div class="sv-card-header sv-filters-container">
            <form method="get" class="sv-bookings-filter-form">
                <input type="hidden" name="page" value="sv-bookings-entries">
                
                <div class="sv-filter-group">
                    <label class="sv-filter-label"><?php esc_html_e('Form:', 'svbookings'); ?></label>
                    <select name="form_id" class="sv-select sv-select-sm" onchange="this.form.submit()">
                        <option value="0"><?php esc_html_e('All Forms', 'svbookings'); ?></option>
                        <?php foreach ($all_forms as $f_item): ?>
                            <option value="<?php echo esc_attr($f_item->id); ?>" <?php selected($form_id, $f_item->id); ?>>
                                <?php echo esc_html($f_item->title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sv-filter-group">
                    <label class="sv-filter-label"><?php esc_html_e('Status:', 'svbookings'); ?></label>
                    <select name="status" class="sv-select sv-select-sm" onchange="this.form.submit()">
                        <option value="all" <?php selected($status, 'all'); ?>><?php esc_html_e('All Statuses', 'svbookings'); ?></option>
                        <option value="pending" <?php selected($status, 'pending'); ?>><?php esc_html_e('Pending', 'svbookings'); ?></option>
                        <option value="confirmed" <?php selected($status, 'confirmed'); ?>><?php esc_html_e('Confirmed', 'svbookings'); ?></option>
                        <option value="completed" <?php selected($status, 'completed'); ?>><?php esc_html_e('Completed', 'svbookings'); ?></option>
                        <option value="cancelled" <?php selected($status, 'cancelled'); ?>><?php esc_html_e('Cancelled', 'svbookings'); ?></option>
                    </select>
                </div>

                <div class="sv-filter-group">
                    <label class="sv-filter-label"><?php esc_html_e('Date From:', 'svbookings'); ?></label>
                    <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" class="sv-input sv-input-sm">
                </div>

                <div class="sv-filter-group">
                    <label class="sv-filter-label"><?php esc_html_e('Date To:', 'svbookings'); ?></label>
                    <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" class="sv-input sv-input-sm">
                </div>

                <div class="sv-filter-group sv-search-group">
                    <div class="sv-search-input-wrap">
                        <span class="dashicons dashicons-search"></span>
                        <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search by name, email, ref...', 'svbookings'); ?>" class="sv-input sv-input-sm">
                    </div>
                    <button type="submit" class="sv-btn sv-btn-sm sv-btn-secondary"><?php esc_html_e('Filter', 'svbookings'); ?></button>
                    <?php if (!empty($search) || !empty($form_id) || $status !== 'all' || !empty($date_from) || !empty($date_to)): ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-entries')); ?>" class="sv-btn-link sv-btn-sm"><?php esc_html_e('Reset', 'svbookings'); ?></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="sv-empty-state">
                <div class="sv-empty-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <span class="dashicons dashicons-calendar"></span>
                </div>
                <h3><?php esc_html_e('No bookings found', 'svbookings'); ?></h3>
                <p><?php esc_html_e('No bookings match your current filter criteria or no submissions have been made yet.', 'svbookings'); ?></p>
            </div>
        <?php else: ?>
            <div class="sv-table-responsive">
                <table class="sv-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Booking Ref', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Customer Details', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Form / Service', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Scheduled Slot', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Price', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Status', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Booked On', 'svbookings'); ?></th>
                            <th style="text-align: right;"><?php esc_html_e('Actions', 'svbookings'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): 
                            $time_formatted = $b->booking_time_start ? date('g:i A', strtotime($b->booking_time_start)) . ' - ' . date('g:i A', strtotime($b->booking_time_end)) : __('Full Day', 'svbookings');
                            $date_formatted = date('M j, Y', strtotime($b->booking_date));
                            $custom_data = json_decode($b->custom_data, true) ?: [];
                        ?>
                            <tr id="sv-booking-row-<?php echo esc_attr($b->id); ?>">
                                <td>
                                    <span class="sv-ref-badge"><?php echo esc_html($b->booking_ref); ?></span>
                                </td>
                                <td>
                                    <div class="sv-customer-cell">
                                        <div class="sv-avatar">
                                            <?php echo esc_html(strtoupper(substr($b->customer_name, 0, 1))); ?>
                                        </div>
                                        <div class="sv-customer-info">
                                            <span class="sv-customer-name"><?php echo esc_html($b->customer_name); ?></span>
                                            <span class="sv-customer-email"><?php echo esc_html($b->customer_email); ?></span>
                                            <?php if (!empty($b->customer_phone)): ?>
                                                <span class="sv-customer-phone"><?php echo esc_html($b->customer_phone); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="sv-service-cell">
                                        <span class="sv-form-tag"><?php echo esc_html($b->form_title ?: __('General Form', 'svbookings')); ?></span>
                                        <?php if (!empty($b->service_name)): ?>
                                            <span class="sv-service-name"><?php echo esc_html($b->service_name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="sv-datetime-cell">
                                        <div class="sv-date-badge">
                                            <span class="dashicons dashicons-calendar-alt"></span>
                                            <?php echo esc_html($date_formatted); ?>
                                        </div>
                                        <div class="sv-time-text">
                                            <span class="dashicons dashicons-clock"></span>
                                            <?php echo esc_html($time_formatted); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="sv-price-badge">$<?php echo esc_html(number_format($b->service_price, 2)); ?></span>
                                </td>
                                <td>
                                    <div class="sv-status-changer">
                                        <select class="sv-status-dropdown sv-status-<?php echo esc_attr($b->status); ?>" data-booking-id="<?php echo esc_attr($b->id); ?>">
                                            <option value="pending" <?php selected($b->status, 'pending'); ?>><?php esc_html_e('Pending', 'svbookings'); ?></option>
                                            <option value="confirmed" <?php selected($b->status, 'confirmed'); ?>><?php esc_html_e('Confirmed', 'svbookings'); ?></option>
                                            <option value="completed" <?php selected($b->status, 'completed'); ?>><?php esc_html_e('Completed', 'svbookings'); ?></option>
                                            <option value="cancelled" <?php selected($b->status, 'cancelled'); ?>><?php esc_html_e('Cancelled', 'svbookings'); ?></option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <span class="sv-date-text"><?php echo esc_html(date('M j, Y', strtotime($b->created_at))); ?></span>
                                </td>
                                <td>
                                    <div class="sv-actions-cell">
                                        <button type="button" class="sv-action-btn sv-action-view sv-view-booking-btn" data-id="<?php echo esc_attr($b->id); ?>" title="<?php esc_attr_e('View Full Details & Custom Fields', 'svbookings'); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </button>
                                        <button type="button" class="sv-action-btn sv-action-delete sv-delete-booking-btn" data-id="<?php echo esc_attr($b->id); ?>" title="<?php esc_attr_e('Delete Booking Entry', 'svbookings'); ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="sv-booking-modal" class="sv-modal-backdrop" style="display: none;">
    <div class="sv-modal-card">
        <div class="sv-modal-header">
            <div class="sv-modal-title-wrap">
                <span class="dashicons dashicons-clipboard"></span>
                <h3 id="sv-modal-booking-ref"><?php esc_html_e('Booking Details', 'svbookings'); ?></h3>
            </div>
            <button type="button" class="sv-modal-close-btn" id="sv-close-modal-btn">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div class="sv-modal-body" id="sv-modal-body-content">
            <div class="sv-loading-spinner"><?php esc_html_e('Loading booking details...', 'svbookings'); ?></div>
        </div>
    </div>
</div>
