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

// Forms with data-confirm="message" open a confirm modal before submitting (approve, reject, delete, save, ...).
// Optional: data-confirm-title, data-confirm-button (confirm button text),
// data-confirm-variant = success | warning | danger (button color + icon, default danger).
// Forms with data-ajax are sent by ajax (ajaxSubmit), the others normally.
function confirmForm(form, onConfirm) {
    var variants = {
        success: { color: "--color-success", icon: "question" },
        warning: { color: "--color-pending", icon: "warning" },
        danger: { color: "--color-danger", icon: "warning" },
    };
    var variant = variants[form.dataset.confirmVariant] || variants.danger;
    var color = getComputedStyle(document.documentElement).getPropertyValue(variant.color).trim();

    if (!window.Swal) {
        if (window.confirm(form.dataset.confirm)) onConfirm();
        return;
    }
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
        if (result.isConfirmed) onConfirm();
    });
}

document.addEventListener("submit", function (e) {
    var form = e.target;
    if (!form.dataset || form.dataset.confirmed || (!form.dataset.confirm && form.dataset.ajax === undefined)) {
        return;
    }
    e.preventDefault();
    // the browser checks (required, email, ...) before asking
    if (!form.reportValidity()) {
        return;
    }

    var send = function () {
        if (form.dataset.ajax !== undefined) {
            ajaxSubmit(form);
        } else {
            form.dataset.confirmed = "1";
            form.submit();
        }
    };
    form.dataset.confirm ? confirmForm(form, send) : send();
});

// Messages: small notification at the top (also after a redirect, kept in sessionStorage)
function toast(message, icon) {
    if (!message) return;
    if (!window.Swal) {
        alert(message);
        return;
    }
    Swal.fire({ toast: true, position: "top-end", icon: icon || "success", title: message, showConfirmButton: false, timer: 3000, timerProgressBar: true });
}

function toastAfterRedirect(message) {
    try {
        sessionStorage.setItem("toast", message || "");
    } catch (e) {}
}

window.addEventListener("load", function () {
    try {
        var message = sessionStorage.getItem("toast");
        sessionStorage.removeItem("toast");
        if (message) toast(message);
    } catch (e) {}
});

// The popup (#app-modal): <a href="..." data-modal> loads the page by ajax (it renders with layoutmodule::modal).
// Links inside the popup with data-modal replace its content (view -> edit).
var appModal = {
    el: function () {
        return document.getElementById("app-modal");
    },
    open: function (url) {
        var modal = this.el();
        var body = modal.querySelector(".app-modal__body");
        return fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest", Accept: "text/html" } })
            .then(function (response) {
                if (!response.ok) throw response;
                return response.text();
            })
            .then(function (html) {
                // viewers (gallery) of the previous content were moved to <body> by the template
                document.querySelectorAll("body > .gallery-modal:not(.show)").forEach(function (old) { old.remove(); });
                body.innerHTML = html;
                var content = body.firstElementChild;
                var size = content && content.dataset.modalSize ? content.dataset.modalSize : "md";
                modal.querySelector(".app-modal__dialog").dataset.size = size;
                createIcons({ icons: icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
                tailwind.Modal.getOrCreateInstance(modal).show();
                setTimeout(function () {
                    var focus = body.querySelector("[autofocus]");
                    if (focus) focus.focus();
                }, 450);
            })
            .catch(function () {
                toast("Could not open the page, please try again.", "error");
            });
    },
    close: function () {
        var modal = this.el();
        if (modal && modal.classList.contains("show")) {
            tailwind.Modal.getOrCreateInstance(modal).hide();
        }
    },
};

document.addEventListener("click", function (e) {
    var link = e.target.closest && e.target.closest("a[data-modal]");
    // ctrl / cmd / middle click: open the full page in a new tab as usual
    if (!link || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) {
        return;
    }
    e.preventDefault();
    appModal.open(link.href);
});

// Ajax forms (data-ajax). The server answers JSON:
//   { message, row: {...} }  -> update the row of the list (null = remove it, e.g. deleted)
//   { message, reload: true } -> reload the lists of the page (added)
//   { message, redirect }     -> where to go when the row / list is not on this page (full page forms)
//   { counts: { name: n } }   -> new numbers for [data-counter="name"] (tabs, titles, sidebar badges with data-hide-zero)
// data-row="{id}" = the list row of the record (row-{id}), its list filters are sent as list[...].
// 422 -> the errors are shown under the fields ([data-field="name"]) or at the top of the form.
function clearFormErrors(form) {
    form.querySelectorAll("[data-ajax-error]").forEach(function (el) { el.remove(); });
    form.querySelectorAll("[data-error-marked]").forEach(function (el) {
        el.classList.replace("border-danger", el.dataset.errorMarked);
        delete el.dataset.errorMarked;
    });
}

function showFormErrors(form, errors) {
    var template = document.getElementById("field-error-template");
    var summary = [];
    Object.keys(errors).forEach(function (key) {
        var name = key.split(".")[0];
        var wrapper = form.querySelector('[data-field="' + name + '"]');
        var messages = [].concat(errors[key]);
        if (!wrapper) {
            summary = summary.concat(messages);
            return;
        }
        wrapper.querySelectorAll("input:not([type=hidden]):not([type=checkbox]):not([type=radio]), select, textarea, button[data-tw-toggle]").forEach(function (input) {
            ["border-slate-200", "border-slate-300/80"].forEach(function (border) {
                if (input.classList.contains(border)) {
                    input.classList.replace(border, "border-danger");
                    input.dataset.errorMarked = border;
                }
            });
        });
        messages.forEach(function (message) {
            var error = template.content.firstElementChild.cloneNode(true);
            error.textContent = message;
            wrapper.appendChild(error);
        });
    });
    if (summary.length) {
        var box = document.createElement("div");
        box.setAttribute("role", "alert");
        box.dataset.ajaxError = "";
        box.className = "alert relative border rounded-md px-5 py-4 bg-danger border-danger bg-opacity-20 border-opacity-5 text-danger mb-5";
        box.textContent = summary.join(" ");
        form.prepend(box);
    }
    var first = form.querySelector("[data-ajax-error]");
    if (first) first.scrollIntoView({ block: "center", behavior: "smooth" });
}

function ajaxSubmit(form) {
    var data = new FormData(form);
    var rowId = form.dataset.row;
    if (rowId && window.dataTables) {
        var filters = window.dataTables.filters(rowId);
        Object.keys(filters).forEach(function (name) { data.append("list[" + name + "]", filters[name]); });
    }

    var buttons = form.querySelectorAll("button[type=submit]");
    buttons.forEach(function (button) { button.disabled = true; });
    form.classList.add("is-sending");
    clearFormErrors(form);

    fetch(form.action, {
        method: "POST",   // PUT / PATCH / DELETE go as _method (Laravel)
        body: data,
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
    })
        .then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (json) {
                return { status: response.status, json: json };
            });
        })
        .then(function (result) {
            var json = result.json;
            if (result.status === 422) {
                if (json.errors) {
                    showFormErrors(form, json.errors);
                } else {
                    toast(json.message || "Please check the data.", "error");
                }
                return;
            }
            if (result.status >= 400) {
                toast(json.message && result.status < 500 ? json.message : "Something went wrong, please try again.", "error");
                return;
            }

            Object.keys(json.counts || {}).forEach(function (name) {
                document.querySelectorAll('[data-counter="' + name + '"]').forEach(function (el) {
                    el.textContent = json.counts[name];
                    if (el.hasAttribute("data-hide-zero")) el.hidden = !Number(json.counts[name]);
                });
            });

            var inModal = form.closest("#app-modal");
            var tables = window.dataTables;
            var done = false;
            if ("row" in json && rowId && tables && tables.has(rowId)) {
                done = tables.update(rowId, json.row, data.get("_method") === "DELETE");
            } else if (json.reload && tables) {
                done = tables.reload();
            }

            if (!done && json.redirect) {
                toastAfterRedirect(json.message);
                window.location.href = json.redirect;
                return;
            }
            toast(json.message);
            if (inModal) appModal.close();
        })
        .catch(function () {
            toast("Could not connect to the server, please try again.", "error");
        })
        .finally(function () {
            buttons.forEach(function (button) { button.disabled = false; });
            form.classList.remove("is-sending");
        });
}

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
