document.addEventListener('DOMContentLoaded', ()=>{
  // Toggle menu movil
  const navToggle = document.getElementById('navToggle');
  const navList = document.getElementById('navList');
  if(navToggle && navList){
    navToggle.addEventListener('click', ()=>{
      const open = navList.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', open);
    });
  }

  // Toggle dropdown usuario
  const userBtn = document.getElementById('userMenuBtn');
  const userDropdown = document.getElementById('userDropdown');
  
  if(userBtn && userDropdown){
    userBtn.addEventListener('click', (e)=>{
      e.stopPropagation(); // Evita que el click cierre inmediatamente si hubiera un listener en window
      const isHidden = userDropdown.getAttribute('aria-hidden') === 'true';
      // Cambiamos el estado
      userDropdown.setAttribute('aria-hidden', isHidden ? 'false' : 'true');
    });

    // Cerrar al hacer click fuera
    window.addEventListener('click', (e)=>{
      if(!userBtn.contains(e.target) && !userDropdown.contains(e.target)){
        userDropdown.setAttribute('aria-hidden', 'true');
      }
    });
  }
});