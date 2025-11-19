document.addEventListener('DOMContentLoaded', ()=>{
  const navToggle = document.getElementById('navToggle');
  const navList = document.getElementById('navList');
  navToggle && navToggle.addEventListener('click', ()=>{
    const open = navList.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', open);
  });

  const userBtn = document.getElementById('userMenuBtn');
  const userDropdown = document.getElementById('userDropdown');
  if(userBtn){
    userBtn.addEventListener('click', ()=>{
      const show = userDropdown.getAttribute('aria-hidden') === 'true';
      userDropdown.setAttribute('aria-hidden', show ? 'false' : 'true');
    });
  }
});