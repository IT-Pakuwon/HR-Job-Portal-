            /* =========================================================
             * LOCATION
             * ========================================================= */
            let locationTable = $('#locationsTable').DataTable({
                ajax: "{{ route('tenancy.master.locations.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleLocationStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewLocationBtn ${window.listCls.view}" title="View" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editLocationBtn ${window.tsEditCls}" title="Edit" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'site.sitename',
                        render: (d, type, row) => d ? `${d} (${row.siteid})` : row.siteid
                    },
                    { data: 'locationname' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.location = locationTable;

            $('#loc_siteid').select2({
                width: '100%',
                dropdownParent: $('#locationModal'),
                placeholder: '-- Select Site --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            function loadSiteOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.sites.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    res.data.forEach(function(site) {
                        $select.append(`<option value="${site.siteid}">${site.sitename} (${site.siteid})</option>`);
                    });
                    tsAutoSelect($select, selectedId, res.data.map(s => s.siteid));
                    $select.trigger('change');
                });
            }

            function openLocationModal(mode, data) {
                let readOnly = mode === 'view';
                $('#locationForm')[0].reset();
                $('#loc_id').val('');
                $('#locationModalTitle').text(mode === 'add' ? 'Add Location' : (mode === 'view' ? 'View Location' : 'Edit Location'));
                $('#locationForm input, #locationForm select').prop('disabled', readOnly);
                $('#loc_siteid').prop('disabled', readOnly).trigger('change');
                $('#locationSaveBtn').toggle(!readOnly);

                loadSiteOptions($('#loc_siteid'), data ? data.siteid : null);

                if (data) {
                    $('#loc_id').val(data.id);
                    $('#loc_locationname').val(data.locationname);
                }

                tsOpenModal('locationModal');
            }

            $('#addLocationBtn').click(function() {
                openLocationModal('add', null);
            });

            $(document).on('click', '.viewLocationBtn, .editLocationBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewLocationBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/locations/${id}/edit`, function(d) {
                    hideLoading();
                    openLocationModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data location');
                    console.error(xhr.responseText);
                });
            });

            $('#locationForm').submit(function(e) {
                e.preventDefault();
                let id = $('#loc_id').val();
                let url = id ? `/tenancy/master/locations/${id}` : "{{ route('tenancy.master.locations.store') }}";
                let formData = new FormData(document.getElementById('locationForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('locationModal');
                        locationTable.ajax.reload(null, false);
                        toastSuccess('Location saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan location');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleLocationStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/locations/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { locationTable.ajax.reload(null, false); }
                });
            });

            /* =========================================================
             * FLOOR
             * ========================================================= */
            let floorTable = $('#floorsTable').DataTable({
                ajax: "{{ route('tenancy.master.floors.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleFloorStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewFloorBtn ${window.listCls.view}" title="View" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editFloorBtn ${window.tsEditCls}" title="Edit" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'sitetype' },
                    { data: 'floor' },
                    { data: 'order', render: d => d ?? '-' },
                    { data: 'status', render: statusBadge },
                ]
            });
            window.tsTables.floor = floorTable;

            $('#flr_sitetype').select2({
                width: '100%',
                dropdownParent: $('#floorModal'),
                placeholder: '-- Select Site Type --',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            function loadSiteTypeOptions($select, selectedValue) {
                return $.get("{{ route('tenancy.master.sites.site-types') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(type) {
                        $select.append(`<option value="${type}">${type}</option>`);
                    });
                    tsAutoSelect($select, selectedValue, res.data || []);
                    $select.trigger('change');
                });
            }

            function loadLocationOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.locations.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(loc) {
                        $select.append(`<option value="${loc.id}">${loc.locationname} (${loc.siteid})</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(loc => loc.id));
                    $select.trigger('change');
                });
            }

            function openFloorModal(mode, data) {
                let readOnly = mode === 'view';
                $('#floorForm')[0].reset();
                $('#flr_id').val('');
                $('#floorModalTitle').text(mode === 'add' ? 'Add Floor' : (mode === 'view' ? 'View Floor' : 'Edit Floor'));
                $('#floorForm input, #floorForm select').prop('disabled', readOnly);
                $('#flr_sitetype').prop('disabled', readOnly).trigger('change');
                $('#floorSaveBtn').toggle(!readOnly);

                loadSiteTypeOptions($('#flr_sitetype'), data ? data.sitetype : null);

                if (data) {
                    $('#flr_id').val(data.id);
                    $('#flr_floor').val(data.floor);
                    $('#flr_order').val(data.order);
                }

                tsOpenModal('floorModal');
            }

            $('#addFloorBtn').click(function() {
                openFloorModal('add', null);
            });

            $(document).on('click', '.viewFloorBtn, .editFloorBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewFloorBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/floors/${id}/edit`, function(d) {
                    hideLoading();
                    openFloorModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data floor');
                    console.error(xhr.responseText);
                });
            });

            $('#floorForm').submit(function(e) {
                e.preventDefault();
                let id = $('#flr_id').val();
                let url = id ? `/tenancy/master/floors/${id}` : "{{ route('tenancy.master.floors.store') }}";
                let formData = new FormData(document.getElementById('floorForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('floorModal');
                        floorTable.ajax.reload(null, false);
                        toastSuccess('Floor saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan floor');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleFloorStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/floors/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { floorTable.ajax.reload(null, false); }
                });
            });

            /* =========================================================
             * TENANT
             * ========================================================= */
            // PIC phone as a "start WhatsApp chat" link. wa.me needs digits with
            // country code; local numbers (08.. / 8..) are treated as Indonesian.
            function waPhoneLink(phone) {
                if (!phone) return '-';
                let text = $('<div>').text(phone).html();
                let raw = String(phone).trim();
                let number = raw.replace(/\D/g, '');
                if (!raw.startsWith('+')) {
                    if (number.startsWith('00')) number = number.slice(2);
                    else if (number.startsWith('0')) number = '62' + number.slice(1);
                    else if (number.startsWith('8')) number = '62' + number;
                }
                // Not a usable single number: show it as plain text
                if (number.length < 9 || number.length > 15) return text;

                return `<a href="https://wa.me/${number}" target="_blank" rel="noopener noreferrer" title="Chat on WhatsApp"
                           class="inline-flex items-center gap-1.5 whitespace-nowrap text-green-600 hover:underline dark:text-green-400">
                            <i class="fab fa-whatsapp"></i><span>${text}</span>
                        </a>`;
            }

            // Free-text values go into the cell as HTML, so escape them
            const tntText = d => d ? $('<div>').text(d).html() : '-';
            const tntBadge = colors => d => d ? window.listBadge(colors[d] || 'gray', tntText(d)) : '-';

            // The table is grouped by Type: these first, other types next, tenants without a type last
            const tntTypeOrder = ['Fit Out', 'Vacant', 'Occupied'];
            const tntTypeBadge = tntBadge({ 'Occupied': 'green', 'Fit Out': 'yellow', 'Vacant': 'orange' });
            const tntTypeSortKey = type => {
                if (!type) return '2';
                let i = tntTypeOrder.indexOf(type);
                return i >= 0 ? `0${i}` : `1${type}`;
            };

            function tntGroupRows() {
                let api = this.api();
                let counts = {};
                api.rows({ search: 'applied' }).data().each(r => counts[r.unittype || ''] = (counts[r.unittype || ''] || 0) + 1);

                let previous = null;
                api.rows({ page: 'current' }).every(function() {
                    let type = this.data().unittype || '';
                    if (type === previous) return;
                    previous = type;
                    $(this.node()).before(`<tr class="tnt-group"><td colspan="${api.columns(':visible').count()}">
                            <div>${tntTypeBadge(type) === '-' ? window.listBadge('slate', 'No Type') : tntTypeBadge(type)}
                            <span>${counts[type].toLocaleString('en-US')} tenant${counts[type] > 1 ? 's' : ''}</span></div>
                        </td></tr>`);
                });
            }

            let tenantTable = $('#tenantsTable').DataTable({
                ajax: "{{ route('tenancy.master.tenants.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                // only the table scrolls sideways; search and paging stay in place
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>r<"overflow-x-auto"t>ip',
                // always sorted by Type first, so each group stays together whatever column is sorted
                orderFixed: { pre: [[1, 'asc']] },
                drawCallback: tntGroupRows,
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleTenantStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewTenantBtn ${window.listCls.view}" title="View" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editTenantBtn ${window.tsEditCls}" title="Edit" data-id="${data}"><i class="fas fa-edit"></i></button>                                </div>
                            `;
                        }
                    },
                    {
                        data: 'unittype',
                        orderable: false,
                        render: (d, type) => type === 'display' ? tntTypeBadge(d) : (type === 'filter' ? (d || '') : tntTypeSortKey(d))
                    },
                    { data: 'typestatus', render: tntText },
                    { data: 'storename' },
                    { data: 'periode', render: tntText },
                    { data: 'location.locationname', defaultContent: '-' },
                    { data: 'floor.floor', defaultContent: '-' },
                    { data: 'unit', render: d => d ?? '-' },
                    { data: 'unitsize', render: (d, type) => d === null ? (type === 'sort' ? -1 : '-') : (type === 'display' ? Number(d).toLocaleString('en-US') : Number(d)) },
                    { data: 'categories', render: d => tntText((d || []).map(c => c.name).join(', ')) },
                    { data: 'brandorigin', render: tntText },
                    { data: 'brandtype', render: tntText },
                    { data: 'tenant_company.tenantcompanyname', defaultContent: '-' },
                    { data: 'contractstatus', render: tntBadge({ 'Active': 'green', 'Pending': 'red', 'Expired': 'orange' }) },
                    { data: 'perusahaan', render: tntText },
                    { data: 'picname', render: d => d || '-' },
                    { data: 'picphone', render: (d, type) => type === 'display' ? waPhoneLink(d) : (d || '-') },
                    { data: 'status', render: d => d === 'V' ? window.listBadge('orange', 'Vacant') : statusBadge(d) },
                ]
            });
            window.tsTables.tenant = tenantTable;

            // Floor + Status filters (client-side; floors are listed from the loaded rows)
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'tenantsTable') return true;
                let row = tenantTable.row(dataIndex).data() || {};
                let floor = $('#filterTenantFloor').val(), status = $('#filterTenantStatus').val();
                if (floor && (row.floor && row.floor.floor) !== floor) return false;
                if (status && row.status !== status) return false;
                return true;
            });
            tenantTable.on('xhr.dt', function(e, settings, json) {
                let cur = $('#filterTenantFloor').val();
                let floors = [...new Set(((json && json.data) || []).map(r => r.floor && r.floor.floor).filter(Boolean))]
                    .sort((a, b) => String(a).localeCompare(String(b), undefined, {numeric: true}));
                $('#filterTenantFloor').html('<option value="">All Floors</option>' + floors.map(f => `<option value="${$('<div>').text(f).html()}">${$('<div>').text(f).html()}</option>`).join('')).val(floors.includes(cur) ? cur : '');
            });
            $('#filterTenantFloor, #filterTenantStatus').on('change', function() { tenantTable.draw(); });

            const tenantSelects = '#tnt_location_id, #tnt_floor_id, #tnt_tenantcompanyid, #tnt_categoryids, #tnt_unittype, #tnt_brandtype, #tnt_contractstatus';

            $(tenantSelects).each(function() {
                $(this).select2({
                    width: '100%',
                    dropdownParent: $('#tenantModal'),
                    placeholder: $(this).data('placeholder') || $(this).find('option:first').text(),
                    allowClear: true,
                    containerCssClass: 'ts-select2-container',
                    dropdownCssClass: 'ts-select2-dropdown',
                });
            });

            function loadFloorOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.floors.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(flr) {
                        $select.append(`<option value="${flr.id}">${flr.floor} (${flr.sitetype})</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(flr => flr.id));
                    $select.trigger('change');
                });
            }

            function loadTenantCompanyOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.tenant-companies.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(c) {
                        $select.append(`<option value="${c.id}">${c.tenantcompanyname}</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(c => c.id));
                    $select.trigger('change');
                });
            }

            // Category choices + suggestions for the free-text fields
            function loadTenantFormOptions(data) {
                let $category = $('#tnt_categoryids').empty().trigger('change');
                let $type = $('#tnt_unittype');
                $type.find('option:not(:first)').remove();
                $type.val('').trigger('change');
                // saving before the choices arrive would clear the tenant's Type and categories
                $('#tenantSaveBtn').prop('disabled', true);
                return $.get("{{ route('tenancy.master.tenants.form-options') }}", function(res) {
                    // keep categories the tenant already has, even if they were deactivated since
                    let current = (data && data.categories) || [];
                    let all = res.categories.concat(current.filter(c => !res.categories.some(r => r.id === c.id)));
                    all.forEach(c => $category.append(new Option(c.name, c.id)));
                    $category.val(current.map(c => String(c.id))).trigger('change');

                    let type = (data && data.unittype) || '';
                    let types = res.unittypes.concat(type && !res.unittypes.includes(type) ? [type] : []);
                    types.forEach(t => $type.append(new Option(t, t)));
                    $type.val(type).trigger('change');

                    let fill = (id, values) => $(id).empty().append(values.map(v => new Option(v, v)));
                    fill('#tntTypeStatusList', res.typestatuses);
                    fill('#tntBrandOriginList', res.brandorigins);
                    $('#tenantSaveBtn').prop('disabled', false);
                }).fail(function() {
                    toastError('Failed to load tenant types and categories');
                });
            }

            // Periode month picker: keeps "YYYY-MM" in the hidden input
            const $mp = $('#tntPeriodePicker');
            const mpMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const mpKey = (year, month) => `${year}-${String(month + 1).padStart(2, '0')}`;
            const mpNow = () => { let d = new Date(); return mpKey(d.getFullYear(), d.getMonth()); };
            let mpYear = new Date().getFullYear();

            function mpRender() {
                let selected = $('#tnt_periode').val(), now = mpNow();
                $mp.find('.mp-year strong').text(mpYear);
                $mp.find('.mp-grid').html(mpMonths.map((name, i) => {
                    let key = mpKey(mpYear, i);
                    return `<button type="button" data-value="${key}" class="${key === selected ? 'is-selected' : (key === now ? 'is-now' : '')}">${name.slice(0, 3)}</button>`;
                }).join(''));
            }

            function mpToggle(open) {
                $mp.toggleClass('is-open', open).find('.mp-pop').prop('hidden', !open);
                $mp.find('.mp-field').attr('aria-expanded', open);
            }

            function mpSet(value) {
                value = value || '';
                $('#tnt_periode').val(value);
                let [year, month] = value.split('-');
                $mp.find('.mp-text').text(value ? `${mpMonths[month - 1]} ${year}` : '-- Select Periode --').toggleClass('is-empty', !value);
                mpToggle(false);
            }

            $mp.on('click', '.mp-field', function() {
                let open = !$mp.hasClass('is-open');
                if (open) {
                    mpYear = Number(($('#tnt_periode').val() || mpNow()).slice(0, 4));
                    mpRender();
                }
                mpToggle(open);
                if (open) $mp.find('.mp-pop')[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            });
            $mp.on('click', '.mp-year button', function() { mpYear += Number($(this).data('step')); mpRender(); });
            $mp.on('click', '.mp-grid button', function() { mpSet($(this).data('value')); });
            $mp.on('click', '.mp-clear', () => mpSet(''));
            $mp.on('click', '.mp-now', () => mpSet(mpNow()));
            $mp.on('keydown', function(e) { if (e.key === 'Escape') { mpToggle(false); $mp.find('.mp-field').trigger('focus'); } });
            // close when pressing anywhere outside the picker
            $(document).on('mousedown', function(e) { if (!$(e.target).closest($mp).length) mpToggle(false); });

            function openTenantModal(mode, data) {
                let readOnly = mode === 'view';
                $('#tenantForm')[0].reset();
                $('#tnt_id').val('');
                $('#tenantModalTitle').text(mode === 'add' ? 'Add Tenant' : (mode === 'view' ? 'View Tenant' : 'Edit Tenant'));
                $('#tenantForm input, #tenantForm select').prop('disabled', readOnly);
                $(tenantSelects).prop('disabled', readOnly).trigger('change');
                $('#tenantSaveBtn').toggle(!readOnly);

                loadLocationOptions($('#tnt_location_id'), data ? data.locationid : null);
                loadFloorOptions($('#tnt_floor_id'), data ? data.floorid : null);
                loadTenantCompanyOptions($('#tnt_tenantcompanyid'), data ? data.tenantcompanyid : null);
                loadTenantFormOptions(data);
                // a form reset leaves hidden inputs as they are, so always set it
                mpSet(data && data.periode);
                $mp.find('.mp-field').prop('disabled', readOnly);

                if (data) {
                    ['brandtype', 'contractstatus'].forEach(f => $('#tnt_' + f).val(data[f] || '').trigger('change'));
                    ['typestatus', 'unitsize', 'brandorigin', 'perusahaan'].forEach(f => $('#tnt_' + f).val(data[f]));
                    $('#tnt_id').val(data.id);
                    $('#tnt_storename').val(data.storename);
                    $('#tnt_unit').val(data.unit);
                    $('#tnt_picname').val(data.picname);
                    $('#tnt_picphone').val(data.picphone);
                }

                tsOpenModal('tenantModal');
            }

            $('#addTenantBtn').click(function() {
                openTenantModal('add', null);
            });

            $(document).on('click', '.viewTenantBtn, .editTenantBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewTenantBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/master/tenants/${id}/edit`, function(d) {
                    hideLoading();
                    openTenantModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data tenant');
                    console.error(xhr.responseText);
                });
            });

            $('#tenantForm').submit(function(e) {
                e.preventDefault();
                let id = $('#tnt_id').val();
                let url = id ? `/tenancy/master/tenants/${id}` : "{{ route('tenancy.master.tenants.store') }}";
                let formData = new FormData(document.getElementById('tenantForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('tenantModal');
                        tenantTable.ajax.reload(null, false);
                        toastSuccess('Tenant saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan tenant');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleTenantStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/master/tenants/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { tenantTable.ajax.reload(null, false); }
                });
            });

