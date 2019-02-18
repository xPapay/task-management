@extends('layouts.app')
@section('content')

    <tasks :tasks="{{ $tasks }}" inline-template>
        <ul>
            <li v-for="task in tasks" v-text="task.title"></li>
        </ul>
    </tasks>

@endsection
