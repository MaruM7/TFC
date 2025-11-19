document.addEventListener('DOMContentLoaded', ()=>{
  // Referencias a los elementos del menú de usuario
  const userBtn = document.getElementById('userMenuBtn');
  const userDropdown = document.getElementById('userDropdown');
  
  // Solo ejecutamos si los elementos existen en la página
  if(userBtn && userDropdown){
    
    // 1. Función para abrir/cerrar al hacer click en el nombre
    const toggleDropdown = (e) => {
      e.stopPropagation(); // Evita conflictos con el click de window
      const isHidden = userDropdown.getAttribute('aria-hidden') === 'true';
      // Cambiamos el estado: si está hidden (true) pasa a visible (false) y viceversa
      userDropdown.setAttribute('aria-hidden', isHidden ? 'false' : 'true');
    };

    // Asignamos el evento al botón
    userBtn.addEventListener('click', toggleDropdown);

    // 2. Función para cerrar si hacemos click fuera del menú
    window.addEventListener('click', (e) => {
      // Si el menú está visible...
      if (userDropdown.getAttribute('aria-hidden') === 'false') {
        // Y el click NO fue en el botón NI dentro del menú...
        if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
          // Entonces cerramos el menú
          userDropdown.setAttribute('aria-hidden', 'true');
        }
      }
    });
  }
});