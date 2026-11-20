@extends('layouts.app')

@section('title', 'Buat Pengaduan / Permohonan Layanan')
@section('page-title', 'Buat Pengaduan Baru')

@section('content')
<div style="max-width: 680px; margin: 0 auto; padding: 10px 16px 40px;">
    
    <div style="margin-bottom: 24px;">
        <a href="{{ route('user.tickets.index') }}" class="back-link" style="color: var(--primary); font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
            <i class="fas fa-arrow-left"></i> Kembali ke Tiket Saya
        </a>
    </div>

    <div style="text-align: center; margin-bottom: 32px;">
        <h2 style="font-size: 26px; font-weight: 700; color: #14472A; margin: 0 0 8px 0; letter-spacing: -0.02em;">
            Formulir Pengaduan Layanan Akademik
        </h2>
        <p style="color: var(--gray-600); font-size: 14px; margin: 0;">
            Lengkapi formulir berikut secara akurat untuk mempercepat proses penanganan oleh staf.
        </p>
    </div>

    @php
        $oldServiceId = old('service_id', $selectedService->id ?? null);
        $selectedCategoryId = null;
        if ($oldServiceId) {
            foreach ($categories as $cat) {
                if ($cat->services->contains('id', $oldServiceId)) {
                    $selectedCategoryId = $cat->id;
                    break;
                }
            }
        }
    @endphp

    <form action="{{ route('user.tickets.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Dropdown 1: Kategori Layanan -->
        <div class="custom-form-group">
            <label class="custom-form-label" for="categorySelect">
                Kategori Layanan <span class="required-star">*</span>
            </label>
            <div class="custom-select-wrapper">
                <select id="categorySelect" class="custom-form-control has-icon" required onchange="handleCategoryChange(this.value)">
                    <option value="">-- Pilih Kategori Layanan --</option>
                    @foreach($categories as $category)
                        @if($category->services->count() > 0)
                        <option value="{{ $category->id }}" {{ $selectedCategoryId == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endif
                    @endforeach
                </select>
                <i class="fas fa-layer-group custom-select-icon"></i>
                <div class="custom-select-arrow">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <div style="font-size: 12px; color: var(--gray-500); margin-top: 5px;">
                Pilih kategori terlebih dahulu untuk memfilter jenis layanan yang tersedia.
            </div>
        </div>

        <!-- Dropdown 2: Jenis Layanan Akademik (Cascading) -->
        <div class="custom-form-group">
            <label class="custom-form-label" for="serviceSelect">
                Jenis Layanan Akademik <span class="required-star">*</span>
            </label>
            <div class="custom-select-wrapper">
                <select name="service_id" id="serviceSelect" class="custom-form-control has-icon @error('service_id') is-invalid @enderror" required onchange="handleServiceChange(this.value)" {{ empty($selectedCategoryId) ? 'disabled' : '' }}>
                    <option value="">{{ empty($selectedCategoryId) ? '-- Pilih Kategori Terlebih Dahulu --' : '-- Pilih Jenis Layanan Akademik --' }}</option>
                </select>
                <i class="fas fa-clipboard-list custom-select-icon"></i>
                <div class="custom-select-arrow">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            @error('service_id') <div class="invalid-feedback-custom">{{ $message }}</div> @enderror

            <div id="serviceInfoBox" style="display: none; margin-top: 10px; padding: 12px 16px; background: rgba(26, 107, 60, 0.06); border: 1px solid rgba(26, 107, 60, 0.18); border-radius: 8px; font-size: 14px;">
                <div style="font-weight: 700; color: #14472A;" id="serviceInfoTitle"></div>
                <div style="color: var(--gray-700); margin-top: 4px;" id="serviceInfoDesc"></div>
                <div style="font-weight: 600; color: var(--primary); margin-top: 6px;" id="serviceInfoSla"></div>
            </div>
        </div>

        <!-- Judul Pengaduan -->
        <div class="custom-form-group">
            <label class="custom-form-label">
                Judul Pengaduan / Subjek <span class="required-star">*</span>
            </label>
            <input type="text" name="subject" class="custom-form-control @error('subject') is-invalid @enderror" 
                value="{{ old('subject') }}" required placeholder="Contoh: Kendala Input Nilai atau Revisi Data KTP">
            @error('subject') <div class="invalid-feedback-custom">{{ $message }}</div> @enderror
        </div>

        <!-- Deskripsi Rinci -->
        <div class="custom-form-group">
            <label class="custom-form-label">
                Penjelasan / Deskripsi Rinci <span class="required-star">*</span>
            </label>
            <textarea name="description" class="custom-form-control @error('description') is-invalid @enderror" 
                rows="5" required placeholder="Jelaskan kendala Anda secara lengkap, kronologi, serta pihak/dosen yang bersangkutan...">{{ old('description') }}</textarea>
            @error('description') <div class="invalid-feedback-custom">{{ $message }}</div> @enderror
        </div>

        <!-- Container Formulir Dinamis (Custom Fields Spesifik Layanan) -->
        <div id="dynamicFieldsSection" style="display: none; margin-bottom: 22px;"></div>

        <!-- Container Persyaratan Dokumen Spesifik Layanan -->
        <div id="dynamicRequirementsSection" style="display: none; margin-bottom: 22px;"></div>

        <!-- Bagian Unggah Dokumen / Lampiran Umum (Fallback jika layanan tidak memiliki syarat khusus) -->
        <div class="custom-form-group" id="generalAttachmentGroup">
            <label class="custom-form-label">
                Lampiran Berkas / Bukti Pendukung
            </label>
            <p style="font-size: 12px; color: var(--gray-500); margin: 0 0 8px 0;">
                Unggah bukti pendukung seperti Kartu Ujian, KHS, bukti chat/revisi dosen, atau identitas (Format: PDF, JPG, PNG, DOC, DOCX. Maks: 5MB).
            </p>

            <div class="custom-file-box" onclick="document.getElementById('fileAttachmentInput').click()">
                <i class="fas fa-paperclip" style="color: var(--gray-400); font-size: 16px;"></i>
                <span id="fileAttachmentLabel" style="color: var(--gray-500); font-size: 14px;">Pilih file</span>
                <input type="file" id="fileAttachmentInput" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" 
                    style="position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none;" 
                    onchange="handleFileInputChange(this)">
            </div>
            @error('attachments') <div class="invalid-feedback-custom">{{ $message }}</div> @enderror
        </div>

        <!-- Action Submit Button (Centered Pill) -->
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; margin-top: 36px;">
            <button type="submit" class="btn-submit-pill">
                Ajukan Pengaduan Sekarang
            </button>
            <a href="{{ route('user.tickets.index') }}" style="color: var(--gray-500); font-size: 14px; text-decoration: none; font-weight: 500;">
                Batal
            </a>
        </div>
    </form>
</div>

<style>
    .custom-form-group {
        margin-bottom: 22px;
    }

    .custom-form-label {
        display: block;
        font-size: 15px;
        font-weight: 700;
        color: #14472A;
        margin-bottom: 8px;
    }

    .required-star {
        color: #DC2626;
        font-weight: 700;
    }

    .custom-form-control {
        width: 100%;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 15px;
        font-family: inherit;
        color: var(--gray-800);
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .custom-form-control:disabled {
        background: #F9FAFB;
        color: #9CA3AF;
        cursor: not-allowed;
    }

    .custom-form-control:focus:not(:disabled) {
        border-color: #1A6B3C;
        box-shadow: 0 0 0 3px rgba(26, 107, 60, 0.12);
    }

    .custom-form-control::placeholder {
        color: #9CA3AF;
        font-size: 14px;
    }

    .custom-form-control.is-invalid {
        border-color: #DC2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .invalid-feedback-custom {
        color: #DC2626;
        font-size: 12px;
        margin-top: 6px;
        font-weight: 500;
    }

    .custom-select-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .custom-select-wrapper select.custom-form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 100%;
        background: #FFFFFF;
        border: 1.5px solid #E2E8F0;
        border-radius: 12px;
        padding: 13px 48px 13px 46px;
        font-size: 14.5px;
        font-weight: 500;
        color: #1E293B;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        line-height: 1.5;
        outline: none;
    }

    .custom-select-wrapper select.custom-form-control:hover:not(:disabled) {
        border-color: #0A5E3D;
        background-color: #FAFDFB;
        box-shadow: 0 2px 8px rgba(10, 94, 61, 0.08);
    }

    .custom-select-wrapper select.custom-form-control:focus {
        border-color: #0A5E3D;
        background-color: #FFFFFF;
        box-shadow: 0 0 0 4px rgba(10, 94, 61, 0.12);
    }

    .custom-select-wrapper select.custom-form-control:disabled {
        background-color: #F8FAFC;
        border-color: #E2E8F0;
        color: #94A3B8;
        cursor: not-allowed;
        box-shadow: none;
    }

    .custom-select-wrapper select.custom-form-control.is-invalid {
        border-color: #DC2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .custom-select-icon {
        position: absolute;
        left: 16px;
        color: #0A5E3D;
        font-size: 15px;
        pointer-events: none;
        z-index: 2;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .custom-select-wrapper:focus-within .custom-select-icon {
        color: #D4A843;
        transform: scale(1.1);
    }

    .custom-select-wrapper select:disabled ~ .custom-select-icon,
    .custom-select-wrapper:has(select:disabled) .custom-select-icon {
        color: #CBD5E1;
    }

    .custom-select-arrow {
        position: absolute;
        right: 10px;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #F1F5F9;
        color: #0A5E3D;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        pointer-events: none;
        z-index: 2;
        transition: all 0.25s ease;
    }

    .custom-select-wrapper:hover:not(:has(select:disabled)) .custom-select-arrow {
        background: #E6F4EA;
        color: #0A5E3D;
    }

    .custom-select-wrapper:focus-within .custom-select-arrow {
        background: linear-gradient(135deg, #0A5E3D, #084930);
        color: #FFFFFF;
        transform: rotate(180deg);
        box-shadow: 0 2px 6px rgba(10, 94, 61, 0.25);
    }

    .custom-select-wrapper select:disabled ~ .custom-select-arrow,
    .custom-select-wrapper:has(select:disabled) .custom-select-arrow {
        background: #F8FAFC;
        color: #CBD5E1;
        opacity: 0.6;
        transform: none;
        box-shadow: none;
    }

    /* Option styling */
    .custom-select-wrapper select option {
        padding: 12px 16px;
        background: #FFFFFF;
        color: #1E293B;
        font-size: 14px;
        font-weight: 500;
    }

    .custom-select-wrapper select option:disabled,
    .custom-select-wrapper select option[value=""] {
        color: #94A3B8;
        font-style: italic;
    }

    .custom-file-box {
        width: 100%;
        background: #FFFFFF;
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 12px 16px;
        box-sizing: border-box;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .custom-file-box:hover {
        border-color: #D1D5DB;
    }

    .custom-file-box:focus-within {
        border-color: #1A6B3C;
        box-shadow: 0 0 0 3px rgba(26, 107, 60, 0.12);
    }

    .btn-submit-pill {
        background: #1A6B3C;
        color: #FFFFFF;
        border-radius: 9999px;
        padding: 12px 42px;
        font-size: 15px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(26, 107, 60, 0.25);
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .btn-submit-pill:hover {
        background: #14472A;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(26, 107, 60, 0.35);
    }

    @media (max-width: 768px) {
        .custom-form-control {
            font-size: 16px !important;
        }
    }
</style>

<script>
    const servicesByCategory = {
        @foreach($categories as $category)
            @if($category->services->count() > 0)
            "{{ $category->id }}": [
                @foreach($category->services as $service)
                {
                    id: {{ $service->id }},
                    name: @json($service->name),
                    sla: @json($service->sla_description),
                    desc: @json($service->description),
                    requirements: @json($service->requirements),
                    fields: @json($service->fields)
                },
                @endforeach
            ],
            @endif
        @endforeach
    };

    const oldFields = @json(old('fields', []));
    let initialServiceId = @json($oldServiceId);

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function handleCategoryChange(catId) {
        const serviceSelect = document.getElementById('serviceSelect');
        serviceSelect.innerHTML = '';

        if (!catId || !servicesByCategory[catId] || servicesByCategory[catId].length === 0) {
            serviceSelect.disabled = true;
            const opt = document.createElement('option');
            opt.value = '';
            opt.textContent = catId ? '-- Tidak ada layanan di kategori ini --' : '-- Pilih Kategori Terlebih Dahulu --';
            serviceSelect.appendChild(opt);
            handleServiceChange('');
            return;
        }

        serviceSelect.disabled = false;
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = '-- Pilih Jenis Layanan Akademik --';
        serviceSelect.appendChild(defaultOpt);

        servicesByCategory[catId].forEach(function(s) {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name + (s.sla ? ' (SLA: ' + s.sla + ')' : '');
            opt.setAttribute('data-sla', s.sla || '');
            opt.setAttribute('data-desc', s.desc || '');
            if (initialServiceId && String(initialServiceId) === String(s.id)) {
                opt.selected = true;
            }
            serviceSelect.appendChild(opt);
        });

        // After populating, update SLA info box and dynamic sections
        handleServiceChange(serviceSelect.value);
    }

    function findServiceById(serviceId) {
        if (!serviceId) return null;
        for (const catId in servicesByCategory) {
            const found = servicesByCategory[catId].find(s => String(s.id) === String(serviceId));
            if (found) return found;
        }
        return null;
    }

    function handleServiceChange(val) {
        const select = document.getElementById('serviceSelect');
        const opt = select.options[select.selectedIndex];
        const infoBox = document.getElementById('serviceInfoBox');
        const service = findServiceById(val);

        if (val && opt && opt.value) {
            const sla = opt.getAttribute('data-sla');
            const desc = opt.getAttribute('data-desc');

            document.getElementById('serviceInfoTitle').innerText = opt.text;
            document.getElementById('serviceInfoDesc').innerText = desc || 'Tidak ada catatan deskripsi khusus.';
            document.getElementById('serviceInfoSla').innerText = 'Estimasi Waktu Pengerjaan (SLA): ' + (sla || '-');
            infoBox.style.display = 'block';
        } else {
            infoBox.style.display = 'none';
        }

        renderDynamicSections(service);
    }

    function renderDynamicSections(service) {
        const fieldsContainer = document.getElementById('dynamicFieldsSection');
        const reqsContainer = document.getElementById('dynamicRequirementsSection');
        const generalAttGroup = document.getElementById('generalAttachmentGroup');
        const generalAttInput = document.getElementById('fileAttachmentInput');

        // 1. Render Custom Fields
        if (service && service.fields && service.fields.length > 0) {
            let html = '<div style="background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 10px; padding: 18px 20px; margin-bottom: 22px;">';
            html += '<div style="font-weight: 700; color: #14472A; font-size: 15px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">';
            html += '<i class="fas fa-wpforms" style="color: var(--primary);"></i> Informasi Tambahan Khusus Layanan';
            html += '</div>';

            service.fields.forEach(f => {
                const isReq = Boolean(f.is_required);
                const reqStar = isReq ? ' <span class="required-star">*</span>' : '';
                const reqAttr = isReq ? 'required' : '';
                const oldVal = (oldFields && oldFields[f.id] !== undefined) ? oldFields[f.id] : '';

                html += '<div class="custom-form-group" style="margin-bottom: 16px;">';
                html += `<label class="custom-form-label" style="font-size: 14px; margin-bottom: 6px;">${escapeHtml(f.label)}${reqStar}</label>`;

                if (f.field_type === 'textarea') {
                    html += `<textarea name="fields[${f.id}]" class="custom-form-control" rows="3" ${reqAttr} placeholder="${escapeHtml(f.placeholder || '')}">${escapeHtml(oldVal)}</textarea>`;
                } else if (f.field_type === 'number') {
                    html += `<input type="number" name="fields[${f.id}]" class="custom-form-control" ${reqAttr} value="${escapeHtml(oldVal)}" placeholder="${escapeHtml(f.placeholder || '')}">`;
                } else if (f.field_type === 'date') {
                    html += `<input type="date" name="fields[${f.id}]" class="custom-form-control" ${reqAttr} value="${escapeHtml(oldVal)}">`;
                } else if (f.field_type === 'select' && f.options) {
                    html += `<select name="fields[${f.id}]" class="custom-form-control" ${reqAttr}>`;
                    html += '<option value="">-- Pilih --</option>';
                    const opts = Array.isArray(f.options) ? f.options : String(f.options).split(',');
                    opts.forEach(optVal => {
                        const trimmed = String(optVal).trim();
                        const selected = (String(oldVal) === trimmed) ? 'selected' : '';
                        html += `<option value="${escapeHtml(trimmed)}" ${selected}>${escapeHtml(trimmed)}</option>`;
                    });
                    html += '</select>';
                } else {
                    html += `<input type="text" name="fields[${f.id}]" class="custom-form-control" ${reqAttr} value="${escapeHtml(oldVal)}" placeholder="${escapeHtml(f.placeholder || '')}">`;
                }

                html += '</div>';
            });

            html += '</div>';
            fieldsContainer.innerHTML = html;
            fieldsContainer.style.display = 'block';
        } else {
            fieldsContainer.innerHTML = '';
            fieldsContainer.style.display = 'none';
        }

        // 2. Render Requirements
        if (service && service.requirements && service.requirements.length > 0) {
            generalAttGroup.style.display = 'none';
            generalAttInput.disabled = true;

            let html = '<div style="background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 10px; padding: 18px 20px; margin-bottom: 22px;">';
            html += '<div style="font-weight: 700; color: #14472A; font-size: 15px; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">';
            html += '<i class="fas fa-file-upload" style="color: var(--primary);"></i> Dokumen Persyaratan Layanan';
            html += '</div>';
            html += '<p style="font-size: 12px; color: var(--gray-600); margin: 0 0 14px 0;">Mohon unggah dokumen sesuai ketentuan di bawah untuk verifikasi administrasi.</p>';

            service.requirements.forEach(r => {
                const isReq = Boolean(r.is_required);
                const reqStar = isReq ? ' <span class="required-star">*</span>' : '';
                const reqBadge = isReq
                    ? '<span style="font-size: 11px; font-weight: 700; color: #DC2626; background: #FEE2E2; padding: 2px 8px; border-radius: 99px;">Wajib</span>'
                    : '<span style="font-size: 11px; font-weight: 600; color: #4B5563; background: #E5E7EB; padding: 2px 8px; border-radius: 99px;">Opsional</span>';
                const maxSizeText = r.max_file_size >= 1024
                    ? (r.max_file_size / 1024) + ' MB'
                    : (r.max_file_size || 2048) + ' KB';
                const acceptTypes = r.allowed_file_types
                    ? r.allowed_file_types.split(',').map(t => '.' + t.trim()).join(',')
                    : '.pdf,.jpg,.jpeg,.png';

                html += '<div class="custom-form-group" style="background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 8px; padding: 14px; margin-bottom: 12px;">';
                html += '<div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 4px;">';
                html += `<div style="font-size: 14px; font-weight: 700; color: #14472A;">${escapeHtml(r.name)}${reqStar}</div>`;
                html += reqBadge;
                html += '</div>';

                if (r.description) {
                    html += `<div style="font-size: 12px; color: #4B5563; margin-bottom: 8px;">${escapeHtml(r.description)}</div>`;
                }

                html += `<div style="font-size: 11px; color: #6B7280; margin-bottom: 8px;"><i class="fas fa-info-circle"></i> Format: <b>${escapeHtml(r.allowed_file_types || 'pdf,jpg,png')}</b> • Maks: <b>${maxSizeText}</b></div>`;

                html += `<div class="custom-file-box" onclick="document.getElementById('reqInput_${r.id}').click()">`;
                html += '<i class="fas fa-upload" style="color: var(--primary); font-size: 14px;"></i>';
                html += `<span id="reqLabel_${r.id}" style="color: var(--gray-500); font-size: 13px;">Pilih berkas ${escapeHtml(r.name)}</span>`;
                html += `<input type="file" id="reqInput_${r.id}" name="attachments[${r.id}]" accept="${acceptTypes}" ${isReq ? 'required' : ''} style="position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none;" onchange="handleReqFileChange(this, ${r.id})">`;
                html += '</div>';

                html += '</div>';
            });

            html += '</div>';
            reqsContainer.innerHTML = html;
            reqsContainer.style.display = 'block';
        } else {
            reqsContainer.innerHTML = '';
            reqsContainer.style.display = 'none';
            generalAttGroup.style.display = 'block';
            generalAttInput.disabled = false;
        }
    }

    function handleReqFileChange(input, reqId) {
        const label = document.getElementById('reqLabel_' + reqId);
        if (input.files && input.files.length > 0) {
            label.innerText = input.files[0].name;
            label.style.color = '#111827';
            label.style.fontWeight = '600';
        } else {
            label.innerText = 'Pilih berkas';
            label.style.color = '#6B7280';
            label.style.fontWeight = 'normal';
        }
    }

    function handleFileInputChange(input) {
        const label = document.getElementById('fileAttachmentLabel');
        if (input.files && input.files.length > 0) {
            if (input.files.length === 1) {
                label.innerText = input.files[0].name;
                label.style.color = '#111827';
            } else {
                label.innerText = input.files.length + ' file dipilih';
                label.style.color = '#111827';
            }
        } else {
            label.innerText = 'Pilih file';
            label.style.color = '#6B7280';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const catSelect = document.getElementById('categorySelect');
        if (catSelect && catSelect.value) {
            handleCategoryChange(catSelect.value);
        }
    });
</script>
@endsection
