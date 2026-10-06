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
 *
 * Each row has id="row-{id}" (DT_RowId from the server). window.dataTables is used by the ajax forms (custom.js)
 * to update / remove one row without reloading the page.
 */
(function (jq) {
    // no browser alert() when the server fails, the table shows a message instead
    jq.fn.dataTable.ext.errMode = 'none';

    // [{ table, filterValues }] of the page
    var lists = [];

    function rowOf(id) {
        var tr = document.getElementById('row-' + id);
        if (!tr) {
            return null;
        }
        var list = lists.find(function (item) { return item.table.table().node().contains(tr); });
        return list ? { tr: tr, list: list } : null;
    }

    function icons() {
        createIcons({ icons: window.icons, 'stroke-width': 1.5, nameAttr: 'data-lucide' });
    }

    // "Showing 1 to 9 of 9 entries" after removing a row (the server sends the counts only on a draw,
    // row().remove() lowers the filtered count, the total is lowered here when the record was deleted)
    function refreshInfo(table, deleted) {
        var settings = table.settings()[0];
        if (deleted) {
            settings._iRecordsTotal = Math.max(0, settings._iRecordsTotal - 1);
        }
        var info = table.page.info();
        var rows = table.table().body().querySelectorAll('tr').length;
        var text = 'Showing ' + (info.start + 1) + ' to ' + (info.start + rows) + ' of ' + info.recordsDisplay + ' entries';
        if (info.recordsDisplay !== info.recordsTotal) {
            text += ' (filtered from ' + info.recordsTotal + ' total entries)';
        }
        var infoEl = table.table().container().querySelector('.dt-info');
        if (infoEl) {
            infoEl.textContent = text;
        }
    }

    window.dataTables = {
        has: function (id) {
            return !!rowOf(id);
        },
        // the filters of the list that shows the row, sent with the form so the server knows if the row still matches
        filters: function (id) {
            var row = rowOf(id);
            return row ? row.list.filterValues() : {};
        },
        // data = the same columns as the list (from the server), null = remove the row
        // (deleted, or it does not match the list filters any more)
        update: function (id, data, deleted) {
            var row = rowOf(id);
            if (!row) {
                return false;
            }
            if (!data) {
                return this.remove(id, deleted);
            }
            row.list.table.row(row.tr).data(data);
            icons();
            row.tr.classList.remove('row-flash');
            void row.tr.offsetWidth;
            row.tr.classList.add('row-flash');
            return true;
        },
        remove: function (id, deleted) {
            var row = rowOf(id);
            if (!row) {
                return false;
            }
            var table = row.list.table;
            row.tr.classList.add('row-removing');
            setTimeout(function () {
                table.row(row.tr).remove();
                row.tr.remove();
                if (!table.table().body().querySelector('tr')) {
                    table.draw(false);   // the page is empty now: load it again (previous page / "no data")
                } else {
                    refreshInfo(table, deleted);
                }
            }, 300);
            return true;
        },
        // reload the rows of every list on the page (after adding), keeps the current page
        reload: function () {
            lists.forEach(function (list) { list.table.draw(false); });
            return lists.length > 0;
        },
    };

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
            drawCallback: icons,
        });

        lists.push({
            table: table,
            filterValues: function () {
                return Object.assign({ search: searchInput ? searchInput.value.trim() : '' }, filterValues());
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
