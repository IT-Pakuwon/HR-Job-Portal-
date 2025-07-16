<header class="sticky top-0 before:absolute before:inset-0 before:backdrop-blur-md max-lg:before:bg-white/90 dark:max-lg:before:bg-gray-800/90 before:-z-10 z-30 {{ $variant === 'v2' || $variant === 'v3' ? 'before:bg-white after:absolute after:h-px after:inset-x-0 after:top-full after:bg-gray-200 dark:after:bg-gray-700/60 after:-z-10' : 'max-lg:shadow-xs lg:before:bg-white dark:lg:before:bg-gray-900/90' }} {{ $variant === 'v2' ? 'dark:before:bg-white' : '' }} {{ $variant === 'v3' ? 'dark:before:bg-gray-900' : '' }}">
    <div class=" px-4 sm:px-6 lg:px-8 {{ $variant === 'v2' || $variant === 'v3' ? '' : 'lg:border-b border-gray-200 dark:border-gray-700/60' }}">
        <div class=" flex items-center justify-between h-16 ">

            <!-- Header: Left side -->
            <div class="flex">
                
                <!-- Hamburger button -->
                <button
                    class="text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 lg:hidden"
                    @click.stop="sidebarOpen = !sidebarOpen"
                    aria-controls="sidebar"
                    :aria-expanded="sidebarOpen"
                >
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="5" width="16" height="2" />
                        <rect x="4" y="11" width="16" height="2" />
                        <rect x="4" y="17" width="16" height="2" />
                    </svg>
                </button>

                <div class="sm:flex sm:justify-between sm:items-center">          
                    <div>
                        <h1 class="text-2xl md:text-2xl text-gray-700 dark:text-gray-100 font-semibold">
                            <span id="greeting"></span>
                        </h1>
                    </div>
                </div>

            </div>

            <!-- Header: Right side -->
            <div class="flex items-center space-x-3">




                {{-- <!-- Search Button with Modal -->
                <x-modal-search />

                <!-- Notifications button -->
                <x-dropdown-notifications align="right" />

                <!-- Info button -->
                <x-dropdown-help align="right" /> --}}
                <!-- Dark mode toggle -->
                {{-- <x-theme-toggle />                 --}}

                <!-- Divider -->
                {{-- <hr class="w-px h-6 bg-gray-200 dark:bg-gray-700/60 border-none" /> --}}

                <!-- User button -->
                <x-dropdown-profile align="right" />

            </div>

        </div>
    </div>

    <script>
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
        const userName = "{{ ucwords(strtolower(Auth::user()->name)) }}";// Get the user's full name from Laravel
    
        // Set the greeting message and emoji
        document.getElementById("greeting").innerText = `${greeting}, ${userName} ${emoji}`;
        
    </script>

</header>