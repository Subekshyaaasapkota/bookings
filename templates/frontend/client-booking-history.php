<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="sv-client-lookup-wrap">
    <div class="sv-form-header">
        <h2 class="sv-form-title"><?php esc_html_e('Look Up Your Booking', 'svbookings'); ?></h2>
        <p class="sv-form-desc"><?php esc_html_e('Enter your booking reference or email address to view status and details.', 'svbookings'); ?></p>
    </div>

    <div class="sv-lookup-search-card">
        <div class="sv-form-grid">
            <div class="sv-col-8">
                <input type="text" id="sv-lookup-input" class="sv-input" placeholder="e.g. SVB-8A7B9C or your@email.com">
            </div>
            <div class="sv-col-4">
                <button type="button" id="sv-lookup-btn" class="sv-btn sv-btn-primary" style="width:100%;height:46px;justify-content:center;">
                    <?php esc_html_e('Find Booking', 'svbookings'); ?>
                </button>
            </div>
        </div>
    </div>

    <div id="sv-lookup-results" class="sv-lookup-results" style="display:none;margin-top:20px;">
        <!-- Dynamic results filled via JS -->
    </div>
</div>
