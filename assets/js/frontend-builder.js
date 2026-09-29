(function ($) {
    'use strict';

    var clientFields = [];

    $(document).ready(function () {

        if (!$('#sv-cf-fields-list').length) return;

        // ── Add field buttons ─────────────────────────────────────────────
        $(document).on('click', '.sv-cf-add-field-btn', function () {
            var type = $(this).data('type');
            addClientField(type);
        });

        // ── Remove field ──────────────────────────────────────────────────
        $(document).on('click', '.sv-cf-remove-field', function () {
            var idx = $(this).data('idx');
            clientFields.splice(idx, 1);
            renderClientFields();
        });

        // ── Label edit ────────────────────────────────────────────────────
        $(document).on('input', '.sv-cf-field-label-input', function () {
            var idx = $(this).data('idx');
            if (clientFields[idx]) clientFields[idx].label = $(this).val();
        });

        // ── Save / Create form ────────────────────────────────────────────
        $('#sv-cf-save-btn').on('click', function () {
            var title = $('#sv-cf-title').val().trim();
            if (!title) { alert('Please enter a form title.'); return; }
            if (!clientFields.length) { alert('Please add at least one field.'); return; }

            var settings = {
                slot_duration:  parseInt($('#sv-cf-slot-duration').val(), 10) || 30,
                currency_symbol: $('#sv-cf-currency').val() || '$',
                primary_color:   $('#sv-cf-color').val() || '#4f46e5',
                admin_email_notify: $('#sv-cf-email').val() || '',
                enable_client_email: 1
            };

            var $btn = $(this).prop('disabled', true).text('Creating…');

            $.post(svBookingsFrontend.ajaxUrl, {
                action: 'sv_save_form',
                nonce:   svBookingsFrontend.nonce,
                form_id: 0,
                title:   title,
                description: $('#sv-cf-desc').val(),
                status: 'active',
                settings: JSON.stringify(settings),
                fields:   JSON.stringify(clientFields)
            }, function (res) {
                $btn.prop('disabled', false).text('Create & Publish Booking Form');
                if (res.success) {
                    $('#sv-cf-shortcode-output').text(res.data.shortcode || '[sv_booking_form id="' + res.data.form_id + '"]');
                    $('#sv-cf-result').slideDown(300);
                } else {
                    alert(res.data.message || 'Could not save form.');
                }
            }).fail(function () {
                $btn.prop('disabled', false).text('Create & Publish Booking Form');
                alert('Network error.');
            });
        });

        // ── Copy shortcode ────────────────────────────────────────────────
        $('#sv-cf-copy-shortcode-btn').on('click', function () {
            var code = $('#sv-cf-shortcode-output').text();
            if (navigator.clipboard) {
                navigator.clipboard.writeText(code).then(function () {
                    alert(svBookingsFrontend.strings.copied || 'Copied!');
                });
            }
        });
    });

    function addClientField(type) {
        var defaults = {
            service:        { label: 'Select Service',      name: 'service_type',  options: [{ label: 'Service A', value: 'svc_a', price: 50, duration: 30 }] },
            datetime_picker:{ label: 'Preferred Date & Time', name: 'booking_schedule', options: [] },
            text:           { label: 'Full Name',           name: 'customer_name', options: [] },
            email:          { label: 'Email Address',       name: 'customer_email', options: [] },
            phone:          { label: 'Phone Number',        name: 'customer_phone', options: [] },
            select:         { label: 'Select Option',       name: 'choice_field',  options: [{ label: 'Option 1', value: 'opt_1' }] },
            checkbox_group: { label: 'Add-on Options',      name: 'addons',        options: [{ label: 'Extra (+$10)', value: 'extra', price: 10 }] }
        };

        var def = defaults[type] || { label: 'New Field', name: 'field_' + Date.now(), options: [] };

        clientFields.push({
            id:       'cf_' + Date.now() + '_' + Math.floor(Math.random() * 1000),
            type:     type,
            label:    def.label,
            name:     def.name,
            required: (type === 'text' || type === 'email' || type === 'datetime_picker' || type === 'service'),
            width:    '100',
            options:  def.options
        });

        renderClientFields();
    }

    function renderClientFields() {
        var $list = $('#sv-cf-fields-list');
        $list.empty();

        if (!clientFields.length) {
            $list.html('<p style="color:#94a3b8;font-size:13px;text-align:center;padding:20px 0;">No fields yet. Click a button above to add one.</p>');
            return;
        }

        $.each(clientFields, function (idx, f) {
            var typeMap = {
                service: 'Service Card', datetime_picker: 'Date + Slots', text: 'Text',
                email: 'Email', phone: 'Phone', select: 'Dropdown', checkbox_group: 'Checkboxes'
            };

            var html =
                '<div class="sv-cf-field-item" data-idx="' + idx + '">' +
                '<span class="sv-pill sv-pill-info" style="white-space:nowrap;font-size:11px;">' + (typeMap[f.type] || f.type) + '</span>' +
                '<input type="text" class="sv-cf-field-label-input sv-input" data-idx="' + idx + '" value="' + esc(f.label) + '" placeholder="Field label" style="flex:1;">' +
                '<button type="button" class="sv-action-btn sv-action-delete sv-cf-remove-field" data-idx="' + idx + '" title="Remove">' +
                '<span class="dashicons dashicons-trash"></span></button>' +
                '</div>';

            $list.append(html);
        });
    }

    function esc(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

})(jQuery);
