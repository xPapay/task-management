export default class {
    constructor({ loadedTimespans } = {}) {
        this._loadedTimespans = {};
        // this._precision = 'day';

        if (Array.isArray(loadedTimespans)) {
            loadedTimespans.map((timespan, index) => this._loadedTimespans[index] = timespan);
            return;
        }

        this._loadedTimespans = loadedTimespans ? loadedTimespans : {};
    }

    /**
     * @return {array}
     */
    get loadedTimespans() {
        let timespans = [];
        Object.keys(this._loadedTimespans).map(key => timespans.push(this._loadedTimespans[key]));
        return timespans;
    }

    getTimespanFor({since, until}) {
        if (since > until) {
            throw new Error('Since date cannot be greater than until date');
        }

        const calculatedSince = this._getSinceFor(since);
        if (!calculatedSince) return {since: null, until: null};
        const calculatedUntil = this._getUntilFor(until);
        if (!calculatedUntil) return {since: null, until: null};
        if (calculatedSince > calculatedUntil) return {since: null, until: null};
        return {
            since: calculatedSince,
            until: calculatedUntil
        };
    }

    /**
     * Calculate until when to load so there is nothing loaded twice
     * 
     * @param {Date} time 
     * @return {Date|null}
     */
    _getSinceFor(time) {
        const loadedIndex = this._getOverlapIndex(time);
        if (loadedIndex) {
            if (this._loadedTimespans[loadedIndex].end === undefined) {
                return null;
            }
            
            return this._moveByDays(+1, this._loadedTimespans[loadedIndex].end);
        }

        return time;
    }

    /**
     * Calculate until when to load so there is nothing loaded twice
     * 
     * @param {Date} time 
     * @return {Date|null}
     */
    _getUntilFor(time) {
        const loadedIndex = this._getOverlapIndex(time);
        if (loadedIndex) {
            if (this._loadedTimespans[loadedIndex].start === undefined) {
                return null;
            }

            return this._moveByDays(-1, this._loadedTimespans[loadedIndex].start);
        }

        return time;
    }

    /**
     * Check if given timespan was already loaded
     * 
     * @param {{start: Date, end: Date}} start
     * @return {boolean}
     */
    isLoaded({ start, end }) {
        const timespanCoveringStartIndex = this._getOverlapIndex(start);
        if (!timespanCoveringStartIndex) return false;
        const timespanCoveringEndIndex = this._getOverlapIndex(end);
        if (!timespanCoveringEndIndex) return false;
        return timespanCoveringStartIndex === timespanCoveringEndIndex;
    }

    /**
     * Save given timespan as loaded timespan
     * 
     * @param {{start: Date, end: Date}} param0 
     */
    save({ start, end }) {
        if (start > end) {
            throw new Error('Start date cannot be smaller than end date');
        }

        if (this.isLoaded({ start, end })) {
            return;
        }

        this._removeContainessBetween(start, end);

        const leftOverlapIndex = this._getOverlapIndex(start);
        const rightOverlapIndex = this._getOverlapIndex(end);

        if (leftOverlapIndex && rightOverlapIndex) {
            this._loadedTimespans[leftOverlapIndex].end = this._loadedTimespans[rightOverlapIndex].end;
            delete this._loadedTimespans[rightOverlapIndex];
            return;
        }

        if (leftOverlapIndex) {
            this._loadedTimespans[leftOverlapIndex].end = end;
            const rightAdjacentIndex = this._getAdjacentIndex('right', end);
            if (rightAdjacentIndex) {
                this._mergeInRightAdjacent(rightAdjacentIndex, leftOverlapIndex);
            }
            return;
        }

        if (rightOverlapIndex) {
            this._loadedTimespans[rightOverlapIndex].start = start;
            const leftAdjacentIndex = this._getAdjacentIndex('left', start);
            if (leftAdjacentIndex) {
                this._mergeInLeftAdjacent(leftAdjacentIndex, rightOverlapIndex);
            }
            return;
        }

        const key = Date.now();
        this._loadedTimespans[key] = { start, end };

        const leftAdjacentIndex = this._getAdjacentIndex('left', start);
        if (leftAdjacentIndex) {
            this._mergeInLeftAdjacent(leftAdjacentIndex, key);
        }

        const rightAdjacentIndex = this._getAdjacentIndex('right', end);
        if (rightAdjacentIndex) {
            this._mergeInRightAdjacent(rightAdjacentIndex, key);
        }
    }

    /**
     * Remove loaded timespans spanning between given boundaries
     * 
     * @param {Date} start 
     * @param {Date} end 
     */
    _removeContainessBetween(start, end) {
        Object.keys(this._loadedTimespans).map(key => {
            let timespan = this._loadedTimespans[key];
            if (((start === undefined) || (start <= timespan.start)) && ((end === undefined) || (end >= timespan.end))) {
                delete this._loadedTimespans[key];
            }
        });
    }

    /**
     * Merge adjacent lying left to the target into the target
     * 
     * @param {number} sourceIndex Index of timeline being merged
     * @param {number} targetIndex Index of timeline being merged in
     */
    _mergeInLeftAdjacent(sourceIndex, targetIndex) {
        this._loadedTimespans[targetIndex].start = this._loadedTimespans[sourceIndex].start;
        delete this._loadedTimespans[sourceIndex];
    }

    /**
     * Merge adjacent lying right to the target into the target
     * 
     * @param {number} sourceIndex Index of timeline being merged
     * @param {number} targetIndex Index of timeline being merged in
     */
    _mergeInRightAdjacent(sourceIndex, targetIndex) {
        this._loadedTimespans[targetIndex].end = this._loadedTimespans[sourceIndex].end;
        delete this._loadedTimespans[sourceIndex];
    }

    /**
     * Find adjacent within given precision
     * 
     * @param {string} side Which side adjacent
     * @param {Date} time Time to which look for adjacent
     * @return {number}
     */
    _getAdjacentIndex(side, time) {
        if (side === 'left') {
            return Object.keys(this._loadedTimespans)
                .find(key => this._loadedTimespans[key].end.getTime() === this._moveByDays(-1, time).getTime());
        }
        return Object.keys(this._loadedTimespans)
            .find(key => this._loadedTimespans[key].start.getTime() === this._moveByDays(+1, time).getTime());
    }

    /**
     * Find timespan covering given time
     * 
     * @param {Date} time Time at which to check for overlaps
     * @return {number}
     */
    _getOverlapIndex(time) {
        return Object.keys(this._loadedTimespans)
            .find(key => (
                ((this._loadedTimespans[key].start === undefined) || (time >= this._loadedTimespans[key].start)) && 
                ((this._loadedTimespans[key].end === undefined) || (time <= this._loadedTimespans[key].end))
            ));
    }

    /**
     * Calculate and returns new copy of data moved by given number of days
     * 
     * @param {number} number Number of days to move
     * @param {Date} date Date to move
     * @return {Date}
     */
    _moveByDays(number, date) {
        let dateCopy = new Date(date);
        dateCopy.setDate(dateCopy.getDate() + number);
        return dateCopy;
    }

}
