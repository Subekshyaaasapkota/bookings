<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="sv-admin-wrap">
    <!-- Header -->
    <div class="sv-header">
        <div class="sv-header-left">
            <div class="sv-logo-badge">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <div>
                <h1 class="sv-title"><?php esc_html_e('Booking Forms', 'svbookings'); ?></h1>
                <p class="sv-subtitle"><?php esc_html_e('Create, customize, and manage client booking forms and scheduling flows.', 'svbookings'); ?></p>
            </div>
        </div>
        <div class="sv-header-actions">
            <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-entries')); ?>" class="sv-btn sv-btn-secondary">
                <span class="dashicons dashicons-list-view"></span>
                <?php esc_html_e('View Bookings', 'svbookings'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-form-builder')); ?>" class="sv-btn sv-btn-primary">
                <span class="dashicons dashicons-plus-alt2"></span>
                <?php esc_html_e('Add New Form', 'svbookings'); ?>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="sv-stats-grid">
        <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings')); ?>" class="sv-stat-card" title="<?php esc_attr_e('View All Forms', 'svbookings'); ?>">
            <div class="sv-stat-icon" style="background: rgba(99, 102, 241, 0.12); color: #6366f1;">
                <span class="dashicons dashicons-feedback"></span>
            </div>
            <div class="sv-stat-info">
                <span class="sv-stat-label"><?php esc_html_e('Total Forms', 'svbookings'); ?></span>
                <span class="sv-stat-value"><?php echo esc_html($stats['total_forms']); ?></span>
            </div>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-entries')); ?>" class="sv-stat-card" title="<?php esc_attr_e('View All Bookings', 'svbookings'); ?>">
            <div class="sv-stat-icon" style="background: rgba(16, 185, 129, 0.12); color: #10b981;">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="sv-stat-info">
                <span class="sv-stat-label"><?php esc_html_e('Total Bookings', 'svbookings'); ?></span>
                <span class="sv-stat-value"><?php echo esc_html($stats['total_bookings']); ?></span>
            </div>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-entries&status=pending')); ?>" class="sv-stat-card" title="<?php esc_attr_e('View Pending Bookings', 'svbookings'); ?>">
            <div class="sv-stat-icon" style="background: rgba(245, 158, 11, 0.12); color: #f59e0b;">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="sv-stat-info">
                <span class="sv-stat-label"><?php esc_html_e('Pending Bookings', 'svbookings'); ?></span>
                <span class="sv-stat-value"><?php echo esc_html($stats['pending_bookings']); ?></span>
            </div>
        </a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-entries&status=confirmed')); ?>" class="sv-stat-card" title="<?php esc_attr_e('View Confirmed Bookings', 'svbookings'); ?>">
            <div class="sv-stat-icon" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6;">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="sv-stat-info">
                <span class="sv-stat-label"><?php esc_html_e('Confirmed Revenue', 'svbookings'); ?></span>
                <span class="sv-stat-value">$<?php echo esc_html(number_format($stats['total_revenue'], 2)); ?></span>
            </div>
        </a>
    </div>

    <!-- Forms Content Card -->
    <div class="sv-card">
        <div class="sv-card-header">
            <div class="sv-filters-row">
                <form method="get" class="sv-search-form">
                    <input type="hidden" name="page" value="sv-bookings">
                    <div class="sv-search-input-wrap">
                        <span class="dashicons dashicons-search"></span>
                        <input type="text" name="s" value="<?php echo esc_attr(isset($_GET['s']) ? $_GET['s'] : ''); ?>" placeholder="<?php esc_attr_e('Search forms by title or description...', 'svbookings'); ?>">
                    </div>
                    <?php if (!empty($_GET['s'])): ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings')); ?>" class="sv-btn-link"><?php esc_html_e('Clear', 'svbookings'); ?></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <?php if (empty($forms)): ?>
            <div class="sv-empty-state">
                <div class="sv-empty-icon">
                    <span class="dashicons dashicons-forms"></span>
                </div>
                <h3><?php esc_html_e('No booking forms found', 'svbookings'); ?></h3>
                <p><?php esc_html_e('Get started by creating your first booking form with custom fields and time slots.', 'svbookings'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-form-builder')); ?>" class="sv-btn sv-btn-primary">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php esc_html_e('Create Your First Form', 'svbookings'); ?>
                </a>
            </div>
        <?php else: ?>
            <div class="sv-table-responsive">
                <table class="sv-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th><?php esc_html_e('Form Title', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Embed Shortcode', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Fields', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Total Bookings', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Status', 'svbookings'); ?></th>
                            <th><?php esc_html_e('Created Date', 'svbookings'); ?></th>
                            <th style="text-align: right;"><?php esc_html_e('Actions', 'svbookings'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms as $f): 
                            $fields = json_decode($f->fields, true) ?: [];
                            $field_count = count($fields);
                        ?>
                            <tr id="sv-form-row-<?php echo esc_attr($f->id); ?>">
                                <td><span class="sv-badge-id">#<?php echo esc_html($f->id); ?></span></td>
                                <td>
                                    <div class="sv-form-title-cell">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-form-builder&id=' . $f->id)); ?>" class="sv-form-title-link">
                                            <?php echo esc_html($f->title); ?>
                                        </a>
                                        <?php if (!empty($f->description)): ?>
                                            <p class="sv-form-desc"><?php echo esc_html(wp_trim_words($f->description, 10)); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="sv-shortcode-chip" data-shortcode="[sv_booking_form id=&quot;<?php echo esc_attr($f->id); ?>&quot;]">
                                        <code>[sv_booking_form id="<?php echo esc_attr($f->id); ?>"]</code>
                                        <button type="button" class="sv-copy-btn" title="<?php esc_attr_e('Copy shortcode', 'svbookings'); ?>">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <span class="sv-pill sv-pill-info">
                                        <?php echo esc_html($field_count); ?> <?php esc_html_e('Fields', 'svbookings'); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-entries&form_id=' . $f->id)); ?>" class="sv-bookings-count-link">
                                        <strong><?php echo esc_html($f->total_bookings); ?></strong> <?php esc_html_e('entries', 'svbookings'); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($f->status === 'active'): ?>
                                        <span class="sv-status-pill sv-status-active"><?php esc_html_e('Active', 'svbookings'); ?></span>
                                    <?php else: ?>
                                        <span class="sv-status-pill sv-status-inactive"><?php esc_html_e('Draft', 'svbookings'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="sv-date-text"><?php echo esc_html(date('M j, Y', strtotime($f->created_at))); ?></span>
                                </td>
                                <td>
                                    <div class="sv-actions-cell">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=sv-bookings-form-builder&id=' . $f->id)); ?>" class="sv-action-btn sv-action-edit" title="<?php esc_attr_e('Edit in Form Builder', 'svbookings'); ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                        </a>
                                        <button type="button" class="sv-action-btn sv-action-duplicate sv-duplicate-form-btn" data-id="<?php echo esc_attr($f->id); ?>" title="<?php esc_attr_e('Duplicate Form', 'svbookings'); ?>">
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </button>
                                        <button type="button" class="sv-action-btn sv-action-delete sv-delete-form-btn" data-id="<?php echo esc_attr($f->id); ?>" title="<?php esc_attr_e('Delete Form', 'svbookings'); ?>">
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

    <!-- Client Interface Tip -->
    <div class="sv-info-box">
        <div class="sv-info-box-icon">
            <span class="dashicons dashicons-info"></span>
        </div>
        <div class="sv-info-box-content">
            <h4><?php esc_html_e('Displaying Form & Client Builder on Your Website', 'svbookings'); ?></h4>
            <p><?php esc_html_e('Use the shortcode', 'svbookings'); ?> <code>[sv_booking_form id="YOUR_FORM_ID"]</code> <?php esc_html_e('on any page or post to show the booking interface to visitors. To allow clients or managers to create forms from the frontend interface, embed', 'svbookings'); ?> <code>[sv_booking_form_builder]</code>.</p>
        </div>
    </div>
</div>
