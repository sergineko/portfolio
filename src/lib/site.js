import product from '../data/product.json';
import translations from '../data/translations.json';
export const PORTFOLIO_ORIGIN='https://sergiotech.es';
export const PRODUCT_ORIGIN='https://savetempo.sergiotech.es';
export const safeJson=value=>JSON.stringify(value).replace(/</g,'\\u003c');
export const productPath=(page,language)=> (language==='es'?'/es':'')+(product.pagePaths[page]?.[language]??'/');
export function productHelpers(language){
 const stCopy=(key,locale=language)=>key.split('.').reduce((v,k)=>v?.[k],translations[locale])??key;
 const stAsset=(key,locale=language)=>'/savetempo-assets'+(typeof product.assets[key]==='object'?product.assets[key][locale]:product.assets[key]).replace(/^\/assets/,'');
 const stStoreLinks=()=>product.googlePlayUrl?[{store:'google',url:product.googlePlayUrl,label:stCopy('chrome.storeGoogle')}]:[];
 return {
  stCopy,stAsset,stStoreLinks,
  stConfig:key=>key?product[key]:product,
  stPath:suffix=>'/savetempo-assets'+suffix.replace(/^\/assets/,''),
  stCanonical:(suffix='')=>PRODUCT_ORIGIN+'/'+suffix.replace(/^\//,'').replace(/^assets\//,'savetempo-assets/'),
  stRoutePath:(page,locale=language)=>PRODUCT_ORIGIN+productPath(page,locale),
  stRouteCanonical:(page,locale=language)=>PRODUCT_ORIGIN+productPath(page,locale),
 };
}
export function portfolioSchema(language,t){
 const origin=PORTFOLIO_ORIGIN, canonical=origin+(language==='en'?'/?lang=en':'/');
 const person=origin+'/#person',website=origin+'/#website',profile=canonical+'#profile-page';
 const save=PRODUCT_ORIGIN+'/#software';
 return {'@context':'https://schema.org','@graph':[
  {'@type':'ProfilePage','@id':profile,url:canonical,name:t('seo_title'),description:t('seo_description'),inLanguage:language,mainEntity:{'@id':person},isPartOf:{'@id':website},mentions:[{'@id':save},{'@id':'https://myworkingarea.com/#website'},{'@id':'https://vyrsea.com/#website'}]},
  {'@type':'SoftwareApplication','@id':save,url:PRODUCT_ORIGIN+'/',name:'SaveTempo',description:t('savetempo_description'),applicationCategory:'FinanceApplication',operatingSystem:product.supportedPlatforms.join(', '),isAccessibleForFree:true,downloadUrl:product.googlePlayUrl,sameAs:product.googlePlayUrl,offers:{'@type':'Offer',price:'0',priceCurrency:'EUR',availability:'https://schema.org/InStock',url:product.googlePlayUrl},creator:{'@id':person}},
  {'@type':'WebSite','@id':website,url:origin+'/',name:'Sergio Moreno García',inLanguage:['es','en'],publisher:{'@id':person}},
  {'@type':'Person','@id':person,name:'Sergio Moreno García',url:origin+'/',image:origin+'/assets/images/sergio-moreno-portrait-v3-1120.webp',sameAs:['https://www.linkedin.com/in/sergiomg93/'],email:'mailto:smorgarc@sergiotech.es',telephone:'+34614839879',mainEntityOfPage:{'@id':profile},homeLocation:{'@type':'Place',name:t('location')},jobTitle:t('schema_job_title'),knowsAbout:['schema_backend','schema_fullstack','schema_integrations','schema_web_apps'].map(t)},
  ...[['myworkingarea.com','MyWorkingArea','mwa_description'],['vyrsea.com','Vyrsea','vyrsea_description']].map(([host,name,key])=>({'@type':'WebSite','@id':'https://'+host+'/#website',url:'https://'+host+'/',name,description:t(key),creator:{'@id':person}})),
 ]};
}
export function productSchema(page,language){
 const {stCopy}=productHelpers(language),isGuide=['week52','day365','challengeApp'].includes(page);
 const url=PRODUCT_ORIGIN+productPath(page,language),title=stCopy('meta.'+page+'Title');
 const schema={'@context':'https://schema.org','@type':page==='landing'?'SoftwareApplication':isGuide?'Article':'WebPage',name:page==='landing'?product.productName:title,url,description:stCopy('meta.'+page+'Description'),inLanguage:language,dateModified:page==='privacy'?product.privacyLastUpdated:'2026-09-06'};
 if(page==='landing')Object.assign(schema,{'@id':PRODUCT_ORIGIN+'/#software',applicationCategory:'FinanceApplication',operatingSystem:product.supportedPlatforms.join(', '),isAccessibleForFree:true,publisher:{'@type':'Organization',name:product.publisherName,url:product.portfolioUrl},creator:{'@type':'Person',name:product.creatorName,url:product.portfolioUrl},image:PRODUCT_ORIGIN+'/savetempo-assets/images/savetempo-og.png',downloadUrl:product.googlePlayUrl,sameAs:product.googlePlayUrl,offers:{'@type':'Offer',price:'0',priceCurrency:'EUR',availability:'https://schema.org/InStock',url:product.googlePlayUrl}});
 if(isGuide)Object.assign(schema,{datePublished:'2026-09-06',headline:title,mainEntityOfPage:url,author:{'@type':'Person',name:product.creatorName,url:product.portfolioUrl},publisher:{'@type':'Organization',name:product.publisherName,url:product.portfolioUrl},image:PRODUCT_ORIGIN+'/savetempo-assets/images/savetempo-og.png'});
 return schema;
}
