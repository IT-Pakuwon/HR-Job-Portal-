const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const source = fs.readFileSync(path.join(__dirname, '../../public/assets/js/multidashboard/pending-training-feedback.js'), 'utf8');
const row = { id: 7, eid: 'abc123', training_name: 'Training Test' };
const questions = [
    { question_order: 1, question_type: 'Rating', question_text: 'Rate training', options: [1, 2, 3, 4, 5] },
    { question_order: 2, question_type: 'Single Choice', question_text: 'Recommend?', options: ['Yes', 'No'] },
    { question_order: 3, question_type: 'Text', question_text: 'Comment' },
];

async function openFeedback({ readOnly = false, confirm = false, fail = false } = {}) {
    const popups = [], requests = [], validations = [];
    const location = { pathname: '/dashboard', search: '' };
    const context = {
        location,
        history: { pushState(state, title, url) { location.pathname = url; } },
        document: {
            readyState: 'loading',
            addEventListener() {},
            getElementById() { return null; },
            createElement() { return {}; },
            head: { appendChild() {} },
            querySelector(selector) {
                if (selector.includes('csrf-token')) return { getAttribute: () => 'csrf' };
                if (confirm) return { value: selector.includes('_1') ? '5' : 'Yes' };
                return null;
            },
        },
        fetch: async (url, options) => {
            requests.push({ url, options });
            if (url.endsWith('pending-feedback')) return { ok: true, json: async () => ({ data: [row] }) };
            if (options.method === 'POST') return { ok: true, json: async () => ({ success: true, message: 'Saved' }) };
            return { ok: !fail, json: async () => ({ is_open: !readOnly, questions }) };
        },
        window: { Swal: {
            fire(options) {
                popups.push(options);
                if (confirm && options.preConfirm) return Promise.resolve({ isConfirmed: true, value: options.preConfirm() });
                return Promise.resolve({ isConfirmed: false });
            },
            showValidationMessage(message) { validations.push(message); },
        } },
    };
    vm.runInNewContext(source, context);
    context.window.openPendingFeedback(row.eid);
    await new Promise(resolve => setImmediate(resolve));
    return { popups, requests, validations, location };
}

test('dashboard opens editable questions without undefined-variable errors', async () => {
    const { popups, location } = await openFeedback();
    assert.equal(popups[0].confirmButtonText, 'Submit');
    for (const q of questions) assert.ok(popups[0].html.includes(q.question_text));
    assert.ok(!popups.some(p => p.title === 'Gagal memuat feedback'));
    assert.equal(location.pathname, '/dashboard');
});

test('unanswered questions fail validation', async () => {
    const { popups, validations } = await openFeedback();
    assert.equal(popups[0].preConfirm(), false);
    assert.equal(validations.length, 1);
});

test('answered feedback posts answers to the correct endpoint', async () => {
    const { requests } = await openFeedback({ confirm: true });
    const post = requests.find(r => r.options.method === 'POST');
    assert.equal(post.url, '/training-list/my/7/feedback');
    assert.equal(JSON.parse(post.options.body).answers.length, 3);
    assert.equal(post.options.headers['X-CSRF-TOKEN'], 'csrf');
});

test('closed feedback opens read-only without submitting', async () => {
    const { popups, requests } = await openFeedback({ readOnly: true });
    assert.equal(popups[0].confirmButtonText, 'Close');
    assert.ok(popups[0].html.includes('disabled'));
    assert.ok(!requests.some(r => r.options.method === 'POST'));
});

test('failed feedback request shows error and restores dashboard address', async () => {
    const { popups, location } = await openFeedback({ fail: true });
    assert.equal(popups[0].title, 'Gagal memuat feedback');
    assert.equal(location.pathname, '/dashboard');
});
