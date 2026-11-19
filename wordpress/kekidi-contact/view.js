(($) => {
  $(() => document.querySelectorAll('.kekidi-contact-container').forEach(root => {
    const form = root.querySelector('form.kekidi-contact');
    const button = form?.querySelector('button[type="submit"]');
    if (!button) return;
    const intro = root.closest('.contact-page')?.querySelector('.contact-intro');
    const success = root.querySelector('.contact-success');
    const originalButton = button.innerHTML;
    const fields = [...form.querySelectorAll('select, input[type="email"], textarea')];
    const feedback = document.createElement('div');
    feedback.className = 'contact-feedback';
    feedback.hidden = true;
    feedback.tabIndex = -1;
    feedback.setAttribute('role', 'alert');
    feedback.innerHTML = '<strong>Votre message n’a pas été envoyé.</strong><p>Un problème est survenu. Réessayez dans quelques instants. Votre texte est conservé dans le formulaire.</p>';
    form.append(feedback);
    form.noValidate = true;
    const errors = new Map(fields.map(field => {
      const error = document.createElement('p');
      error.className = 'contact-error';
      error.id = `${field.id}-kekidi-error`;
      error.hidden = true;
      field.closest('.contact-field').append(error);
      const describedBy = field.getAttribute('aria-describedby');
      field.setAttribute('aria-describedby', [describedBy, error.id].filter(Boolean).join(' '));
      const setError = message => {
        error.textContent = message;
        error.hidden = !message;
        field.setAttribute('aria-invalid', String(Boolean(message)));
      };
      field.addEventListener('input', () => setError(''));
      return [field, setError];
    }));
    let pending = false;
    let failed = false;
    form.addEventListener('submit', event => {
      if (pending) { event.preventDefault(); event.stopImmediatePropagation(); return; }
      feedback.hidden = true;
      const invalid = fields.filter(field => {
        let message = '';
        if (field.type === 'email') {
          message = !field.value.trim() ? 'Indiquez votre adresse mail.' : !field.validity.valid ? 'Saisissez une adresse mail valide, comme nom@exemple.fr.' : '';
        } else if (!field.value.trim()) {
          message = field.tagName === 'TEXTAREA' ? 'Écrivez votre question.' : 'Choisissez un objet.';
        }
        errors.get(field)(message);
        return Boolean(message);
      });
      if (invalid.length) {
        event.preventDefault(); event.stopImmediatePropagation(); invalid[0].focus();
      }
    }, true);
    // Forminator reste responsable de la validation serveur, du stockage et du résultat réel.
    $(form).on('before:forminator:form:submit', () => {
      pending = true;
      form.setAttribute('aria-busy', 'true');
      button.innerHTML = 'Envoi en cours…';
    }).on('forminator:form:submit:failed', () => {
      failed = true;
      pending = false;
      form.setAttribute('aria-busy', 'false');
      button.textContent = 'Réessayer l’envoi';
      feedback.hidden = false;
      feedback.focus();
    }).on('forminator:form:submit:success', () => {
      pending = false;
      form.setAttribute('aria-busy', 'false');
      form.hidden = true;
      if (intro) intro.hidden = true;
      success.hidden = false;
      success.focus();
    }).on('forminator:form:submit:complete', () => {
      pending = false;
      form.setAttribute('aria-busy', 'false');
      button.innerHTML = failed ? 'Réessayer l’envoi' : originalButton;
    });
  }));
})(jQuery);
