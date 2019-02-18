<template>
    <div
        v-show="visible"
        class="flash-message" 
        v-text="body"
    ></div>
</template>

<script>
export default {
    props: ['message'],

    data() {
        return {
            body: '',
            visible: false
        }
    },

    methods: {
        flash(message, duration = 3000) {
            this.body = message;
            this.visible = true;
            this.hide(duration);
        },

        hide(afterTime) {
            let timeout = setTimeout(() => {
                this.visible = false;
                clearTimeout(timeout);
            }, afterTime);
        }
    },

    created() {
        if (this.message) {
            this.flash(this.message);
        }

        Event.$on('flash', (message, duration) => {
            this.flash(message, duration);
        });
    }
}
</script>
