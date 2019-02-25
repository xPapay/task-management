<template>
<div class="comment">
    <div class="comment__author">
        <img 
        :src="author.picture_path" 
        alt="assignee" class="photo-circle photo-medium m-bottom-xs"
        > 
        <div>{{ this.author.name }}</div>
    </div>
    <div class="comment__body">
        <div>
            {{ this.body }}
        </div>
        <div class="inline-list">
            <attachment v-for="(attachment, index) in attachments" :key="index" :source="attachment" :deletable="false"></attachment>
        </div>
        <div class="comment__timestamp">{{ this.date }}</div>
    </div>
</div>
</template>
<script>
import Attachment from './Attachment.vue';

export default {
    props: ['body', 'date', 'author', 'attachments'],
    components: { Attachment }
}
</script>
