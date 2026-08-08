@extends('layouts.client')
@php($title = 'Edit Staff')
@section('content')
<x-page-header title="Edit Staff" :subtitle="$staff->name" />
<form method="POST" action="{{ route('client.staff.update',$staff) }}">
    @csrf @method('PUT')
    @include('client.staff.form',['staff'=>$staff])
    <div class="d-flex gap-2">
        <button class="btn btn-primary">Update Staff</button>
        <a class="btn btn-light" href="{{ route('client.staff.index') }}">Cancel</a>
    </div>
</form>
@endsection
