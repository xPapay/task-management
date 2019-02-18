@extends('layouts.app')
@section('content')
<h2>Notifications</h2>
<notifications :notifications="{{ $notifications }}"></notifications>
@endsection
