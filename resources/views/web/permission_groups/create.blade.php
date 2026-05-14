@extends('layouts.app')

@section('content')
<div class="table-container" style="max-width: 720px; margin: 0 auto;">
    <h2 style="margin-bottom: 1rem;">مجموعة جديدة</h2>
    <form action="{{ route('permission-groups.store') }}" method="POST">
        @csrf
        <div style="display:grid;gap:1.25rem;margin-bottom:1.5rem;">
            <div>
                <label style="display:block;margin-bottom:0.35rem;color:var(--text-muted);">اسم المجموعة</label>
                <input type="text" name="name" required value="{{ old('name') }}" style="width:100%;padding:0.9rem;border-radius:12px;background:rgba(255,255,255,0.05);border:1px solid var(--border-color);color:#fff;">
            </div>
            <div>
                <label style="display:block;margin-bottom:0.35rem;color:var(--text-muted);">وصف مختصر</label>
                <input type="text" name="description" value="{{ old('description') }}" style="width:100%;padding:0.9rem;border-radius:12px;background:rgba(255,255,255,0.05);border:1px solid var(--border-color);color:#fff;">
            </div>
            <div>
                <p style="color:var(--text-muted);margin-bottom:0.75rem;font-size:0.9rem;">الصلاحيات ضمن المجموعة</p>
                <div style="display:grid;gap:0.5rem;max-height:320px;overflow-y:auto;padding:0.5rem;border-radius:12px;border:1px solid var(--border-color);">
                    @foreach($permissions as $perm)
                    <label style="display:flex;gap:0.6rem;align-items:flex-start;cursor:pointer;font-size:0.9rem;">
                        <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}" style="margin-top:3px;">
                        <span>{{ $perm->description }} <code style="color:#64748b;font-size:0.75rem;">{{ $perm->name }}</code></span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div style="display:flex;gap:1rem;justify-content:flex-end;">
            <a href="{{ route('permission-groups.index') }}" class="btn" style="background:rgba(255,255,255,0.06);color:#fff;">إلغاء</a>
            <button type="submit" class="btn btn-primary">حفظ</button>
        </div>
    </form>
</div>
@endsection
