<template>
  <div>
    <div class="overlay" v-if="isOpen"></div>
    <div class="add-task" :class="{['add-task--open']: isOpen}">
    <h2>Add Task</h2>
    <form method="POST" @submit.prevent="createTask" @keydown="errors.clear($event.target.name)">
      <div class="row">
        <input type="text" name="title" class="input m-bottom-xs col-s-2 col-l-1" v-model="title" placeholder="Title">
        <div class="red italic m-bottom-s" v-if="errors.has('title')" v-text="errors.get('title')"></div>
      </div>
      <div class="row">
        <textarea name="description" class="input m-bottom-s col-s-2 no-resize" v-model="description" placeholder="Description"></textarea>
        <div class="red italic m-bottom-s" v-if="errors.has('description')" v-text="errors.get('description')"></div>      
        <div class="col-s-2 col-l-1">
          <v-date-picker
            mode="range" 
            v-model="dateRange" 
            show-caps
            @input="errors.clear('start_date') || errors.clear('due_date')"
          >
          <div class="input-group m-bottom-s" slot-scope="{inputValue, updateValue}">
            <i class="icon-calendar icon-2x"></i>
            <input
              name="start_date"
              class="input"
              type="text"
              :value="inputValue"
              placeholder="YYYY/MM/DD - YYYY/MM/DD"
              @change="updateValue($event.target.value)"
            >
          </div>
          </v-date-picker>
          <div class="red italic m-bottom-s" v-text="errors.get('start_date')"></div>
          <div class="red italic m-bottom-s" v-text="errors.get('due_date')"></div>
        </div>
      </div>
      <h3>Assignees</h3>
      <selectbox
        @select="assign"
        @deselect="deassign"
        :selected="assignees"
        class="col-s-2 col-l-1"
      />
      <div class="red italic m-bottom-s" v-text="errors.get('assignees')"></div>
      <h3>Attachments</h3>
      <ul class="inline-list m-bottom-s">
        <li 
          v-for="(attachment, index) in attachments" 
          :key="index" 
          class="text-center"
        >
              <attachment 
                :source="attachment" 
                @remove="attachments.splice(index, 1)"
                :downloadable="false"
              >
              </attachment>
        </li>
        <li class="valign">
          <label for="attachment-upload" class="pointer">
            <i class="icon-plus icon-2x bg-black white icon--circle"></i>
          </label>
          <input @change="attachFiles" type="file" multiple name="attachment" id="attachment-upload">
        </li>
      </ul>
      <div class="m-top-m t-right">
        <button class="button button--green white" type="submit">Create Task</button>
        <button class="button" @click.prevent="isOpen = false">Cancel</button>
      </div>
    </form>
    </div>
    <fixed-button 
      class="button"
      :clickHandler="() => isOpen = true"
    >
      <i class="fas fa-plus fa-2x"></i>
    </fixed-button>
  </div>
</template>
<script>
import Errors from '../Errors';
import FixedButton from './FixedButton.vue';
import Attachment from './Attachment.vue';
export default {
  components: { FixedButton, Attachment },
  data() {
    return {
      isOpen: false,
      title: "",
      description: "",
      dateRange: {
        start: null,
        end: null
      },
      attachments: [],
      assignees: {},
      errors: new Errors()
    };
  },
  methods: {
    createTask() {
      let formData = new FormData();
      formData.append('title', this.title);
      formData.append('description', this.description);
      this.dateRange.start && formData.append('start_date', App.formatDate(this.dateRange.start));
      this.dateRange.end && formData.append('due_date', App.formatDate(this.dateRange.end));
      Object.keys(this.assignees).map(assignee => formData.append("assignees[]", assignee));
      this.attachments.map(attachment => formData.append("attachments[]", attachment));

      axios.post('/tasks', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(({ data }) => {
          Event.$emit('taskCreated', data);
          App.flash('Task created');
          this.resetInputs();
          this.errors.clearAll();
          this.isOpen = false;
        })
      .catch(error => this.errors.record(error.response.data.errors)); 
    },

    attachFiles(event) {
      this.attachments = [...this.attachments, ...event.target.files];
    },

    assign(user) {
      this.assignees = {...this.assignees, [user.id]: user};
    },

    deassign(user) {
      let assignees = {...this.assignees};
      delete assignees[user.id];
      this.assignees = assignees;
    },

    resetInputs() {
      this.title = "",
      this.description = "",
      this.dateRange = {
        start: null,
        end: null
      },
      this.assignees = {},
      this.attachments = []
    }
  }
};
</script>

