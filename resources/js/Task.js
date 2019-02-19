// @ts-check

class Task {
    /**
     * 
     * @param {object} data
     */
    constructor(data) {
        if (!data) throw new Error(`Data parameter must be object, ${data} given`);
        if (data.constructor !== Object) throw new Error(`Data parameter must be object, ${data.constructor.name} given`);

        const properties = {
            id: {required: true},
            start_date: {required: true},
            due_date: {required: true},
            title: {required: true},
            finished_at: {required: false}
        }

        Object.keys(properties).map(property => {
            if (properties[property].required && !data[property]) {
                throw new Error(`${property} is required property`);
            }

            if (['start_date', 'due_date'].includes(property) && !(data[property] instanceof Date)) {
                const dateString = data[property].replace(/\s/, 'T');
                this[`_${property}`] = new Date(dateString);
                return;
            }

            this[`_${property}`] = data[property];
        });
    }

    /**
     * @return {number}
     */
    get id() {
        //@ts-ignore
        return this._id;
    }

    /**
     * @return {Date | null}
     */
    get finished_at() {
        // @ts-ignore
        return this._finished_at;
    }

    /**
     * @return {Date}
     */
    get start_date() {
        // @ts-ignore
        return this._start_date;
    }

    /**
     * @return {Date}
     */
    get due_date() {
        // @ts-ignore
        return this._due_date;
    }

    /**
     * @return {string}
     */
    get title() {
        // @ts-ignore
        return this._title;
    }

    /**
     * @return {boolean}
     */
    isFinished() {
        // @ts-ignore
        return !!this._finished_at;
    }

    finish(date = new Date()) {
        this._finished_at = date;
    }

    unfinish() {
        this._finished_at = null;
    }
}

export default Task;
