const currentTimeCallback = (function () {
    const currentTimeContainer = document.getElementsByClassName("current-time").item(0);
    return function () {
        const currentDate = new Date();
        currentTimeContainer.innerHTML = currentDate.timeNow();
        if (currentDate.getSeconds() === 59 || currentDate.getSeconds() === 29) {
            location.reload();
        }
    }
})();

setInterval(currentTimeCallback, 900);