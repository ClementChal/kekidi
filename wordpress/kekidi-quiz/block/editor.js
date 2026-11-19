(() => {
  const { createElement: el } = wp.element;
  const { InnerBlocks, useBlockProps } = wp.blockEditor;
  const { Button, CheckboxControl, Notice, TextControl } = wp.components;
  const defaults = [{ label: '', correct: true }, { label: '', correct: false }];
  const explanation = [['core/group', {
    tagName: 'section', className: 'answer-detail', layout: { type: 'default' }, lock: { move: true, remove: true },
  }, [
    ['core/heading', { level: 2, content: 'On vous explique.', lock: { move: true, remove: true } }],
    ['core/paragraph', { placeholder: 'Développer ici les explications.' }],
  ]]];

  wp.blocks.registerBlockType('kekidi/quiz', {
    apiVersion: 3,
    title: 'Réponse quiz', category: 'kekidi', icon: 'editor-help',
    attributes: { choices: { type: 'array', default: defaults } },
    supports: { html: false, multiple: false, reusable: false, customClassName: false },
    edit({ attributes, setAttributes, clientId }) {
      const choices = (Array.isArray(attributes.choices) ? attributes.choices : []).map(choice => ({
        label: typeof choice?.label === 'string' ? choice.label : '', correct: choice?.correct === true,
      }));
      const correctCount = choices.filter(choice => choice.correct).length;
      const valid = choices.length >= 1 && choices.length <= 5 && correctCount > 0 && choices.every(choice => choice.label.trim());
      const hasSources = wp.data.useSelect(select => select('core/block-editor').getBlocks(clientId).some(block => block.attributes.className === 'sources'), [clientId]);
      const update = (index, value) => setAttributes({ choices: choices.map((choice, i) => i === index ? { ...choice, ...value } : choice) });
      const move = (index, direction) => {
        const next = choices.slice();
        [next[index], next[index + direction]] = [next[index + direction], next[index]];
        setAttributes({ choices: next });
      };
      const addSources = () => {
        const source = wp.blocks.createBlock('core/group', { tagName: 'section', className: 'sources', layout: { type: 'default' } }, [
          wp.blocks.createBlock('core/heading', { level: 2, content: 'Pour aller plus loin' }),
          wp.blocks.createBlock('core/paragraph', { placeholder: 'Nom de la source et lien.' }),
        ]);
        wp.data.dispatch('core/block-editor').insertBlocks(source, undefined, clientId);
      };
      return el('div', useBlockProps({ className: 'kekidi-quiz-editor' }),
        el('h2', {}, 'Réponse quiz'),
        el('p', {}, 'Renseignez les propositions et cochez les bonnes réponses. Testez le quiz avec Prévisualiser.'),
        !valid && el(Notice, { status: 'warning', isDismissible: false }, 'Avant publication : 1 à 5 propositions renseignées et au moins une bonne réponse.'),
        el('p', { className: 'kekidi-quiz-editor-instructions' }, correctCount > 1 ? 'Plusieurs réponses possibles.' : 'Une seule réponse possible.'),
        ...choices.map((choice, index) => el('div', { className: 'kekidi-quiz-editor-choice', key: index },
          el(TextControl, { label: `Proposition ${String.fromCharCode(65 + index)}`, value: choice.label, onChange: label => update(index, { label }) }),
          el(CheckboxControl, { label: 'Bonne réponse', checked: choice.correct, onChange: correct => update(index, { correct }) }),
          el('div', { className: 'kekidi-quiz-editor-actions' },
            el(Button, { variant: 'secondary', disabled: index === 0, onClick: () => move(index, -1) }, 'Monter'),
            el(Button, { variant: 'secondary', disabled: index === choices.length - 1, onClick: () => move(index, 1) }, 'Descendre'),
            el(Button, { isDestructive: true, disabled: choices.length <= 1, onClick: () => setAttributes({ choices: choices.filter((_, i) => i !== index) }) }, 'Supprimer'),
          ),
        )),
        el(Button, { variant: 'secondary', disabled: choices.length >= 5, onClick: () => setAttributes({ choices: [...choices, { label: '', correct: false }] }) }, 'Ajouter une proposition'),
        el('h3', {}, 'Explication et sources'),
        el(InnerBlocks, { template: explanation, allowedBlocks: ['core/group'], renderAppender: false }),
        !hasSources && el(Button, { variant: 'secondary', onClick: addSources }, 'Ajouter des sources'),
        el('p', { className: 'kekidi-quiz-editor-note' }, 'Seul le dernier résultat juste ou faux est conservé dans le navigateur. À chaque visite, le formulaire repart à zéro.'),
      );
    },
    save() { return el(InnerBlocks.Content); },
  });
})();
