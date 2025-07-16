<x-app-layout>
    <div class="mt-6 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-lg">
          
        <table id="screensTable" class="w-full border-collapse table-fixed">
            <thead class="bg-white dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-center w-32">DocID</th>                    
                    <th class="px-4 py-3 text-left">Apply Date</th>
                    <th class="px-4 py-3 text-left">Job Title</th>
                    <th class="px-4 py-3 text-center w-32">Step</th>
                    <th class="px-4 py-3 text-center w-32">Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <script>
        $(document).ready(function () {
            let table = $('#screensTable').DataTable({
                ajax: "{{ route('myjobapply.json') }}",
                processing: true,
                serverSide: false,                
                columns: [                   
                    {
                        data: 'docid',
                        className: 'no-pointer',
                        render: function (data, type, row) {
                            return `<a href="/showjob/${data}" class="text-blue-600 hover:underline font-semibold">${data}</a>`;
                        }
                    },          
                    { data: 'apply_date',className: 'no-pointer'  },          
                    { data: 'job_title',className: 'no-pointer'  },                    
                    {
                        data: 'apply_step', className: 'no-pointer',
                        render: function (data) {                            
                            let labelMap = {
                                'JOAPP': 'Job Apply',
                                'WIHC': 'Waiting Interview HC',
                                'IHC': 'Interview HC',
                                'WIU': 'Waiting Interview User',
                                'IU': 'Interview User',
                                'WPT': 'Waiting Psycho Test',
                                'PT': 'Psycho Test',
                                'OFF': 'Offering',
                                'JOIN': 'Join'
                            };

                            let label = labelMap[data] || data;

                            return `<span class="w-full max-w-40 bg-gray-300/30 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold px-3 py-1 rounded text-center block">${label}</span>`;
                        }
                    },                   
                    {
                        data: 'status', className: 'no-pointer',
                        render: function (data) {
                            console.log('Row data:', data);
                            let statusText = "";
                            let badgeClass = "";
    
                           if (data == 'P') {
                                statusText = "On Progress";
                                badgeClass = "w-32 bg-blue-300/30 dark:bg-blue-300 text-blue-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            } else if (data == 'C') {
                                statusText = "Completed";
                                badgeClass = "w-32 bg-green-300/30 dark:bg-green-300 text-green-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            } else if (data == 'X') {
                                statusText = "Cancel";
                                badgeClass = "w-32 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            } else if (data == 'R') {
                                statusText = "Rejected";
                                badgeClass = "w-32 bg-red-300/30 dark:bg-red-300 text-red-600 focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            } else {
                                statusClass = "  w-full max-w-32 bg-gray-300/30  bg-gray-300  text-gray-600 flex justify-items-center  focus:outline-none pointer-events-none border-none font-semibold px-4 py-2 text-center rounded";
                            }
                            return `<span class="${badgeClass}">${statusText}</span>`;
                        }
    
                    }
                ]
            });
        
            
        
        });
    </script>    
    
</x-app-layout>
