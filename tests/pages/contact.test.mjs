import {test} from 'node:test';
import assert from 'node:assert/strict';
import {contact} from '../../edge/contact.js';
const now=1792080000000;
function setup(){
 const store=new Map(),calls=[];
 const env={CONTACT_SECRET:'a'.repeat(64),TURNSTILE_SITE_KEY:'public-key',TURNSTILE_SECRET_KEY:'private-key',RESEND_API_KEY:'test-only',CONTACT_RATE:{get:async k=>store.get(k),put:async(k,v)=>store.set(k,v)}};
 const fetcher=async(url,options)=>{calls.push({url,options});return url.includes('siteverify')?Response.json({success:true,hostname:'sergiotech.es',action:'contact'}):Response.json({id:'email-test'})};
 return {env,fetcher,calls};
}
async function formRequest(env,changes={},origin='https://sergiotech.es'){
 const session=await contact(new Request('https://sergiotech.es/api/contact/session'),env,{now});
 const {requestToken}=await session.json();
 const data=new URLSearchParams({name:'Contact test',email:'visitor@example.net',subject:'Test contact',message:'A sufficiently long contact form test message.',privacy:'accepted',language:'en',request_token:requestToken,'cf-turnstile-response':'valid-once',...changes});
 return new Request('https://sergiotech.es/api/contact',{method:'POST',headers:{Origin:origin,Cookie:session.headers.get('Set-Cookie').split(';')[0],'CF-Connecting-IP':'192.0.2.1'},body:data});
}
test('sends only to configured recipient and preserves Reply-To',async()=>{
 const {env,fetcher,calls}=setup();
 const response=await contact(await formRequest(env),env,{fetcher,now:now+3000});
 assert.equal(response.status,200);assert.equal((await response.json()).success,true);
 const email=JSON.parse(calls.find(c=>c.url.includes('resend')).options.body);
 assert.deepEqual(email.to,['smorgarc@sergiotech.es']);assert.equal(email.reply_to,'visitor@example.net');
 assert.match(email.from,/@contact\.sergiotech\.es/);
});
test('provider failure never becomes success',async()=>{
 const {env}=setup();const fetcher=async url=>url.includes('siteverify')?Response.json({success:true,hostname:'sergiotech.es',action:'contact'}):Response.json({message:'unavailable'},{status:500});
 const response=await contact(await formRequest(env),env,{fetcher,now:now+3000});assert.equal(response.status,503);assert.equal((await response.json()).success,false);
});
test('rejects cross-origin, invalid token, consent and header injection before sending',async()=>{
 for(const [changes,origin,status]of [[{},'https://attacker.example',403],[{request_token:'invalid'},undefined,419],[{privacy:''},undefined,422],[{subject:'hello\r\nBcc: victim@example.org'},undefined,422]]){
  const {env,fetcher,calls}=setup();const response=await contact(await formRequest(env,changes,origin),env,{fetcher,now:now+3000});assert.equal(response.status,status);assert.equal(calls.length,0);
 }
});
test('rejects reused IP submissions for one minute',async()=>{
 const {env,fetcher,calls}=setup();assert.equal((await contact(await formRequest(env),env,{fetcher,now:now+3000})).status,200);
 assert.equal((await contact(await formRequest(env),env,{fetcher,now:now+4000})).status,429);
 assert.equal(calls.filter(c=>c.url.includes('resend')).length,1);
});
test('validates Turnstile action and host',async()=>{
 for(const verified of [{success:false},{success:true,hostname:'other.example',action:'contact'},{success:true,hostname:'sergiotech.es',action:'other'}]){
  const {env}=setup();const response=await contact(await formRequest(env),env,{fetcher:async()=>Response.json(verified),now:now+3000});assert.equal(response.status,403);
 }
});
test('rejects large bodies and tokens submitted before two seconds',async()=>{
 const {env,fetcher}=setup();assert.equal((await contact(await formRequest(env),env,{fetcher,now:now+500})).status,419);
 const request=new Request('https://sergiotech.es/api/contact',{method:'POST',headers:{Origin:'https://sergiotech.es','Content-Type':'application/x-www-form-urlencoded'},body:'a'.repeat(20_001)});
 assert.equal((await contact(request,env,{fetcher,now})).status,413);
});
test('fails closed if production bindings are missing',async()=>{assert.equal((await contact(new Request('https://sergiotech.es/api/contact/session'),{})).status,503)});
