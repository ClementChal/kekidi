(() => {
  const {createElement: el, useState, useEffect} = wp.element;
  const {InnerBlocks, useBlockProps, MediaUpload, MediaUploadCheck} = wp.blockEditor;
  const {Button, CheckboxControl, Notice, SelectControl, TextControl, TextareaControl, Spinner} = wp.components;
  const expected = (groups, direction) => groups.filter(group => !['NI', 'PO840056'].includes(group.id) && group[direction] * 2 > group.pour + group.contre + group.abstention).map(group => group.id);
  wp.blocks.registerBlockType('kekidi/quiz-scrutin', {
    apiVersion: 3, title: 'Réponse scrutin', category: 'kekidi', icon: 'chart-bar',
    attributes: {snapshotId: {type: 'string', default: ''}, direction: {type: 'string', default: 'pour'}, measure: {type: 'string', default: ''}, chartTitle: {type: 'string', default: ''}, excludedGroupIds: {type: 'array', items: {type: 'string'}, default: []}, logos: {type: 'object', default: {}}, editableExplanation: {type: 'boolean', default: false}},
    supports: {html: false, multiple: false, reusable: false, customClassName: false},
    edit({attributes, setAttributes, clientId}) {
      const postId = wp.data.useSelect(select => select('core/editor').getCurrentPostId(), []);
      const [chamber, setChamber] = useState('assemblee');
      const [period, setPeriod] = useState('17');
      const [number, setNumber] = useState('');
      const [snapshot, setSnapshot] = useState(null);
      const [busy, setBusy] = useState(false);
      const [loading, setLoading] = useState(false);
      const [error, setError] = useState('');
      const [mediaUrls, setMediaUrls] = useState({});
      const savedMedia = wp.data.useSelect(select => Object.fromEntries(Object.entries(attributes.logos).filter(([, id]) => id > 0).map(([id, attachment]) => [id, select('core').getMedia(attachment)?.source_url])), [attributes.logos]);
      const [reviewMeasure, setReviewMeasure] = useState(false);
      useEffect(() => {
        let active = true;
        setSnapshot(null);
        if (!attributes.snapshotId || !postId) return;
        setLoading(true);
        wp.apiFetch({path: `/kekidi/v1/scrutins/${postId}/${attributes.snapshotId}?direction=${attributes.direction}`}).then(data => {
          if (!active) return;
          if (!attributes.editableExplanation) {
            const defaults = wp.blocks.parse(data.defaultExplanation);
            const existing = wp.data.select('core/block-editor').getBlocks(clientId);
            wp.data.dispatch('core/block-editor').replaceInnerBlocks(clientId, [...defaults.slice(0, 2), ...existing, ...defaults.slice(2)], false);
            setAttributes({editableExplanation: true});
          }
          setSnapshot(data); setChamber(data.chamber); setPeriod(data.period); setNumber(String(data.number));
        }).catch(problem => { if (active) setError(problem.message || 'Impossible de lire les données. Rechargez le scrutin.'); }).finally(() => { if (active) setLoading(false); });
        return () => { active = false; };
      }, [postId, attributes.snapshotId, attributes.direction]);
      const importScrutin = () => {
        if (busy) return;
        setBusy(true); setError('');
        wp.apiFetch({path: '/kekidi/v1/scrutins/import', method: 'POST', data: {postId, chamber, period, number: Number(number)}}).then(data => {
          const automatic = !attributes.measure.trim() || attributes.measure === snapshot?.title;
          setReviewMeasure(!automatic && data.snapshotId !== attributes.snapshotId);
          setAttributes({snapshotId: data.snapshotId, measure: automatic ? data.snapshot.title : attributes.measure});
        }).catch(problem => setError(problem.message || 'Chargement impossible. Les données précédentes sont conservées.')).finally(() => setBusy(false));
      };
      const groups = snapshot?.groups.filter(group => !['NI', 'PO840056'].includes(group.id)) || [];
      const {excludedGroupIds} = attributes;
      const correct = expected(groups.filter(group => !excludedGroupIds.includes(group.id)), attributes.direction);
      const setLogo = (group, id, url = '') => {
        setAttributes({logos: {...attributes.logos, [group.id]: id}});
        setMediaUrls({...mediaUrls, [group.id]: url});
      };
      return el('div', useBlockProps({className: 'kekidi-scrutins-editor'}),
        el('h2', {}, 'Réponse scrutin'),
        el('p', {}, 'Chargez les décomptes officiels, puis rédigez la question dans le titre de l’article. Les chiffres restent conservés dans WordPress.'),
        el('div', {className: 'scrutin-import-fields'},
          el(SelectControl, {label: 'Chambre', value: chamber, disabled: busy, options: [{label: 'Assemblée nationale', value: 'assemblee'}, {label: 'Sénat', value: 'senat'}], onChange: value => {setChamber(value);setPeriod(value === 'assemblee' ? '17' : '2025');}}),
          el(TextControl, {label: chamber === 'assemblee' ? 'Législature' : 'Année de début de session', value: period, type: 'number', min: chamber === 'assemblee' ? 17 : 2000, max: chamber === 'assemblee' ? 17 : 2099, disabled: busy || chamber === 'assemblee', onChange: setPeriod, help: chamber === 'assemblee' ? 'XVIIe législature.' : '2025 correspond à la session 2025–2026.'}),
          el(TextControl, {label: 'Numéro du scrutin', value: number, type: 'number', min: 1, max: 999999, disabled: busy, onChange: setNumber}),
        ),
        el(Button, {variant: 'secondary', disabled: busy || !postId || !/^[1-9]\d{0,5}$/.test(number), isBusy: busy, onClick: importScrutin}, busy ? 'Vérification des sources…' : 'Charger le scrutin'),
        !postId && el('p', {}, 'Enregistrez d’abord un brouillon pour charger ses données.'),
        error && el(Notice, {status: 'error', isDismissible: false}, error),
        loading && el(Spinner),
        snapshot && el('div', {className: 'scrutin-import-summary'},
          el('h3', {}, `Scrutin n° ${snapshot.number} · ${snapshot.date}`),
          el('p', {}, snapshot.title),
          el('p', {}, el('a', {href: snapshot.sourceUrl, target: '_blank', rel: 'noopener noreferrer'}, 'Vérifier sur la page officielle (nouvel onglet)')),
          el('p', {}, `Données enregistrées le ${new Date(snapshot.retrievedAt).toLocaleString('fr-FR')}. Elles seront utilisées par l’article après son enregistrement.`),
          ...snapshot.warnings.map((warning, index) => el(Notice, {key: index, status: 'warning', isDismissible: false}, warning)),
        ),
        el(SelectControl, {label: 'Question posée aux visiteurs', value: attributes.direction, options: [{label: 'Qui a voté majoritairement pour ?', value: 'pour'}, {label: 'Qui a voté majoritairement contre ?', value: 'contre'}], onChange: direction => setAttributes({direction})}),
        el(TextareaControl, {label: 'Présentation de la mesure', value: attributes.measure, help: 'Expliquez ce qui était soumis au vote. Le libellé officiel restera accessible dans les sources.', onChange: measure => setAttributes({measure})}),
        el(TextControl, {label: 'Titre du graphique', value: attributes.chartTitle, help: 'Si le champ est vide, le graphique s’intitule « Et dans l’hémicycle ? ».', onChange: chartTitle => setAttributes({chartTitle})}),
        reviewMeasure && el(Notice, {status: 'warning', isDismissible: false}, 'Le scrutin a changé. Vérifiez le titre de l’article et votre présentation personnalisée.'),
        !attributes.snapshotId && el(Notice, {status: 'warning', isDismissible: false}, 'Chargez un scrutin vérifié avant publication.'),
        snapshot && el('div', {className: 'scrutin-data-preview'},
          el('h3', {}, `Corrigé calculé : ${correct.length} groupe${correct.length > 1 ? 's' : ''}`),
          el('p', {}, 'Décochez les groupes à retirer des choix du quiz. Tous les groupes restent visibles dans le diagramme.'),
          el('p', {}, 'Majorité strictement supérieure à 50 % des pour, contre et abstentions. Une sélection vide reste une réponse possible.'),
          ...groups.map(group => {
            const included = !excludedGroupIds.includes(group.id);
            const hasCustom = Object.hasOwn(attributes.logos, group.id);
            const logoId = attributes.logos[group.id];
            const url = hasCustom ? (mediaUrls[group.id] || savedMedia[group.id]) : window.KekidiScrutins?.logoUrls[group.id];
            return el('div', {key: group.id, className: 'scrutin-group-preview'},
              el('div', {}, url ? el('img', {src: url, alt: '', width: 40, height: 40}) : el('span', {}, group.short), el('strong', {}, group.name)),
              el(CheckboxControl, {
                label: `Proposer dans le quiz : ${group.name}`,
                checked: included,
                onChange: checked => setAttributes({excludedGroupIds: checked
                  ? excludedGroupIds.filter(id => id !== group.id)
                  : [...excludedGroupIds, group.id]}),
              }),
              el('p', {}, `${group.pour} pour · ${group.contre} contre · ${group.abstention} abstentions. ${included ? (correct.includes(group.id) ? 'À sélectionner.' : 'À ne pas sélectionner.') : ''}`),
              el(MediaUploadCheck, {}, el(MediaUpload, {allowedTypes: ['image'], value: logoId || 0, onSelect: media => setLogo(group, media.id, media.sizes?.thumbnail?.url || media.url), render: ({open}) => el(Button, {variant: 'secondary', onClick: open, 'aria-label': `Choisir le logo de ${group.name}`}, 'Choisir un logo')})),
              el(Button, {variant: 'tertiary', onClick: () => setLogo(group, 0)}, 'Utiliser le sigle'),
              hasCustom && el(Button, {variant: 'tertiary', onClick: () => setAttributes({logos: Object.fromEntries(Object.entries(attributes.logos).filter(([id]) => id !== group.id))})}, 'Logo par défaut'),
            );
          }),
        ),
        el(InnerBlocks, {template: [['core/paragraph', {placeholder: 'Apportez le contexte et les explications utiles.'}]], allowedBlocks: ['core/paragraph', 'core/heading', 'core/list', 'core/image', 'core/group', 'core/quote', 'core/details']}),
        el('p', {className: 'scrutin-editor-note'}, 'Le titre et les paragraphes ci-dessus sont modifiables ou supprimables. Le texte initial est proposé une seule fois ; un nouvel import ne remplace pas vos modifications. Vérifiez son contenu si vous changez de scrutin ou de sens du quiz.'),
      );
    },
    save() { return el(InnerBlocks.Content); },
  });
})();
