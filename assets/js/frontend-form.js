(function ($) {
    'use strict';

    $(document).ready(function () {

        // ── Bootstrap every booking form on the page ──────────────────────
        $('.sv-client-booking-form').each(function () {
            initBookingForm($(this));
        });

        function initBookingForm($form) {
            var formId   = $form.data('form-id');
            var currency = $form.data('currency') || '$';

            // ── Flatpickr on date input ───────────────────────────────────
            var $dateInput = $form.find('.sv-booking-date-input');
            if ($dateInput.length && typeof flatpickr !== 'undefined') {
                flatpickr($dateInput[0], {
                    minDate: 'today',
                    dateFormat: 'Y-m-d',
                    onChange: function (selDates, dateStr) {
                        if (dateStr) loadSlots($form, formId, dateStr);
                    }
                });
            }

            // ── Service card selection ────────────────────────────────────
            $form.on('click', '.sv-service-card', function () {
                $form.find('.sv-service-card').removeClass('selected');
                $(this).addClass('selected');
                $(this).find('input[type="radio"]').prop('checked', true);
                updateTotal($form, currency);
            });

            // ── Add-on checkbox price ─────────────────────────────────────
            $form.on('change', '.sv-addon-checkbox', function () {
                updateTotal($form, currency);
            });

            // ── Submit ────────────────────────────────────────────────────
            $form.on('submit', function (e) {
                e.preventDefault();
                submitBooking($form, formId, currency);
            });

            // ── New booking button (after success) ────────────────────────
            $form.closest('.sv-booking-container').on('click', '.sv-new-booking-btn', function () {
                $form.closest('.sv-booking-container').find('.sv-booking-success-view').hide();
                $form.show().trigger('reset');
                $form.find('.sv-service-card').first().addClass('selected').find('input').prop('checked', true);
                updateTotal($form, currency);
            });

            // Initial total
            updateTotal($form, currency);
        }

        // ── Load available time slots ─────────────────────────────────────
        function loadSlots($form, formId, date) {
            var $slotsGrid   = $form.find('.sv-slots-grid');
            var $slotsNotice = $form.find('.sv-slots-notice');
            var $timeStart   = $form.find('.sv-selected-time-start');
            var $timeEnd     = $form.find('#sv_time_end_' + formId);

            $slotsGrid.hide().empty();
            $slotsNotice.show().find('span').text(svBookingsFrontend.strings.loadingSlots);
            $timeStart.val('');
            if ($timeEnd.length) $timeEnd.val('');

            $.get(svBookingsFrontend.ajaxUrl, {
                action: 'sv_get_available_slots',
                form_id: formId,
                date: date
            }, function (res) {
                if (!res.success || !res.data.available || !res.data.slots.length) {
                    $slotsNotice.find('span').text(svBookingsFrontend.strings.noSlots);
                    return;
                }

                $slotsNotice.hide();
                $slotsGrid.show();

                $.each(res.data.slots, function (i, slot) {
                    var cls = slot.available ? 'sv-slot-chip' : 'sv-slot-chip disabled';
                    var $chip = $('<div>')
                        .addClass(cls)
                        .text(slot.formatted)
                        .data('start', slot.start)
                        .data('end', slot.end);

                    if (slot.available) {
                        $chip.on('click', function () {
                            $slotsGrid.find('.sv-slot-chip').removeClass('selected');
                            $(this).addClass('selected');
                            $timeStart.val(slot.start);
                            if ($timeEnd.length) $timeEnd.val(slot.end);
                        });
                    }

                    $slotsGrid.append($chip);
                });
            }).fail(function () {
                $slotsNotice.find('span').text(svBookingsFrontend.strings.noSlots);
            });
        }

        // ── Live price total ──────────────────────────────────────────────
        function updateTotal($form, currency) {
            var formId = $form.data('form-id');
            var base   = parseFloat($form.find('input[name="service_name"]:checked').data('price') || 0);
            var addons = 0;

            $form.find('.sv-addon-checkbox:checked').each(function () {
                addons += parseFloat($(this).data('addon-price') || 0);
            });

            var total = base + addons;
            $form.find('#sv_service_price_' + formId).val(total.toFixed(2));
            $form.closest('.sv-booking-container').find('#sv-live-total-' + formId)
                 .text(currency + total.toFixed(2));
        }

        // ── Submit booking form ───────────────────────────────────────────
        function submitBooking($form, formId, currency) {
            // Basic validation
            var $dateInput = $form.find('.sv-booking-date-input');
            var $timeStart = $form.find('.sv-selected-time-start');

            if ($dateInput.length && !$dateInput.val()) {
                alert(svBookingsFrontend.strings.requiredMissing || 'Please fill all required fields.');
                return;
            }
            if ($form.find('.sv-slots-grid').is(':visible') && !$timeStart.val()) {
                alert(svBookingsFrontend.strings.selectSlot || 'Please select a time slot.');
                return;
            }

            var $btn = $form.find('.sv-submit-btn');
            $btn.prop('disabled', true).find('.sv-btn-label').text(svBookingsFrontend.strings.submitting || 'Processing…');

            var formData = $form.serializeArray();
            formData.push({ name: 'action', value: 'sv_submit_booking' });
            formData.push({ name: 'nonce',  value: svBookingsFrontend.nonce });

            $.post(svBookingsFrontend.ajaxUrl, $.param(formData), function (res) {
                $btn.prop('disabled', false).find('.sv-btn-label').text('Confirm & Complete Booking');

                if (!res.success) {
                    alert(res.data.message || 'Booking failed. Please try again.');
                    return;
                }

                var d = res.data.details;
                var $container = $form.closest('.sv-booking-container');

                // Populate success receipt
                $container.find('#sv-receipt-ref-code').text(res.data.booking_ref);
                $container.find('#sv-receipt-customer').text(d.customer_name);
                $container.find('#sv-receipt-service').text(d.service || '—');
                $container.find('#sv-receipt-datetime').text(d.date + (d.time ? ' @ ' + d.time : ''));
                $container.find('#sv-receipt-price').text(d.price);
                $container.find('#sv-success-msg-text').text(res.data.message);

                $form.hide();
                $container.find('.sv-booking-success-view').fadeIn(300);
            }).fail(function () {
                $btn.prop('disabled', false).find('.sv-btn-label').text('Confirm & Complete Booking');
                alert('Network error. Please try again.');
            });
        }

    });

})(jQuery);
