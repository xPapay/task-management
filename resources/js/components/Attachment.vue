<template>
<div class="attachment">
    <div class="p-relative text-center" @mouseenter="showOptions = true" @mouseleave="showOptions = false">
        <span v-html="preview"></span>
        <div class="attachment__options" v-show="showOptions">
            <a :href="downloadUrl" class="attachment__option" v-show="downloadable">
                <i class="fas fa-arrow-down fa-2x"></i>
            </a>
            <button class="attachment__option" v-show="deletable">
                <i class="far fa-trash-alt fa-2x" @click.prevent="$emit('remove')"></i>
            </button>
        </div>
    </div>
    <div class="m-top-s">{{ file && file.name }}</div>
</div>
</template>

<script>
import axios from 'axios';

export default {
    props: {
        source: {
            type: [Object, File]
        },
        deletable: {
            type: Boolean,
            default: true
        },
        downloadable: {
            type: Boolean,
            default: true
        }
    },

    data() {
        return {
            preview: null,
            file: null,
            showOptions: false
        }
    },

    async created() {
        this.file = await this.getFile();
        this.makePreviewFromFile();
    },

    computed: {
        downloadUrl() {
            return (this.source instanceof File) ? "#" : `/attachments/${this.source.id}`;
        }
    },

    methods: {
        async getFile() {
            let source = this.source;

            if (! (source instanceof File)) {
                source = await this.getFileFromUrl(source.public_path, source.name);
            }

            return source;
        },

        async makePreviewFromFile() {
            if (! (this.file instanceof File)) {
                throw new Error(`Argument must be of type File. ${this.file.constructor.name} given.`);
            }

            const { type } = this.file;

            if (type == "application/pdf") {
                this.preview = '<i class="fas fa-file-pdf fa-5x"></i>';
                return;
            }

            if (type.match(/image\/*/i)) {
                let dataUrl = await this.convertImageToUrl(this.file);
                this.preview = `<img class="attachment__thumbnail" src="${dataUrl}">`;
                return;
            }

            this.preview = '<i class="icon-file icon-5x"></i>';
        },

        convertImageToUrl(image) {
            return new Promise((resolve, reject) => {
                if (! (image instanceof File) || ! image.type.match(/image\/*/i)) {
                    reject(new Error(`Argument must have image mime-type.`));
                }

                const fileReader = new FileReader();
                fileReader.onload = event => resolve(event.target.result);
                fileReader.readAsDataURL(image)
            })
        },

        async getFileFromUrl(path, name) {
            let { data } = await axios.get(path, { responseType: 'blob' });
            let file = this.blobToFile(data, name);
            return file;
        },

        blobToFile(blob, filename) {
            const formData = new FormData();
            formData.set('file', blob, filename);
            return formData.get('file');
        }
    }
}
</script>

