@extends('layouts.admin')
@section('title','ویرایش سابقه') @section('heading','ویرایش سابقه') @section('eyebrow',$experience->title)
@section('content')<form class="admin-form" method="POST" action="{{ route('admin.experiences.update',$experience) }}">@csrf @method('PUT') @include('admin.experiences.partials.form')</form>@endsection