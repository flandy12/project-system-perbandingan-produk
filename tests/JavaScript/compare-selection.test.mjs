import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
const source = readFileSync(new URL('../../resources/js/helper.js', import.meta.url), 'utf8');
const { default: createCompareApp } = await import(`data:text/javascript;base64,${Buffer.from(source).toString('base64')}`);
function environment(saved = []) {
    const store = new Map([['compare_items', JSON.stringify(saved)]]);
    const listeners = {};
    globalThis.localStorage = { getItem: key => store.get(key) ?? null, setItem: (key, value) => store.set(key, value), removeItem: key => store.delete(key) };
    globalThis.window = { location: { pathname: '/gallery', href: '' }, addEventListener: (type, fn) => { listeners[type] = fn; } };
    const app = createCompareApp();
    app.initScrollSpy = () => {};
    app.init();
    return { app, listeners, store };
}
test('recovers from old completed pairs including numeric IDs', () => {
    const { app } = environment([4, 6]);
    assert.deepEqual(app.items, []);
    app.toggle(10); app.toggle(11);
    assert.equal(window.location.href, '/compare?ids=10,11');
    assert.equal(localStorage.getItem('compare_items'), null);
});
test('normalizes one pending selection and supports cancelling it', () => {
    const { app } = environment([4, '4', null, 'bad']);
    assert.deepEqual(app.items, ['4']);
    assert.equal(app.isSelected(4), true);
    app.clearSelection();
    assert.deepEqual(app.items, []);
});
test('browser Back restores a fresh comparison rather than a completed pair', () => {
    const { app, listeners } = environment();
    app.toggle(10); app.toggle(11);
    listeners.pageshow();
    app.toggle(12); app.toggle(14);
    assert.equal(window.location.href, '/compare?ids=12,14');
});
