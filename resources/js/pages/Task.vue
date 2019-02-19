<script>
import axios from 'axios';
import Swal from 'sweetalert2';
import FileUpload from '../components/FileUpload.vue';
import Attachment from '../components/Attachment.vue';
import UniqueIdMixin from '../mixins/UniqueIdMixin';
import Errors from '../Errors.js';

export default {
    components: { Attachment, FileUpload },
    props: ['task'],
    mixins: [UniqueIdMixin],

    data() {
        return {
            editing: false,
            start_date: this.formatDate(this.task.start_date),
            due_date: this.formatDate(this.task.due_date),
            title: this.task.title,
            description: this.task.description,
            assignees: this.toObject(this.task.assignees),
            attachments: [],
            oldAttachments: [...this.task.attachments],
            errors: new Errors()
        }
    },

    computed: {
        isCreator() {
            return this.task.creator_id == App.user.id;
        }
    },

    methods: {
        changeDates(item) {
            this.start_date = item.start,
            this.due_date = item.end
        },

        formatDate(dateString) {
            return new Date(dateString.replace(/\s/, 'T'));
        },

        assign(user) {
            this.assignees = {...this.assignees, [user.id]: user};
        },

        deassign(user) {
            let assignees = {...this.assignees};
            delete assignees[user.id];
            this.assignees = assignees;
        },

        edit() {
            this.editing = true;
        },

        update() {
            let formData = new FormData();
            formData.append('title', this.title);
            formData.append('description', this.description);
            if ( (new Date(this.task.start_date)).getTime() !=  this.start_date.getTime()) {
                formData.append('start_date', App.formatDate(this.start_date));
            }

            if ( (new Date(this.task.due_date)).getTime() !=  this.due_date.getTime()) {
                formData.append('due_date', App.formatDate(this.due_date));
            }

            formData.append('assignees', '');
            Object.keys(this.assignees).map(id => formData.append('assignees[]', id));
            this.attachments.map(attachment => formData.append("attachments[]", attachment));
            this.oldAttachments.map(attachment => formData.append("old_attachments[]", attachment.id));
            formData.append('_method', 'patch');

            axios
                .post(`/tasks/${this.task.id}`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                })
                .then(() => this.editing = false)
                .catch(error => this.errors.record(error.response.data.errors)); 
        },

        cancel() {
            this.revertChanges();
            this.editing = false;
        },

        revertChanges() {
            this.start_date = this.formatDate(this.task.start_date),
            this.due_date = this.formatDate(this.task.due_date),
            this.title = this.task.title,
            this.description = this.task.description,
            this.assignees = this.toObject(this.task.assignees),
            this.attachments = [],
            this.oldAttachments = [...this.task.attachments]
        },

        deleteTask() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.value) {
                    this.$refs.deleteTaskForm.submit();
                }
            })
        },

        attachFiles(files) {
            this.attachments = [
                ...this.attachments,
                ...files
            ];
        },

        removeAttachment(index) {
            this.oldAttachments.splice(index, 1);
        },

        toObject(arr) {
            let obj = {};
            arr.map(item => obj[item.id] = item);
            return obj;
        }
    }
}
</script>
