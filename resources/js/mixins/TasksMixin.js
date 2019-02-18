import TimespanLoader from '../TimespanLoader';

export default {
    methods: {
        fetch() {
            axios.get(this.queryStringBuilder.getQueryString())
                .then(({ data }) => {
                    this.tasks = data;
                    this.timespanLoader.save();
                    console.log(this.timespanLoader.loadedTimespans);
                });
        },

        handleRangeChanged(props) {
            this.queryFilters = {
                ...this.queryFilters,
                sinceDate: { value: App.formatDate(props.start) },
                untilDate: { value: App.formatDate(props.end) }
            }

            let timespan = {
                start: this.queryFilters.sinceDate.value,
                end: this.queryFilters.untilDate.value
            }

            if ((timespan = this.timespanLoader.shouldLoad(timespan)) === false) {
                return;
            }

            this.queryStringBuilder.setFilters({
                ...this.queryFilters,
                sinceDate: { value: App.formatDate(timespan.start) },
                untilDate: { value: App.formatDate(timespan.end) }
            });

            this.fetch();
        }
    },

    // watch: {
    //     queryFilters: {
    //         handler: function () {
    //             this.handleQueryFilterChange();
    //         },
    //         deep: true
    //     }
    // },

    computed: {
        tasksDataset() {
            let dataset = [];
            this.tasks.forEach(task => {
                dataset.push({
                    id: task.id,
                    start: task.start_date,
                    end: task.due_date,
                    content: `<a href="/tasks/${task.id}">${task.title}</a>`,
                    finished_at: task.finished_at,
                    class:
                        task.finished_at === null
                            ? "timeline-item--unfinished"
                            : "timeline-item--finished"
                });
            });
            return dataset;
        }
    },

    created() {
        this.timespanLoader = new TimespanLoader();
    }
}
