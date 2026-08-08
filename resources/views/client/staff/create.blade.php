@extends('layouts.client')
@php($title = 'Add Staff')
@section('content')
<x-page-header title="Add Staff" subtitle="Give a team member access to your CRM" />
<form method="POST" action="{{ route('client.staff.store') }}">
    @csrf
    @include('client.staff.form')
    <div class="d-flex gap-2">
        <button class="btn btn-primary">Add Staff</button>
        <a class="btn btn-light" href="{{ route('client.staff.index') }}">Cancel</a>
    </div>
</form>
@endsection
