// @ts-check
import {guid} from './helpers';

class Collection {
    /**
     * 
     * @param {array} items 
     */
    constructor(items = []) {
        this._items = {};

        if (Array.isArray(items)) {
            items.forEach((item, index) => this._items[`${guid()}-${index}`] = item);
            return;
        }

        this._items[guid()] = items;
    }

    toArray() {
        let items = [];
        Object.keys(this._items).map(key => items.push(this._items[key]));
        return items;
    }

    /**
     * @return {object}
     */
    all() {
        return this._items;
    }

    /**
     * 
     * @param {any} items
     * @return {this}
     */
    add(items) {
        if (items instanceof Array) {
            items.map((item, index) => this._items[`${guid()}-${index}`] = item);
            return this;
        }

        if (items instanceof this.constructor) {
            this._items = {...this._items, ...items.all()};
            return this;
        }

        this._items[guid()] = items;
        return this;
    }

    /**
     * 
     * @param {number} index 
     * @return {this}
     */
    remove(index) {
        let newItems = { ...this._items };
        delete newItems[index];
        this._items = newItems;
        return this;
    }

    forEach(callback) {
        Object.keys(this._items).forEach((key, index) => callback(this._items[key], index));
    }

    count() {
        return Object.keys(this._items).length;
    }
}

export default Collection;
