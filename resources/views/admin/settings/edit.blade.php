@extends('layouts.admin')
@section('title', 'تنظیمات سایت')
@section('heading', 'تنظیمات سایت')
@section('eyebrow', 'Editable portfolio copy')
@section('content')
    <form class="admin-form" method="POST" action="{{ route('admin.settings.update') }}">
        @csrf @method('PUT')

        @if ($errors->any())
            <div class="admin-error-summary"><strong>تنظیمات ذخیره نشد.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        @foreach (collect($definitions)->groupBy('group') as $group => $items)
            <section class="admin-panel admin-form-section">
                <div class="admin-panel-heading"><div><p>Settings</p><h2>{{ $group }}</h2></div></div>
                <div class="admin-input-grid">
                    @foreach ($items as $key => $definition)
                        <label @class(['admin-input-span' => $definition['type'] === 'textarea'])>
                            <span>{{ $definition['label'] }}</span>
                            @if ($definition['type'] === 'textarea')
                                <textarea name="{{ $key }}" rows="4">{{ old($key, $values[$key] ?? '') }}</textarea>
                            @else
                                <input type="{{ in_array($definition['type'], ['email', 'url']) ? $definition['type'] : 'text' }}" name="{{ $key }}" value="{{ old($key, $values[$key] ?? '') }}" @if($definition['type'] === 'url') dir="ltr" @endif>
                            @endif
                            @error($key) <small>{{ $message }}</small> @enderror
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="admin-sticky-actions"><button class="admin-primary-button" type="submit">ذخیره تنظیمات</button></div>
    </form>
@endsection
