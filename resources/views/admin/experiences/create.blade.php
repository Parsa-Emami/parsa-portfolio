@extends('layouts.admin')
@section('title','سابقه جدید') @section('heading','سابقه جدید') @section('eyebrow','Create timeline item')
@section('content')<form class="admin-form" method="POST" action="{{ route('admin.experiences.store') }}">@csrf @include('admin.experiences.partials.form')</form>@endsection