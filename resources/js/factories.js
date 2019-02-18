let id = 0;
export function taskFactory(override = {}) {
    const {title, start_date, due_date, finished_at} = override;
    return {
        id: ++id,
        title: title || `Task ${id}`,
        start_date: start_date || new Date(),
        due_date: due_date || new Date(),
        finished_at: finished_at
    }
}

taskFactory({title: 'TaskTitle', finished_at: '2018'})
