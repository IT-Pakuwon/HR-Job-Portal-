@props(['news'])

@php
    $newsData = $news
        ->map(function ($item) {
            // $attachment = $item->attachments->first();
            $attachment = $item->attachments->where('extention', 'jpg')->first();

            // Dapatkan tahun dari created_at
            $year = \Carbon\Carbon::parse($item->created_at)->format('Y');

            // Jika ada attachment, generate URL-nya
            $imageUrl = $attachment
                ? url('/attachments/' . $year . '/' . $attachment->attachfile)
                : '/images/default.jpg';

            return [
                'title' => $item->title,
                'description1' => html_entity_decode(strip_tags($item->description)),
                'description2' => html_entity_decode(
                    strip_tags($item->description, '<p><br><ul><li><ol><b><strong><i><em>'),
                ),
                'image' => $imageUrl,
                'link' => url('/news/' . $item->id),
            ];
        })
        ->values()
        ->toJson();
@endphp
@props(['news'])

@php
    $newsData = $news
        ->map(function ($item) {
            $attachment = $item->attachments->where('extention', 'jpg')->first();
            $year = \Carbon\Carbon::parse($item->created_at)->format('Y');
            $imageUrl = $attachment
                ? url('/attachments/' . $year . '/' . $attachment->attachfile)
                : '/images/default.jpg';

            return [
                'title' => $item->title,
                'description1' => html_entity_decode(strip_tags($item->description)),
                'description2' => html_entity_decode(
                    strip_tags($item->description, '<p><br><ul><li><ol><b><strong><i><em>'),
                ),
                'image' => $imageUrl,
                'link' => url('/news/' . $item->id),
                'date' => \Carbon\Carbon::parse($item->created_at)->format('Y-m-d'),
                'author' => $item->created_user ?? '—',
            ];
        })
        ->values()
        ->toJson();
@endphp

<div class="col-span-12 rounded-2xl bg-white p-6 dark:bg-gray-800">
    <!-- Header -->
    <div class="mb-4 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-bold dark:text-white">📣 Announcement</h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('news') }}"
                class="w-full max-w-xs text-center text-sm font-medium text-blue-600 hover:text-blue-800">
                See More
            </a>
        </div>
    </div>

    <!-- Table Section -->
    <div x-data="carousel({{ $newsData }})" class="relative">
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-100 text-xs font-semibold uppercase dark:bg-gray-700">
                    <tr>
                        <th class="cursor-pointer select-none px-4 py-2 text-left" @click="sort('title')"><span
                                x-html="getSortLabel('title', 'Title')"></span></th>
                        <th class="cursor-pointer select-none px-4 py-2 text-left" @click="sort('date')"><span
                                x-html="getSortLabel('date', 'Created Date')"></span></th>
                        <th class="cursor-pointer select-none px-4 py-2 text-left" @click="sort('author')"><span
                                x-html="getSortLabel('author', 'Author')"></span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-for="(event, index) in paginatedEvents" :key="index">
                        <tr class="cursor-pointer transition hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td @click="openModal(event)"
                                class="px-6 py-4 font-medium text-violet-600 hover:underline dark:text-violet-400"
                                x-text="event.title"></td>
                            <td class="px-6 py-4" x-text="event.date ?? '—'"></td>
                            <td class="px-6 py-4" x-text="event.author ?? '—'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="mt-4 flex items-center justify-between">
            <button @click="goToPage(page - 1)" :disabled="page === 1"
                class="rounded bg-gray-200 px-4 py-1.5 text-sm disabled:opacity-50 dark:bg-gray-700">
                Prev
            </button>

            <div class="text-sm text-gray-600 dark:text-gray-300">
                Page <span x-text="page"></span> of <span x-text="totalPages"></span>
            </div>

            <button @click="goToPage(page + 1)" :disabled="page === totalPages"
                class="rounded bg-gray-200 px-4 py-1.5 text-sm disabled:opacity-50 dark:bg-gray-700">
                Next
            </button>
        </div>

        <!-- Modal -->
        <div x-show="showModal" x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4 backdrop-blur-sm"
            style="display: none">
            <div
                class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white p-6 shadow-lg dark:bg-gray-800">
                <!-- Close Button -->
                <button @click="closeModal()"
                    class="absolute right-3 top-3 text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Modal Content -->
                <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white" x-text="modalData.title"></h2>

                <img :src="modalData.image" alt="News Image"
                    class="mb-4 h-auto max-h-64 w-full rounded-lg object-contain" x-show="modalData.image">

                <div class="space-y-4 text-justify text-sm leading-relaxed text-gray-700 dark:text-gray-300"
                    x-html="modalData.description2"></div>
            </div>
        </div>
    </div>
</div>

{{-- 
<script>
    function carousel(newsData) {
        return {
            currentIndex: 0,
            events: newsData,
            showModal: false,
            modalData: {
                title: '',
                description1: '',
                description2: '',
                image: ''
            },
            truncate(text, limit = 120) {
                if (!text) return '';
                if (text.length <= limit) return text;
                return text.substring(0, text.lastIndexOf(' ', limit)) + '...';
            },
            openModal(event) {
                this.modalData = {
                    title: event.title,
                    description1: event.description1,
                    description2: event.description2,
                    image: event.image
                };
                this.showModal = true;
            },
            closeModal() {
                this.showModal = false;
            },
            next() {
                this.currentIndex = (this.currentIndex + 1) % this.events.length;
            },
            prev() {
                this.currentIndex = (this.currentIndex - 1 + this.events.length) % this.events.length;
            }
        }
    }
</script> 

 --}}
<script>
    function carousel(newsData) {
        return {
            currentIndex: 0,
            events: newsData,
            showModal: false,
            modalData: {
                title: '',
                description1: '',
                description2: '',
                image: ''
            },
            page: 1,
            perPage: 5,
            sortBy: 'date',
            sortDirection: 'desc',

            get sortedEvents() {
                return [...this.events].sort((a, b) => {
                    let valA = a[this.sortBy] ?? '';
                    let valB = b[this.sortBy] ?? '';

                    if (this.sortBy === 'date') {
                        valA = new Date(valA);
                        valB = new Date(valB);
                    }

                    if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1;
                    if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1;
                    return 0;
                });
            },

            get paginatedEvents() {
                const start = (this.page - 1) * this.perPage;
                const end = start + this.perPage;
                return this.sortedEvents.slice(start, end);
            },

            get totalPages() {
                return Math.ceil(this.events.length / this.perPage);
            },

            truncate(text, limit = 120) {
                if (!text) return '';
                if (text.length <= limit) return text;
                return text.substring(0, text.lastIndexOf(' ', limit)) + '...';
            },

            openModal(event) {
                this.modalData = {
                    title: event.title,
                    description1: event.description1,
                    description2: event.description2,
                    image: event.image
                };
                this.showModal = true;
            },

            closeModal() {
                this.showModal = false;
            },

            goToPage(page) {
                if (page >= 1 && page <= this.totalPages) {
                    this.page = page;
                }
            },

            sort(column) {
                if (this.sortBy === column) {
                    this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sortBy = column;
                    this.sortDirection = 'asc';
                }
                this.page = 1;
            },

            getSortLabel(column, label) {
                if (this.sortBy === column) {
                    const arrow = this.sortDirection === 'asc' ? '▲' : '▼';
                    return `${label} <span class='ml-1'>${arrow}</span>`;
                }
                return label;
            }
        }
    }
</script>
