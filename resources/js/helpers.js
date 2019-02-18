export const formatDate = (date, format = 'days') => {
    switch (format) {
        case 'days': return new Date(`${date.getFullYear()}/${date.getMonth() + 1}/${date.getDate()}`);
        default: return date;
    }
}

export const guid = () => {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
}
