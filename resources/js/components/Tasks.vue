<template>
    <div>
        <select class="input input--narrow m-bottom-s" name="status" v-model="filters.status">
            <option value="all">All</option>
            <option value="unfinished">Unfinished</option>
            <option value="finished">Finished</option>
        </select>
        <timeline :items="tasksDataset" @onRangeChanged="handleRangeChanged"></timeline>
    </div>
</template>

<script>
import Timeline from './Timeline.vue';
import TaskApiRepository from '../TaskApiRepository';
import TasksCollection from '../TasksCollection';
import TimespanLoader from '../TimespanLoader';
import {formatDate} from '../helpers';
import Task from '../Task';
import axios from 'axios';

export default {
    props: {
        type: {
            default: () => "my-tasks"
        }
    },
    components: {Timeline},

    data() {
        return {
            tasks: new TasksCollection(),
            filters: {
                status: 'all',
                since: null,
                until: null
            }
        }
    },
    
    methods: {
        async handleRangeChanged({start, end}) {
            const {since, until} = this.loader.getTimespanFor({since: formatDate(start), until: formatDate(end)});

            if (since && until) {
                const tasks = await this.repo.since(since).until(until).get();
                this.tasks.add(tasks);
                this.loader.save({start: since, end: until});
            }

            this.filters.since = formatDate(start);
            this.filters.until = formatDate(end);
        },

        handleNewTaskCreated(data) {
            if (this.type === 'supervising') {
                this.tasks.add(new Task(data));
                return;
            }

            const { assignees } = data;
            if (assignees.findIndex(assignee => assignee.id == App.user.id) === -1) {
                return;
            }
            this.tasks.add(new Task(data));
        },

        getButtons(task) {
            if (this.type === 'my-tasks') {
                if (! task.isFinished()) {
                    return {
                        finish: {
                            label: '<i class="far fa-check-circle fa-2x green"></i>',
                            handler: ({ currentTarget }) => {
                                console.log("Finish button was pressed");
                                console.log(currentTarget.dataset.itemId);
                                const taskId = currentTarget.dataset.itemId;
                                axios.post(`finished-tasks/${taskId}`).then(({data}) => this.tasks.get(taskId).finish());
                            }
                        }
                    }
                }
                return {
                    unfinish: {
                        label: '<i class="fas fa-ban fa-2x green"></i>',
                        handler: ({ currentTarget }) => {
                            console.log("Unfinish button was pressed");
                            console.log(currentTarget.dataset.itemId);
                            const taskId = currentTarget.dataset.itemId;
                            axios.delete(`finished-tasks/${taskId}`).then(({data}) => this.tasks.get(taskId).unfinish());
                        }
                    }
                }
            }

            if (this.type === 'supervising') {
                return;
                return {
                    delete: {
                        label: '<i class="far fa-trash-alt fa-2x red"></i>',
                        handler: ({ currentTarget }) => {
                            console.log("Delete button was pressed");
                            console.log(currentTarget.dataset.itemId);
                            const taskId = currentTarget.dataset.itemId;
                            axios.delete(`/tasks/${taskId}`).then(() => this.tasks.remove(taskId));
                        }
                    }
                }
            }
        }
    },

    computed: {
        filteredTasks() {
            return Object.keys(this.filters).reduce((tasks, filterName) => {
                return tasks[filterName](this.filters[filterName]);
            }, this.tasks);
        },

        tasksDataset() {
            let dataset = [];
            this.filteredTasks.forEach(task => {
                dataset.push({
                    id: task.id,
                    start: task.start_date,
                    end: task.due_date,
                    content: `<a href="/tasks/${task.id}">${task.title}</a>`,
                    finished_at: task.finished_at,
                    buttons: {
                        ...this.getButtons(task)
                    },
                    class:
                        task.isFinished()
                            ? "timeline-item--unfinished"
                            : "timeline-item--finished"
                });
            });
            return dataset;
        },

        url() {
            return this.type === 'my-tasks' ? '/tasks' : '/tasks/supervising';
        },
    },

    created() {
        this.repo = new TaskApiRepository(this.url);
        this.loader = new TimespanLoader();
        Event.$on('taskCreated', this.handleNewTaskCreated);
    }
}
</script>
