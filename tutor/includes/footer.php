</div> </div> </div> 
     <script src="<?php echo $ruta; ?>bt/bootstrap.min.js"></script>
     <script src="<?php echo $ruta; ?>bt/bootstrap.bundle.min.js"></script>
     <script src="<?php echo $ruta; ?>alertify/alertify.min.js"></script>

     <script>
          const menusitemDropDown = document.querySelectorAll('.menu-item-dropdown');
          const menusItemStatic = document.querySelectorAll('.menu-item-static');
          const dnone = document.querySelectorAll('.d-none');
          const menuBtn = document.getElementById('menu-btn');
          const exitBtn = document.getElementById('exit-btn');
          const sidebar = document.getElementById('sidebar');

          menuBtn.addEventListener('click', () => {
               sidebar.classList.toggle('minimize');
               //1/05/2026
               // Agregar clase al body para controlar el main-content
               document.body.classList.toggle('sidebar-minimized');
               
               // Forzar actualización del layout
               setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                    
                    // Recargar DataTables si existe
                    if ($.fn.DataTable && $('.dataTable').length) {
                         $('.dataTable').each(function() {
                              if ($.fn.DataTable.isDataTable(this)) {
                                   $(this).DataTable().columns.adjust();
                              }
                         });
                    }
               }, 350);
          });

          exitBtn.addEventListener('click', (e) => {
               e.preventDefault(); // Evita que el enlace actúe normal (ir al #)

               alertify.confirm("Cerrar Sesión", "¿Está seguro que desea salir del sistema?",
                    function() {
                         // Si el usuario da OK:
                         window.location.href = "<?php echo $ruta; ?>salirTutor.php";
                    },
                    function() {
                         // Si el usuario da Cancelar:
                         alertify.error('Cancelado');
                    }
               ).set('labels', {ok:'Sí, Salir', cancel:'Cancelar'});

          });


          menusitemDropDown.forEach((menuItem) => {
               menuItem.addEventListener('click', () => {
                    const subMenu = menuItem.querySelector('.sub-menu');
                    const isActive = menuItem.classList.toggle('sub-menu-toggle');
                    if (subMenu) {
                         if (isActive) {
                              subMenu.style.height = `${subMenu.scrollHeight + 6}px`;
                              subMenu.style.padding = '0.2rem 0';
                         } else {
                              subMenu.style.height = '0';
                              subMenu.style.padding = '0';
                         }
                    }
                    menusitemDropDown.forEach((item) => {
                         if (item !== menuItem) {
                              const otherSubmenu = item.querySelector('.sub-menu');
                              if (otherSubmenu) {
                                   item.classList.remove('sub-menu-toggle');
                                   otherSubmenu.style.height = '0';
                                   otherSubmenu.style.padding = '0';
                              }
                         }
                    });
               });


          });


          menusItemStatic.forEach((menuItem) => {
          menuItem.addEventListener('mouseenter', () => {

               if (!sidebar.classList.contains('minimize'))return;

               menusitemDropDown.forEach((item) => {
                    const otherSubmenu = item.querySelector('.sub-menu');
                    if (otherSubmenu) {
                         item.classList.remove('sub-menu-toggle');
                         otherSubmenu.style.height = '0';
                         otherSubmenu.style.padding = '0';
                    }
               });

          });
          });

          if ('<?php echo $_SESSION['rol']; ?>' === 'Director') {
               dnone.forEach((element) => {
                    element.classList.remove('d-none');
               });
          }

          //11:05
          let lastActivity = Date.now();
          const SESSION_TIMEOUT_MS = 30 * 60 * 1000; // 30 minutos en ms
          let currentToken = '<?php echo $_SESSION['token']; ?>';

          function updateActivity() {
               lastActivity = Date.now();
          }

          // Detectar actividad del usuario
          document.addEventListener('mousemove', updateActivity);
          document.addEventListener('keypress', updateActivity);
          document.addEventListener('click', updateActivity);
//11:05
          function cerrarSesionExpirada() {
               alertify.error('Sesión expirada, inicie nuevamente');
               setTimeout(() => {
                    window.location.href = '<?php echo $ruta; ?>Tutor/index.php?error=sesion_expirada';
               }, 800);
          }

          function refrescarToken() {
               if (Date.now() - lastActivity > SESSION_TIMEOUT_MS) {
                    cerrarSesionExpirada();
                    return;
               }

               fetch('<?php echo $ruta; ?>refresh_token.php', {
                    method: 'POST',
                    headers: {
                         'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ token: currentToken })
               })
               .then(response => {
                    if (!response.ok) {
                         throw new Error('Token inválido o sesión expirada');
                    }
                    return response.json();
               })
               .then(data => {
                    if (data.success && data.new_token) {
                         currentToken = data.new_token;
                    }
               })
               .catch(() => {
                    cerrarSesionExpirada();
               });
          }

          setInterval(refrescarToken, 5 * 60 * 1000); // Cada 5 minutos
          setInterval(() => {
               if (Date.now() - lastActivity > SESSION_TIMEOUT_MS) {
                    cerrarSesionExpirada();
               }
          }, 1000);
     </script>

             

</body>
</html>