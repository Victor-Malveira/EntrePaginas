(function(){
 function getConsent(){try{return localStorage.getItem('ep_analytics_consent')}catch(e){return null}}
 function sendEvents(){if(getConsent()!=='accepted'||typeof gtag!=='function')return; (window.epEventos||[]).forEach(function(ev){gtag('event',ev.nome,ev.dados)})}
 document.addEventListener('DOMContentLoaded',function(){
  var box=document.getElementById('ep-cookies'),yes=document.getElementById('ep-aceitar'),no=document.getElementById('ep-recusar');
  if(!getConsent()&&box)box.hidden=false;
  function choose(value){try{localStorage.setItem('ep_analytics_consent',value)}catch(e){} if(box)box.hidden=true;gtag('consent','update',{analytics_storage:value==='accepted'?'granted':'denied'});if(value==='accepted'){gtag('event','page_view');sendEvents()}}
  if(yes)yes.addEventListener('click',function(){choose('accepted')});
  if(no)no.addEventListener('click',function(){choose('rejected')});
  if(getConsent()==='accepted')sendEvents();
 });
})();
