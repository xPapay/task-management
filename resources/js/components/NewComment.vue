<template>
    <form method="POST" class="comment-form" @submit.prevent="addComment">
        <div class="comment-form__left">
            <textarea v-model="body" rows="3" class="comment-form__body no-resize" placeholder="Comment ..."></textarea>
            <div class="inline-list">
                <attachment 
                    v-for="(attachment, index) in attachments" 
                    :key="index" 
                    :source="attachment"
                    @remove="attachments.splice(index, 1)"
                    :downloadable="false"
                ></attachment>
            </div>
            <file-upload @files-selected="attachFiles"></file-upload>
        </div>
        <button class="button comment-form__submit">Send</button>
    </form>
</template>

<script>
import axios from 'axios';
import FileUpload from './FileUpload.vue';
import Attachment from './Attachment.vue';

export default {
    props: ['url'],
    components: { FileUpload, Attachment },

    data() {
        return {
            body: '',
            attachments: []
        }
    },

    methods: {
        addComment() {
            let formData = new FormData();

            formData.append('body', this.body);
            this.attachments.map(attachment => formData.append('attachments[]', attachment));

            axios.post(this.url, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then(({data}) => {
                this.body = '';
                this.attachments = [];
                Event.$emit('commentAdded', data);
            })

        },

        attachFiles(files) {
            this.attachments = [
                ...this.attachments,
                ...files
            ]
        }
    }
}
</script>

