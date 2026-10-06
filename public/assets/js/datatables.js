/*
 * Server side DataTables for every list (yajra/laravel-datatables on the server).
 * Loaded by layoutmodule::partials.datatable-assets (after jQuery + DataTables).
 *
 * <table class="data-table" data-datatable data-url="{{ route(...) }}" data-filters="#my-filters"
 *        data-order='[[1, "asc"]]' data-empty="No items found.">
 *   <thead><tr>
 *     <th data-data="name_html" data-name="name">Name</th>          (data-name = column used to order)
 *     <th data-data="actions" data-orderable="false"></th>
 *     <th data-data="created_at" data-class="whitespace-nowrap">Added</th>
 *   </tr></thead>
 * </table>
 *
 * The filters form (optional):
 *   - input[name=search]                -> DataTables search, while typing (Enter = now)
 *   - any other select / input          -> sent with the request, applied on change
 *   - [data-filter-tab][data-name][data-value] buttons -> set the hidden input with that name (status tabs)
 *   - [data-clear-button] (type=reset)  -> clears everything, disabled when nothing is applied, [data-count] badge
 */
(function (jq) {
    // no browser alert() when the server fails, the table shows a message instead
    jq.fn.dataTable.ext.errMode = 'none';

    function initDataTable(tableEl) {
        var form = tableEl.dataset.filters ? document.querySelector(tableEl.dataset.filters) : null;
        var searchInput = form ? form.querySelector('[name=search]') : null;
        var clearButton = form ? form.querySelector('[data-clear-button]') : null;

        var columns = Array.prototype.map.call(tableEl.querySelectorAll('thead th'), function (th) {
            return {
                data: th.dataset.data,
                name: th.dataset.name || th.dataset.data,
                orderable: th.dataset.orderable !== 'false' && !!th.dataset.name,
                searchable: false,
                className: th.dataset.class || '',
            };
        });

        // the filters to send (all named fields except the search box)
        function filterValues() {
            var values = {};
            if (form) {
                Array.prototype.forEach.call(form.elements, function (el) {
                    if (el.name && el.name !== 'search' && el.type !== 'submit' && el.type !== 'reset' && el.type !== 'button') {
                        values[el.name] = el.value;
                    }
                });
            }
            return values;
        }

        var table = jq(tableEl).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: tableEl.dataset.url,
                data: function (d) {
                    Object.assign(d, filterValues());
                },
            },
            columns: columns,
            order: tableEl.dataset.order ? JSON.parse(tableEl.dataset.order) : [],
            pageLength: Number(tableEl.dataset.pageLength || 15),
            lengthMenu: [10, 15, 25, 50, 100],
            search: { search: searchInput ? searchInput.value : '' },
            layout: { topStart: null, topEnd: null, bottomStart: ['pageLength', 'info'], bottomEnd: 'paging' },
            language: {
                emptyTable: tableEl.dataset.empty || 'No data found.',
                zeroRecords: 'Nothing matches your search.',
                processing: 'Loading...',
                lengthMenu: '_MENU_ per page',
            },
            drawCallback: function () {
                createIcons({ icons: icons, 'stroke-width': 1.5, nameAttr: 'data-lucide' });
            },
        });

        table.on('error', function (e, settings, techNote, message) {
            console.error('DataTables:', message);
            var columnsCount = tableEl.querySelectorAll('thead th').length;
            tableEl.querySelector('tbody').innerHTML = '<tr><td colspan="' + columnsCount + '" class="empty-state text-danger">Could not load the data, please try again.</td></tr>';
        });

        if (!form) {
            return;
        }

        function updateClearButton() {
            if (!clearButton) {
                return;
            }
            var count = (searchInput && searchInput.value.trim() ? 1 : 0);
            var values = filterValues();
            Object.keys(values).forEach(function (name) {
                count += values[name] !== '' ? 1 : 0;
            });
            clearButton.disabled = count === 0;
            var badge = clearButton.querySelector('[data-count]');
            if (badge) {
                badge.textContent = count;
                badge.hidden = count === 0;
            }
        }

        // search while typing (small delay), Enter = now
        var timer;
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () { table.search(searchInput.value).draw(); }, 400);
            });
        }
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            clearTimeout(timer);
            table.search(searchInput ? searchInput.value : '').draw();
            updateClearButton();
        });

        // selects / other inputs
        form.addEventListener('change', function (e) {
            if (e.target !== searchInput) {
                table.draw();
            }
            updateClearButton();
        });
        form.addEventListener('input', updateClearButton);

        // status tabs: set the hidden input of the same name
        form.querySelectorAll('[data-filter-tab]').forEach(function (tab) {
            tab.addEventListener('click', function () {
                form.querySelector('input[type=hidden][name="' + tab.dataset.name + '"]').value = tab.dataset.value;
                form.querySelectorAll('[data-filter-tab][data-name="' + tab.dataset.name + '"]').forEach(function (other) {
                    other.classList.toggle('active', other === tab);
                });
                table.draw();
                updateClearButton();
            });
        });

        form.addEventListener('reset', function () {
            setTimeout(function () {
                // hidden inputs are not reset by the browser
                form.querySelectorAll('input[type=hidden]').forEach(function (input) { input.value = ''; });
                form.querySelectorAll('[data-filter-tab]').forEach(function (tab) { tab.classList.toggle('active', tab.dataset.value === ''); });
                clearTimeout(timer);
                table.search('').draw();
                updateClearButton();
            });
        });

        updateClearButton();
    }

    document.querySelectorAll('table[data-datatable]').forEach(initDataTable);
})(jQuery.noConflict(true));
