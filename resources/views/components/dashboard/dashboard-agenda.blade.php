@props(['agendas'])

<div x-data="agendaViewer()"
    class="col-span-full flex h-[45vh] flex-col rounded-xl bg-white p-4 sm:col-span-12 xl:col-span-5 dark:bg-gray-800">
    {{-- Header --}}
    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
        <div>
            <h1 class="text-lg font-bold dark:text-white">📅 Today's Agenda</h1>
            <p class="text-m ml-8 mt-2 dark:text-white">See what's your task for today!</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('agendas') }}"
                class="w-full max-w-xs text-center text-xs font-medium text-blue-600 hover:text-blue-800">
                See More
            </a>
        </div>
    </div>

    {{-- Daftar Agenda --}}
    <div class="mt-4 overflow-x-auto rounded-lg bg-white p-2 dark:bg-gray-800">
        @if (count($agendas) > 0)
            @foreach ($agendas as $agenda)
                <div class="hover: mb-2 cursor-pointer rounded-lg bg-gray-100 p-4 shadow-sm transition-all duration-200 hover:bg-indigo-50 dark:bg-gray-700 dark:hover:bg-gray-600"
                    @click="openModal({
                        title: '{{ addslashes($agenda->title) }}',
                        time: '{{ \Carbon\Carbon::parse($agenda->startdate)->format('H:i') }} - {{ \Carbon\Carbon::parse($agenda->enddate)->format('H:i') }}',
                        description: '{{ addslashes($agenda->description ?? '') }}',
                        participant: '{{ addslashes($agenda->participant ?? '') }}'
                    })">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-white">{{ $agenda->title }}</h2>
                    <p class="mt-1 flex items-center text-xs text-gray-500 dark:text-gray-300">
                        ⏰ {{ \Carbon\Carbon::parse($agenda->startdate)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($agenda->enddate)->format('H:i') }}
                    </p>
                </div>
            @endforeach
        @else
            <div class="py-6 text-center text-sm text-gray-400">
                📭 No agenda today. Take a breather!
            </div>
        @endif
    </div>

    {{-- More Agenda Link --}}
    <div class="mt-4 text-center">
        <a href="/agendas" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">
            See More Agenda...
        </a>
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4  "
        style="display: none">
        <div class="relative w-full max-w-2xl rounded-xl bg-white p-4 dark:bg-gray-800">
            {{-- Close Button --}}
            <button @click="closeModal()"
                class="absolute right-3 top-3 text-gray-400 hover:text-gray-600 dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            {{-- Content --}}
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white" x-text="modalData.title"></h2>

            <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                <div>
                    <span class="font-semibold">⏰ Time:</span>
                    <span x-text="modalData.time"></span>
                </div>

                <template x-if="modalData.description">
                    <div>
                        <span class="font-semibold">📄 Description:</span>
                        <p class="mt-1 whitespace-normal break-words" x-text="modalData.description"></p>
                    </div>
                </template>

                <template x-if="modalData.participant">
                    <div>
                        <span class="font-semibold">👥 Participants:</span>
                        <p class="mt-1 whitespace-normal break-words" x-text="modalData.participant"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function agendaViewer() {
        return {
            showModal: false,
            modalData: {
                title: '',
                time: '',
                description: '',
                participant: ''
            },
            openModal(data) {
                this.modalData = data;
                this.showModal = true;
            },
            closeModal() {
                this.showModal = false;
            }
        }
    }
</script>
