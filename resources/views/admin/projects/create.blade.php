@extends('layouts.admin')
@section('title', 'پروژه جدید')
@section('heading', 'پروژه جدید')
@section('eyebrow', 'Create case study')
@section('content')
    <form class="admin-form admin-project-form" method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.projects.partials.form')
    </form>
@endsection
