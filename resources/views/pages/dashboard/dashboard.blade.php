<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-2 py-2 sm:px-6 lg:px-2">
        {{-- <div class="px-6 sm:px-6 lg:px-6 py-6 w-full max-w-9xl mx-auto rounded-tl-2xl"> --}}
        {{-- <div x-data="dashboardData()" x-init="init()" class="px-6 sm:px-6 lg:px-6 py-6 w-full max-w-9xl mx-auto rounded-tl-2xl"> --}}
        <!-- Dashboard actions -->
        {{-- <div class="sm:flex sm:justify-between sm:items-center mb-6">          
            <div class="mb-4 sm:mb-4">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">
                    <span id="greeting"></span>
                </h1>
                <p id="message" class="text-lg text-gray-600 dark:text-gray-400 mt-2"></p>
            </div>
        </div> --}}

        <!-- Cards -->
        <div class="grid grid-cols-12 gap-2">
            <x-dashboard.dashboard-agenda :agendas="$agendas" />
            <x-dashboard.dashboard-approval :tr_approval="$tr_approval" />
            <x-dashboard.dashboard-news :news="$news" />

        </div>
    </div>

    {{-- <script>
        function getGreeting() {
        const hour = new Date().getHours();
        let greeting = "Good day"; // Default greeting
        let emoji = "🌞"; // Default emoji
        let message = "Here's everything for you today!"; // Default message

        if (hour < 12) {
            greeting = "Good morning";
            emoji = "☀️";
        } else if (hour >= 12 && hour < 18) {
            greeting = "Good afternoon";
            emoji = "🌤️";
        } else {
            greeting = "Good evening";
            emoji = "🌙";
        }

        return { greeting, emoji, message };
    }

    const { greeting, emoji, message } = getGreeting();

    document.getElementById("greeting").innerText = `${greeting} ${emoji}`;
    document.getElementById("message").innerText = message;

        // function dashboardData() {
        //     return {                            
        //         paginatedGroups: [],              

        //         paginateGroups() {
        //             this.paginatedGroups = this.groups.slice(0, 5);
        //         },                          


        //         // init() {
        //         //     this.fetchTasks();
        //         //     this.fetchAgendas();
        //         // }
        //     };
        // }
    </script> --}}
</x-app-layout>
