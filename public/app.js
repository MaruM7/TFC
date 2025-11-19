document.addEventListener('DOMContentLoaded', ()=>{
  // 1. Lógica del menú móvil (hamburguesa)
  const navToggle = document.getElementById('navToggle');
  const navList = document.getElementById('navList');
  if(navToggle && navList){
    navToggle.addEventListener('click', ()=>{
      const open = navList.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', open);
    });
  }

  // 2. Lógica del menú de usuario (Ibai ▾)
  const userBtn = document.getElementById('userMenuBtn');
  const userDropdown = document.getElementById('userDropdown');
  
  if(userBtn && userDropdown){
    // Función para alternar la visibilidad
    const toggleDropdown = (e) => {
      e.stopPropagation(); // Previene que el click llegue al listener de la ventana
      const isHidden = userDropdown.getAttribute('aria-hidden') === 'true';
      userDropdown.setAttribute('aria-hidden', isHidden ? 'false' : 'true');
    };

    userBtn.addEventListener('click', toggleDropdown);

    // Lógica para cerrar el menú si se hace click fuera
    window.addEventListener('click', (e) => {
      // Si el click no es ni en el botón ni en el menú desplegable, lo ocultamos
      if (userDropdown.getAttribute('aria-hidden') === 'false' && 
          !userBtn.contains(e.target) && 
          !userDropdown.contains(e.target)) {
        userDropdown.setAttribute('aria-hidden', 'true');
      }
    });
  }
});