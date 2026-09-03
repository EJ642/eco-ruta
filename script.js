const menusitemDropDown = document.querySelectorAll('.menu-item-dropdown');
const menusItemStatic = document.querySelectorAll('.menu-item-static');
const menuBtn = document.getElementById('menu-btn');
const sidebar = document.getElementById('sidebar');

menuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('minimize');

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


