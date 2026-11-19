// Lecture seule, dans la console de l’éditeur Gutenberg.
(async () => {
  const patterns = await wp.apiFetch({path:'/wp/v2/block-patterns/patterns'});
  const pattern = patterns.find(item => item.name === 'kekidi/contact');
  console.log(JSON.stringify({
    compositionRecue: Boolean(pattern),
    blocForminatorCharge: Boolean(wp.blocks.getBlockType('forminator/forms')),
    visible: wp.data.select('core/block-editor').__experimentalGetAllowedPatterns().some(item => item.name === 'kekidi/contact'),
    contenu: pattern?.content,
  }, null, 2));
})();
