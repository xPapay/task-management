<template>
  <div>
    <comment 
      v-for="comment in comments" 
      :key="comment.id" 
      :body="comment.body" 
      :date="comment.created_ago" 
      :author="comment.author"
      :attachments="comment.attachments"
    >
    </comment>
  </div>
</template>

<script>
import Comment from './Comment.vue';
export default {
  components: { Comment },

  props: {
    initComments: {
      default: []
    }
  },

  data() {
    return {
      comments: this.initComments
    }
  },

  created() {
    Event.$on('commentAdded', data => this.comments.push(data));
  }
};
</script>

