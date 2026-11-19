// Chromium de test lancé avec --remote-debugging-port=9223 ; Studio sur localhost:8881.
import assert from 'node:assert/strict';
const pages = await (await fetch('http://127.0.0.1:9223/json')).json();
const page = pages.find(item => item.type === 'page');
const socket = new WebSocket(page.webSocketDebuggerUrl);
const pending = new Map();
let id = 0;
socket.onmessage = event => {
  const message = JSON.parse(event.data), task = pending.get(message.id);
  if (task) { pending.delete(message.id); message.error ? task.reject(message.error) : task.resolve(message.result); }
};
await new Promise(resolve => { socket.onopen = resolve; });
const send = (method, params = {}) => new Promise((resolve, reject) => {
  pending.set(++id, { resolve, reject }); socket.send(JSON.stringify({ id, method, params }));
});
const evaluate = async expression => {
  const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
  assert(!result.exceptionDetails, JSON.stringify(result.exceptionDetails)); return result.result.value;
};
const wait = milliseconds => new Promise(resolve => setTimeout(resolve, milliseconds));
const navigate=async (slug)=>{
 await send('Page.navigate',{url:'http://localhost:8881/reponses/'+slug+'/'});await wait(300);
 for(let attempt=0;attempt<40;attempt++){
  if(await evaluate(`document.readyState==='complete'&&location.pathname==='/reponses/${slug}/'`))return;
  await wait(250);
 }
 throw new Error('La page locale n’a pas terminé son chargement : '+slug);
};
const submit=()=>evaluate(`document.querySelector('.vote-form').requestSubmit()`);
try {
 await send('Page.enable');await send('Network.enable');await send('Network.setCacheDisabled',{cacheDisabled:true});await send('Network.clearBrowserCookies');
 for(const width of [1440,390,320]){
  await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<600});
  await navigate('scrutin-7987-pour');
  assert.equal(await evaluate('document.querySelectorAll(".vote-option").length'),11);
  assert.equal(await evaluate('document.querySelectorAll(".vote-option input[data-correct=true]").length'),7);
  assert.equal(await evaluate('document.querySelector(".vote-form button").disabled'),false);
  assert.equal(await evaluate('document.documentElement.scrollWidth>innerWidth'),false);
  assert.equal(await evaluate('getComputedStyle(document.querySelector(".vote-context")).fontSize'),'14px');
  await evaluate(`window.scrutinBursts=[];window.confetti=config=>window.scrutinBursts.push(config)`);
  await submit();
  assert.equal(await evaluate('window.scrutinBursts.length'),0);
  assert.equal(await evaluate('document.querySelector(".vote-result").hidden'),false);
  assert.equal(await evaluate('document.querySelector(".vote-result").dataset.state'),'wrong');
  assert.equal(await evaluate('document.querySelector(".vote-form").hidden'),true);
  assert.equal(await evaluate('document.querySelector(".vote-chart").hidden'),false);
  assert.equal(await evaluate('[...document.querySelectorAll("[data-comparison]")].filter(e=>e.textContent==="Groupe oublié").length'),7);
  assert.equal(await evaluate('localStorage.getItem("kekidi:quiz:1037")'),'false');
  assert.equal(await evaluate('document.activeElement.classList.contains("vote-result")'),true);
  await navigate('scrutin-7987-pour');
  assert.equal(await evaluate('document.querySelector(".vote-result").hidden'),true);
  assert.equal(await evaluate('document.querySelectorAll(".vote-option input:checked").length'),0);
  assert.equal(await evaluate('typeof window.confetti'),'function','Bibliothèque locale chargée');
  await evaluate(`window.scrutinBursts=[];const originalConfetti=window.confetti;window.confetti=config=>{window.scrutinBursts.push(config);originalConfetti(config);}`);
  await evaluate(`document.querySelectorAll('.vote-option input[data-correct=true]').forEach(input=>input.click())`);await submit();
  assert.equal(await evaluate('window.scrutinBursts.length'),1);
  assert.equal(await evaluate('window.scrutinBursts[0].count'),180);
  assert.equal(await evaluate('document.querySelectorAll("canvas").length'),1,'Le vrai effet crée son canvas');
  assert.equal(await evaluate('window.scrutinBursts[0].position.y>=0&&window.scrutinBursts[0].position.y<=innerHeight'),true,'Gerbe visible après affichage du résultat');
  assert.equal(await evaluate('window.scrutinBursts[0].color.length'),4);
  await submit();assert.equal(await evaluate('window.scrutinBursts.length'),1,'Une seule gerbe par validation');
  assert.equal(await evaluate('document.querySelector(".vote-result").dataset.state'),'correct');
  assert.equal(await evaluate('localStorage.getItem("kekidi:quiz:1037")'),'true');
  assert.equal(await evaluate('document.querySelectorAll(".vote-option input:disabled").length'),11);
  assert.equal(await evaluate('document.documentElement.scrollWidth>innerWidth'),false);
  assert.equal(await evaluate('[...document.querySelectorAll(".vote-explanation a")].every(a=>a.target==="_blank"&&a.rel.includes("noopener")&&a.rel.includes("noreferrer"))'),true);
  console.log(`${width}px : sélection vide, résultat juste/faux, rechargement vierge, liens et stockage vérifiés.`);
 }
 await send('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});
 await navigate('scrutin-7987-pour');
 await evaluate(`window.scrutinBursts=[];window.confetti=config=>window.scrutinBursts.push(config);document.querySelectorAll('.vote-option input[data-correct=true]').forEach(input=>input.click())`);await submit();
 assert.equal(await evaluate('window.scrutinBursts.length'),0,'Pas de confettis si animations réduites');
 assert.equal(await evaluate('document.querySelector(".vote-result").dataset.state'),'correct');
 await send('Emulation.setEmulatedMedia',{features:[]});
 await navigate('scrutin-7987-pour');
 await evaluate(`window.confetti=()=>{throw new Error('Canvas indisponible')};document.querySelectorAll('.vote-option input[data-correct=true]').forEach(input=>input.click())`);await submit();
 assert.equal(await evaluate('document.querySelector(".vote-result").dataset.state'),'correct','Un échec décoratif ne bloque pas le résultat');
 await navigate('scrutin-7987-contre');
 assert.equal(await evaluate('document.querySelectorAll(".vote-option input[data-correct=true]").length'),4);
 await evaluate(`document.querySelectorAll('.vote-option input[data-correct=true]').forEach(input=>input.click())`);await submit();
 assert.equal(await evaluate('document.querySelector(".vote-result").dataset.state'),'correct');
 await navigate('scrutin-senat-2025-33');
 assert.equal(await evaluate('document.querySelectorAll(".vote-option").length'),8);
 assert.equal(await evaluate('document.querySelector(".vote-context").textContent.includes("Sénat")'),true);
 assert.equal(await evaluate('document.querySelectorAll(".vote-option input[value=NI]").length'),0);
 await evaluate(`document.querySelectorAll('.vote-option input[data-correct=true]').forEach(input=>input.click())`);await submit();
 assert.equal(await evaluate('document.querySelector(".vote-result").dataset.state'),'correct');
 await navigate('scrutin-7987-pour');
 // Cas limite volontaire : aucun groupe n’atteint le seuil ; le vide est une réponse juste.
 await evaluate(`document.querySelectorAll('.vote-option input').forEach(input=>input.dataset.correct='false')`);await submit();
 assert.equal(await evaluate('document.querySelector(".vote-result").dataset.state'),'correct');
 await navigate('scrutin-7987-pour');
 await evaluate(`document.querySelector('.vote-emblem img').src='/image-logo-inexistante.png'`);await wait(200);
 assert.equal(await evaluate('document.querySelector(".vote-emblem img").hidden'),true);
 assert.equal(await evaluate('document.querySelector(".vote-emblem .vote-logo-fallback").hidden'),false);
 await send('Page.navigate',{url:'http://localhost:8881/studio-auto-login?redirect_to=%2Fwp-admin%2Fpost.php%3Fpost%3D1037%26action%3Dedit'});await wait(2600);
 const editor=await evaluate(`(async()=>{
  const block=wp.data.select('core/block-editor').getBlocks().find(b=>b.name==='kekidi/quiz-scrutin');
  const heading=block.innerBlocks.find(b=>b.name==='core/heading');
  if(!block.attributes.editableExplanation||!heading)throw new Error('Explication Gutenberg absente');
  wp.data.dispatch('core/block-editor').updateBlockAttributes(heading.clientId,{content:'Mon titre personnalisé'});
  const editableBefore=wp.blocks.serialize(wp.data.select('core/block-editor').getBlock(block.clientId).innerBlocks);
  const frame=document.querySelector('iframe[name="editor-canvas"]');const doc=frame?.contentDocument||document;
  const before=await wp.apiFetch({path:'/wp/v2/posts/1037?context=edit'});
  const response=await wp.apiFetch({path:'/kekidi/v1/scrutins/import',method:'POST',data:{postId:1037,chamber:'assemblee',period:'17',number:4042}});
  wp.data.dispatch('core/block-editor').updateBlockAttributes(block.clientId,{snapshotId:response.snapshotId});
  await new Promise(resolve=>setTimeout(resolve,1000));
  const editableAfter=wp.blocks.serialize(wp.data.select('core/block-editor').getBlock(block.clientId).innerBlocks);
  const after=await wp.apiFetch({path:'/wp/v2/posts/1037?context=edit'});
  return {explanationUnchanged:editableBefore===editableAfter,registered:!!wp.blocks.getBlockType('kekidi/quiz-scrutin'),blockValid:block.isValid,controls:doc.querySelectorAll('.scrutin-import-fields input').length,snapshot:response.snapshot.number,publishedUnchanged:before.content.raw===after.content.raw,oldReference:before.content.raw.includes('6566ebe0a6f0bae983a683e1ae187ca7af7116076e742c763baf97e35d3c960e')};
 })()`);
 assert.equal(editor.explanationUnchanged,true);assert.equal(editor.registered,true);assert.equal(editor.blockValid,true);assert.equal(editor.controls,2);assert.equal(editor.snapshot,4042);assert.equal(editor.publishedUnchanged,true);assert.equal(editor.oldReference,true);
 console.log('Sénat, sens contre, vide juste, logo manquant et import Gutenberg réel sans modification du publié vérifiés. Confettis : réussite seule, gerbe unique, palette, canvas réel, réduction des animations et erreur décorative vérifiés.');
}finally{socket.close();}
