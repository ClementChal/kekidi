(() => {
  document.querySelectorAll('[data-kekidi-quiz]').forEach(root => {
    const form = root.querySelector('[data-quiz]');
    const inputs = [...form.querySelectorAll('[data-choice]')];
    const button = form.querySelector('[data-validate-quiz]');
    const error = form.querySelector('.quiz-error');
    const result = root.querySelector('[data-quiz-result]');
    const explanation = root.querySelector('[data-quiz-explanation]');
    const expected = inputs.map(input => input.dataset.correct === 'true');
    const key = `kekidi:quiz:${root.dataset.quizKey}`;
    let submitted = false;

    inputs.forEach(input => { input.checked = false; input.disabled = false; });
    button.disabled = false;
    button.hidden = false;
    const selection = () => inputs.map(input => input.checked);
    const updateSelected = () => inputs.forEach(input => input.closest('label').classList.toggle('is-selected', input.checked));

    const validate = () => {
      if (submitted) return;
      const selected = selection();
      if (!selected.some(Boolean)) {
        error.hidden = false;
        inputs[0].focus();
        return;
      }
      submitted = true;
      const origin = button.getBoundingClientRect();
      const errors = expected.reduce((count, correct, index) => count + Number(correct !== selected[index]), 0);
      inputs.forEach((input, index) => {
        const option = input.closest('label');
        option.dataset.state = expected[index] ? (selected[index] ? 'correct' : 'missed') : (selected[index] ? 'wrong' : 'neutral');
        option.querySelector('.quiz-option-status').textContent = expected[index]
          ? (selected[index] ? '✓ Bonne réponse' : '+ Bonne réponse oubliée')
          : (selected[index] ? '× Réponse incorrecte' : '✓ Non retenue');
        input.checked = expected[index];
        input.disabled = true;
      });
      result.dataset.state = errors === 0 ? 'correct' : 'wrong';
      result.querySelector('h2').textContent = errors === 0 ? 'Bien joué !' : 'Pas tout à fait !';
      result.querySelector('p').textContent = errors === 0 ? (expected.filter(Boolean).length === 1 ? 'Vous avez trouvé la bonne réponse.' : 'Vous avez trouvé toutes les bonnes réponses.') : `Vous avez fait ${errors} erreur${errors > 1 ? 's' : ''}.`;
      result.hidden = false;
      error.hidden = true;
      button.disabled = true;
      button.hidden = true;
      form.classList.add('is-validated');
      explanation.removeAttribute('inert');
      explanation.removeAttribute('aria-hidden');
      explanation.classList.add('is-revealed');
      try { localStorage.setItem(key, JSON.stringify(errors === 0)); } catch { /* Le quiz reste utilisable sans stockage. */ }
      result.focus({ preventScroll: true });
      if (errors === 0 && typeof window.confetti === 'function' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        try {
          const palette = getComputedStyle(root);
          window.confetti({
            position: { x: origin.left + origin.width / 2, y: origin.top + origin.height / 2 },
            count: 180, velocity: 260, size: 1.2, fade: true,
            color: ['pink', 'green', 'yellow', 'blue'].map(name => palette.getPropertyValue(`--wp--preset--color--${name}`).trim()).filter(Boolean),
          });
        } catch { /* L’effet décoratif ne doit jamais bloquer le résultat. */ }
      }
    };

    form.addEventListener('change', () => {
      if (submitted) return;
      error.hidden = true;
      updateSelected();
    });
    form.addEventListener('submit', event => { event.preventDefault(); validate(); });
    button.addEventListener('click', event => { event.preventDefault(); validate(); });

  });
})();
