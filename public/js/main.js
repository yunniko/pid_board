// For the time now
Date.prototype.timeNow = function () {
    return ((this.getHours() < 10) ? "0" : "") + this.getHours() + ":" + ((this.getMinutes() < 10) ? "0" : "") + this.getMinutes() + ":" + ((this.getSeconds() < 10) ? "0" : "") + this.getSeconds();
};

const navList = document.getElementsByClassName("nav");

let activeNavItem = navList.item(0);
for (const navItem of navList) {
    if (navItem.getAttribute('href') === window.location.pathname + window.location.search) {
        activeNavItem = navItem;
        break;
    }
}
activeNavItem.classList.add('active');