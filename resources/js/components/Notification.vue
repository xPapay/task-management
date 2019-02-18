<template>
<div class="valign" :class="{muted: isRead}">
    <img
        :src="notification.data.iniciator_avatar"
        alt="assignee" class="photo-circle photo-small m-bottom-xs"
    >
    <p class="m-left-xs">
        <span class="bolder">{{ notification.data.iniciator_name }}</span> {{ notification.data.action }} 
        <a ref="link" :href="notification.data.link" class="bold" @click.prevent="$emit('click', notification)">"{{ notification.data.task_name }}".</a>
        <span class="muted italic">{{ moment(notification.created_at).fromNow() }}</span>
    </p>
</div>
</template>

<script>
import moment from 'moment';
import axios from 'axios';

export default {
    props: ['notification'],
    methods: {
        moment(date) {
            return moment(date);
        }
    },

    computed: {
        isRead() {
            return this.notification.read_at !== null;
        }
    }
}
</script>
