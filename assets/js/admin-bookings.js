(function ($) {
    'use strict';

    $(document).ready(function () {

        // -- Status dropdown quick-change
        $(document).on('change', '.sv-status-dropdown', function () {
            var $select   = $(this);
            var bookingId = $select.data('booking-id');
            var newStatus = $select.val();

            $select.removeClass('sv-status-pending sv-status-confirmed sv-status-completed sv-status-cancelled')
                   .addClass('sv-status-' + newStatus);

            $.post(svBookingsAdmin.ajaxUrl, {
                action:     'sv_update_booking_status',
                nonce:      svBookingsAdmin.nonce,
                booking_id: bookingId,
                status:     newStatus
            }, function (res) {
                if (!res.success) { alert(res.data.message || 'Could not update status.'); }
            });
        });

        // -- Delete booking
        $(document).on('click', '.sv-delete-booking-btn', function () {
            if (!confirm(svBookingsAdmin.strings.confirmDeleteBooking)) return;
            var bookingId = $(this).data('id');
            $.post(svBookingsAdmin.ajaxUrl, {
                action: 'sv_delete_booking', nonce: svBookingsAdmin.nonce, booking_id: bookingId
            }, function (res) {
                if (res.success) { $('#sv-booking-row-' + bookingId).fadeOut(300, function () { $(this).remove(); }); }
                else { alert(res.data.message || 'Delete failed.'); }
            });
        });

        // -- Delete form
        $(document).on('click', '.sv-delete-form-btn', function () {
            if (!confirm(svBookingsAdmin.strings.confirmDeleteForm)) return;
            var formId = $(this).data('id');
            $.post(svBookingsAdmin.ajaxUrl, {
                action: 'sv_delete_form', nonce: svBookingsAdmin.nonce, form_id: formId
            }, function (res) {
                if (res.success) { $('#sv-form-row-' + formId).fadeOut(300, function () { $(this).remove(); }); }
                else { alert(res.data.message || 'Delete failed.'); }
            });
        });

        // -- Duplicate form
        $(document).on('click', '.sv-duplicate-form-btn', function () {
            var formId = $(this).data('id');
            $.post(svBookingsAdmin.ajaxUrl, {
                action: 'sv_duplicate_form', nonce: svBookingsAdmin.nonce, form_id: formId
            }, function (res) {
                if (res.success) { window.location.reload(); }
                else { alert(res.data.message || 'Duplicate failed.'); }
            });
        });

        // -- View booking modal
        $(document).on('click', '.sv-view-booking-btn', function () {
            var bookingId = $(this).data('id');
            $('#sv-booking-modal').fadeIn(200);
            $('#sv-modal-body-content').html('<div class="sv-loading-spinner">Loading...</div>');

            $.get(svBookingsAdmin.ajaxUrl, {
                action: 'sv_get_booking_details', nonce: svBookingsAdmin.nonce, booking_id: bookingId
            }, function (res) {
                if (!res.success) {
                    $('#sv-modal-body-content').html('<p>Could not load booking.</p>');
                    return;
                }
                var b = res.data;

                // Custom field rows
                var customRows = '';
                if (b.custom_data && typeof b.custom_data === 'object') {
                    $.each(b.custom_data, function (k, v) {
                        var val = Array.isArray(v) ? v.join(', ') : v;
                        if (!val || val === '') val = '-';
                        customRows += '<div class="sv-detail-row"><span class="sv-detail-label">' + escHtml(k) + ':</span><span class="sv-detail-val">' + escHtml(String(val)) + '</span></div>';
                    });
                }

                // Raw editable values
                var rawDate      = b.booking_date       || '';
                var rawTimeStart = b.booking_time_start ? b.booking_time_start.substring(0, 5) : '';
                var rawTimeEnd   = b.booking_time_end   ? b.booking_time_end.substring(0, 5)   : '';

                var html =
                    '<div class="sv-modal-section">' +
                        '<div class="sv-modal-section-title">Booking Details</div>' +
                        '<div class="sv-detail-row"><span class="sv-detail-label">Reference:</span><span class="sv-detail-val">' + escHtml(b.booking_ref) + '</span></div>' +
                        '<div class="sv-detail-row"><span class="sv-detail-label">Status:</span><span class="sv-detail-val sv-status-pill sv-status-' + escHtml(b.status) + '">' + escHtml(b.status) + '</span></div>' +
                        '<div class="sv-detail-row"><span class="sv-detail-label">Form:</span><span class="sv-detail-val">' + escHtml(b.form_title || '-') + '</span></div>' +
                    '</div>' +
                    '<div class="sv-modal-section">' +
                        '<div class="sv-modal-section-title">Customer</div>' +
                        '<div class="sv-detail-row"><span class="sv-detail-label">Name:</span><span class="sv-detail-val">'  + escHtml(b.customer_name)         + '</span></div>' +
                        '<div class="sv-detail-row"><span class="sv-detail-label">Email:</span><span class="sv-detail-val">' + escHtml(b.customer_email)        + '</span></div>' +
                        '<div class="sv-detail-row"><span class="sv-detail-label">Phone:</span><span class="sv-detail-val">' + escHtml(b.customer_phone || '-') + '</span></div>' +
                    '</div>' +
                    '<div class="sv-modal-section" id="sv-appt-section">' +
                        '<div class="sv-modal-section-title">Appointment <small style="font-weight:400;color:#64748b;font-size:11px;">(Edit &amp; Save to reschedule)</small></div>' +
                        '<div class="sv-appt-edit-grid">' +
                            '<div class="sv-appt-field">' +
                                '<label class="sv-detail-label" style="display:block;margin-bottom:4px;">Date</label>' +
                                '<input type="date" id="sv-edit-date" class="sv-input sv-input-sm" value="' + escHtml(rawDate) + '" style="width:100%;">' +
                            '</div>' +
                            '<div class="sv-appt-field">' +
                                '<label class="sv-detail-label" style="display:block;margin-bottom:4px;">Start Time</label>' +
                                '<input type="time" id="sv-edit-time-start" class="sv-input sv-input-sm" value="' + escHtml(rawTimeStart) + '" style="width:100%;">' +
                            '</div>' +
                            '<div class="sv-appt-field">' +
                                '<label class="sv-detail-label" style="display:block;margin-bottom:4px;">End Time</label>' +
                                '<input type="time" id="sv-edit-time-end" class="sv-input sv-input-sm" value="' + escHtml(rawTimeEnd) + '" style="width:100%;">' +
                            '</div>' +
                        '</div>' +
                        '<div style="margin-top:10px;display:flex;align-items:center;gap:10px;">' +
                            '<button type="button" class="sv-btn sv-btn-sm sv-btn-primary" id="sv-save-appt-btn" data-booking-id="' + escHtml(String(b.id)) + '">' +
                                '<span class="dashicons dashicons-saved" style="font-size:14px;width:14px;height:14px;vertical-align:middle;margin-right:4px;"></span>Save Appointment' +
                            '</button>' +
                            '<span id="sv-appt-save-msg" style="font-size:12px;color:#10b981;display:none;">Appointment saved</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="sv-modal-section">' +
                        '<div class="sv-modal-section-title">Service &amp; Pricing</div>' +
                        '<div class="sv-detail-row"><span class="sv-detail-label">Service:</span><span class="sv-detail-val">' + escHtml(b.service_name || '-') + '</span></div>' +
                        '<div class="sv-detail-row"><span class="sv-detail-label">Price:</span><span class="sv-detail-val">$' + parseFloat(b.service_price).toFixed(2) + '</span></div>' +
                    '</div>';

                if (customRows) {
                    html += '<div class="sv-modal-section"><div class="sv-modal-section-title">Custom Field Responses</div>' + customRows + '</div>';
                }
                if (b.admin_notes) {
                    html += '<div class="sv-modal-section"><div class="sv-modal-section-title">Admin Notes</div><p style="margin:0;font-size:13px;">' + escHtml(b.admin_notes) + '</p></div>';
                }
                html += '<div class="sv-modal-section"><div class="sv-detail-row"><span class="sv-detail-label">Submitted:</span><span class="sv-detail-val">' + escHtml(b.formatted_created) + '</span></div></div>';

                $('#sv-modal-booking-ref').text('#' + b.booking_ref);
                $('#sv-modal-body-content').html(html);
            });
        });

        // -- Save appointment (reschedule)
        $(document).on('click', '#sv-save-appt-btn', function () {
            var $btn      = $(this);
            var bookingId = $btn.data('booking-id');
            var newDate   = $('#sv-edit-date').val();
            var newStart  = $('#sv-edit-time-start').val();
            var newEnd    = $('#sv-edit-time-end').val();

            if (!newDate) { alert('Please select a date.'); return; }

            $btn.prop('disabled', true).text('Saving...');

            $.post(svBookingsAdmin.ajaxUrl, {
                action:             'sv_update_booking_appointment',
                nonce:              svBookingsAdmin.nonce,
                booking_id:         bookingId,
                booking_date:       newDate,
                booking_time_start: newStart,
                booking_time_end:   newEnd
            }, function (res) {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved" style="font-size:14px;width:14px;height:14px;vertical-align:middle;margin-right:4px;"></span>Save Appointment');
                if (res.success) {
                    $('#sv-appt-save-msg').stop(true, true).fadeIn(200).delay(2500).fadeOut(400);
                    // Update table row inline
                    var $row = $('#sv-booking-row-' + bookingId);
                    if ($row.length && newDate) {
                        var parts  = newDate.split('-');
                        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        var fmtDate = months[parseInt(parts[1], 10) - 1] + ' ' + parseInt(parts[2], 10) + ', ' + parts[0];
                        $row.find('.sv-date-badge').html('<span class="dashicons dashicons-calendar-alt"></span> ' + fmtDate);
                        if (newStart && newEnd) {
                            $row.find('.sv-time-text').html('<span class="dashicons dashicons-clock"></span> ' + formatAmPm(newStart) + ' - ' + formatAmPm(newEnd));
                        }
                    }
                } else {
                    alert(res.data.message || 'Could not save appointment.');
                }
            }).fail(function () {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved" style="font-size:14px;width:14px;height:14px;vertical-align:middle;margin-right:4px;"></span>Save Appointment');
                alert('Network error. Please try again.');
            });
        });

        // -- Close modal: button always fires
        $(document).on('click', '#sv-close-modal-btn', function () {
            $('#sv-booking-modal').fadeOut(200);
        });
        // Backdrop click
        $('#sv-booking-modal').on('click', function (e) {
            if (e.target === this) { $('#sv-booking-modal').fadeOut(200); }
        });
        // Escape key
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $('#sv-booking-modal').is(':visible')) { $('#sv-booking-modal').fadeOut(200); }
        });

        // -- Shortcode copy chips
        $(document).on('click', '.sv-copy-btn', function (e) {
            e.preventDefault(); e.stopPropagation();
            var text = $(this).closest('[data-shortcode]').data('shortcode');
            if (text && navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function () { alert(svBookingsAdmin.strings.copied || 'Copied!'); });
            }
        });

        // -- Helpers
        function escHtml(str) {
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
        function formatAmPm(hhmm) {
            if (!hhmm) return '';
            var p = hhmm.split(':'), h = parseInt(p[0], 10), m = parseInt(p[1], 10);
            var ampm = h < 12 ? 'AM' : 'PM', h12 = h % 12 === 0 ? 12 : h % 12;
            return (h12 < 10 ? '0' + h12 : h12) + ':' + (m < 10 ? '0' + m : m) + ' ' + ampm;
        }

    });

})(jQuery);
