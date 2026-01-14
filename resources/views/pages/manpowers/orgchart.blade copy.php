<x-app-layout>
    @php
    $currentPage = Route::currentRouteName() == 'manpowers' ? 'HR' : '';
    @endphp
    <div class="px-2 sm:px-6 lg:px-2 w-full py-0 max-w-9xl mx-auto">
        {{-- <div class="px-4 sm:px-6 lg:px-8 w-full max-w-9xl mx-auto"> --}}
        <!-- Dashboard actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8"></div>
        <!-- Breadcrumb dengan Dropdown -->
        
        <div class="grid">
            <style>
                .no-border{
                    border : none !important;
                }
                .grid {
                    width: 100%;
                }
            
                select, textarea, input {
                    width: 100%; /* Make all input elements take full width */
                }
            
                table.dataTable {
                    width: 100% !important;
                }
            
                .dataTables_wrapper {
                    width: 100%;
                }
            
                @media (max-width: 600px) {
                    .dataTables_wrapper {
                        padding: 0 10px;
                    }
                }
                #manpowersTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start; /* Aligns items to the left */
                    align-items: center; /* Vertically aligns items */
                    }
            
                #manpowersTable_filter label {
                    margin-right: 2px;
                }
            
                #manpowersTable_filter input {
                    width: 200px; /* Adjust the width of the input box */
                    }
            
            
                #manpowersTable_wrapper {
                    margin-top: 20px;
                    width: 100%;
                }
            
                /* Prevent text from wrapping */
                #manpowersTable td {
                    white-space: nowrap;        /* Prevent text from wrapping */
                    overflow: hidden;           /* Hide overflow content */
                    text-overflow: ellipsis;    /* Display ellipsis ("...") for overflowing content */
                }
            
                /* Optional: Adjust the width for table cells */
                #manpowersTable th, #manpowersTable td {
                    padding: 10px; /* Adjust padding for better appearance */
                    max-width: 200px;  /* You can set a maximum width to control overflow */
                }
            
            
                #manpowersTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }
            
                #manpowersTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                }
            
                #manpowersTable_length select option {
                    padding: 5px; /* Mengatur jarak antar opsi */
                }
            
                #manpowersTable_info{
                    margin-top:10px;
                    margin-bottom:10px;
                }
            
                .dataTables_paginate {
                    margin-top:10px;
                    margin-bottom:10px;
            
                }
                #manpowersTable tbody tr td {
                    padding: 8px 8px; /* Adjust padding for uniform height */
                    line-height: 2; /* Optional, for better text alignment */
                }
            
                #manpowersTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }
            
                #manpowersTable tbody tr:hover {
                    background-color: #8f8f8f11;
                        opacity: 100%;
                        cursor: pointer;
                }
            
                #manpowersTable tbody tr:hover td {
                    color: black;
                }
            
            
            </style>
            <style>
                /* ✅ Custom Switch Button */
                .switch {
                    position: relative;
                    display: inline-block;
                    width: 40px;
                    height: 22px;
                }
                .switch input {
                    opacity: 0;
                    width: 0;
                    height: 0;
                }
                .slider {
                    position: absolute;
                    cursor: pointer;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: #ccc;
                    transition: .4s;
                    border-radius: 34px;
                }
                .slider:before {
                    position: absolute;
                    content: "";
                    height: 16px;
                    width: 16px;
                    left: 3px;
                    bottom: 3px;
                    background-color: white;
                    transition: .4s;
                    border-radius: 50%;
                }
                input:checked + .slider {
                    background-color: #4CAF50;
                }
                input:checked + .slider:before {
                    transform: translateX(18px);
                }
                
                /* ✅ Memperkecil Lebar Kolom Actions */
                #manpowersTable th:nth-child(1), #manpowersTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }
                #manpowersTable th:nth-child(4), #manpowersTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }
            </style>
            {{-- <div class="mt-6  overflow-y-auto bg-white  dark:bg-gray-800 p-4 rounded-xl">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h1 class=" align-middle text-lg font-bold  dark:text-white">Org Chart</h1>                    
                           
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg">
                    <div class="chart-container" style="width: 100%; height: 800px; margin-top: 30px;"></div>
                </div>   
            </div>   --}}
            <div class="chart-container" style="width: 100%; height: 800px;"></div>

                       
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://d3js.org/d3.v7.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/d3-org-chart@3.1.0"></script>
            <script src="https://cdn.jsdelivr.net/npm/d3-flextree@2.1.2/build/d3-flextree.js"></script>

            <script>
            var chart = null;

            $(document).ready(function () {
                $.ajax({
                url: "{{ route('orgchart.data') }}", // route dari Laravel
                type: "GET",
                dataType: "json",
                success: function (data) {
                    chart = new d3.OrgChart()
                    .nodeHeight((d) => 110)
                    .nodeWidth((d) => 222)
                    .childrenMargin((d) => 50)
                    .compactMarginBetween((d) => 35)
                    .compactMarginPair((d) => 30)
                    .neighbourMargin((a, b) => 20)
                    .nodeContent(function (d, i, arr, state) {
                        const color = '#FFFFFF';
                        const imageDiffVert = 25 + 2;
                        return `
                        <div style='width:${d.width}px;height:${d.height}px;padding-top:${imageDiffVert - 2}px;padding-left:1px;padding-right:1px'>
                            <div style="font-family: 'Inter', sans-serif;background-color:${color}; margin-left:-1px;width:${d.width - 2}px;height:${d.height - imageDiffVert}px;border-radius:10px;border: ${
                            d.data._highlighted || d.data._upToTheRootHighlighted
                                ? '5px solid #E27396"'
                                : '1px solid #E4E2E9"'
                            }>
                            <div style="display:flex;justify-content:flex-end;margin-top:5px;margin-right:8px">#${d.data.id}</div>
                            <div style="background-color:${color};margin-top:${-imageDiffVert - 20}px;margin-left:${15}px;border-radius:100px;width:50px;height:50px;" ></div>
                            <div style="margin-top:${-imageDiffVert - 20}px;">
                                <img src="${d.data.image}" style="margin-left:20px;border-radius:100px;width:40px;height:40px;" />
                            </div>
                            <div style="font-size:15px;color:#08011E;margin-left:20px;margin-top:10px">${d.data.name}</div>
                            <div style="color:#716E7B;margin-left:20px;margin-top:3px;font-size:10px;">${d.data.position}</div>
                            </div>
                        </div>
                        `;
                    })
                    .container('.chart-container')
                    .data(data)
                    .render();
                },
                error: function () {
                    alert("Failed to load org chart data");
                }
                });
            });
            </script>
    
        </div>
    </div>
</x-app-layout>
