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
                // the session ended (redirected to the login page): reload, the user logs in again
                if (response.redirected && /\/(login|admin)\/?$/.test(new URL(response.url).pathname)) {
                    window.location.reload();
                    throw null;
                }
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
                initForms(body);
                tailwind.Modal.getOrCreateInstance(modal).show();
                setTimeout(function () {
                    var focus = body.querySelector("[autofocus]");
                    if (focus) focus.focus();
                }, 450);
            })
            .catch(function (error) {
                if (error === null) return;
                // say why (server error code / script error), the details are in the browser console
                console.error("Popup " + url, error);
                var reason = error instanceof Response
                    ? (error.status === 404 ? "it was not found, it may have been deleted" : error.status === 403 ? "you can not open it" : "server error " + error.status)
                    : "script error: " + (error && error.message ? error.message : error);
                toast("Could not open the page (" + reason + ").", "error");
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
//   { modal: url }            -> a form in the popup: the popup shows this page after saving (e.g. back to the reservation view)
//   { logout: true }          -> log out now (the employee's shift popups: closed / still working at logout)
// The X-Shift-Reminder header (PaymentModule ShiftReminder middleware) -> the "close your shift" popup after the action.
// data-row="{id}" = the list row of the record (row-{id}), its list filters are sent as list[...].
// 422 -> the errors are shown under the fields ([data-field="name"]) or at the top of the form.
function clearFormErrors(form) {
    form.querySelectorAll("[data-ajax-error]").forEach(function (el) { el.remove(); });
    form.querySelectorAll(".ts-wrapper.is-invalid").forEach(function (el) { el.classList.remove("is-invalid"); });
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
            // "html_" errors are built (escaped) by the server with links, shown as HTML; the others as text
            messages.forEach(function (message) { summary.push({ text: message, html: key.indexOf("html_") === 0 }); });
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
        wrapper.querySelectorAll(".ts-wrapper").forEach(function (box) { box.classList.add("is-invalid"); });
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
        summary.forEach(function (item) {
            var line = document.createElement("div");
            item.html ? (line.innerHTML = item.text) : (line.textContent = item.text);
            box.appendChild(line);
        });
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

    // the buttons (and the switch of a row toggle) are locked while sending, FormData is already taken
    var buttons = form.querySelectorAll("button[type=submit], [data-toggle-form] input[type=checkbox]");
    buttons.forEach(function (button) { button.disabled = true; });
    form.classList.add("is-sending");
    clearFormErrors(form);
    // a row toggle that fails goes back to its previous position
    var toggle = form.matches("[data-toggle-form]") ? form.querySelector("input[type=checkbox]") : null;
    var failed = function () {
        if (toggle) toggle.checked = !toggle.checked;
    };

    // the attribute, not form.action: a field named "action" (the shift reminder / logout popups) hides that property
    fetch(form.getAttribute("action") || window.location.href, {
        method: "POST",   // PUT / PATCH / DELETE go as _method (Laravel)
        body: data,
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
    })
        .then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (json) {
                return { status: response.status, json: json, reminder: response.headers.get("X-Shift-Reminder") };
            });
        })
        .then(function (result) {
            var json = result.json;
            if (result.status >= 400) {
                failed();
            }
            if (result.status === 422) {
                if (json.errors) {
                    showFormErrors(form, json.errors);
                } else {
                    toast(json.message || "Please check the data.", "error");
                }
                return;
            }
            if (result.status === 404) {
                toast("This record was not found, it may have been deleted.", "error");
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

            if (json.logout) {
                toastAfterRedirect(json.message);
                logoutNow();
                return;
            }

            var inModal = form.closest("#app-modal");
            var tables = window.dataTables;
            var done = false;
            if ("row" in json && rowId && tables && tables.has(rowId)) {
                done = tables.update(rowId, json.row, data.get("_method") === "DELETE");
            } else if (json.reload && tables) {
                done = tables.reload();
            }

            // the Today page: its sections are loaded again (no list rows there, no redirect away)
            if (!done && window.todayPage && window.todayPage.active()) {
                window.todayPage.refresh();
                done = true;
            }

            if (!done && json.redirect) {
                toastAfterRedirect(json.message);
                window.location.href = json.redirect;
                return;
            }
            toast(json.message);
            if (inModal && json.modal) {
                appModal.open(json.modal);
            } else if (result.reminder) {
                appModal.open(result.reminder);
            } else if (inModal) {
                appModal.close();
            }
        })
        .catch(function () {
            failed();
            toast("Could not connect to the server, please try again.", "error");
        })
        .finally(function () {
            buttons.forEach(function (button) { button.disabled = false; });
            form.classList.remove("is-sending");
        });
}

// Employee shift (PaymentModule, docs/payments-plan.md §2b): never a block, popups only.
// - logout form with data-logout-prompt (payments not closed): the popup "Close my shift and log out" / "Log out, I'm still working"
// - [data-shift-reminder] on the page (the shift is open too long): the "close your shift" popup when the page opens
function logoutNow() {
    var form = document.querySelector("form[data-logout-prompt]") || document.querySelector('form[action$="/logout"]');
    if (form) {
        form.submit();   // submit() skips the submit listeners (no prompt again)
    } else {
        window.location.reload();
    }
}

document.addEventListener("submit", function (e) {
    var form = e.target;
    if (form.dataset && form.dataset.logoutPrompt) {
        e.preventDefault();
        appModal.open(form.dataset.logoutPrompt);
    }
});

document.addEventListener("DOMContentLoaded", function () {
    var reminder = document.querySelector("[data-shift-reminder]");
    if (reminder) appModal.open(reminder.dataset.shiftReminder);
});

// ONE date field synced with the list tabs (layoutmodule::partials.date-range-filter + partials.filter-tabs; reservations,
// packages, payments lists). flatpickr range mode (MIT): one day clicked = that day, two = the range; × clears it.
// - a tab click: no dates sent (the tab has its own rule), the field shows the tab's days (data-date-select "Y-m-d[,Y-m-d]")
//   or its text (data-date-label, e.g. "From 14-10-2026"); All = empty
// - days picked: the tab with exactly those days is clicked (today → Today, this month → This Month, cleared → All);
//   other days = the All tab with date_from / date_to, no tab highlighted
// - data-date-default (a list without tabs: the employees' payments): starts on that day, × / Clear go back to it, never empty
// Capture phase: runs before datatables.js reads the filters and reloads the list.
function dateRangeYmd(date) {
    return date.getFullYear() + "-" + String(date.getMonth() + 1).padStart(2, "0") + "-" + String(date.getDate()).padStart(2, "0");
}
function dateRangeTabs(form) {
    return Array.prototype.slice.call(form.querySelectorAll('[data-filter-tab][data-name="tab"]'));
}
// a list without tabs: back to the default day (sent as date_from / date_to)
function dateRangeShowDefault(box) {
    var day = box.dataset.dateDefault;
    box.querySelector('[name="date_from"]').value = day;
    box.querySelector('[name="date_to"]').value = day;
    box._picker.setDate(day, false, "Y-m-d");
    // × only when another day is shown
    box.querySelector("[data-date-range-clear]").hidden = true;
}
// the field shows a tab (its days / its text), sends no dates
function dateRangeShowTab(box, tab) {
    if (box.dataset.dateDefault) return dateRangeShowDefault(box);
    var input = box.querySelector("[data-date-range-input]");
    box.querySelector('[name="date_from"]').value = "";
    box.querySelector('[name="date_to"]').value = "";
    var days = tab ? tab.dataset.dateSelect : "";
    box._picker.setDate(days ? days.split(",") : [], false, "Y-m-d");
    if (!days) input.value = tab ? tab.dataset.dateLabel || "" : "";
    box.querySelector("[data-date-range-clear]").hidden = !input.value;
}
// days picked in the calendar (or cleared by ×)
function dateRangeApply(box, dates) {
    var form = box.closest("form");
    if (!dates.length && box.dataset.dateDefault) {
        box._picker.setDate(box.dataset.dateDefault, false, "Y-m-d");
        dates = box._picker.selectedDates;
    }
    var from = dates.length ? dateRangeYmd(dates[0]) : "";
    var to = dates.length > 1 ? dateRangeYmd(dates[1]) : from;
    var key = from ? from + (to !== from ? "," + to : "") : "";
    var tab = dateRangeTabs(form).find(function (tab) {
        return key ? tab.dataset.dateSelect === key : tab.dataset.tab === "all";
    });
    if (tab) {
        tab.click();
        return;
    }
    // other days: the All tab with the range, no tab highlighted
    var all = dateRangeTabs(form).find(function (tab) { return tab.dataset.tab === "all"; });
    var tabInput = form.querySelector('input[name="tab"]');
    if (tabInput) tabInput.value = all ? all.dataset.value : "all";
    dateRangeTabs(form).forEach(function (tab) { tab.classList.remove("active"); });
    box.querySelector('[name="date_from"]').value = from;
    box.querySelector('[name="date_to"]').value = to;
    box.querySelector("[data-date-range-clear]").hidden = from === box.dataset.dateDefault;
    // datatables.js reloads the list on a change in the form
    box.querySelector('[name="date_from"]').dispatchEvent(new Event("change", { bubbles: true }));
}
document.addEventListener("DOMContentLoaded", function () {
    if (!window.flatpickr) return;
    var arabic = document.documentElement.lang === "ar" && flatpickr.l10ns.ar;
    document.querySelectorAll("[data-date-range]").forEach(function (box) {
        var changed = false;
        box._picker = flatpickr(box.querySelector("[data-date-range-input]"), {
            // data-date-single: one day only (the employees' payments list)
            mode: box.hasAttribute("data-date-single") ? "single" : "range",
            dateFormat: "d-m-Y",
            locale: Object.assign({}, arabic || {}, { rangeSeparator: " → " }),
            onChange: function () { changed = true; },
            onClose: function (dates) {
                if (changed) dateRangeApply(box, dates);
                changed = false;
            },
        });
        var form = box.closest("form");
        // flatpickr's own change of the shown text: not a filter change (datatables.js reloads only on dateRangeApply())
        box.querySelector("[data-date-range-input]").addEventListener("change", function (e) { e.stopPropagation(); });
        dateRangeShowTab(box, form.querySelector('[data-filter-tab][data-name="tab"].active'));
        box.querySelector("[data-date-range-clear]").addEventListener("click", function () {
            box._picker.clear(false);
            dateRangeApply(box, []);
        });
        // the Clear button: back to the default tab / day (datatables.js empties the hidden inputs after the browser reset,
        // then reloads; the server uses the same default day for an empty one)
        form.addEventListener("reset", function () {
            setTimeout(function () {
                dateRangeShowTab(box, form.querySelector('[data-filter-tab][data-name="tab"][data-value=""]'));
                // datatables.js: recount the Clear button
                form.dispatchEvent(new Event("input"));
            });
        });
    });
});
document.addEventListener("click", function (e) {
    var tab = e.target.closest && e.target.closest('[data-filter-tab][data-name="tab"]');
    var box = tab && tab.form && tab.form.querySelector("[data-date-range]");
    if (box && box._picker) dateRangeShowTab(box, tab);
}, true);

// Row toggles (layoutmodule::partials.row-toggle): save as soon as the switch is clicked
document.addEventListener("change", function (e) {
    var form = e.target.closest && e.target.closest("form[data-toggle-form]");
    if (form && e.target.type === "checkbox") {
        form.requestSubmit();
    }
});

// Repeated rows (e.g. employee attachments): [data-repeat] > [data-repeat-list] + <template data-repeat-template> (__INDEX__)
// + [data-repeat-add] button; [data-repeat-remove] inside a row removes it.
document.addEventListener("click", function (e) {
    if (!e.target.closest) return;
    var add = e.target.closest("[data-repeat-add]");
    if (add) {
        var box = add.closest("[data-repeat]");
        var template = box.querySelector("template[data-repeat-template]");
        box.dataset.next = Number(box.dataset.next || box.querySelectorAll("[data-repeat-list] [data-repeat-row]").length) + 1;
        box.querySelector("[data-repeat-list]").insertAdjacentHTML("beforeend", template.innerHTML.replace(/__INDEX__/g, box.dataset.next));
        createIcons({ icons: icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
        return;
    }
    var remove = e.target.closest("[data-repeat-remove]");
    if (remove) {
        remove.closest("[data-repeat-row]").remove();
    }
});

// ------------------------------------------------------------------
// Dynamic forms (run on page load and when the popup content is loaded: initForms(root))
//  - [data-show-if="checkbox name"]: shown only while the checkbox is checked
//    [data-show-if="field=value other"]: shown only while the field (a select) has one of the values ("field=" = empty)
//  - [data-disable-if="checkbox name"]: disabled (and emptied) while the checkbox is checked,
//    data-default-from="field": its value when enabled again and empty
//  - select[data-options-url][data-parent="field"]: its options are loaded by ajax (?field=value) when the parent changes
//    (data-also="other_field ...": their values are sent too),
//    with a loading spinner; it is disabled while the parent is empty; the json is [{id, name, ...}], the other keys become data-*
//  - Reservation form ([data-reservation-form]): package -> member (locked), plan -> amount, discount % / discount value / net, unit capacity -> people, subscription type -> repeat options
//  - Package price ([data-package-price]): discount % <-> after discount (from the amount)
//  - Payment form ([data-payment-form]): "for" (reservation / package) -> the member's list is loaded again
// ------------------------------------------------------------------
function formField(form, name) {
    return form.querySelector('[name="' + name + '"]:not([type=hidden])') || form.querySelector('[name="' + name + '"]');
}

function applyConditions(form) {
    form.querySelectorAll("[data-show-if]").forEach(function (el) {
        var rule = el.dataset.showIf.split("=");
        var field = formField(form, rule[0]);
        el.hidden = rule.length > 1
            ? !(field && rule[1].split(" ").indexOf(field.value) >= 0)
            : !(field && field.checked);
    });
    form.querySelectorAll("[data-disable-if]").forEach(function (el) {
        var checkbox = formField(form, el.dataset.disableIf);
        var off = !!(checkbox && checkbox.checked);
        el.disabled = off;
        if (off) {
            el.value = "";
        } else if (!el.value && el.dataset.defaultFrom) {
            var from = formField(form, el.dataset.defaultFrom);
            el.value = from ? from.value : "";
        }
    });
}

function loadOptions(select) {
    var form = select.form;
    var parent = formField(form, select.dataset.parent);
    var wrapper = select.closest("[data-field]") || select.parentNode;
    var placeholder = "- Select -";
    var token = (select._request = (select._request || 0) + 1);
    // a searchable select (Tom Select) is rebuilt after its options change
    var searchable = !!select.tomselect;
    if (searchable) select.tomselect.destroy();
    var done = function () {
        if (searchable) initSearchable(select.parentNode);
        // the next drop menus in the chain follow
        select.dispatchEvent(new Event("change", { bubbles: true }));
    };

    select.innerHTML = "";
    if (!parent || !parent.value) {
        select.add(new Option(placeholder, ""));
        select.disabled = true;
        done();
        return;
    }

    select.add(new Option("Loading...", ""));
    select.disabled = true;
    wrapper.classList.add("is-loading");

    var url = select.dataset.optionsUrl + (select.dataset.optionsUrl.indexOf("?") < 0 ? "?" : "&") + encodeURIComponent(select.dataset.parent) + "=" + encodeURIComponent(parent.value);
    // data-also="field another_field": their values are sent too (e.g. the units of the space AND the subscription type)
    (select.dataset.also || "").split(" ").filter(Boolean).forEach(function (name) {
        var field = formField(form, name);
        url += "&" + encodeURIComponent(name) + "=" + encodeURIComponent(field ? field.value : "");
    });
    fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" } })
        .then(function (response) {
            if (!response.ok) throw response;
            return response.json();
        })
        .then(function (items) {
            if (token !== select._request) return;
            select.innerHTML = "";
            select.add(new Option(items.length ? placeholder : select.dataset.none || "Nothing to select", ""));
            items.forEach(function (item) {
                var option = new Option(item.name, item.id);
                Object.keys(item).forEach(function (key) {
                    if (key !== "id" && key !== "name") option.dataset[key] = item[key];
                });
                select.add(option);
            });
            select.disabled = items.length === 0;
        })
        .catch(function () {
            if (token !== select._request) return;
            select.innerHTML = "";
            select.add(new Option("Could not load, change the selection again", ""));
            toast("Could not load the list, please try again.", "error");
        })
        .finally(function () {
            if (token !== select._request) return;
            wrapper.classList.remove("is-loading");
            done();
        });
}

function round2(number) {
    return Math.round((Number(number) || 0) * 100) / 100;
}

// $source: the field the user changed (amount | percentage | value | net); discount % / discount value / net calculate each other,
// discount_type keeps the one typed last (when the amount changes it stays and the others follow)
function reservationCalculate(form, source) {
    var amountEl = formField(form, "amount");
    var percentageEl = formField(form, "discount_percentage");
    var valueEl = formField(form, "discount_value");
    var typeEl = form.querySelector('[name="discount_type"]');
    var netEl = form.querySelector("[data-net]");
    if (!amountEl || !percentageEl || !valueEl || !typeEl || !netEl) return;

    if (source === "percentage" || source === "value" || source === "net") typeEl.value = source;
    var amount = Math.max(0, Number(amountEl.value) || 0);
    var value;
    if (typeEl.value === "net") {
        value = amount - Math.min(Math.max(0, Number(netEl.value) || 0), amount);
    } else if (typeEl.value === "value") {
        value = Math.min(Math.max(0, Number(valueEl.value) || 0), amount);
    } else {
        value = amount * Math.min(Math.max(0, Number(percentageEl.value) || 0), 100) / 100;
    }
    // the typed field is left as it is
    if (source !== "percentage") percentageEl.value = round2(amount > 0 ? value / amount * 100 : 0);
    if (source !== "value") valueEl.value = round2(value);
    if (source !== "net") netEl.value = round2(amount - value);
}

// the package member is used and can not be changed
function reservationLockMember(form) {
    var packageEl = formField(form, "package_id");
    var memberEl = formField(form, "member_id");
    // the Renew form: both are fixed hidden inputs
    if (!packageEl || !memberEl || !packageEl.selectedOptions) return;
    var option = packageEl.selectedOptions[0];
    var memberId = option && option.dataset.memberId;
    var searchable = memberEl.tomselect;
    if (memberId) {
        searchable ? searchable.setValue(memberId, true) : (memberEl.value = memberId);
    }
    if (searchable) {
        memberId ? searchable.disable() : searchable.enable();
    } else {
        memberEl.disabled = !!memberId;
    }
}

// the end date can not be before the start date: it follows the start when needed
function reservationEndAfterStart(form) {
    var start = formField(form, "start_at");
    var end = formField(form, "end_at");
    if (start && end && !end.disabled && start.value && end.value && end.value < start.value) {
        end.value = start.value;
    }
}

// the end follows the plan's lease period from the start (one period): hour = start + 1 hour; day = the same day; week = +6 days;
// month / year = the same day of the next month / year − 1 day (31-01 → 27-02 / 28-02: the short month's last day − 1).
// Runs when the plan or the start changes; an end typed by hand is kept (data-end-touched) until the plan changes again.
function reservationEndFromPlan(form, planChanged) {
    var planEl = formField(form, "plan_id");
    var startEl = formField(form, "start_at");
    var endEl = formField(form, "end_at");
    var option = planEl && planEl.selectedOptions[0];
    var period = option && option.dataset.leasePeriod;
    if (planChanged) delete form.dataset.endTouched;
    if (!period || !startEl || !startEl.value || !endEl || form.dataset.endTouched) return;

    var parts = startEl.value.split("-").map(Number);
    var pad = function (n) { return (n < 10 ? "0" : "") + n; };
    var ymd = function (d) { return d.getFullYear() + "-" + pad(d.getMonth() + 1) + "-" + pad(d.getDate()); };
    var end = new Date(parts[0], parts[1] - 1, parts[2]);
    var months = { month: 1, year: 12 }[period];
    if (period === "hour") {
        var startTime = formField(form, "start_time");
        var endTime = formField(form, "end_time");
        if (!startTime || !startTime.value || !endTime) return;
        var hm = startTime.value.split(":").map(Number);
        end.setHours(hm[0] + 1, hm[1]);
        endTime.value = pad(end.getHours()) + ":" + pad(end.getMinutes());
    } else if (months) {
        var target = new Date(end.getFullYear(), end.getMonth() + months, 1);
        target.setDate(Math.min(end.getDate(), new Date(target.getFullYear(), target.getMonth() + 1, 0).getDate()));
        end = target;
        end.setDate(end.getDate() - 1);
    } else if (period === "week") {
        end.setDate(end.getDate() + 6);
    }
    endEl.value = ymd(end);
}

// the repeat section is shown only when the subscription type allows it
// ([data-type-allows="can-repeat"] + the option data-can-repeat); hidden = unchecked
function reservationTypeOptions(form) {
    var typeEl = formField(form, "subscription_type_id");
    if (!typeEl) return;
    var option = typeEl.selectedOptions ? typeEl.selectedOptions[0] : null;
    form.querySelectorAll("[data-type-allows]").forEach(function (box) {
        var key = box.dataset.typeAllows.replace(/-([a-z])/g, function (m, c) { return c.toUpperCase(); });
        var allowed = !!(option && option.dataset[key] === "1");
        box.hidden = !allowed;
        if (!allowed) {
            box.querySelectorAll("input[type=checkbox]").forEach(function (checkbox) { checkbox.checked = false; });
        }
    });
    applyConditions(form);
}

// the amount of the selected plan; a time based plan: the plan amount x the periods from the start to the end
// (data-period-hours: the hours of one lease period, e.g. 2.5 hours x 50 = 125; the same calculation as Plan::amountBetween)
function reservationPlanAmount(form) {
    var planEl = formField(form, "plan_id");
    var amountEl = formField(form, "amount");
    var option = planEl && planEl.selectedOptions[0];
    if (!option || option.dataset.amount === undefined || !amountEl) return;

    var amount = Number(option.dataset.amount) || 0;
    if (option.dataset.timeBased === "1") {
        var at = function (date, time) {
            var d = formField(form, date), t = formField(form, time);
            return d && d.value && t && t.value ? new Date(d.value + "T" + t.value) : null;
        };
        var start = at("start_at", "start_time");
        var end = at("end_at", "end_time");
        if (start && end) {
            var hours = Math.max(0, (end - start) / 3600000);
            amount = amount * round2(hours / (Number(option.dataset.periodHours) || 1));
        }
    }
    amountEl.value = round2(amount);
    reservationCalculate(form, "amount");
}

// a time based plan (option data-time-based="1"): the start / end time inputs are shown ([data-time-fields])
function reservationTimeBased(form) {
    var planEl = formField(form, "plan_id");
    if (!planEl) return;
    var option = planEl.selectedOptions[0];
    var timeBased = !!(option && option.dataset.timeBased === "1");
    form.querySelectorAll("[data-time-fields]").forEach(function (box) {
        box.hidden = !timeBased;
    });
    // a new reservation at night (before the business day's cutoff): a time based one is for now (data-real-date), a full day
    // one for the business date (the day before) — only the dates the user did not change
    form.querySelectorAll("input[data-business-date][data-real-date]").forEach(function (input) {
        if (input.value === input.dataset.businessDate || input.value === input.dataset.realDate) {
            input.value = timeBased ? input.dataset.realDate : input.dataset.businessDate;
        }
    });
}

// the number of people follows the unit capacity: max = capacity, a unit for 1 person locks it to 1
function reservationCapacity(form) {
    var unitEl = formField(form, "unit_id");
    var peopleEl = formField(form, "number_of_peoples");
    if (!unitEl || !peopleEl) return;
    var option = unitEl.selectedOptions[0];
    var capacity = option && option.dataset.capacity ? Number(option.dataset.capacity) : 0;
    // the Renew form: a fixed hidden value
    if (peopleEl.type === "hidden") return;

    peopleEl.max = capacity > 0 ? capacity : 100000;
    peopleEl.readOnly = capacity === 1;
    peopleEl.tabIndex = capacity === 1 ? -1 : 0;
    if (capacity === 1) peopleEl.value = 1;
    // the hint under the field (the div after the input, not an error)
    var hint = peopleEl.closest("[data-field]").querySelector("input ~ div:not([data-ajax-error])");
    if (hint) hint.textContent = capacity === 1 ? "This unit is for 1 person." : capacity > 0 ? "Up to " + capacity + " persons." : "Not more than the unit capacity.";
}

// $source: the field the user changed (amount | discount | result); the discount % and the after discount calculate each other
function packageCalculate(box, source) {
    var discountEl = box.querySelector('[data-calc="discount"]');
    var resultEl = box.querySelector('[data-calc="result"]');
    var typeEl = box.querySelector('[data-calc="type"]');
    if (source === "discount") typeEl.value = "percentage";
    if (source === "result") typeEl.value = "after";

    var amount = Math.max(0, Number(box.querySelector('[data-calc="amount"]').value) || 0);
    if (typeEl.value === "after") {
        var after = Math.min(Math.max(0, Number(resultEl.value) || 0), amount);
        discountEl.value = round2(amount > 0 ? (amount - after) / amount * 100 : 0);
        if (source !== "result") resultEl.value = round2(after);
    } else {
        var discount = Math.min(Math.max(0, Number(discountEl.value) || 0), 100);
        resultEl.value = round2(amount - amount * discount / 100);
    }
}

// select[data-searchable]: type to search the options (e.g. members by name or mobile: the option text is "name - phone")
function initSearchable(root) {
    if (!window.TomSelect) return;
    root.querySelectorAll("select[data-searchable]").forEach(function (select) {
        if (select.tomselect) return;
        var searchable = new TomSelect(select, {
            plugins: { dropdown_input: {} },
            maxOptions: null,
            allowEmptyOption: true,
            placeholder: select.options[0] ? select.options[0].text : "",
            render: {
                no_results: function () {
                    return '<div class="no-results">Nothing found</div>';
                },
            },
        });
        // the search box in the open menu
        if (searchable.control_input) searchable.control_input.placeholder = "Search...";
    });
}

function initForms(root) {
    initSearchable(root);
    (root.tagName === "FORM" ? [root] : root.querySelectorAll("form")).forEach(function (form) {
        applyConditions(form);
        if (form.matches("[data-reservation-form]")) {
            reservationLockMember(form);
            reservationCapacity(form);
            reservationTypeOptions(form);
            reservationTimeBased(form);
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initForms(document);
});

document.addEventListener("change", function (e) {
    var form = e.target.form;
    if (!form || !e.target.name) return;
    var name = e.target.name;

    if (form.querySelector('[data-show-if="' + name + '"], [data-show-if^="' + name + '="], [data-disable-if="' + name + '"]')) {
        applyConditions(form);
    }
    form.querySelectorAll('select[data-options-url][data-parent="' + name + '"]').forEach(loadOptions);

    // the member's reservations / packages follow "for" too (the member is the data-parent)
    if (form.matches("[data-payment-form]") && name === "payable") {
        form.querySelectorAll('select[data-options-url][data-also~="payable"]').forEach(loadOptions);
    }

    if (form.matches("[data-reservation-form]")) {
        if (name === "package_id") reservationLockMember(form);
        if (name === "unit_id") reservationCapacity(form);
        if (name === "subscription_type_id") reservationTypeOptions(form);
        if (name === "start_at") reservationEndAfterStart(form);
        if (name === "plan_id") reservationTimeBased(form);
        // the end follows the plan's lease period (an end typed by hand is kept until the plan changes)
        if (name === "end_at" || name === "end_time") form.dataset.endTouched = "1";
        if (["plan_id", "start_at", "start_time"].indexOf(name) >= 0) reservationEndFromPlan(form, name === "plan_id");
        // the amount follows the plan (and the start / end of a time based plan)
        if (["plan_id", "start_at", "start_time", "end_at", "end_time"].indexOf(name) >= 0) reservationPlanAmount(form);
    }
});

document.addEventListener("input", function (e) {
    var form = e.target.form;
    if (!form) return;
    if (form.matches("[data-reservation-form]")) {
        var sources = { amount: "amount", discount_percentage: "percentage", discount_value: "value", net_amount: "net" };
        if (sources[e.target.name]) reservationCalculate(form, sources[e.target.name]);
        if (e.target.name === "start_at") reservationEndAfterStart(form);
    }
    var box = e.target.closest("[data-package-price]");
    if (box && e.target.dataset.calc) packageCalculate(box, e.target.dataset.calc);
});

// Reservation status menu (reservationmodule::Reservation.partials.status-menu): the chosen status is saved by ajax,
// the badge is locked with a spinner until the answer, then it gets the new name + color (and the list row is updated)
document.addEventListener("click", function (e) {
    var option = e.target.closest && e.target.closest("[data-status-option]");
    if (!option) return;
    var toggle = document.getElementById(option.dataset.toggle);
    if (!toggle || toggle.classList.contains("is-sending")) return;

    var data = new FormData();
    data.append("_method", "PATCH");
    data.append("_token", document.querySelector('meta[name="csrf-token"]').content);
    data.append("reservation_status_id", option.dataset.statusId);
    // the list filters: the row leaves the list when it does not match them any more
    if (window.dataTables) {
        var filters = window.dataTables.filters(option.dataset.row);
        Object.keys(filters).forEach(function (name) { data.append("list[" + name + "]", filters[name]); });
    }

    toggle.classList.add("is-sending");
    toggle.disabled = true;
    fetch(option.dataset.url, { method: "POST", body: data, headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" } })
        .then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (json) { return { ok: response.ok, json: json }; });
        })
        .then(function (result) {
            var json = result.json;
            if (!result.ok || !json.status) {
                // "html_" errors (built and escaped by the server, with links: e.g. the slot is taken) stay open until closed
                if (json.errors && Object.keys(json.errors)[0].indexOf("html_") === 0 && window.Swal) {
                    Swal.fire({ icon: "error", title: "Can't change the status", html: [].concat(Object.values(json.errors)[0])[0] });
                    return;
                }
                var errors = json.errors ? Object.values(json.errors)[0] : null;
                toast((errors && errors[0]) || json.message || "Could not change the status, please try again.", "error");
                return;
            }
            // the new badge
            toggle.className = json.status.class + " status-menu__toggle";
            toggle.querySelector("[data-status-name]").textContent = json.status.name;
            // the check mark on the chosen status
            document.querySelectorAll('[data-status-option][data-toggle="' + option.dataset.toggle + '"]').forEach(function (item) {
                var check = item.querySelector("[data-lucide='check'], svg.lucide-check");
                if (check) check.remove();
                if (item.dataset.statusId === String(json.status.id)) {
                    item.insertAdjacentHTML("beforeend", '<i data-lucide="check" class="ml-auto h-4 w-4 text-slate-500"></i>');
                }
            });
            createIcons({ icons: icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
            if (window.dataTables && "row" in json) window.dataTables.update(option.dataset.row, json.row);
            if (window.todayPage && window.todayPage.active()) window.todayPage.refresh();
            toast(json.message);
        })
        .catch(function () {
            toast("Could not connect to the server, please try again.", "error");
        })
        .finally(function () {
            toggle.classList.remove("is-sending");
            toggle.disabled = false;
        });
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


// Today page ([data-today], ReservationModule Today/*): todayPage.refresh() loads its sections again (data-url) after every save
// in a popup (ajaxSubmit, status menu); Timeline | List per type ([data-today-toggle], remembered in localStorage, optional);
// the now line of today's timelines (.tl[data-tl-start] = the first hour, unix seconds; 15 minute slots, custom.css .tl-from-N)
window.todayPage = (function () {
    function root() {
        return document.querySelector("[data-today]");
    }
    function remembered(type) {
        try {
            return localStorage.getItem("todayView." + type);
        } catch (e) {
            return null;
        }
    }
    function remember(type, view) {
        try {
            localStorage.setItem("todayView." + type, view);
        } catch (e) { /* private mode: not remembered */ }
    }
    function showView(toggle, view) {
        var section = toggle.closest(".today-section");
        toggle.querySelectorAll("[data-view]").forEach(function (button) {
            button.classList.toggle("active", button.dataset.view === view);
        });
        section.querySelectorAll("[data-today-view]").forEach(function (box) {
            box.hidden = box.dataset.todayView !== view;
        });
    }
    function applyViews(box) {
        box.querySelectorAll("[data-today-toggle]").forEach(function (toggle) {
            var view = remembered(toggle.dataset.todayToggle);
            showView(toggle, view === "list" ? "list" : "timeline");
        });
    }
    function nowLines(box) {
        box.querySelectorAll(".tl[data-tl-start]").forEach(function (timeline) {
            var slots = Number(timeline.dataset.tlSlots);
            var slot = Math.floor((Date.now() / 1000 - Number(timeline.dataset.tlStart)) / 900) + 1;
            timeline.querySelectorAll("[data-tl-now]").forEach(function (line) {
                line.className = line.className.replace(/\btl-from-\d+\b/g, "").trim();
                line.hidden = slot < 1 || slot > slots;
                if (!line.hidden) line.classList.add("tl-from-" + slot);
            });
        });
    }
    function init(box) {
        applyViews(box);
        nowLines(box);
    }

    document.addEventListener("click", function (e) {
        var button = e.target.closest && e.target.closest("[data-today-toggle] [data-view]");
        if (!button) return;
        var toggle = button.closest("[data-today-toggle]");
        remember(toggle.dataset.todayToggle, button.dataset.view);
        showView(toggle, button.dataset.view);
    });

    document.addEventListener("DOMContentLoaded", function () {
        var box = root();
        if (!box) return;
        init(box);
        setInterval(function () { nowLines(box); }, 60000);
    });

    return {
        active: function () {
            return !!root();
        },
        refresh: function () {
            var box = root();
            if (!box) return;
            fetch(box.dataset.url, { headers: { "X-Requested-With": "XMLHttpRequest", Accept: "text/html" } })
                .then(function (response) {
                    if (!response.ok) throw response;
                    return response.text();
                })
                .then(function (html) {
                    box.innerHTML = html;
                    createIcons({ icons: icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
                    init(box);
                })
                .catch(function () {
                    toast("Could not refresh the page, please reload it.", "error");
                });
        },
    };
})();
