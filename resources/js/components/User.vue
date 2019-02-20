<template>
<div class="m-top-l">
    <div class="cropping-area" v-if="pictureToCrop">
        <img 
            ref="pictureToCrop" 
            :src="pictureToCrop" 
            @load="handleImageLoad"
            style="max-width: 100%;"
        >
        <div class="cropping-area__buttons">
            <button class="cropping-area__button" @click="uploadPicture">Confirm</button>
            <button class="cropping-area__button" @click="cancelCropping">Cancel</button>
        </div>
    </div> 

    <div class="valign">

        <div class="avatar-container f-no-shrink">
            <img 
                :src="picture" 
                alt="avatar-picture" 
                class="avatar-picture"
            >
            <div 
                class="avatar-preview" 
                ref="avatarPreview" 
            >
            </div>
        </div>
        
        <div class="m-left-s">
            <div class="edit-name flex" v-if="editName">
                <input class="edit-name__input" type="text" v-model="newName">
                <div class="f-no-shrink">
                    <button @click="updateName" class="edit-name__button"><i class="far fa-check-circle fa-3x green"></i></button>
                    <button @click="cancelRenaming" class="edit-name__button"><i class="far fa-times-circle fa-3x red"></i></button>
                </div>
            </div>
            <div class="m-bottom-s font-bigger" v-else>
                {{ name }}
            </div>
            <div class="p-relative m-top-s">
                <i class="icon-pencil icon-3x pointer" @click="open = true"></i>
                <div class="contextual-menu" v-show="open">
                    <i class="far fa-times-circle fa-2x m-bottom-s pointer" @click="open = false"></i>
                    <ul class="contextual-menu__list block">
                        <li class="m-bottom-xs">
                            <label for="attachment-upload" class="pointer">
                                Change picture
                            </label>
                            <input @change="previewPicture" type="file" accept="image/*" name="attachment" id="attachment-upload">
                        </li>
                        <li @click="changeName" class="m-bottom-xs pointer">Change name</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
import 'cropperjs/dist/cropper.css';
import Cropper from 'cropperjs';
export default {
    props: ['user'],

    data() {
        return {
            name: this.user.name,
            newName: this.user.name,
            picture: this.user.picture_path || "https://s3.amazonaws.com/uifaces/faces/twitter/kerem/128.jpg",
            open: false,
            pictureToCrop: null,
            editName: false
        }
    },

    methods: {
        handleImageLoad(event) {
            this.cropper = new Cropper(this.$refs.pictureToCrop, {aspectRatio: 1, preview: this.$refs.avatarPreview});   
        },

        changeName() {
            this.open = false;
            this.editName = true;
        },

        previewPicture({target}) {
            this.open = false;
            const { files } = target;

            if (! files || ! files[0] || ! this.isImage(files[0])) {
                console.error('Selected file is not an image');
                return;
            }

            const file = files[0];

            let reader = new FileReader();
            reader.onload = event => this.pictureToCrop = event.target.result;
            reader.readAsDataURL(file);
        },

        cancelCropping() {
            this.cropper.destroy();
            this.pictureToCrop = null;
        },

        cancelRenaming() {
            this.editName = false;
            this.newName = this.name;
        },

        updateName() {
            axios.patch('/profile', {name: this.newName})
                .then(response => {
                    this.editName = false;
                    this.name = this.newName;
                    App.flash('Name was updated');
                });
        },

        uploadPicture() {
            this.cropper.getCroppedCanvas().toBlob(async blob => {
                let formData = new FormData();
                formData.append('picture', blob);

                let response = await axios.post('/profile', formData, {
                    Headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                console.log(response);

                let { data } = response;
                this.picture = data.picture_path;
                this.cropper.destroy();
                this.pictureToCrop = null;
            })
        },

        isImage(file) {
            if (! (file instanceof File)) {
                throw new Error('Argument must be instance of File ' + file.constructor.name + ' given.');
            }

            return !!file.type.match(/^image\/*/);
        }
    }
}
</script>
