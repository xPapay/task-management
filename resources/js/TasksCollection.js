import Collection from './Collection';
import Task from './Task';

class TasksCollection extends Collection {
    /**
     * 
     * @param {object[]} data 
     */
    constructor(data = []) {
        super();
        
        if (data instanceof Task) {
            this._items[data.id] = data;
            return;
        }

        this._items = data.reduce((prevValue, currentValue) => {
            if (currentValue instanceof Task) {
                prevValue[currentValue.id] = currentValue;
                return prevValue;
            }
            let task = new Task(currentValue); // todo if not task, do task first and then continue as others;
            prevValue[task.id] = task;
            return prevValue;
        }, {}); // todo, start with collection and then collection.add()
    }

    /**
     * @return {this}
     */
    finished() {
        const finished = [];
        Object.keys(this._items).map(key => this._items[key].isFinished() && finished.push(this._items[key]));
        
        return new this.constructor(finished);
    }

    /**
     * @return {this}
     */
    unfinished() {
        const unfinished = [];
        Object.keys(this._items).map(key => !this._items[key].isFinished() && unfinished.push(this._items[key]));
        
        return new this.constructor(unfinished);
    }

    /**
     * 
     * @param {string} status 
     * @return {this}
     */
    status(status) {
        if (status === 'all') {
            return this;
        }
        
        if (typeof this[status] !== 'function') {
            throw new Error(`There is no such a function with name: ${status}`);
        }
        
        return this[status]();
    }

    /**
     * 
     * @param {Date} time 
     * @return {this}
     */
    since(time) {
        let tasks = [];
        Object.keys(this._items).map(key => this._items[key].due_date >= time && tasks.push(this._items[key]));
        return new this.constructor(tasks);
    }

    /**
     * 
     * @param {Date} time 
     * @return {this}
     */
    until(time) {
        let tasks = [];
        Object.keys(this._items).map(key => this._items[key].start_date <= time && tasks.push(this._items[key]));
        return new this.constructor(tasks);
    }

    get(id) {
        return this._items[id];
    }

    add(items) {
        if (items instanceof Task) {
            this._items = {
                ...this._items,
                [items.id]: items
            }
            // this._items[items.id] = items;
            return this;
        }

        super.add(items);
    }
}

export default TasksCollection;
