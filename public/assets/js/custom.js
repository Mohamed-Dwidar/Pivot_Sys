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

// Forms with data-confirm="message" open a confirm modal before submitting (approve, reject, delete, ...).
// Optional: data-confirm-title, data-confirm-button (confirm button text),
// data-confirm-variant = success | warning | danger (button color + icon, default danger).
document.addEventListener("submit", function (e) {
    var form = e.target;
    if (!form.dataset || !form.dataset.confirm || form.dataset.confirmed) {
        return;
    }
    e.preventDefault();

    var submit = function () {
        form.dataset.confirmed = "1";
        form.submit();
    };

    var variants = {
        success: { color: "--color-success", icon: "question" },
        warning: { color: "--color-pending", icon: "warning" },
        danger: { color: "--color-danger", icon: "warning" },
    };
    var variant = variants[form.dataset.confirmVariant] || variants.danger;
    var color = getComputedStyle(document.documentElement).getPropertyValue(variant.color).trim();

    if (window.Swal) {
        Swal.fire({
            title: form.dataset.confirmTitle || "Are you sure?",
            text: form.dataset.confirm,
            icon: variant.icon,
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmButton || "Yes",
            cancelButtonText: "Cancel",
            confirmButtonColor: color ? "rgb(" + color + ")" : undefined,
            reverseButtons: true,
            focusCancel: true,
        }).then(function (result) {
            if (result.isConfirmed) submit();
        });
    } else if (window.confirm(form.dataset.confirm)) {
        submit();
    }
});
