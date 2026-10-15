import messages from '../src/data/portfolio.json' with {type:'json'};
const encoder=new TextEncoder();
const cookieName='__Host-sergiotech-contact';
const maxBody=20_000;
const base64=bytes=>btoa(String.fromCharCode(...new Uint8Array(bytes))).replace(/\+/g,'-').replace(/\//g,'_').replace(/=+$/,'');
const decode=value=>Uint8Array.from(atob(value.replace(/-/g,'+').replace(/_/g,'/')),c=>c.charCodeAt(0));
const cookie=request=>(request.headers.get('Cookie')??'').split(';').map(v=>v.trim()).find(v=>v.startsWith(cookieName+'='))?.slice(cookieName.length+1)??'';
const key=secret=>crypto.subtle.importKey('raw',encoder.encode(secret),{name:'HMAC',hash:'SHA-256'},false,['sign','verify']);
async function sign(secret,value){return base64(await crypto.subtle.sign('HMAC',await key(secret),encoder.encode(value)))}
async function issue(secret,session,now){const value=`${Math.floor(now/1000)}.${crypto.randomUUID()}`;return value+'.'+await sign(secret,session+'.'+value)}
async function validToken(secret,session,token,now){
 const parts=token.split('.');
 if(parts.length!==3||!/^\d{10}$/.test(parts[0])||!session||token.length>150)return false;
 const age=now/1000-Number(parts[0]);
 if(age<2||age>3600)return false;
 try{return await crypto.subtle.verify('HMAC',await key(secret),decode(parts[2]),encoder.encode(session+'.'+parts.slice(0,2).join('.')))}catch{return false}
}
async function limitedBody(request){
 if(Number(request.headers.get('Content-Length'))>maxBody)throw new RangeError();
 const reader=request.body?.getReader();if(!reader)return new Uint8Array();
 const chunks=[];let size=0;
 try{while(true){const {done,value}=await reader.read();if(done)break;size+=value.byteLength;if(size>maxBody){await reader.cancel();throw new RangeError()}chunks.push(value)}}finally{reader.releaseLock()}
 const result=new Uint8Array(size);let offset=0;for(const chunk of chunks){result.set(chunk,offset);offset+=chunk.length}return result;
}
export async function contact(request,env,{fetcher=fetch,now=Date.now()}={}){
 const url=new URL(request.url);
 let language='es';
 const respond=(success,key,status=200,extra={})=>Response.json({success,message:messages[language][key]??key,...extra},{status,headers:{'Cache-Control':'no-store, max-age=0','Content-Language':language,'X-Content-Type-Options':'nosniff','Content-Security-Policy':"default-src 'none'; frame-ancestors 'none'"}});
 if(!env.CONTACT_SECRET||env.CONTACT_SECRET.length<32||!env.TURNSTILE_SITE_KEY||!env.TURNSTILE_SECRET_KEY||!env.RESEND_API_KEY||!env.CONTACT_RATE)return respond(false,'contact_unavailable',503);
 if(url.pathname==='/api/contact/session'){
  if(request.method!=='GET')return respond(false,'contact_method_not_allowed',405);
  let session=cookie(request);if(!/^[A-Za-z0-9_-]{43}$/.test(session))session=base64(crypto.getRandomValues(new Uint8Array(32)));
  const response=Response.json({siteKey:env.TURNSTILE_SITE_KEY,requestToken:await issue(env.CONTACT_SECRET,session,now)},{headers:{'Cache-Control':'no-store, max-age=0','Set-Cookie':`${cookieName}=${session}; Secure; HttpOnly; SameSite=Strict; Path=/; Max-Age=3600`,'X-Content-Type-Options':'nosniff'}});
  return response;
 }
 if(request.method!=='POST')return respond(false,'contact_method_not_allowed',405);
 if(request.headers.get('Origin')!==url.origin)return respond(false,'contact_invalid_request',403);
 const type=request.headers.get('Content-Type')??'';
 if(!/^(multipart\/form-data|application\/x-www-form-urlencoded)(;|$)/i.test(type))return respond(false,'contact_invalid_request',415);
 let form;
 try{form=await new Response(await limitedBody(request),{headers:{'Content-Type':type}}).formData()}catch(error){return respond(false,error instanceof RangeError?'contact_too_large':'contact_invalid_request',error instanceof RangeError?413:400)}
 const value=name=>typeof form.get(name)==='string'?form.get(name).trim():'';
 language=value('language')==='en'?'en':'es';
 const token=value('request_token'),session=cookie(request);
 if(!await validToken(env.CONTACT_SECRET,session,token,now))return respond(false,'contact_expired',419);
 if(value('website'))return respond(true,'form_success');
 const name=value('name'),email=value('email'),subject=value('subject'),message=value('message');
 const length=value=>Array.from(value).length;
 if(!name||length(name)>80||/[\r\n]/.test(name)||!/^\S+@[^\s@]+\.[^\s@]+$/.test(email)||length(email)>120||/[\r\n]/.test(email)||!subject||length(subject)>120||/[\r\n]/.test(subject)||length(message)<20||length(message)>5000||value('privacy')!=='accepted')return respond(false,'contact_invalid_fields',422);
 const captcha=value('cf-turnstile-response');
 if(!captcha||captcha.length>2048)return respond(false,'contact_invalid_request',403);
 let verified;
 try{verified=await (await fetcher('https://challenges.cloudflare.com/turnstile/v0/siteverify',{method:'POST',body:new URLSearchParams({secret:env.TURNSTILE_SECRET_KEY,response:captcha,remoteip:request.headers.get('CF-Connecting-IP')??'',idempotency_key:crypto.randomUUID()}),signal:AbortSignal.timeout(10_000)})).json()}catch{return respond(false,'contact_unavailable',503)}
 if(!verified.success||verified.hostname!==url.hostname||verified.action!=='contact')return respond(false,'contact_invalid_request',403);
 const rateKey='ip:'+await sign(env.CONTACT_SECRET,request.headers.get('CF-Connecting-IP')??session);
 try{
  const previous=await env.CONTACT_RATE.get(rateKey);
  if(previous&&now-Number(previous)<60_000)return respond(false,'contact_rate_limit',429);
  // KV limits repeated submissions without retaining IP addresses or message contents.
  await env.CONTACT_RATE.put(rateKey,String(now),{expirationTtl:60});
 }catch{return respond(false,'contact_unavailable',503)}
 const t=k=>messages[language][k];
 const body=[t('email_heading'),'---------------------------------',`${t('email_name')}: ${name}`,`${t('email_address')}: ${email}`,`${t('email_subject')}: ${subject}`,'',t('email_message')+':',message,'',t('email_consent'),`${t('email_date')}: ${new Date(now).toISOString()}`].join('\n');
 const idempotency=await sign(env.CONTACT_SECRET,token+'\n'+body);
 try{
  const result=await fetcher('https://api.resend.com/emails',{method:'POST',headers:{'Authorization':'Bearer '+env.RESEND_API_KEY,'Content-Type':'application/json','Idempotency-Key':'contact/'+idempotency},body:JSON.stringify({from:env.CONTACT_FROM??'Sergiotech <portfolio@contact.sergiotech.es>',to:[env.CONTACT_TO??'smorgarc@sergiotech.es'],reply_to:email,subject:'Portfolio: '+subject,text:body}),signal:AbortSignal.timeout(12_000)});
  const data=await result.json();
  if(!result.ok||typeof data.id!=='string'||!data.id)return respond(false,'contact_send_error',503);
 }catch{return respond(false,'contact_send_error',503)}
 return respond(true,'form_success',200,{requestToken:await issue(env.CONTACT_SECRET,session,now)});
}
