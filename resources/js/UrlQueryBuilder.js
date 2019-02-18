class UrlQueryBuilder {
    /**
     * 
     * @param {object} parameters 
     */
    constructor(parameters = {}) {
        this._parameters = parameters;
    }

    /**
     * 
     * @param {object} parameters
     */
    setParameters(parameters) {
        Object.keys(parameters).map(key => {
            let parameter = parameters[key];
            if (parameter instanceof Date) {
                parameters[key] = `${parameter.getFullYear()}-${parameter.getMonth() + 1}-${parameter.getDate()} ${parameter.getHours()}:${parameter.getMinutes()}:${parameter.getSeconds()}`;
            }
        });

        this.parameters = {
            ...this.parameters,
            ...parameters
        }
    }

    /**
     * @return {string}
     */
    getQueryString() {
        let queryString = '';
        Object.keys(this.parameters).forEach((key, index) => {
            if (!this.parameters[key]) return;
            if (index === 0) {
                queryString += `?${key}=${this.parameters[key]}`;
                return;
            }
            queryString += `&${key}=${this.parameters[key]}`;
        });

        this._clearParameters();
        return queryString;
    }

    _clearParameters() {
        this.parameters = {};
    }
};

export default UrlQueryBuilder;
