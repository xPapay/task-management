<template>
    <ul>
        <li v-for="assignee in assignees" v-text="assignee.name" :key="assignee.id"></li>
    </ul>
</template>

<script>
export default {
  props: ["taskId"],
  data() {
      return {
          assignees: []
      }
  },
  created() {
    axios
      .get(`/tasks/${this.taskId}/assignees`)
      .then(({ data }) => this.assignees = data);
  }
};
</script>

