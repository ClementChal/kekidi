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
try {
  await send('Page.enable'); await send('Network.enable');
  await send('Network.setCacheDisabled', {cacheDisabled:true});
  await send('Network.clearBrowserCookies');
  await send('Emulation.setDeviceMetricsOverride', {width:390,height:844,deviceScaleFactor:1,mobile:true});
  await send('Page.navigate', {url:'http://localhost:8881/contact/'}); await wait(1800);
  assert.equal(await evaluate('document.documentElement.scrollWidth>innerWidth'),false);
  assert.equal(await evaluate('document.querySelector("select").value'),'Poser une question');
  assert.equal(await evaluate('document.querySelector("button[type=submit]").getBoundingClientRect().height'),52);
  await evaluate(`(() => {
    window.contactTestCalls=0;
    const original=jQuery.ajax;
    jQuery.ajax=(options,...args)=>{
      if(options.data instanceof FormData && options.data.get('action')==='forminator_submit_form_custom-forms'){
        window.contactTestCalls++;
        const deferred=jQuery.Deferred();
        options.beforeSend();
        window.contactTestFail=()=>{
          options.error({status:500}); options.complete({},'error'); deferred.reject();
        };
        return deferred.promise();
      }
      return original(options,...args);
    };
    document.querySelector('form.kekidi-contact').requestSubmit();
  })()`);
  assert.equal(await evaluate('window.contactTestCalls'),0);
  assert.equal(await evaluate('document.activeElement.type'),'email');
  assert.equal(await evaluate(`document.querySelector('[type=email]').getAttribute('aria-invalid')`),'true');
  await evaluate(`(() => {
    const email=document.querySelector('[type=email]'),message=document.querySelector('textarea');
    email.value='invalide';message.value='Question';document.querySelector('button[type=submit]').click();
  })()`);
  assert.equal(await evaluate('window.contactTestCalls'),0);
  assert.equal(await evaluate(`document.querySelector('[type=email]').closest('.contact-field').querySelector('.contact-error').textContent`),'Saisissez une adresse mail valide, comme nom@exemple.fr.');
  await evaluate(`(() => {
    const email=document.querySelector('[type=email]'),message=document.querySelector('textarea');
    email.value='kekidi-test@example.invalid';email.dispatchEvent(new Event('input',{bubbles:true}));
    message.value='Texte conservé après un échec.';message.dispatchEvent(new Event('input',{bubbles:true}));
    document.querySelector('button[type=submit]').click();document.querySelector('button[type=submit]').click();
  })()`);
  await wait(100);
  assert.equal(await evaluate('window.contactTestCalls'),1);
  assert.equal(await evaluate('document.querySelector("button[type=submit]").disabled'),true);
  assert.equal(await evaluate('document.querySelector("button[type=submit]").textContent'),'Envoi en cours…');
  assert.equal(await evaluate('document.querySelector("form").getAttribute("aria-busy")'),'true');
  await evaluate('window.contactTestFail()');await wait(150);
  assert.equal(await evaluate('document.querySelector(".contact-feedback").hidden'),false);
  assert.equal(await evaluate('document.querySelector("textarea").value'),'Texte conservé après un échec.');
  assert.equal(await evaluate('document.querySelector("button[type=submit]").textContent'),'Réessayer l’envoi');
  assert.equal(await evaluate('document.activeElement.className'),'contact-feedback');
  // Recharger restaure le vrai transport ; le prochain envoi doit être enregistré par WordPress.
  await send('Page.navigate',{url:'http://localhost:8881/contact/'});await wait(1400);
  const invalid=await evaluate(`(async()=>{
    const data=new FormData(document.querySelector('form'));
    data.set('email-1','adresse-invalide');data.set('textarea-1','');
    const response=await fetch('/wp-admin/admin-ajax.php',{method:'POST',body:data});return response.json();
  })()`);
  assert.equal(invalid.success,false);
  assert(invalid.data.errors.some(item=>item['email-1']));
  assert(invalid.data.errors.some(item=>item['textarea-1']));
  await evaluate(`(() => {
    document.querySelector('[type=email]').value='kekidi-test@example.invalid';
    document.querySelector('textarea').value='KEKIDI-FORMINATOR-TEST : test de relance et confirmation.';
    document.querySelector('button[type=submit]').click();
  })()`);await wait(1400);
  assert.equal(await evaluate('document.querySelector(".contact-success").hidden'),false);
  assert.equal(await evaluate('document.querySelector("form").hidden'),true);
  assert.equal(await evaluate('document.querySelector(".contact-intro").hidden'),true);
  assert.equal(await evaluate('document.activeElement.className'),'contact-success');
  assert.equal(await evaluate('document.querySelector(".contact-success h2").textContent'),'Merci, votre message est envoyé !');
  for(const width of [320,1440]){
    await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:width<600});
    await send('Page.navigate',{url:'http://localhost:8881/contact/'});await wait(900);
    assert.equal(await evaluate('document.documentElement.scrollWidth>innerWidth'),false,`Débordement à ${width}px`);
    assert(await evaluate('document.querySelector("button[type=submit]").getBoundingClientRect().height >= 52'));
  }
  await send('Page.navigate',{url:'http://localhost:8881/studio-auto-login?redirect_to=%2Fwp-admin%2Fpost-new.php%3Fpost_type%3Dpage'});await wait(3500);
  const editor=await evaluate(`(async()=>{
    const patterns=await wp.apiFetch({path:'/wp/v2/block-patterns/patterns'});
    return {received:patterns.some(p=>p.name==='kekidi/contact'),block:!!wp.blocks.getBlockType('forminator/forms'),visible:wp.data.select('core/block-editor').__experimentalGetAllowedPatterns().some(p=>p.name==='kekidi/contact')};
  })()`);
  assert.deepEqual(editor,{received:true,block:true,visible:true});
  console.log('Contact Forminator : erreurs client et serveur, attente, double clic, panne, texte conservé, succès réel, 320/390/1440 px et composition visible dans Gutenberg vérifiés.');
} finally {socket.close();}
