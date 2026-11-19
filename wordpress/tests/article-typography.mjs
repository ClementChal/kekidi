import assert from 'node:assert/strict';
const siteUrl = (process.env.KEKIDI_SITE_URL ?? 'http://localhost:8881').replace(/\/$/, '');
const cdpUrl = (process.env.KEKIDI_CDP_URL ?? 'http://127.0.0.1:9223').replace(/\/$/, '');
const postId = Number(process.env.KEKIDI_ARTICLE_POST_ID ?? 1001);
assert(Number.isSafeInteger(postId) && postId > 0, 'Identifiant d’article invalide');
const pages = await (await fetch(cdpUrl + '/json')).json();
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
const inject = `(() => {
 const root=document.querySelector('.wp-block-post-content');
 const add=(tag,text,parent=root)=>{const e=document.createElement(tag);e.textContent=text;parent.append(e);return e;};
 const heading=add('h2','Un autre titre H2');heading.className='wp-block-heading';heading.id='added-heading';
 const paragraph=add('p','Un paragraphe ajouté après la composition doit conserver la même typographie.');paragraph.id='added-paragraph';
 const group=add('div','');group.className='wp-block-group is-layout-flow';add('h2','Un titre dans un groupe',group).id='nested-heading';add('p','Un texte dans ce groupe.',group).id='nested-paragraph';
 const figure=add('figure','');figure.className='wp-block-image';figure.id='added-image';
 const img=add('img','',figure);img.src='/wp-includes/images/w-logo-blue.png';img.width=1200;img.alt='Image de test WordPress';
 add('figcaption','Une légende pour cette image.',figure);
 const sources=add('section','');sources.className='sources';add('h2','Pour aller plus loin',sources);add('p','Une source doit garder son propre style.',sources);
 return true;
})()`;
const metrics = `(() => {
 const style=selector=>{const element=document.querySelector(selector);if(!element)throw new Error('Élément absent : '+selector);const s=getComputedStyle(element);return {family:s.fontFamily,size:s.fontSize,weight:s.fontWeight,line:s.lineHeight};};
 return {heading:style('.answer-detail h2'),newHeading:style('#added-heading'),nestedHeading:style('#nested-heading'),paragraph:style('.answer-detail p'),newParagraph:style('#added-paragraph'),nestedParagraph:style('#nested-paragraph'),sources:style('.sources p'),sourceTitle:style('.sources h2'),shortAnswer:style('.short-answer'),paragraphGap:getComputedStyle(document.querySelector('#added-paragraph')).marginTop,image:document.querySelector('#added-image img').getBoundingClientRect().toJSON(),imageReady:document.querySelector('#added-image img').naturalWidth>0,contentWidth:document.querySelector('.wp-block-post-content').getBoundingClientRect().width,overflow:document.documentElement.scrollWidth>innerWidth};
})()`;
try {
 await send('Page.enable');await send('Network.enable');await send('Network.setCacheDisabled',{cacheDisabled:true});
 for(const width of [1440,390,320]){
  await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<600});
  await send('Page.navigate',{url:siteUrl + '/reponses/espace-public/'});await wait(1000);
  await evaluate(inject);await wait(150);
  const m=await evaluate(metrics);
  assert.deepEqual(m.newHeading,m.heading);assert.deepEqual(m.nestedHeading,m.heading);
  assert.deepEqual(m.newParagraph,m.paragraph);assert.deepEqual(m.nestedParagraph,m.paragraph);
  assert.equal(m.heading.size,'24px');assert.equal(m.paragraph.size,'18px');assert.equal(m.paragraph.line,'29.7px');
  assert.equal(m.sources.size,'15px');assert.equal(m.sourceTitle.size,'20px');assert.notEqual(m.shortAnswer.size,'18px');
  assert.equal(m.paragraphGap,'16px');assert(m.imageReady);assert(m.image.width<=m.contentWidth);assert(!m.overflow);
  assert.equal(await evaluate('getComputedStyle(document.querySelector("#nested-heading").parentElement).marginTop'),'32px');
  console.log(`Article ${width}px : titres, textes, groupes, images et styles réservés vérifiés.`);
 }
 await send('Page.navigate',{url:siteUrl + '/reponses/missions-police/'});await wait(900);
 assert.equal(await evaluate(`getComputedStyle(document.querySelector('.quiz-instructions')).fontSize`),'14px');
 await send('Page.navigate',{url:siteUrl + '/studio-auto-login?redirect_to=%2Fwp-admin%2Fpost.php%3Fpost%3D' + postId + '%26action%3Dedit'});await wait(3000);
 const editor=await evaluate(`(() => {
  const frame=document.querySelector('iframe[name="editor-canvas"]');const doc=frame?.contentDocument||document;
  const root=doc.querySelector('.is-root-container');const oldP=doc.querySelector('.answer-detail p');const oldH=doc.querySelector('.answer-detail h2');
  if(!root||!oldP||!oldH)return {error:'Éditeur indisponible'};
  const h=doc.createElement('h2');h.className='wp-block-heading';h.textContent='Nouveau titre';root.append(h);
  const p=doc.createElement('p');p.textContent='Nouveau paragraphe';root.append(p);
  const win=doc.defaultView;return {oldP:win.getComputedStyle(oldP).fontSize,newP:win.getComputedStyle(p).fontSize,oldH:win.getComputedStyle(oldH).fontSize,newH:win.getComputedStyle(h).fontSize};
 })()`);
 assert(!editor.error,JSON.stringify(editor));assert.equal(editor.oldP,editor.newP);assert.equal(editor.oldH,editor.newH);
 console.log('Éditeur : nouveaux blocs et blocs existants ont les mêmes tailles. Quiz : consigne inchangée.');
}finally{socket.close();}
