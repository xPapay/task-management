@extends('layouts.app')
@section('content')
<task :task="{{ $task }}" inline-template v-cloak>
    <div @keydown="errors.clear($event.target.name)" class="task">
        <input class="input task__title" type="text" name="title" v-if="editing" v-model="title">
        <h2 class="task__title" v-else v-text="title"></h2>
        <div class="red italic m-bottom-s" v-text="errors.get('title')"></div>
        <div class="inline-list m-bottom-s" v-if="isCreator">
            <div v-if="editing">
                <button
                    class="button button--outline green border--green valign-inline"
                    @click="update"
                >
                    <i class="far fa-save fa-2x m-right-s"></i>
                    Save
                </button>
                <button
                    class="button button--outline m-left-s valign-inline m-left-s"
                    @click="cancel"
                >
                    <i class="fas fa-ban fa-2x m-right-s"></i>
                    Cancel
                </button>
            </div>

            <div v-else>
                <button
                    class="button button--outline valign-inline"
                    @click="edit"
                >
                    <i class="icon-pencil icon-2x m-right-s"></i>
                    Edit
                </button>
                <button
                    class="button button--outline red border--red valign-inline m-left-s"
                    @click="deleteTask"
                >
                    <i class="icon-trash icon-2x m-right-s"></i>
                    Delete
                </button>
            </div>
            
            <form ref="deleteTaskForm" action="/tasks/{{$task->id}}" method="POST" style="display: none;">
                @method('delete')
                @csrf
            </form>
        </div>
    
        <timeline
            :editable="editing"
            :init-start="new Date(start_date)" 
            :items="[{start: start_date, end: due_date, content: title}]"
            @itemmoved="changeDates"
            class="m-top-l"
        ></timeline>
        <div class="row m-top-l">
            <textarea class="input no-resize col-s-2 font-bigger" v-if="editing" v-text="description" v-model="description"></textarea>
            <p v-else class="font-bigger" v-text="description"></p>
        </div>
        <p>Created by: {{ $task->creator->name }} at {{ $task->createdAtFormatted }}</p>
        <h2 class="m-top-l">Attachments</h2>
        <ul class="inline-list valign-inline">
            <li 
                v-for="(attachment, index) in oldAttachments" 
                :key="guid()"
                class="valign"
            >
                <attachment :deletable="editing" :downloadable="!editing" :source="attachment" @remove="oldAttachments.splice(index, 1)"></attachment>
            </li>

            <li 
                v-for="(attachment, index) in attachments" 
                :key="guid()"
            >
                <attachment :deletable="editing" :downloadable="!editing" :source="attachment" @remove="attachments.splice(index, 1)"></attachment>
            </li>

            <li 
                :key="guid()" 
                v-show="oldAttachments.length < 1 && attachments.length < 1 && !editing"
                class="muted"
            >
                No Attachments
            </li>
            
            <li>
                <file-upload v-if="editing" @files-selected="attachFiles"></file-upload>
            </li>
        </ul>

        <h2 class="m-top-l">Assignees</h2>
        <selectbox 
            name="assignees"
            :selected="assignees"
            :editable="editing"
            @select="assign"
            @deselect="deassign"
        ></selectbox>
        <div class="red italic m-bottom-s" v-text="errors.get('assignees')"></div>
        <h2 class="m-top-l">Comments</h2>
        <div class="row">
            <div class="col-s-2">
                <comments :init-comments="{{ $task->comments }}"></comments>
            </div>
        </div>
        <new-comment url="{{ route('comment.add', ['task' => $task]) }}"></new-comment>
    </div>
</task>
@endsection
