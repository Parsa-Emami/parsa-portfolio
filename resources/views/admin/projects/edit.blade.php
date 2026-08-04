@extends('layouts.admin')
@section('title', 'ویرایش ' . $project->title)
@section('heading', 'ویرایش پروژه')
@section('eyebrow', $project->title)
@section('content')
    <form class="admin-form admin-project-form" method="POST" action="{{ route('admin.projects.update', $project) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.projects.partials.form')
    </form>
@endsection
