import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const script = readFileSync(new URL('../block/view.js', import.meta.url), 'utf8');
const expected = [true, true, false, true, true];
const classList = () => {
  const values = new Set();
  return { add: value => values.add(value), contains: value => values.has(value), toggle(value, on) { on ? values.add(value) : values.delete(value); } };
};

function fixture({ storage = new Map(), blocked = false, answers = expected, reducedMotion = false, confettiAvailable = true, confettiFails = false } = {}) {
  const handlers = {};
  const bursts = [];
  const options = answers.map(() => ({ dataset: {}, classList: classList(), status: { textContent: '' }, querySelector() { return this.status; } }));
  const inputs = answers.map((correct, i) => ({ dataset: { correct: String(correct) }, checked: false, disabled: false,
    type: answers.filter(Boolean).length > 1 ? 'checkbox' : 'radio', closest: () => options[i], focus() { this.focused = true; } }));
  const button = { disabled: false, hidden: false, getBoundingClientRect: () => ({ left: 20, top: 100, width: 200, height: 50 }), addEventListener(name, handler) { handlers[name] = handler; } };
  const error = { hidden: true };
  const heading = { textContent: '' }, message = { textContent: '' };
  const result = { hidden: true, dataset: {}, querySelector: name => name === 'h2' ? heading : message, focus() { this.focused = true; } };
  const explanation = { inert: true, hidden: true, classList: classList(), removeAttribute(name) { if (name === 'inert') this.inert = false; } };
  const form = { classList: classList(), querySelectorAll: () => inputs, querySelector: selector => selector === '.quiz-error' ? error : button, addEventListener(name, handler) { handlers[name] = handler; } };
  const root = { classList: classList(), dataset: { quizKey: '44' }, querySelector: selector => selector === '[data-quiz]' ? form : selector === '[data-quiz-result]' ? result : explanation };
  const localStorage = {
    getItem(key) { if (blocked) throw new Error('Storage blocked'); return storage.get(key) ?? null; },
    setItem(key, value) { if (blocked) throw new Error('Quota exceeded'); storage.set(key, value); },
    removeItem: key => storage.delete(key),
  };
  const window = { matchMedia: () => ({ matches: reducedMotion }), confetti: confettiAvailable ? config => { if (confettiFails) throw new Error('Canvas indisponible'); bursts.push(config); } : undefined };
  runInNewContext(script, { document: { querySelectorAll: () => [root] }, localStorage, window, getComputedStyle: () => ({ getPropertyValue: () => '#FFB5D8' }) });
  return { inputs, options, error, result, heading, message, explanation, button, storage, root, bursts,
    choose(selection) { inputs.forEach((input, i) => { input.checked = selection[i]; }); handlers.change(); },
    click() { handlers.click({ preventDefault() {} }); },
  };
}

Array.from({ length: 32 }, (_, mask) => {
  const selected = expected.map((_, i) => Boolean(mask & (1 << i)));
  const f = fixture();
  f.choose(selected); f.click();
  if (mask === 0) {
    assert.equal(f.bursts.length, 0);
    assert.equal(f.error.hidden, false); assert.equal(f.result.hidden, true); assert.equal(f.explanation.inert, true);
    return;
  }
  const errors = expected.filter((correct, i) => correct !== selected[i]).length;
  assert.equal(f.bursts.length, errors === 0 ? 1 : 0);
  f.click(); assert.equal(f.bursts.length, errors === 0 ? 1 : 0, 'Pas de deuxième gerbe au double clic');
  assert.equal(f.result.dataset.state, errors ? 'wrong' : 'correct');
  assert.equal(f.message.textContent, errors ? `Vous avez fait ${errors} erreur${errors > 1 ? 's' : ''}.` : 'Vous avez trouvé toutes les bonnes réponses.');
  assert.equal(f.explanation.inert, false); assert(f.button.hidden);
  assert.deepEqual(f.inputs.map(input => input.checked), expected);
  assert(f.inputs.every(input => input.disabled));
  assert.equal(JSON.parse(f.storage.get('kekidi:quiz:44')), errors === 0);
  const fresh = fixture({ storage: f.storage });
  assert(fresh.result.hidden); assert(fresh.inputs.every(input => !input.checked && !input.disabled));
  assert(!fresh.button.hidden); assert(fresh.explanation.inert);
  fresh.choose(expected); fresh.click();
  assert.equal(JSON.parse(f.storage.get('kekidi:quiz:44')), true);
  const retry = fixture({ storage: f.storage }); retry.choose([false, false, true, false, false]); retry.click();
  assert.equal(JSON.parse(f.storage.get('kekidi:quiz:44')), false);
});

const partial = fixture();
partial.choose([true, false, false, false, false]);
assert.equal(partial.storage.size, 0, 'Aucun stockage avant validation');
partial.click();
const previous = partial.storage.get('kekidi:quiz:44');
const empty = fixture({ storage: partial.storage }); empty.click();
assert.equal(empty.storage.get('kekidi:quiz:44'), previous, 'Une sélection vide ne remplace pas le résultat');
['invalid JSON', JSON.stringify({ version: 'v1', selected: expected, submitted: true }), 'true', 'false'].forEach(value => {
  const f = fixture({ storage: new Map([['kekidi:quiz:44', value]]) });
  assert(f.result.hidden); assert(f.inputs.every(input => !input.checked && !input.disabled));
  f.choose(expected); f.click(); assert.equal(f.storage.get('kekidi:quiz:44'), 'true');
});
const blocked = fixture({ blocked: true }); blocked.choose(expected); blocked.click();
assert.equal(blocked.result.dataset.state, 'correct');
const single = fixture({ answers: [false, true] }); single.choose([false, true]); single.click();
assert.equal(single.result.dataset.state, 'correct');
assert.equal(single.message.textContent, 'Vous avez trouvé la bonne réponse.');
assert.equal(single.bursts.length, 1);
assert.equal(single.bursts[0].position.x, 120);
assert.equal(single.bursts[0].position.y, 125);
[{ reducedMotion: true }, { confettiAvailable: false }, { confettiFails: true }].forEach(options => {
  const f = fixture(options); f.choose(expected); f.click();
  assert.equal(f.bursts.length, 0); assert.equal(f.result.dataset.state, 'correct');
  assert.equal(f.storage.get('kekidi:quiz:44'), 'true');
});
console.log('32 sélections ; résultat booléen uniquement ; formulaire neuf ; résultat remplaçable ; absence de stockage avant validation ; ancien format ignoré ; stockage bloqué ; texte au singulier : vérifiés.');
