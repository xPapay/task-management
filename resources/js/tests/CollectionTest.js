import test from 'ava';
import sinon from 'sinon';
import Collection from '../Collection';

test('that it can be instantiated with array of items', t => {
    const items = [1, 2, 3];
    const collection = new Collection(items);
    t.is(collection.toArray().length, items.length);
});

test('that it can be instantiated with single item', t => {
    const collection = new Collection({});
    t.is(collection.toArray().length, 1);
});

test('that it can add item', t => {
    let collection = new Collection();
    collection.add('something');
    t.is(collection.toArray()[0], 'something');
});

test('that it can add collection of items', t => {
    let collection = new Collection([1,2]);
    let anotherCollection = new Collection([3,4]);
    collection.add(anotherCollection);
    t.is(collection.toArray().length, 4);
    t.true(collection.toArray().includes(3));
})

test('that it can remove item at specific index', t => {
    let collection = new Collection(['item 1', 'item 2', 'item 3']);
    collection.remove(Object.keys(collection.all())[1]);
    t.true(collection.toArray().includes('item 1'));
    t.false(collection.toArray().includes('item 2'));
    t.is(collection.toArray().length, 2);
});

test('that callback can be called on every item in array', t => {
    let collection = new Collection(['item 1', 'item 2']);
    const spy = sinon.spy();
    collection.forEach(spy);
    t.true(spy.calledTwice);
    t.true(spy.calledWith('item 1'));
    t.true(spy.calledWith('item 2'));
});

test('that collection has a lenght', t => {
    const items = [1, 2];
    let collection = new Collection(items);
    t.is(collection.count(), items.length);
});
