import test from 'ava';
import Task from '../Task';
import { taskFactory } from '../factories';

test('that it throws exception during instantiation if data parameter is ommited', t => {
    const error = t.throws(() => {
        new Task();
    }, Error);

    t.is(error.message, 'Data parameter must be object, undefined given');
});

test('that it throws exception during instantiation if data parameter is not object', t => {
    let error = t.throws(() => {
        new Task([]);
    }, Error);

    t.is(error.message, 'Data parameter must be object, Array given');

    error = t.throws(() => {
        new Task('string');
    }, Error);

    t.is(error.message, 'Data parameter must be object, String given');
});

test('that it throws exception if there is missing argument upon instantiation', t => {
    let error = t.throws(() => {
        new Task({});
    }, Error);

    t.is(error.message, 'id is required property');

    error = t.throws(() => {
        new Task({id: 1});
    }, Error);

    t.is(error.message, 'start_date is required property');
});

test('that it instantiate object if all required properties are provided', t => {
    const params = taskFactory();
    const task = new Task(params);
    t.true(task instanceof Task);
    t.is(task.id, params.id);
    t.is(task.title, params.title);
    t.is(task.start_date, params.start_date);
    t.is(task.due_date, params.due_date);
    t.is(task.finished_at, params.finished_at);
});

test('that it instantiate dates as Date object', t => {
    const params = taskFactory({start_date: '2018-12-24 00:00:00', due_date: '2018-12-26 00:00:00'});
    const task = new Task(params);
    t.true(task.start_date instanceof Date);
    t.true(task.due_date instanceof Date);
});

test('that it can tell if task was finished', t => {
    const finishedTask = new Task(taskFactory({finished_at: new Date()}));
    const unfnishedTask = new Task(taskFactory());

    t.log(finishedTask);
    t.log(unfnishedTask);

    t.true(finishedTask.isFinished());
    t.false(unfnishedTask.isFinished());
});

test('that task can be finished', t => {
    const task = new Task(taskFactory());
    task.finish();
    t.true(task.isFinished());
});

test('that task can be unfinished', t => {
    const task = new Task(taskFactory({finished_at: new Date()}));
    t.true(task.isFinished());
    task.unfinish();
    t.false(task.isFinished());
})

test('that task can be finished at specified datetime', t => {
    const task = new Task(taskFactory());
    const finishTime = new Date();
    task.finish(finishTime);
    t.is(task.finished_at, finishTime);
});
