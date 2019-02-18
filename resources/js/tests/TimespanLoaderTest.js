import test from 'ava';
import TimespanLoader from '../TimespanLoader';

test('that it returns timespan which is not overlaping with already loaded timespans', t => {
    const loadedTimespans = [
        {start: new Date('2019/01/10'), end: new Date('2019/01/20')}
    ];

    const loader = new TimespanLoader({loadedTimespans});

    // left
    let result = loader.getTimespanFor({since: new Date('2019/01/01'), until: new Date('2019/01/12')});
    let expected = {
        since: new Date('2019/01/01'),
        until: new Date('2019/01/09')
    };

    t.is(result.since.getTime(), expected.since.getTime(), `${result.since} is not equal ${expected.since}`);
    t.is(result.until.getTime(), expected.until.getTime(), `${result.until} is not equal ${expected.until}`);

    //right
    result = loader.getTimespanFor({since: new Date('2019/01/18'), until: new Date('2019/01/25')});
    expected = {
        since: new Date('2019/01/21'),
        until: new Date('2019/01/25')
    };

    t.is(result.since.getTime(), expected.since.getTime(), `${result.since} is not equal ${expected.since}`);
    t.is(result.until.getTime(), expected.until.getTime(), `${result.until} is not equal ${expected.until}`);
});

test('that it return timespan with null start and null end if there is nothing to load', t => {
    const loadedTimespans = [
        {start: new Date('2019/01/10'), end: undefined}
    ];

    const loader = new TimespanLoader({loadedTimespans});

    const {since, until} = loader.getTimespanFor({since: new Date('2019/01/12'), until: new Date('2019/01/18')});
    t.is(since, null);
    t.is(until, null);
});

test('that it return timespan with null start and null end if it is already contained', t => {
    const loadedTimespans = [
        {start: new Date('2019/01/10'), end: new Date('2019/01/20')}
    ];

    const loader = new TimespanLoader({loadedTimespans});

    const {since, until} = loader.getTimespanFor({since: new Date('2019/01/12'), until: new Date('2019/01/18')});
    t.is(since, null);
    t.is(until, null);
});

test('that that it throws error when given since date is greater than until date', t => {
    const loader = new TimespanLoader();
    const error = t.throws(() => {
        loader.getTimespanFor({since: new Date('2019/01/20'), until: new Date('2019/01/10')});
    }, Error);

    t.is(error.message, "Since date cannot be greater than until date");
});

test('that it can save loaded timespan', t => {
    const loader = new TimespanLoader({loadedTimespans: []});
    const since = new Date('2019/01/01');
    const until = new Date('2019/01/10');
    t.is(loader.loadedTimespans.length, 0);
    t.false(loader.isLoaded({start: since, end: until}));
    loader.save({start: since, end: until});
    t.true(loader.isLoaded({start: since, end: until})); 
    t.is(loader.loadedTimespans.length, 1);
});

test('that it merges overlaping timespans upon saving', t => {
    const loadedTimespans = [
        {start: new Date('2019/01/01'), end: new Date('2019/01/05')},
        {start: new Date('2019/01/10'), end: new Date('2019/01/15')}
    ];

    const loader = new TimespanLoader({loadedTimespans});
    t.is(loader.loadedTimespans.length, 2);

    // both sided overlaping
    loader.save({start: new Date('2019/01/03'), end: new Date('2019/01/12')});
    t.is(loader.loadedTimespans.length, 1);
    t.is(loader.loadedTimespans[0].start.getTime(), new Date('2019/01/01').getTime());
    t.is(loader.loadedTimespans[0].end.getTime(), new Date('2019/01/15').getTime());

    // left side overlaping
    loader.save({start: new Date('2019/01/11'), end: new Date('2019/01/20')});
    t.is(loader.loadedTimespans.length, 1);
    t.is(loader.loadedTimespans[0].start.getTime(), new Date('2019/01/01').getTime());
    t.is(loader.loadedTimespans[0].end.getTime(), new Date('2019/01/20').getTime());

    // right side overlaping
    loader.save({start: new Date('2018/12/25'), end: new Date('2019/01/01')});
    t.is(loader.loadedTimespans.length, 1);
    t.is(loader.loadedTimespans[0].start.getTime(), new Date('2018/12/25').getTime());
    t.is(loader.loadedTimespans[0].end.getTime(), new Date('2019/01/20').getTime());
});

test('that it merges adjacent timespans upon saving', t => {
    const loadedTimespans = [{start: new Date('2019/01/01'), end: new Date('2019/01/05')}];

    const loader = new TimespanLoader({loadedTimespans});
    t.is(loader.loadedTimespans.length, 1);

    loader.save({start: new Date('2019/01/06'), end: new Date('2019/01/10')});
    t.is(loader.loadedTimespans.length, 1);
    t.is(loader.loadedTimespans[0].start.getTime(), new Date('2019/01/01').getTime(), `${loader.loadedTimespans[0].start} is not equal ${new Date('2019/01/01')}`);
    t.is(loader.loadedTimespans[0].end.getTime(), new Date('2019/01/10').getTime(), `${loader.loadedTimespans[0].end} is not equal ${new Date('2019/01/10')}`);
});

test('that it merges left overlaping and also right adjacent', t => {
    const loadedTimespans = [
        {start: new Date('2019/01/01'), end: new Date('2019/01/05')},
        {start: new Date('2019/01/11'), end: new Date('2019/01/15')}
    ];

    const loader = new TimespanLoader({loadedTimespans});
    t.is(loader.loadedTimespans.length, 2);
    loader.save({start: new Date('2019/01/04'), end: new Date('2019/01/10')});
    t.is(loader.loadedTimespans.length, 1);
    t.is(loader.loadedTimespans[0].start.getTime(), new Date('2019/01/01').getTime(), `${loader.loadedTimespans[0].start} is not equal ${new Date('2019/01/01')}`);
    t.is(loader.loadedTimespans[0].end.getTime(), new Date('2019/01/15').getTime(), `${loader.loadedTimespans[0].end} is not equal ${new Date('2019/01/15')}`);
});

test('that it does not merge adjacent more than 1 day away from each other', t => {
    const loadedTimespans = [
        {start: new Date('2019/01/01'), end: new Date('2019/01/05')}
    ];

    const loader = new TimespanLoader({loadedTimespans});
    t.is(loader.loadedTimespans.length, 1);
    const start = new Date('2019/01/07');
    const end = new Date('2019/01/10');
    loader.save({start, end});
    t.is(loader.loadedTimespans.length, 2);
    t.true(loader.isLoaded({start, end}));
});

test('that it does not save if given timespan is already loaded', t => {
    const loadedTimespans = [{start: new Date('2019/01/01'), end: new Date('2019/01/30')}];
    const loader = new TimespanLoader({loadedTimespans});
    t.is(loader.loadedTimespans.length, 1);
    loader.save({start: new Date('2019/01/05'), end: new Date('2019/01/10')});
    t.is(loader.loadedTimespans.length, 1);
});

test('that it merges overlaps with timespan which has undefined end', t => {
    const loadedTimespans = [{start: new Date('2019/01/01'), end: new Date('2019/01/30')}];
    const loader = new TimespanLoader({loadedTimespans});
    t.is(loader.loadedTimespans.length, 1);
    loader.save({start: new Date('2019/01/10'), end: undefined});
    t.is(loader.loadedTimespans.length, 1);
    t.is(loader.loadedTimespans[0].start.getTime(), new Date('2019/01/01').getTime(), `${loader.loadedTimespans[0].start} is not equal ${new Date('2019/01/01')}`);
    t.is(loader.loadedTimespans[0].end, undefined);
});

test('that it merges when saving timespan which contains one of already loaded', t => {
    const loadedTimespans = [{start: new Date('2019/01/10'), end: new Date('2019/01/20')}];
    const loader = new TimespanLoader({loadedTimespans});
    t.is(loader.loadedTimespans.length, 1);
    loader.save({start: new Date('2019/01/01'), end: new Date('2019/01/30')});
    t.is(loader.loadedTimespans.length, 1);
    t.is(loader.loadedTimespans[0].start.getTime(), new Date('2019/01/01').getTime(), `${loader.loadedTimespans[0].start} is not equal ${new Date('2019/01/01')}`);
    t.is(loader.loadedTimespans[0].end.getTime(), new Date('2019/01/30').getTime(), `${loader.loadedTimespans[0].end} is not equal ${new Date('2019/01/30')}`);
});

test('that it throws an exception upon saving start date greater than end date', t => {
    const loader = new TimespanLoader();
    const error = t.throws(() => {
        loader.save({start: new Date('2019/01/02'), end: new Date('2019/01/01')});
    }, Error);
    t.is(error.message, 'Start date cannot be smaller than end date');
});
