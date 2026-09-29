(function ($) {
    'use strict';

    let currentFields = [];
    let activeFieldId = null;
    let sortableInstance = null;

    $(document).ready(function () {
        initBuilder();
    });

    function initBuilder() {
        // Load initial fields from hidden input
        const initialFieldsRaw = $('#sv-initial-fields').val();
        if (initialFieldsRaw) {
            try {
                currentFields = JSON.parse(initialFieldsRaw);
            } catch (e) {
                currentFields = [];
            }
        }

        // Pre-populate default fields for a brand-new (empty) form
        if (currentFields.length === 0) {
            var ts = Date.now();
            currentFields = [
                {
                    id: 'field_' + ts + '_2',
                    type: 'datetime_picker',
                    label: 'Preferred Date & Time',
                    name: 'booking_schedule',
                    placeholder: '',
                    required: true,
                    width: '100',
                    help_text: 'Pick an available date and time slot.',
                    options: []
                },
                {
                    id: 'field_' + ts + '_3',
                    type: 'text',
                    label: 'Full Name',
                    name: 'customer_name',
                    placeholder: 'John Doe',
                    required: true,
                    width: '50',
                    help_text: '',
                    options: []
                },
                {
                    id: 'field_' + ts + '_4',
                    type: 'email',
                    label: 'Email Address',
                    name: 'customer_email',
                    placeholder: 'john@example.com',
                    required: true,
                    width: '50',
                    help_text: 'Confirmation details will be sent here.',
                    options: []
                },
                {
                    id: 'field_' + ts + '_5',
                    type: 'phone',
                    label: 'Phone Number',
                    name: 'customer_phone',
                    placeholder: '+1 (555) 000-0000',
                    required: false,
                    width: '50',
                    help_text: '',
                    options: []
                },
                {
                    id: 'field_' + ts + '_6',
                    type: 'textarea',
                    label: 'Additional Notes',
                    name: 'project_notes',
                    placeholder: 'Any specific details or requests...',
                    required: false,
                    width: '100',
                    help_text: '',
                    options: []
                }
            ];
        }

        renderCanvas();
        initSortable();
        bindEvents();
    }

    function initSortable() {
        const el = document.getElementById('sv-form-canvas');
        if (!el || typeof Sortable === 'undefined') return;

        sortableInstance = Sortable.create(el, {
            animation: 150,
            handle: '.sv-drag-handle',
            ghostClass: 'sv-sortable-ghost',
            onEnd: function () {
                reorderFieldsFromDOM();
            }
        });
    }

    function reorderFieldsFromDOM() {
        const newOrder = [];
        $('#sv-form-canvas .sv-canvas-item').each(function () {
            const fid = $(this).data('field-id');
            const found = currentFields.find(f => f.id === fid);
            if (found) newOrder.push(found);
        });
        currentFields = newOrder;
        updateFieldCounter();
    }

    function renderCanvas() {
        const $canvas = $('#sv-form-canvas');
        $canvas.empty();

        if (currentFields.length === 0) {
            $('#sv-canvas-empty').show();
            $('#sv-field-inspector .sv-inspector-empty').show();
            $('#sv-field-inspector .sv-inspector-form').hide();
            updateFieldCounter();
            return;
        }

        $('#sv-canvas-empty').hide();

        currentFields.forEach(function (field) {
            const isActive = field.id === activeFieldId ? 'active' : '';
            const typeLabel = getFieldTypeLabel(field.type);
            const reqBadge = field.required ? '<span style="color:#ef4444;margin-left:4px;">*</span>' : '';
            const widthLabel = field.width ? field.width + '%' : '100%';

            const html = `
                <div class="sv-canvas-item ${isActive}" data-field-id="${field.id}">
                    <div class="sv-item-handle-wrap">
                        <span class="dashicons dashicons-menu sv-drag-handle"></span>
                        <div class="sv-item-meta">
                            <span class="sv-item-label">${escapeHtml(field.label || 'Untitled Field')} ${reqBadge}</span>
                            <span class="sv-item-type">${typeLabel} &bull; Width: ${widthLabel}</span>
                        </div>
                    </div>
                    <div class="sv-item-actions">
                        <button type="button" class="sv-action-btn sv-dup-field-btn" title="Duplicate Field" data-id="${field.id}">
                            <span class="dashicons dashicons-admin-page"></span>
                        </button>
                        <button type="button" class="sv-action-btn sv-action-delete sv-del-field-btn" title="Remove Field" data-id="${field.id}">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
            `;
            $canvas.append(html);
        });

        updateFieldCounter();

        if (activeFieldId) {
            const activeField = currentFields.find(f => f.id === activeFieldId);
            if (activeField) {
                openInspector(activeField);
            }
        }
    }

    function updateFieldCounter() {
        const count = currentFields.length;
        $('#sv-field-counter').text(count + (count === 1 ? ' field' : ' fields'));
    }

    function getFieldTypeLabel(type) {
        const map = {
            'service': 'Service / Package',
            'datetime_picker': 'Date & Time Scheduler',
            'text': 'Single-line Text',
            'email': 'Email Address',
            'phone': 'Phone Number',
            'number': 'Number',
            'textarea': 'Multi-line Textarea',
            'select': 'Dropdown Select',
            'radio': 'Radio Buttons',
            'checkbox_group': 'Checkboxes / Add-ons',
            'section': 'Section Header'
        };
        return map[type] || type;
    }

    function addField(type) {
        const uniqueId = 'field_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
        let defaultLabel = 'New ' + getFieldTypeLabel(type);
        let defaultName = type + '_' + Math.floor(Math.random() * 10000);
        let defaultWidth = '100';
        let defaultOptions = [];

        if (type === 'service') {
            defaultLabel = 'Select Service';
            defaultName = 'service_type';
            defaultOptions = [
                { label: 'Standard Session (30m)', value: 'standard', price: 50, duration: 30 },
                { label: 'Extended Session (60m)', value: 'extended', price: 90, duration: 60 }
            ];
        } else if (type === 'datetime_picker') {
            defaultLabel = 'Preferred Appointment Slot';
            defaultName = 'booking_schedule';
        } else if (type === 'email') {
            defaultLabel = 'Email Address';
            defaultName = 'customer_email';
            defaultWidth = '50';
        } else if (type === 'phone') {
            defaultLabel = 'Phone Number';
            defaultName = 'customer_phone';
            defaultWidth = '50';
        } else if (type === 'text') {
            defaultLabel = 'Full Name';
            defaultName = 'customer_name';
            defaultWidth = '50';
        } else if (type === 'select' || type === 'radio') {
            defaultLabel = 'Select Option';
            defaultOptions = [
                { label: 'Option 1', value: 'opt_1' },
                { label: 'Option 2', value: 'opt_2' }
            ];
        } else if (type === 'checkbox_group') {
            defaultLabel = 'Add-on Options';
            defaultOptions = [
                { label: 'Priority Support (+$25)', value: 'priority_support', price: 25 },
                { label: 'Detailed Summary Report (+$15)', value: 'summary_report', price: 15 }
            ];
        }

        const newField = {
            id: uniqueId,
            type: type,
            label: defaultLabel,
            name: defaultName,
            placeholder: '',
            required: (type === 'text' || type === 'email' || type === 'datetime_picker' || type === 'service'),
            width: defaultWidth,
            help_text: '',
            options: defaultOptions
        };

        currentFields.push(newField);
        activeFieldId = newField.id;
        renderCanvas();
    }

    function openInspector(field) {
        $('#sv-field-inspector .sv-inspector-empty').hide();
        $('#sv-field-inspector .sv-inspector-form').show();

        $('#sv-inspector-title').text('Edit: ' + (field.label || 'Field'));
        $('#sv-inspector-type-badge').text(getFieldTypeLabel(field.type));

        $('#sv-prop-label').val(field.label || '');
        $('#sv-prop-name').val(field.name || '');
        $('#sv-prop-placeholder').val(field.placeholder || '');
        $('#sv-prop-help').val(field.help_text || '');
        $('#sv-prop-width').val(field.width || '100');
        $('#sv-prop-required').prop('checked', !!field.required);

        // Hide placeholder for non-text fields
        if (field.type === 'service' || field.type === 'datetime_picker' || field.type === 'section' || field.type === 'checkbox_group' || field.type === 'radio') {
            $('#sv-prop-placeholder-group').hide();
        } else {
            $('#sv-prop-placeholder-group').show();
        }

        // Options editor for choice / service types
        if (field.type === 'select' || field.type === 'radio' || field.type === 'checkbox_group' || field.type === 'service') {
            $('#sv-options-editor-group').show();
            renderOptionsEditor(field);
        } else {
            $('#sv-options-editor-group').hide();
        }
    }

    function renderOptionsEditor(field) {
        const $list = $('#sv-options-list');
        $list.empty();
        const options = field.options || [];

        options.forEach(function (opt, idx) {
            const hasPrice = (field.type === 'service' || field.type === 'checkbox_group');
            const hasDuration = (field.type === 'service');

            let extraInputs = '';
            if (hasPrice) {
                extraInputs += `<input type="number" class="sv-opt-price sv-input" style="width:70px;" placeholder="Price" value="${opt.price || 0}" data-idx="${idx}" title="Price Add-on">`;
            }
            if (hasDuration) {
                extraInputs += `<input type="number" class="sv-opt-duration sv-input" style="width:65px;" placeholder="Mins" value="${opt.duration || 30}" data-idx="${idx}" title="Duration in Mins">`;
            }

            const rowHtml = `
                <div class="sv-option-row" data-idx="${idx}">
                    <input type="text" class="sv-opt-label sv-input" placeholder="Label" value="${escapeHtml(opt.label || '')}" data-idx="${idx}">
                    <input type="text" class="sv-opt-val sv-input" placeholder="Value" value="${escapeHtml(opt.value || '')}" data-idx="${idx}" style="width:90px;">
                    ${extraInputs}
                    <button type="button" class="sv-remove-opt-btn" data-idx="${idx}" title="Remove option">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
            `;
            $list.append(rowHtml);
        });
    }

    function bindEvents() {
        // Tab Switcher
        $('.sv-tab-btn').on('click', function () {
            const tab = $(this).data('tab');
            $('.sv-tab-btn').removeClass('active');
            $(this).addClass('active');

            $('.sv-tab-content').removeClass('active');
            $('#sv-tab-' + tab).addClass('active');
        });

        // Add field from palette
        $('.sv-palette-item').on('click', function () {
            const type = $(this).data('type');
            addField(type);
        });

        // Click canvas item to select & inspect
        $('#sv-form-canvas').on('click', '.sv-canvas-item', function (e) {
            if ($(e.target).closest('.sv-item-actions').length || $(e.target).hasClass('sv-drag-handle')) {
                return;
            }
            const fid = $(this).data('field-id');
            activeFieldId = fid;
            $('.sv-canvas-item').removeClass('active');
            $(this).addClass('active');

            const field = currentFields.find(f => f.id === fid);
            if (field) openInspector(field);
        });

        // Delete field
        $('#sv-form-canvas').on('click', '.sv-del-field-btn', function (e) {
            e.stopPropagation();
            const fid = $(this).data('id');
            currentFields = currentFields.filter(f => f.id !== fid);
            if (activeFieldId === fid) {
                activeFieldId = currentFields.length ? currentFields[0].id : null;
            }
            renderCanvas();
        });

        // Duplicate field
        $('#sv-form-canvas').on('click', '.sv-dup-field-btn', function (e) {
            e.stopPropagation();
            const fid = $(this).data('id');
            const orig = currentFields.find(f => f.id === fid);
            if (orig) {
                const clone = JSON.parse(JSON.stringify(orig));
                clone.id = 'field_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
                clone.label = clone.label + ' (Copy)';
                clone.name = clone.name + '_copy';
                const idx = currentFields.findIndex(f => f.id === fid);
                currentFields.splice(idx + 1, 0, clone);
                activeFieldId = clone.id;
                renderCanvas();
            }
        });

        // Clear All
        $('#sv-clear-canvas-btn').on('click', function () {
            if (confirm('Clear all fields from this form?')) {
                currentFields = [];
                activeFieldId = null;
                renderCanvas();
            }
        });

        // Inspector Real-time updates
        $('#sv-prop-label').on('input', function () {
            if (!activeFieldId) return;
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f) {
                f.label = $(this).val();
                $(`.sv-canvas-item[data-field-id="${activeFieldId}"] .sv-item-label`).text(f.label || 'Untitled');
                $('#sv-inspector-title').text('Edit: ' + (f.label || 'Field'));
            }
        });

        $('#sv-prop-name').on('input', function () {
            if (!activeFieldId) return;
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f) f.name = $(this).val().toLowerCase().replace(/[^a-z0-9_]/g, '_');
        });

        $('#sv-prop-placeholder').on('input', function () {
            if (!activeFieldId) return;
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f) f.placeholder = $(this).val();
        });

        $('#sv-prop-help').on('input', function () {
            if (!activeFieldId) return;
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f) f.help_text = $(this).val();
        });

        $('#sv-prop-width').on('change', function () {
            if (!activeFieldId) return;
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f) {
                f.width = $(this).val();
                $(`.sv-canvas-item[data-field-id="${activeFieldId}"] .sv-item-type`).text(getFieldTypeLabel(f.type) + ' • Width: ' + f.width + '%');
            }
        });

        $('#sv-prop-required').on('change', function () {
            if (!activeFieldId) return;
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f) f.required = $(this).is(':checked');
        });

        // Options Manager Events
        $('#sv-add-option-btn').on('click', function () {
            if (!activeFieldId) return;
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f) {
                if (!f.options) f.options = [];
                const count = f.options.length + 1;
                const defaultLabel = 'Option ' + count;
                f.options.push({
                    label: defaultLabel,
                    value: defaultLabel.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, ''),
                    price: 0,
                    duration: 30
                });
                renderOptionsEditor(f);
            }
        });

        $('#sv-options-list').on('input', '.sv-opt-label', function () {
            const idx = $(this).data('idx');
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f && f.options && f.options[idx]) {
                f.options[idx].label = $(this).val();
                // Auto-sync value field only if value still matches the auto-generated slug pattern
                const $valueInput = $(this).closest('.sv-option-row').find('.sv-opt-val');
                const currentVal = $valueInput.val();
                const autoSlug = currentVal.match(/^option_\d+$/) || currentVal.match(/^opt_\d+$/);
                if (autoSlug || currentVal === '') {
                    const newSlug = $(this).val().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '');
                    $valueInput.val(newSlug);
                    f.options[idx].value = newSlug;
                }
            }
        });

        $('#sv-options-list').on('input', '.sv-opt-val', function () {
            const idx = $(this).data('idx');
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f && f.options && f.options[idx]) {
                f.options[idx].value = $(this).val();
            }
        });

        $('#sv-options-list').on('input', '.sv-opt-price', function () {
            const idx = $(this).data('idx');
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f && f.options && f.options[idx]) {
                f.options[idx].price = parseFloat($(this).val()) || 0;
            }
        });

        $('#sv-options-list').on('input', '.sv-opt-duration', function () {
            const idx = $(this).data('idx');
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f && f.options && f.options[idx]) {
                f.options[idx].duration = parseInt($(this).val(), 10) || 30;
            }
        });

        $('#sv-options-list').on('click', '.sv-remove-opt-btn', function () {
            const idx = $(this).data('idx');
            const f = currentFields.find(f => f.id === activeFieldId);
            if (f && f.options) {
                f.options.splice(idx, 1);
                renderOptionsEditor(f);
            }
        });

        // Color picker sync
        $('#sv-set-primary-color').on('input', function () {
            $('#sv-set-primary-color-text').val($(this).val());
        });
        $('#sv-set-primary-color-text').on('input', function () {
            $('#sv-set-primary-color').val($(this).val());
        });

        // Copy shortcode button
        $(document).on('click', '.sv-copy-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const text = $(this).closest('[data-shortcode]').data('shortcode');
            if (text) {
                navigator.clipboard.writeText(text).then(() => {
                    alert(svBookingsAdmin.strings.copied || 'Copied to clipboard!');
                });
            }
        });

        // Save Form (Publish)
        $('#sv-save-form-btn').on('click', function () {
            saveForm();
        });

        // Preview Form Modal
        $('#sv-preview-form-btn').on('click', function () {
            openPreviewModal();
        });

        $('#sv-close-preview-btn').on('click', function () {
            closePreviewModal();
        });

        $('#sv-preview-modal').on('click', function (e) {
            if (e.target === this) {
                closePreviewModal();
            }
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $('#sv-preview-modal').is(':visible')) {
                closePreviewModal();
            }
        });

        // Device switch in preview modal
        $(document).on('click', '.sv-device-btn', function () {
            const device = $(this).data('device');
            $('.sv-device-btn').removeClass('active');
            $(this).addClass('active');

            const $card = $('#sv-preview-modal .sv-modal-card-lg');
            if (device === 'mobile') {
                $card.addClass('sv-preview-mobile');
            } else {
                $card.removeClass('sv-preview-mobile');
            }
        });
    }

    function openPreviewModal() {
        const title = $('#sv-builder-form-title').val().trim() || 'Booking Form Preview';
        const description = $('#sv-set-description').val().trim();
        const primaryColor = $('#sv-set-primary-color').val() || '#4f46e5';
        const currency = $('#sv-set-currency').val() || '$';
        const confirmMsg = $('#sv-set-confirm-msg').val() || 'Thank you for booking with us! We have sent a confirmation email with all details.';

        let fieldsHtml = '';

        if (currentFields.length === 0) {
            fieldsHtml = '<div class="sv-col-12" style="text-align:center;padding:40px 20px;color:#64748b;">This form currently has no fields. Add fields from the left panel to see them in this preview.</div>';
        } else {
            currentFields.forEach(function (f) {
                const label = escapeHtml(f.label || '');
                const req = f.required ? '<span class="sv-required">*</span>' : '';
                const help = f.help_text ? `<small class="sv-field-help">${escapeHtml(f.help_text)}</small>` : '';
                const placeholder = escapeHtml(f.placeholder || '');
                const width = f.width || '100';
                let colClass = 'sv-col-12';
                if (width === '50') colClass = 'sv-col-6';
                if (width === '33') colClass = 'sv-col-4';

                if (f.type === 'section') {
                    fieldsHtml += `
                        <div class="sv-col-12 sv-section-header-wrap">
                            <h3 class="sv-section-title">${label}</h3>
                            ${f.help_text ? `<p class="sv-section-desc">${escapeHtml(f.help_text)}</p>` : ''}
                        </div>
                    `;
                } else if (f.type === 'service') {
                    let optionsHtml = '';
                    if (f.options && f.options.length) {
                        f.options.forEach(function (opt, idx) {
                            const optLabel = escapeHtml(opt.label || 'Service');
                            const optPrice = parseFloat(opt.price || 0).toFixed(2);
                            const optDur = parseInt(opt.duration || 30, 10);
                            const isChecked = idx === 0 ? 'checked' : '';
                            const isSelected = idx === 0 ? 'selected' : '';
                            optionsHtml += `
                                <label class="sv-service-card ${isSelected}">
                                    <input type="radio" name="preview_service" value="${optLabel}" data-price="${optPrice}" data-duration="${optDur}" ${isChecked}>
                                    <div class="sv-service-card-body">
                                        <div class="sv-service-card-top">
                                            <span class="sv-service-name">${optLabel}</span>
                                            <span class="sv-service-price">${currency}${optPrice}</span>
                                        </div>
                                        <div class="sv-service-meta">
                                            <span class="sv-service-duration">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                ${optDur} mins
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            `;
                        });
                    }
                    fieldsHtml += `
                        <div class="sv-col-12 sv-service-group-wrap">
                            <label class="sv-label">${label} ${req}</label>
                            ${f.help_text ? `<div class="sv-field-help">${escapeHtml(f.help_text)}</div>` : ''}
                            <div class="sv-services-cards">
                                ${optionsHtml || '<p style="color:#94a3b8;font-size:13px;">No service options configured.</p>'}
                            </div>
                        </div>
                    `;
                } else if (f.type === 'datetime_picker') {
                    const todayStr = new Date().toISOString().split('T')[0];
                    // Generate time slots from business hours settings
                    const startTime = $('#sv-set-hours-start').val() || '09:00';
                    const endTime   = $('#sv-set-hours-end').val()   || '17:00';
                    const slotDur   = parseInt($('#sv-set-slot-duration').val(), 10) || 30;
                    const slotsHtml = generateTimeSlots(startTime, endTime, slotDur);
                    fieldsHtml += `
                        <div class="sv-col-12 sv-scheduler-box">
                            <label class="sv-label">${label} ${req}</label>
                            ${f.help_text ? `<div class="sv-field-help">${escapeHtml(f.help_text)}</div>` : ''}
                            <div class="sv-scheduler-grid">
                                <div class="sv-date-picker-column">
                                    <label class="sv-sublabel">1. Select Date</label>
                                    <div class="sv-input-icon-wrap">
                                        <svg class="sv-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        <input type="text" class="sv-input sv-booking-date-input sv-preview-date-input" placeholder="Pick an available date..." value="${todayStr}" readonly>
                                    </div>
                                </div>
                                <div class="sv-time-slots-column">
                                    <label class="sv-sublabel">2. Select Available Time Slot</label>
                                    <div class="sv-slots-wrapper">
                                        <div class="sv-slots-grid">
                                            ${slotsHtml}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else if (f.type === 'textarea') {
                    fieldsHtml += `
                        <div class="${colClass}">
                            <label class="sv-label" for="preview_${f.id}">${label} ${req}</label>
                            <textarea id="preview_${f.id}" class="sv-textarea" rows="4" placeholder="${placeholder}"></textarea>
                            ${help}
                        </div>
                    `;
                } else if (f.type === 'select') {
                    let optsHtml = `<option value="">${placeholder ? placeholder : '-- Select an Option --'}</option>`;
                    if (f.options && f.options.length) {
                        f.options.forEach(function (opt) {
                            optsHtml += `<option value="${escapeHtml(opt.value || opt.label)}">${escapeHtml(opt.label)}</option>`;
                        });
                    }
                    fieldsHtml += `
                        <div class="${colClass}">
                            <label class="sv-label">${label} ${req}</label>
                            <select class="sv-select">${optsHtml}</select>
                            ${help}
                        </div>
                    `;
                } else if (f.type === 'radio') {
                    let optsHtml = '';
                    if (f.options && f.options.length) {
                        f.options.forEach(function (opt, idx) {
                            const optLabel = escapeHtml(opt.label);
                            const isChecked = idx === 0 ? 'checked' : '';
                            optsHtml += `
                                <label class="sv-radio-option">
                                    <input type="radio" name="preview_${f.id}" value="${escapeHtml(opt.value || opt.label)}" ${isChecked}>
                                    <span>${optLabel}</span>
                                </label>
                            `;
                        });
                    }
                    fieldsHtml += `
                        <div class="${colClass}">
                            <label class="sv-label">${label} ${req}</label>
                            <div class="sv-choice-group">${optsHtml}</div>
                            ${help}
                        </div>
                    `;
                } else if (f.type === 'checkbox_group') {
                    let optsHtml = '';
                    if (f.options && f.options.length) {
                        f.options.forEach(function (opt) {
                            const optLabel = escapeHtml(opt.label);
                            const price = parseFloat(opt.price || 0);
                            const priceTag = price > 0 ? ` (+$${price.toFixed(2)})` : '';
                            optsHtml += `
                                <label class="sv-checkbox-option">
                                    <input type="checkbox" name="preview_${f.id}[]" value="${escapeHtml(opt.value || opt.label)}" data-addon-price="${price}" class="sv-preview-addon-checkbox">
                                    <span>${optLabel}${priceTag}</span>
                                </label>
                            `;
                        });
                    }
                    fieldsHtml += `
                        <div class="${colClass}">
                            <label class="sv-label">${label} ${req}</label>
                            <div class="sv-choice-group">${optsHtml}</div>
                            ${help}
                        </div>
                    `;
                } else {
                    const inputType = (f.type === 'email' || f.type === 'number') ? f.type : (f.type === 'phone' ? 'tel' : 'text');
                    fieldsHtml += `
                        <div class="${colClass}">
                            <label class="sv-label" for="preview_${f.id}">${label} ${req}</label>
                            <div class="sv-input-wrap">
                                <input type="${inputType}" id="preview_${f.id}" class="sv-input sv-preview-input-${f.type}" placeholder="${placeholder}">
                            </div>
                            ${help}
                        </div>
                    `;
                }
            });
        }

        const previewContainerHtml = `
            <div class="sv-booking-container" style="--sv-primary: ${primaryColor};">
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
                    <h2 class="sv-form-title">${escapeHtml(title)}</h2>
                    ${description ? `<p class="sv-form-desc">${escapeHtml(description)}</p>` : ''}
                </div>

                <!-- Main Booking Form -->
                <form onsubmit="return false;" class="sv-client-booking-form sv-preview-booking-form">
                    <div class="sv-form-grid">
                        ${fieldsHtml}
                    </div>

                    <!-- Summary & Submission Bar -->
                    <div class="sv-booking-summary-bar">
                        <div class="sv-summary-left">
                            <div class="sv-summary-price-label">Estimated Total:</div>
                            <div class="sv-summary-price-value" id="sv-preview-total-display">${currency}0.00</div>
                        </div>
                        <div class="sv-summary-right">
                            <button type="button" class="sv-submit-btn" id="sv-preview-confirm-btn">
                                <span class="sv-btn-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                                <span class="sv-btn-label">Confirm &amp; Complete Booking</span>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Success Screen (Animated Confirmation) -->
                <div class="sv-booking-success-view" id="sv-preview-success-view" style="display: none;">
                    <div class="sv-success-icon-wrap">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h3 class="sv-success-title">Booking Confirmed! (Demo)</h3>
                    <p class="sv-success-msg" id="sv-preview-success-msg-text">${escapeHtml(confirmMsg)}</p>

                    <div class="sv-receipt-card">
                        <div class="sv-receipt-header">
                            <span class="sv-receipt-title">Booking Summary</span>
                            <span class="sv-receipt-ref">SVB-DEMO-2026</span>
                        </div>
                        <div class="sv-receipt-body">
                            <div class="sv-receipt-row">
                                <span class="sv-receipt-label">Customer:</span>
                                <span class="sv-receipt-val" id="sv-preview-receipt-customer">John Doe</span>
                            </div>
                            <div class="sv-receipt-row">
                                <span class="sv-receipt-label">Service:</span>
                                <span class="sv-receipt-val" id="sv-preview-receipt-service">Standard Session</span>
                            </div>
                            <div class="sv-receipt-row">
                                <span class="sv-receipt-label">Date &amp; Time:</span>
                                <span class="sv-receipt-val" id="sv-preview-receipt-datetime">Today @ 09:00 AM</span>
                            </div>
                            <div class="sv-receipt-row sv-receipt-total">
                                <span class="sv-receipt-label">Total Amount:</span>
                                <span class="sv-receipt-val" id="sv-preview-receipt-price">${currency}0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="sv-success-actions">
                        <button type="button" class="sv-btn sv-btn-outline" id="sv-preview-restart-btn">Book Another Appointment</button>
                    </div>
                </div>
            </div>
        `;

        $('#sv-preview-modal-body').html(previewContainerHtml);

        // Calculate and update total price
        function updatePreviewPrice() {
            let total = 0;
            const $selectedService = $('#sv-preview-modal-body').find('.sv-service-card.selected input[type="radio"]');
            if ($selectedService.length) {
                total += parseFloat($selectedService.data('price') || 0);
            }
            $('#sv-preview-modal-body').find('.sv-preview-addon-checkbox:checked').each(function () {
                total += parseFloat($(this).data('addon-price') || 0);
            });
            const formatted = currency + total.toFixed(2);
            $('#sv-preview-total-display').text(formatted);
            $('#sv-preview-receipt-price').text(formatted);
        }

        // Service card selection
        $('#sv-preview-modal-body').on('click', '.sv-service-card', function () {
            $('#sv-preview-modal-body').find('.sv-service-card').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
            updatePreviewPrice();
        });

        // Add-on checkboxes
        $('#sv-preview-modal-body').on('change', '.sv-preview-addon-checkbox', function () {
            updatePreviewPrice();
        });

        // Slot selection
        $('#sv-preview-modal-body').on('click', '.sv-slot-chip', function () {
            $('#sv-preview-modal-body').find('.sv-slot-chip').removeClass('selected');
            $(this).addClass('selected');
        });

        // Interactive Date Picker via Flatpickr
        const $previewDateInput = $('#sv-preview-modal-body').find('.sv-preview-date-input');
        if ($previewDateInput.length && typeof flatpickr !== 'undefined') {
            flatpickr($previewDateInput[0], {
                minDate: 'today',
                dateFormat: 'Y-m-d',
                defaultDate: new Date()
            });
        }

        // Demo submit & receipt view
        $('#sv-preview-confirm-btn').on('click', function () {
            const customerName = $('#sv-preview-modal-body').find('.sv-preview-input-text').val() || 'John Doe';
            const selectedServiceName = $('#sv-preview-modal-body').find('.sv-service-card.selected .sv-service-name').text() || 'Standard Booking';
            const selectedDate = $('#sv-preview-modal-body').find('.sv-preview-date-input').val() || 'Today';
            const selectedSlot = $('#sv-preview-modal-body').find('.sv-slot-chip.selected').text() || '09:00 AM';

            $('#sv-preview-receipt-customer').text(customerName);
            $('#sv-preview-receipt-service').text(selectedServiceName);
            $('#sv-preview-receipt-datetime').text(selectedDate + ' @ ' + selectedSlot);

            $('#sv-preview-modal-body').find('.sv-preview-booking-form').hide();
            $('#sv-preview-success-view').fadeIn(300);
        });

        $('#sv-preview-restart-btn').on('click', function () {
            $('#sv-preview-success-view').hide();
            $('#sv-preview-modal-body').find('.sv-preview-booking-form').fadeIn(300);
        });

        // Initialize total
        updatePreviewPrice();

        // Show modal
        $('#sv-preview-modal').fadeIn(200);
    }

    function closePreviewModal() {
        $('#sv-preview-modal').fadeOut(200);
    }

    function saveForm() {
        const formId = $('#sv-form-id').val();
        const title = $('#sv-builder-form-title').val().trim();

        if (!title) {
            alert('Please enter a form title.');
            $('#sv-builder-form-title').focus();
            return;
        }

        const selectedDays = [];
        $('input[name="sv_days[]"]:checked').each(function () {
            selectedDays.push($(this).val());
        });

        const settings = {
            slot_duration: parseInt($('#sv-set-slot-duration').val(), 10) || 30,
            max_bookings_per_slot: parseInt($('#sv-set-slot-capacity').val(), 10) || 1,
            business_hours_start: $('#sv-set-hours-start').val() || '09:00',
            business_hours_end: $('#sv-set-hours-end').val() || '17:00',
            available_days: selectedDays,
            primary_color: $('#sv-set-primary-color').val() || '#4f46e5',
            currency_symbol: $('#sv-set-currency').val() || '$',
            admin_email_notify: $('#sv-set-admin-email').val() || '',
            enable_client_email: $('#sv-set-client-email').is(':checked') ? 1 : 0,
            confirmation_message: $('#sv-set-confirm-msg').val()
        };

        const description = $('#sv-set-description').val();

        const $btn = $('#sv-save-form-btn');
        $btn.prop('disabled', true).find('.sv-btn-text').text('Saving...');

        $.ajax({
            url: svBookingsAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'sv_save_form',
                nonce: svBookingsAdmin.nonce,
                form_id: formId,
                title: title,
                description: description,
                status: 'active',
                settings: JSON.stringify(settings),
                fields: JSON.stringify(currentFields)
            },
            success: function (res) {
                $btn.prop('disabled', false).find('.sv-btn-text').text('Save Changes');
                if (res.success) {
                    alert(res.data.message || 'Form saved successfully!');
                    if (res.data.redirect && !formId) {
                        window.location.href = res.data.redirect;
                    }
                } else {
                    alert(res.data.message || 'An error occurred.');
                }
            },
            error: function () {
                $btn.prop('disabled', false).find('.sv-btn-text').text('Save Changes');
                alert('Connection error while saving form.');
            }
        });
    }

    /**
     * Generate time slot chips from business hours settings.
     * @param {string} startTime  "HH:MM" 24-hour start
     * @param {string} endTime    "HH:MM" 24-hour end
     * @param {number} slotMinutes  Duration in minutes per slot
     * @returns {string} HTML string of .sv-slot-chip elements
     */
    function generateTimeSlots(startTime, endTime, slotMinutes) {
        if (!startTime || !endTime || !slotMinutes) {
            return '<div class="sv-slot-chip selected" data-time="09:00 AM">09:00 AM</div>';
        }

        const toMins = function (t) {
            const parts = t.split(':');
            return parseInt(parts[0], 10) * 60 + parseInt(parts[1] || 0, 10);
        };
        const toAmPm = function (totalMins) {
            const h = Math.floor(totalMins / 60);
            const m = totalMins % 60;
            const ampm = h < 12 ? 'AM' : 'PM';
            const h12 = h % 12 === 0 ? 12 : h % 12;
            return (h12 < 10 ? '0' + h12 : h12) + ':' + (m < 10 ? '0' + m : m) + ' ' + ampm;
        };

        let start = toMins(startTime);
        const end = toMins(endTime);
        let html = '';
        let first = true;

        while (start < end) {
            const label = toAmPm(start);
            const selectedClass = first ? ' selected' : '';
            html += `<div class="sv-slot-chip${selectedClass}" data-time="${label}">${label}</div>`;
            start += slotMinutes;
            first = false;
        }

        if (!html) {
            html = '<p style="color:#94a3b8;font-size:12px;">No slots available with current settings.</p>';
        }

        return html;
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})(jQuery);
