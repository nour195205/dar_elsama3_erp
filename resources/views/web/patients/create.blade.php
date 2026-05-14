@extends('layouts.app')

@section('content')
<div class="table-container" style="max-width: 800px; margin: 0 auto;">
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.25rem;">
        اكتب اسم المريض أو رقم هاتفه لعرض سجلات سابقة؛ عند الاختيار تُملأ الحقول الشخصية تلقائياً (يمكنك تعديلها قبل الحفظ).
    </p>
    <form action="{{ route('patients.store') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group" style="grid-column: span 2; position: relative;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">اسم المريض بالكامل</label>
                <input type="text" name="name" id="patient_name" required autocomplete="off" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
                <div id="patient_suggest_dropdown" style="display: none; position: absolute; top: 100%; margin-top: 4px; left: 0; right: 0; z-index: 50; background: #121a2b; border: 1px solid var(--border-color); border-radius: 12px; max-height: 260px; overflow-y: auto; box-shadow: 0 12px 32px rgba(0,0,0,0.4);"></div>
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">رقم الهاتف</label>
                <input type="text" name="phone" id="patient_phone" required autocomplete="off" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">العمر</label>
                <input type="number" name="age" id="patient_age" required min="0" max="150" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">العنوان</label>
                <input type="text" name="address" id="patient_address" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">نوع الزيارة</label>
                <select name="visit_type" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25, 33, 49, 1); border: 1px solid var(--border-color); color: white;">
                    <option value="Initial">كشف جديد</option>
                    <option value="Follow-up">إعادة كشف</option>
                </select>
            </div>
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">نوع الفحص</label>
                <select name="test_type_id" id="test_type_select" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25, 33, 49, 1); border: 1px solid var(--border-color); color: white;" onchange="updatePrice()">
                    <option value="" data-price="0">اختر الفحص (اختياري)</option>
                    @foreach($testTypes as $test)
                        <option value="{{ $test->id }}" data-price="{{ $test->price }}">{{ $test->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">تكلفة الفحص (ج.م)</label>
                <input type="number" name="test_price" id="test_price_input" value="0" step="0.1" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); color: white;">
            </div>

            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">الطبيب الداخلي (المعالج)</label>
                <select name="internal_doctor_id" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25, 33, 49, 1); border: 1px solid var(--border-color); color: white;">
                    <option value="">لا يوجد</option>
                    @foreach($internalDoctors as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text-muted);">الطبيب المحول (الخارجي)</label>
                <select name="referring_doctor_id" style="width: 100%; padding: 1rem; border-radius: 12px; background: rgba(25, 33, 49, 1); border: 1px solid var(--border-color); color: white;">
                    <option value="">لا يوجد</option>
                    @foreach($externalDoctors as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="{{ route('patients.index') }}" class="btn" style="background: rgba(255,255,255,0.05); color: white;">إلغاء</a>
            <button type="submit" class="btn btn-primary">تسجيل المريض</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updatePrice() {
    var select = document.getElementById('test_type_select');
    var priceInput = document.getElementById('test_price_input');
    var selectedOption = select.options[select.selectedIndex];
    var price = selectedOption.getAttribute('data-price');
    if (price) {
        priceInput.value = price;
    }
}

(function () {
    var nameInput = document.getElementById('patient_name');
    var phoneInput = document.getElementById('patient_phone');
    var ageInput = document.getElementById('patient_age');
    var addressInput = document.getElementById('patient_address');
    var dropdown = document.getElementById('patient_suggest_dropdown');
    if (!nameInput || !dropdown) return;

    var timer = null;
    var latestPatients = [];

    function hideDropdown() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        latestPatients = [];
    }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/"/g, '&quot;');
    }

    function applyPatient(p) {
        nameInput.value = p.name || '';
        phoneInput.value = p.phone || '';
        if (p.age !== null && p.age !== undefined && p.age !== '') {
            ageInput.value = String(p.age);
        }
        addressInput.value = p.address || '';
        hideDropdown();
    }

    function renderSuggestions(patients) {
        if (!patients || patients.length === 0) {
            hideDropdown();
            return;
        }
        latestPatients = patients;
        var html = patients.map(function (p, idx) {
            return '<button type="button" class="patient-suggest-item" data-idx="' + idx + '" style="display:block;width:100%;text-align:right;padding:0.75rem 1rem;background:none;border:none;border-bottom:1px solid rgba(255,255,255,0.06);color:#e2e8f0;cursor:pointer;font-size:0.95rem;">' +
                esc(p.label) + '</button>';
        }).join('');
        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
        dropdown.querySelectorAll('.patient-suggest-item').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var idx = parseInt(this.getAttribute('data-idx'), 10);
                if (!isNaN(idx) && latestPatients[idx]) {
                    applyPatient(latestPatients[idx]);
                }
            });
        });
    }

    function runSuggest(source) {
        clearTimeout(timer);
        var q = (source === 'phone' ? phoneInput.value : nameInput.value).trim();
        if (q.length < 2) {
            hideDropdown();
            return;
        }
        timer = setTimeout(function () {
            fetch('{{ route('global.search') }}?' + new URLSearchParams({ q: q }).toString())
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    renderSuggestions(data.patients || []);
                })
                .catch(function () { hideDropdown(); });
        }, 280);
    }

    nameInput.addEventListener('input', function () { runSuggest('name'); });
    nameInput.addEventListener('focus', function () { runSuggest('name'); });
    phoneInput.addEventListener('input', function () { runSuggest('phone'); });
    phoneInput.addEventListener('focus', function () { runSuggest('phone'); });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && e.target !== nameInput && e.target !== phoneInput) {
            hideDropdown();
        }
    });
})();
</script>
@endpush
