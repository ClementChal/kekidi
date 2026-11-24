(() => {
  document.querySelectorAll('[data-kekidi-vote]').forEach(root => {
    const form = root.querySelector('[data-vote-form]');
    const inputs = [...form.querySelectorAll('input[type=checkbox]')];
    const counter = root.querySelector('.vote-selection-count');
    const result = root.querySelector('.vote-result');
    const chart = root.querySelector('.vote-chart');
    let submitted = false;
    const updateSelection = () => {
      if (!counter) return;
      const count = inputs.filter(input => input.checked).length;
      counter.textContent = count ? `${count} groupe${count > 1 ? 's' : ''} sélectionné${count > 1 ? 's' : ''}` : 'Aucun groupe sélectionné';
      inputs.forEach(input => input.closest('label').classList.toggle('is-selected', input.checked));
    };
    form.addEventListener('change', updateSelection);
    form.addEventListener('submit', event => {
      event.preventDefault();
      if (submitted) return;
      submitted = true;
      if (!inputs.length) {
        form.hidden = true;
        chart.hidden = false;
        chart.querySelector('h2').focus();
        return;
      }
      const errors = inputs.filter(input => input.checked !== (input.dataset.correct === 'true')).length;
      result.dataset.state = errors ? 'wrong' : 'correct';
      result.querySelector('h2').textContent = errors ? 'Pas tout à fait !' : 'Bien joué !';
      result.querySelector('p').textContent = errors ? `${errors} erreur${errors > 1 ? 's' : ''}. Voici comment les groupes ont réellement voté.` : 'Vous avez tout juste !';
      root.querySelectorAll('[data-chart-party]').forEach(row => {
        const input = inputs.find(item => item.value === row.dataset.chartParty);
        if (!input) return;
        const expected = input.dataset.correct === 'true';
        row.querySelector('[data-comparison]').textContent = input.checked && !expected ? 'Choix incorrect' : !input.checked && expected ? 'Groupe oublié' : '';
      });
      inputs.forEach(input => { input.disabled = true; });
      root.classList.add('is-validated');
      form.hidden = true;
      result.hidden = false;
      chart.hidden = false;
      try { localStorage.setItem(`kekidi:quiz:${root.dataset.quizKey}`, JSON.stringify(errors === 0)); } catch { /* Le jeu reste utilisable sans stockage. */ }
      result.focus();
      if (errors === 0 && typeof window.confetti === 'function' && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        try {
          const origin = result.getBoundingClientRect();
          const palette = getComputedStyle(root);
          window.confetti({
            position: { x: origin.left + origin.width / 2, y: origin.top + origin.height / 2 },
            count: 180, velocity: 260, size: 1.2, fade: true,
            color: ['pink', 'green', 'yellow', 'blue'].map(name => palette.getPropertyValue(`--wp--preset--color--${name}`).trim()).filter(Boolean),
          });
        } catch { /* L’effet décoratif ne doit jamais bloquer le résultat. */ }
      }
    });
    root.addEventListener('error', event => {
      const image = event.target;
      if (!(image instanceof HTMLImageElement) || !image.closest('.vote-emblem')) return;
      image.hidden = true;
      image.parentElement.querySelector('.vote-logo-fallback').hidden = false;
    }, true);
    root.querySelectorAll('.vote-emblem img').forEach(image => {
      if (image.complete && !image.naturalWidth) {
        image.hidden = true;
        image.parentElement.querySelector('.vote-logo-fallback').hidden = false;
      }
    });
    updateSelection();
  });
})();
