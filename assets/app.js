/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/main.less';

// start the Stimulus application
import './bootstrap';


// For the time now
Date.prototype.timeNow = function () {
    return ((this.getHours() < 10) ? "0" : "") + this.getHours() + ":" + ((this.getMinutes() < 10) ? "0" : "") + this.getMinutes() + ":" + ((this.getSeconds() < 10) ? "0" : "") + this.getSeconds();
};

(function () {
    const navItemsList = document.getElementsByClassName("nav");
    const toggleNavigation = document.getElementsByClassName("nav-toggle").item(0);
    const navContainer = document.getElementsByClassName("nav-container").item(0);
    
    if (sessionStorage.getItem('nav-toggle-hidden') == 'true') {
        navContainer.classList.add('hidden');
    }
    
    let activeNavItem = navItemsList.item(0);
    for (const navItem of navItemsList) {
        if (navItem.getAttribute('href') === window.location.pathname + window.location.search) {
            activeNavItem = navItem;
            break;
        }
    }
    activeNavItem.classList.add('active');
    window.title = activeNavItem.innerHTML;
    
    toggleNavigation.onclick = function (e) {
        navContainer.classList.toggle('hidden');
        sessionStorage.setItem('nav-toggle-hidden', navContainer.classList.contains('hidden'));
    };
})();
