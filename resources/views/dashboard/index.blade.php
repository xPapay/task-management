@extends('layouts.app')
@section('content')
    <h2 class="m-top-l">My tasks</h2>
    <tasks></tasks>
    <h2 class="m-top-l">Supervised tasks</h2>
    <tasks type="supervising"></tasks>
    <new-task :users="{{ $users }}"></new-task>
@endsection