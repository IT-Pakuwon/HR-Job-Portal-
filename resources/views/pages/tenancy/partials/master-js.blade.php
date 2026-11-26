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
            let tenantTable = $('#tenantsTable').DataTable({
                ajax: "{{ route('tenancy.master.tenants.json') }}",
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
                                        <input type="checkbox" class="toggleTenantStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewTenantBtn ${window.listCls.view}" title="View" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editTenantBtn ${window.tsEditCls}" title="Edit" data-id="${data}"><i class="fas fa-edit"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'location.locationname', defaultContent: '-' },
                    { data: 'floor.floor', defaultContent: '-' },
                    { data: 'tenant_company.tenantcompanyname', defaultContent: '-' },
                    { data: 'storename' },
                    { data: 'unit', render: d => d ?? '-' },
                    { data: 'picname', render: d => d || '-' },
                    { data: 'picphone', render: d => d || '-' },
                    { data: 'status', render: statusBadge },
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

            $('#tnt_location_id, #tnt_floor_id, #tnt_tenantcompanyid').each(function() {
                $(this).select2({
                    width: '100%',
                    dropdownParent: $('#tenantModal'),
                    placeholder: $(this).find('option:first').text(),
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

            function openTenantModal(mode, data) {
                let readOnly = mode === 'view';
                $('#tenantForm')[0].reset();
                $('#tnt_id').val('');
                $('#tenantModalTitle').text(mode === 'add' ? 'Add Tenant' : (mode === 'view' ? 'View Tenant' : 'Edit Tenant'));
                $('#tenantForm input, #tenantForm select').prop('disabled', readOnly);
                $('#tnt_location_id, #tnt_floor_id, #tnt_tenantcompanyid').prop('disabled', readOnly).trigger('change');
                $('#tenantSaveBtn').toggle(!readOnly);

                loadLocationOptions($('#tnt_location_id'), data ? data.locationid : null);
                loadFloorOptions($('#tnt_floor_id'), data ? data.floorid : null);
                loadTenantCompanyOptions($('#tnt_tenantcompanyid'), data ? data.tenantcompanyid : null);

                if (data) {
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

