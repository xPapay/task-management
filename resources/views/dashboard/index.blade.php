@extends('layouts.app')
@section('content')
    <h2 class="m-top-l carbon fs-2x font-thinner">My tasks</h2>
    <tasks></tasks>
    <h2 class="m-top-l carbon fs-2x font-thinner">Supervised tasks</h2>
    <tasks type="supervising"></tasks>
    <new-task :users="{{ $users }}"></new-task>
@endsection