import TimespanLoader from './TimespanLoader';
import TasksCollection from './TasksCollection';
import Task from './Task';

class TaskSmartLoading {
    /**
     * 
     * @param {ITaskRepository} repository 
     * @param {TimespanLoader} loader 
     */
    constructor(repository, loader = new TimespanLoader()) {
        this._repository = repository;
        this._loader = loader;

        this._filters = {};
        this._tasks = new TasksCollection();
    }

    /**
     * @return {Promise<TasksCollection>}
     */
    async all() {
        // TODO: prevent from fetching multiple times on repeating call. Introduce fresh() to fetch new
        this._tasks = await this.repository.all();
        return this._tasks;
    }

    /**
     * @return {Promise<TasksCollection>}
     */
    async get() {
        const { _repository: repo, _loader: loader } = this;
        if (loader.needLoad()) {
            let query = repo, since, until;

            if (since = loader.getSince()) {
                query = query.since(since);
            }

            if (until = loader.getUntil()) {
                query = query.until(until);
            }

            try {
                const tasks = await query.get();
                loader.save();
                this._tasks.add(tasks);
            } catch (error) {
                console.error(error);
                loader.clear();
            }
        }

        // return this._applyFilters();
        console.log(this._applyFilters());
        return this._tasks;
    }

    /**
     * 
     * @param {Date} time 
     * @return {this}
     */
    since(time) {
        this._loader.setSince(time);
        this._addFilter({ since: time });
        return this;
    }

    /**
     * 
     * @param {Date} time 
     * @return {this}
     */
    until(time) {
        this._loader.setUntil(time);
        this._addFilter({ until: time });
        return this;
    }

    /**
     * @return {this}
     */
    finished() {
        this._addFilter({ status: 'finished' });
        return this;
    }

    /**
     * @return {this}
     */
    unfinished() {
        this._addFilter({ status: 'unfinished' });
        return this;
    }

    /**
     * 
     * @param {Task} task 
     * @return {Promise<Task>}
     */
    async finish(task) {
        const updatedTask = await this.repository.finish(task);
        this._tasks.add(updatedTask); // TODO: if taskscollection use object with task.id as unique property, this should update task with given id
        return updatedTask;
    }

    /**
     * 
     * @param {object} filter 
     */
    _addFilter(filter) {
        this._filters = {
            ...this._filters,
            ...filter
        }
    }

    /**
     * @return {TasksCollection}
     */
    _applyFilters() {
        return Object.keys(this._filters).reduce((accumulator, currentValue) => {
            let bla = accumulator[currentValue](this._filters[currentValue]);
            console.log(`accumulator.${currentValue}(${this._filters[currentValue]})`);
            return bla;
            // return accumulator[this.filters[currentValue]]()
        }, new TasksCollection(this._tasks.all())); // or this.tasks.all()
    }

}

export default TaskSmartLoading;
