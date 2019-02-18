<script>
export default {
    props: ['task'],
    data() {
        return {
            finished: this.task.finished_at != null
        }
    },
    methods: {
        toggleFinish() {
            axios[this.finished ? 'delete' : 'post'](`/finished-tasks/${this.task.id}`);
            this.finished = !this.finished;
        }
    }
}
</script>
