import test from 'ava';
import Axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import Task from '../../Task';
import TasksCollection from '../../TasksCollection';

// maybe test ITaskRepository and not specific repository
// test('that I can fetch all tasks', async t => {
//     const mock = new MockAdapter(Axios);

//     mock.onGet('/tasks').reply(200,
//         [
//             { id: 1, title: "Task 1" },
//             { id: 2, title: "Task 2" }
//         ]
//     );

//     const tasks = await Task.all();
//     t.true(tasks instanceof TasksCollection)
//     t.is(tasks.count(), 2);
// });

// given there are 3 tasks
// and 1 of them is outside of the range
// when I request task in given range
// I should get 2 tasks


