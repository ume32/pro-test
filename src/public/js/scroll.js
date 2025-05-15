const scrollableDiv = document.getElementById("scroll__item__info");
const commentForm = document.getElementById("comment__form");

commentForm.addEventListener("submit", function () {
    sessionStorage.setItem("scrollPosition", scrollableDiv.scrollTop);
});

window.addEventListener("load", function () {
    const scrollPosition = sessionStorage.getItem("scrollPosition");
    if (scrollPosition !== null) {
        scrollableDiv.scrollTop = parseInt(scrollPosition, 0);
        sessionStorage.removeItem("scrollPosition");
    }
});
