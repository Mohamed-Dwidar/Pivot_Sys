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

// Colors drop menu (unitmodule::partials.color-select): pick the clicked color and show it + its name on the button.
function pickColorOption(option) {
    // the open menu is moved to <body> by the template, so find the button by id, not by parents
    var radio = option.querySelector(".color-select__radio");
    radio.checked = true;
    var current = document.getElementById(option.dataset.current);
    current.innerHTML = option.querySelector(".color-swatch").outerHTML + " " + option.querySelector("span").textContent;
}

document.addEventListener("click", function (e) {
    var option = e.target.closest && e.target.closest(".color-select__option");
    if (option) {
        pickColorOption(option);
    }
});

document.addEventListener("change", function (e) {
    if (e.target.matches(".color-select__radio")) {
        pickColorOption(e.target.closest(".color-select__option"));
    }
});

// Images gallery (layoutmodule::partials.gallery): open the viewer on a thumbnail and navigate between the images.
function galleryShow(modal, index) {
    var items = modal.querySelectorAll(".gallery-modal__strip-item");
    index = (index + items.length) % items.length;
    modal.dataset.index = index;
    modal.querySelector(".gallery-modal__image").src = items[index].dataset.src;
    modal.querySelector(".gallery-modal__counter").textContent = (index + 1) + " / " + items.length;
    items.forEach(function (item, i) {
        item.classList.toggle("active", i === index);
    });
    items[index].scrollIntoView({ block: "nearest", inline: "center" });
    modal.querySelectorAll(".gallery-modal__nav").forEach(function (nav) {
        nav.hidden = items.length < 2;
    });
}

document.addEventListener("click", function (e) {
    if (!e.target.closest) {
        return;
    }
    var thumb = e.target.closest("[data-gallery-open]");
    if (thumb) {
        var modal = document.getElementById(thumb.dataset.galleryOpen);
        galleryShow(modal, Number(thumb.dataset.index));
        tailwind.Modal.getOrCreateInstance(modal).show();
        return;
    }
    var step = e.target.closest("[data-gallery-step]");
    if (step) {
        var stepModal = step.closest(".gallery-modal");
        galleryShow(stepModal, Number(stepModal.dataset.index) + Number(step.dataset.galleryStep));
        return;
    }
    var go = e.target.closest("[data-gallery-go]");
    if (go) {
        galleryShow(go.closest(".gallery-modal"), Number(go.dataset.galleryGo));
    }
});

document.addEventListener("keydown", function (e) {
    var modal = document.querySelector(".gallery-modal.show");
    if (!modal || (e.key !== "ArrowLeft" && e.key !== "ArrowRight")) {
        return;
    }
    galleryShow(modal, Number(modal.dataset.index) + (e.key === "ArrowRight" ? 1 : -1));
});
