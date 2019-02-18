import test from 'ava';
import { taskFactory } from '../factories';
import TasksCollection from '../TasksCollection';
import Task from '../Task';

test('that it initializes that way that every item is Task', t => {
    const taskData = [taskFactory(), taskFactory()];
    const tasks = new TasksCollection(taskData);
    t.is(tasks.count(), 2);
    tasks.forEach(task => t.true(task instanceof Task));
});

test('that it can be initialized with array of tasks', t => {
    // TODO: since there are currently no interfaces in js
    // I cannot use dummy task object implementing interface
    const task = new Task(taskFactory())
    const tasks = new TasksCollection([task]);
    t.is(tasks.count(), 1);
    t.is(tasks.toArray()[0], task);
});

test('that it can be initialized with single task', t => {
    const task = new Task(taskFactory())
    const tasks = new TasksCollection(task);
    t.is(tasks.count(), 1);
    t.is(tasks.toArray()[0], task);
});

test('that it can filter out finished tasks', t => {
    const finishedTask = taskFactory({finished_at: new Date()});
    const unfinishedTask = taskFactory();
    const tasks = new TasksCollection([finishedTask, unfinishedTask]);
    const finishedTasks = tasks.finished();
    t.not(finishedTasks, tasks);
    t.is(tasks.count(), 2);
    t.is(finishedTasks.count(), 1);
});

test('that it can filter out unfinished tasks', t => {
    const finishedTask = taskFactory({finished_at: new Date()});
    const unfinishedTask = taskFactory();
    const tasks = new TasksCollection([finishedTask, unfinishedTask]);
    const unfinishedTasks = tasks.unfinished();
    t.not(unfinishedTasks, tasks);
    t.is(tasks.count(), 2);
    t.is(unfinishedTasks.count(), 1);
});

test('that it can filter out tasks based on status', t => {
    const finishedTask = taskFactory({finished_at: new Date()});
    const unfinishedTask = taskFactory();
    const tasks = new TasksCollection([finishedTask, unfinishedTask]);
    const unfinishedTasks = tasks.status('unfinished');
    t.not(unfinishedTasks, tasks);
    t.is(tasks.count(), 2);
    t.is(unfinishedTasks.count(), 1);
});

test('that it throws exception if used unimplemented status name', t => {
    const error = t.throws(() => {
        const tasks = new TasksCollection();
        tasks.status('wrong');
    }, Error);

    t.is(error.message, 'There is no such a function with name: wrong');
});

test('that it filters out tasks since given date', t => {
    const tasks = [
        new Task(taskFactory({start_date: new Date('2019/01/01'), due_date: new Date('2019/01/10')})),
        new Task(taskFactory({start_date: new Date('2019/01/01'), due_date: new Date('2019/01/05')}))
    ];

    const tasksCollection = new TasksCollection(tasks);
    const tasksSince = tasksCollection.since(new Date('2019/01/06'));

    t.is(tasksCollection.count(), 2);
    t.not(tasksSince, tasksCollection);
    t.is(tasksSince.count(), 1);
    t.is(tasksSince.toArray()[0], tasks[0]);
});

test('that it filters out tasks until given date', t => {
    const tasks = [
        new Task(taskFactory({start_date: new Date('2019/01/01'), due_date: new Date('2019/01/05')})),
        new Task(taskFactory({start_date: new Date('2019/01/03'), due_date: new Date('2019/01/10')}))
    ];

    const tasksCollection = new TasksCollection(tasks);
    const tasksUntil = tasksCollection.until(new Date('2019/01/02'));

    t.is(tasksCollection.count(), 2);
    t.not(tasksUntil, tasksCollection);
    t.is(tasksUntil.count(), 1);
    t.is(tasksUntil.toArray()[0], tasks[0]);
});

test('it allow for fluent chaining', t => {
    const tasks = [
        new Task(taskFactory({start_date: new Date('2019/01/01'), due_date: new Date('2019/01/10')})),
        new Task(taskFactory({start_date: new Date('2019/01/01'), due_date: new Date('2019/01/05'), finished_at: new Date()})),
        new Task(taskFactory({start_date: new Date('2019/01/03'), due_date: new Date('2019/01/10')}))
    ];

    const tasksCollection = new TasksCollection(tasks);
    const filtered = tasksCollection.until(new Date('2019/01/02')).finished();

    t.not(filtered, tasksCollection);
    t.is(tasksCollection.count(), 3);
    t.is(filtered.count(), 1);
    t.is(filtered.toArray()[0], tasks[1]);
});

test('that task can be retrieved by its id', t => {
    const task = new Task(taskFactory());
    const tasksCollection = new TasksCollection(task);
    t.is(tasksCollection.get(task.id), task);
});
