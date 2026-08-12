// START: Profile dynamic content section
(function(){
fetch('../api/profile-pages.php?slug='+document.body.dataset.profilePage)
.then(r=>r.json()).then(r=>{
 if(!r.success)return;
 const p=r.data;
 document.title=p.title;
 document.querySelector('h2').textContent=p.name;
 document.querySelector('#profileContent').innerHTML=p.content;
}).catch(()=>{});
// END: Profile dynamic content section
})();