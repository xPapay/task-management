// @ts-check

import TasksCollection from './TasksCollection';
import Task from './Task';
import Axios from 'axios';

class TaskApiRepository {
    /**
     * 
     * @param {string} baseUrl 
     */
    constructor(baseUrl) {
        this._baseUrl = baseUrl
        this._parameters = {};
    }

    /**
     * @return {Promise<TasksCollection>}
     */
    async all() {
        const { data } = await Axios.get(this._baseUrl);
        return new TasksCollection(data);
    }

    /**
     * @return {Promise<TasksCollection>}
     */
    async get() {
        const { data } = await Axios.get(this._baseUrl, {params: this._parameters});
        return new TasksCollection(data);
    }

    /**
     * 
     * @param {Task} task 
     * @return {Promise<Task>}
     */
    async finish(task) {
        const { data } = await Axios.post(`/finished-tasks/${task.id}`);
        return new Task(data);
    }

    /**
     * @return {this}
     */
    finished() {
        this._setParameter('status', 'finished');
        return this;
    }

    /**
     * @return {this}
     */
    unfinished() {
        this._setParameter('status', 'unfinished');
        return this;
    }

    /**
     * @param {Date} time
     * @return {this}
     */
    since(time) {
        this._setParameter('sinceDate', time);
        return this;
    }

    /**
     * @param {Date} time
     * @return {this}
     */
    until(time) {
        this._setParameter('untilDate', time);
        return this;
    }

    /**
     * Set query parameter
     * 
     * @param {string} name
     * @param {any} value 
     */
    _setParameter(name, value) {
        if (value instanceof Date) {
            value = `${value.getFullYear()}-${value.getMonth() + 1}-${value.getDate()} ${value.getHours()}:${value.getMinutes()}:${value.getSeconds()}`;
        }
        this._parameters[name] = value;
    }
}

export default TaskApiRepository;
