import test from 'ava';
import sinon from 'sinon';
import Axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import TaskApiRepository from '../TaskApiRepository';
import TasksCollection from '../TasksCollection';
import Task from '../Task';
import {taskFactory} from '../factories';

// This is api repo and it's results depends solely on constructing right url to get right results

test.beforeEach(t => {
    const baseUrl = '/tasks';
    t.context = {
        axiosMock: new MockAdapter(Axios),
        baseUrl,
        repo: new TaskApiRepository(baseUrl)
    }
})

test.serial('that it can fetch all tasks', async t => {
    t.context.axiosMock.onAny().replyOnce(config => {
        t.is(config.method, 'get');
        t.is(config.url, t.context.baseUrl);
        return [200, [taskFactory()]];
    });
    
    const tasks = await t.context.repo.all();
    t.true(tasks instanceof TasksCollection);
});

test.serial('that it can fetch all finished tasks', async t => {
    t.context.axiosMock.onAny().replyOnce(config => {
        t.is(config.method, 'get');
        t.is(config.url, t.context.baseUrl);
        t.is(config.params.status, 'finished');
        return [200, [taskFactory()]];
    })
    
    const tasks = await t.context.repo.finished().get();
    t.true(tasks instanceof TasksCollection);
});

test.serial('that it can fetch all unfinished tasks', async t => {
    t.context.axiosMock.onAny().replyOnce(config => {
        t.is(config.method, 'get');
        t.is(config.url, t.context.baseUrl);
        t.is(config.params.status, 'unfinished');
        return [200, [taskFactory()]];
    })
    
    const tasks = await t.context.repo.unfinished().get();
    t.true(tasks instanceof TasksCollection);
});

test.serial('that it can fetch tasks since given date', async t => {
    const date = new Date();
    const formatedDate = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;

    t.context.axiosMock.onAny().replyOnce(config => {
        t.is(config.method, 'get');
        t.is(config.url, t.context.baseUrl);
        t.is(config.params.sinceDate, formatedDate);
        return [200, [taskFactory()]];
    })
    
    const tasks = await t.context.repo.since(date).get();
    t.true(tasks instanceof TasksCollection);
});

test.serial('that it can fetch tasks until given date', async t => {
    const date = new Date();
    const formatedDate = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;

    
    t.context.axiosMock.onAny().replyOnce(config => {
        t.is(config.method, 'get');
        t.is(config.url, t.context.baseUrl);
        t.is(config.params.untilDate, formatedDate);
        return [200, [taskFactory()]];
    })
    
    const tasks = await t.context.repo.until(date).get();
    t.true(tasks instanceof TasksCollection);
});

test.serial('that it allows for fluent chaining', async t => {
    const date = new Date();
    const formatedDate = `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;

    
    t.context.axiosMock.onAny().replyOnce(config => {
        t.is(config.method, 'get');
        t.is(config.url, t.context.baseUrl);
        t.is(config.params.status, 'finished');
        t.is(config.params.sinceDate, formatedDate);
        t.is(config.params.untilDate, formatedDate);
        return [200, [taskFactory()]];
    })
    
    const repo = t.context.repo.finished();
    t.true(repo instanceof TaskApiRepository);
    const tasks = await repo.since(date).until(date).get();
    t.true(tasks instanceof TasksCollection);
});

test.serial('that it can mark task as finished', async t => {
    const params = taskFactory();  
    const task = new Task(params);
    sinon.stub(task, 'id').get(() => 1);
    
    t.context.axiosMock.onAny().replyOnce(config => {
        t.is(config.method, 'post');
        t.is(config.url, `/finished-tasks/${task.id}`);
        return [200, {...params, finished_at: new Date()}];
    })
    
    const updatedTask = await t.context.repo.finish(task);
    t.true(updatedTask instanceof Task);
});
