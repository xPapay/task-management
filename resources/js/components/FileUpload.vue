<template>
    <label for="attachment-upload">
        <i class="icon-plus icon--circle bg-black white icon-2x pointer"></i>
        <input type="file" multiple name="attachment" id="attachment-upload" @change="attachFiles">
    </label>
</template>

<script>
export default {
    methods: {
        attachFiles(event) {
            this.$emit('files-selected', event.target.files);
        }
    }
}
</script>
