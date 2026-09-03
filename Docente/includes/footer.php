</div> </div> </div>

     <script src="<?php echo $ruta; ?>bt/bootstrap.bundle.min.js"></script>
     <script src="<?php echo $ruta; ?>alertify/alertify.min.js"></script>

     <script>
          let lastActivity = Date.now();
          const SESSION_TIMEOUT_MS = 30 * 60 * 1000;
          let currentToken = '<?php echo isset($_SESSION['token']) ? $_SESSION['token'] : ''; ?>';

          const menusitemDropDown = document.querySelectorAll('.menu-item-dropdown');
          const menusItemStatic = document.querySelectorAll('.menu-item-static');
          const menuBtn = document.getElementById('menu-btn');
          const exitBtn = document.getElementById('exit-btn');
          const sidebar = document.getElementById('sidebar');

          function updateActivity() {
               lastActivity = Date.now();
          }

          document.addEventListener('mousemove', updateActivity);
          document.addEventListener('keypress', updateActivity);
          document.addEventListener('click', updateActivity);

          function cerrarSesionExpirada() {
               alertify.error('Sesion expirada, inicie nuevamente');
               setTimeout(() => {
                    window.location.href = '<?php echo $ruta; ?>Docente/index.php?error=sesion_expirada';
               }, 800);
          }

          function setSubMenuState(menuItem, open) {
               const subMenu = menuItem.querySelector('.sub-menu');
               const toggle = menuItem.querySelector('.menu-toggle');

               menuItem.classList.toggle('sub-menu-toggle', open);
               if (toggle) {
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
               }

               if (!subMenu) {
                    return;
               }

               if (open) {
                    subMenu.style.height = `${subMenu.scrollHeight + 6}px`;
                    subMenu.style.padding = '0.2rem 0';
               } else {
                    subMenu.style.height = '0';
                    subMenu.style.padding = '0';
               }
          }

          function closeOtherSubMenus(currentItem) {
               menusitemDropDown.forEach((item) => {
                    if (item !== currentItem) {
                         setSubMenuState(item, false);
                    }
               });
          }

          function refreshMenuTitles() {
               document.querySelectorAll('.menu-item').forEach((item) => {
                    const label = item.querySelector('.menu-link span');
                    if (label) {
                         item.setAttribute('data-title', label.textContent.trim());
                    }
               });

               const user = document.querySelector('.user');
               if (user) {
                    user.setAttribute('data-title', 'Cerrar sesion');
               }
          }

          function adjustDataTables() {
               if (window.jQuery && $.fn.DataTable && $('.dataTable').length) {
                    $('.dataTable').each(function() {
                         if ($.fn.DataTable.isDataTable(this)) {
                              $(this).DataTable().columns.adjust();
                         }
                    });
               }
          }

          refreshMenuTitles();

          menusitemDropDown.forEach((menuItem) => {
               if (menuItem.classList.contains('active')) {
                    setSubMenuState(menuItem, true);
               }

               const toggle = menuItem.querySelector('.menu-toggle');
               if (!toggle) {
                    return;
               }

               toggle.addEventListener('click', () => {
                    const isOpen = menuItem.classList.contains('sub-menu-toggle');
                    closeOtherSubMenus(menuItem);
                    setSubMenuState(menuItem, !isOpen);
               });
          });

          menusItemStatic.forEach((menuItem) => {
               menuItem.addEventListener('mouseenter', () => {
                    if (!sidebar || !sidebar.classList.contains('minimize')) {
                         return;
                    }
                    closeOtherSubMenus(null);
               });
          });

          if (menuBtn && sidebar) {
               menuBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('minimize');
                    document.body.classList.toggle('sidebar-minimized', sidebar.classList.contains('minimize'));

                    if (sidebar.classList.contains('minimize')) {
                         closeOtherSubMenus(null);
                    } else {
                         menusitemDropDown.forEach((item) => {
                              if (item.classList.contains('active')) {
                                   setSubMenuState(item, true);
                              }
                         });
                    }

                    setTimeout(() => {
                         window.dispatchEvent(new Event('resize'));
                         adjustDataTables();
                    }, 300);
               });
          }

          if (exitBtn) {
               exitBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    alertify.confirm("Cerrar Sesion", "Esta seguro que desea salir del sistema?",
                         function() {
                              window.location.href = "<?php echo $ruta; ?>salirDocente.php";
                         },
                         function() {
                              alertify.error('Cancelado');
                         }
                    ).set('labels', {ok:'Si, salir', cancel:'Cancelar'});
               });
          }

          function refrescarToken() {
               if (Date.now() - lastActivity > SESSION_TIMEOUT_MS) {
                    cerrarSesionExpirada();
                    return;
               }

               fetch('<?php echo $ruta; ?>Docente/refresh_token.php', {
                    method: 'POST',
                    headers: {
                         'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ token: currentToken })
               })
               .then(response => {
                    if (!response.ok) {
                         throw new Error('Token invalido o sesion expirada');
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

          setInterval(refrescarToken, 5 * 60 * 1000); //cada 5 minutos renueva el token
          setInterval(() => {
               if (Date.now() - lastActivity > SESSION_TIMEOUT_MS) {
                    cerrarSesionExpirada();
               }
          }, 1000);
     </script>
</body>
</html>
