// Sidebar defaults to open; the toggle button switches it and the choice is
// remembered. Must load before themes/dagger.js, which reads "compactMenu" on init.
if (localStorage.getItem("compactMenu") === null) {
    localStorage.setItem("compactMenu", "false");
}

// Disable dagger.js hover-to-expand so a collapsed sidebar stays collapsed.
window.addEventListener("mouseover", function (e) {
    if (e.target.closest && e.target.closest(".side-menu")) {
        e.stopPropagation();
    }
}, true);

// dagger.js forces the compact menu on resize below 1600px; keep only the
// mobile-menu reset so the user's choice is respected.
document.addEventListener("DOMContentLoaded", function () {
    window.onresize = function () {
        $(".side-menu").first().removeClass("side-menu--mobile-menu-open");
        $(".close-mobile-menu").first().removeClass("close-mobile-menu--mobile-menu-open");
    };
});
