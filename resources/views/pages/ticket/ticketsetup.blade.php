<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- HEADER TAB --}}
        <div
            class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-white/5">

            <button id="tabType"
                class="tab-button active-tab inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition">

                <span class="text-base">🎫</span>

                <span>
                    Ticket Type
                </span>

            </button>

            <button id="tabCategory"
                class="tab-button inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                <span class="text-base">📂</span>

                <span>
                    Category
                </span>

            </button>

            <button id="tabSubcategory"
                class="tab-button inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                <span class="text-base">🧩</span>

                <span>
                    Subcategory
                </span>

            </button>

            <button id="tabPriority"
                class="tab-button inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                <span class="text-base">⚡</span>

                <span>
                    Priority
                </span>

            </button>

            <button id="tabDept"
                class="tab-button inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                <span class="text-base">👨‍💻</span>

                <span>
                    Department PIC
                </span>

            </button>

            <button id="tabWaSetting"
                class="tab-button inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                <span class="text-base">📱</span>

                <span>
                    WhatsApp Setting
                </span>

            </button>

        </div>

        {{-- TYPE PANEL --}}
        <div id="typePanel">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Ticket Type
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage ticket type master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createTypeModal', true)"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">

                        <span>＋</span>

                        <span>
                            Add Type
                        </span>

                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="tableType" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Type</th>
                                <th>Type Name</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- CREATE TYPE MODAL --}}
        <div id="createTypeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Ticket Type
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create ticket type master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createTypeModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="createTypeForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <input type="text" name="ticket_type"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase uppercase shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="IT">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type Name
                            </label>

                            <input type="text" name="ticket_type_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="IT SUPPORT">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#createTypeModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">

                            Save Type

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- EDIT TYPE MODAL --}}
        <div id="editTypeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Ticket Type
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update ticket type master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#editTypeModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="editTypeForm">

                    @csrf
                    @method('PUT')

                    <input type="hidden" id="edit_ticket_type_old">

                    <div class="grid grid-cols-1 gap-5 p-6">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <input type="text" name="ticket_type" id="edit_ticket_type" readonly
                                class="w-full rounded-xl border border-gray-300 bg-gray-100 px-4 py-3 text-sm uppercase shadow-sm dark:border-white/10 dark:bg-white/10 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type Name
                            </label>

                            <input type="text" name="ticket_type_name" id="edit_ticket_type_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status" id="edit_type_status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#editTypeModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">

                            Update Type

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- CATEGORY PANEL --}}
        <div id="categoryPanel" class="hidden">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Ticket Category
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage ticket category master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createCategoryModal', true)"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">

                        <span>＋</span>

                        <span>
                            Add Category
                        </span>

                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="tableCategory" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Category ID</th>
                                <th>Category Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- CREATE CATEGORY MODAL --}}
        <div id="createCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Ticket Category
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create ticket category master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createCategoryModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="createCategoryForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category ID
                            </label>

                            <input type="text" name="ticket_categoryid"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category Name
                            </label>

                            <input type="text" name="ticket_category_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <select name="ticket_type"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Type
                                </option>

                                @foreach ($types as $type)
                                    <option value="{{ $type->ticket_type }}">
                                        {{ $type->ticket_type_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#createCategoryModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700">

                            Save Category

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- EDIT CATEGORY MODAL --}}
        <div id="editCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Ticket Category
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update ticket category master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#editCategoryModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="editCategoryForm">

                    @csrf
                    @method('PUT')

                    <input type="hidden" id="edit_ticket_categoryid_old">

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category ID
                            </label>

                            <input type="text" name="ticket_categoryid" id="edit_ticket_categoryid" readonly
                                class="w-full rounded-xl border border-gray-300 bg-gray-100 px-4 py-3 text-sm uppercase shadow-sm dark:border-white/10 dark:bg-white/10 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category Name
                            </label>

                            <input type="text" name="ticket_category_name" id="edit_ticket_category_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <select name="ticket_type" id="edit_category_ticket_type"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Type
                                </option>

                                @foreach ($types as $type)
                                    <option value="{{ $type->ticket_type }}">
                                        {{ $type->ticket_type_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status" id="edit_category_status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#editCategoryModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700">

                            Update Category

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- SUBCATEGORY PANEL --}}
        <div id="subcategoryPanel" class="hidden">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Ticket Subcategory
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage ticket subcategory master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createSubcategoryModal', true)"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700">

                        <span>＋</span>

                        <span>
                            Add Subcategory
                        </span>

                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="tableSubcategory" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Subcategory ID</th>
                                <th>Subcategory Name</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- CREATE SUBCATEGORY MODAL --}}
        <div id="createSubcategoryModal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Ticket Subcategory
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create ticket subcategory master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createSubcategoryModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="createSubcategoryForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Subcategory ID
                            </label>

                            <input type="text" name="ticket_subcategoryid"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Subcategory Name
                            </label>

                            <input type="text" name="ticket_subcategory_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <select name="ticket_type" id="subcategory_ticket_type"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Type
                                </option>

                                @foreach ($types as $type)
                                    <option value="{{ $type->ticket_type }}">
                                        {{ $type->ticket_type_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category
                            </label>

                            <select name="ticket_categoryid" id="subcategory_category"
                                class="dynamic-select w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Category
                                </option>

                            </select>

                        </div>

                        <div class="md:col-span-2">

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#createSubcategoryModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">

                            Save Subcategory

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- EDIT SUBCATEGORY MODAL --}}
        <div id="editSubcategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Ticket Subcategory
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update ticket subcategory master data.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#editSubcategoryModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="editSubcategoryForm">

                    @csrf
                    @method('PUT')

                    <input type="hidden" id="edit_ticket_subcategoryid_old">

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Subcategory ID
                            </label>

                            <input type="text" name="ticket_subcategoryid" id="edit_ticket_subcategoryid" readonly
                                class="w-full rounded-xl border border-gray-300 bg-gray-100 px-4 py-3 text-sm uppercase shadow-sm dark:border-white/10 dark:bg-white/10 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Subcategory Name
                            </label>

                            <input type="text" name="ticket_subcategory_name" id="edit_ticket_subcategory_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <select name="ticket_type" id="edit_subcategory_ticket_type"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Type
                                </option>

                                @foreach ($types as $type)
                                    <option value="{{ $type->ticket_type }}">
                                        {{ $type->ticket_type_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category
                            </label>

                            <select name="ticket_categoryid" id="edit_subcategory_category"
                                class="dynamic-select w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Category
                                </option>

                            </select>

                        </div>

                        <div class="md:col-span-2">

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status" id="edit_subcategory_status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#editSubcategoryModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">

                            Update Subcategory

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- PRIORITY PANEL --}}
        <div id="priorityPanel" class="hidden">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Ticket Priority
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage ticket priority and SLA.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createPriorityModal', true)"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700">

                        <span>＋</span>

                        <span>
                            Add Priority
                        </span>

                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="tablePriority" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Priority</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>SLA Days</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- CREATE PRIORITY PANEL --}}

        <div id="createPriorityModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Ticket Priority
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create ticket priority and SLA setup.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createPriorityModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="createPriorityForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Priority Code
                            </label>

                            <input type="text" name="ticket_priority"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Priority Name
                            </label>

                            <input type="text" name="ticket_priority_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <select name="ticket_type" id="priority_ticket_type"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Type
                                </option>

                                @foreach ($types as $type)
                                    <option value="{{ $type->ticket_type }}">
                                        {{ $type->ticket_type_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category
                            </label>

                            <select name="ticket_categoryid" id="priority_category"
                                class="dynamic-select w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Category
                                </option>

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                SLA Days
                            </label>

                            <input type="number" min="0" name="ticket_sla_days"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Default Priority
                            </label>

                            <select name="is_default"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="N">
                                    No
                                </option>

                                <option value="Y">
                                    Yes
                                </option>

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#createPriorityModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-amber-700">

                            Save Priority

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- EDIT PRIORITY MODAL --}}
        <div id="editPriorityModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Ticket Priority
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update ticket priority and SLA setup.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#editPriorityModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="editPriorityForm">

                    @csrf
                    @method('PUT')

                    <input type="hidden" id="edit_priority_id">

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Priority Code
                            </label>

                            <input type="text" name="ticket_priority" id="edit_ticket_priority"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Priority Name
                            </label>

                            <input type="text" name="ticket_priority_name" id="edit_ticket_priority_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <select name="ticket_type" id="edit_priority_ticket_type"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Type
                                </option>

                                @foreach ($types as $type)
                                    <option value="{{ $type->ticket_type }}">
                                        {{ $type->ticket_type_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category
                            </label>

                            <select name="ticket_categoryid" id="edit_priority_category"
                                class="dynamic-select w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Category
                                </option>

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                SLA Days
                            </label>

                            <input type="number" min="0" name="ticket_sla_days" id="edit_ticket_sla_days"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Default Priority
                            </label>

                            <select name="is_default" id="edit_is_default"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="N">
                                    No
                                </option>

                                <option value="Y">
                                    Yes
                                </option>

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status" id="edit_priority_status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#editPriorityModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-amber-700">

                            Update Priority

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- DEPT PANEL --}}
        <div id="deptPanel" class="hidden">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Department PIC
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage category department assignment.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createDeptModal', true)"
                        class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-700">

                        <span>＋</span>

                        <span>
                            Add PIC
                        </span>

                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="tableDept" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Department</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- CREATE DEPT MODAL --}}
        <div id="createDeptModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Department PIC
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Assign department PIC for ticket category.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createDeptModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="createDeptForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <select name="ticket_type" id="dept_ticket_type"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Type
                                </option>

                                @foreach ($types as $type)
                                    <option value="{{ $type->ticket_type }}">
                                        {{ $type->ticket_type_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category
                            </label>

                            <select name="ticket_categoryid" id="dept_category"
                                class="dynamic-select w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Category
                                </option>

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Department
                            </label>

                            <select name="department_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Department
                                </option>

                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->department_id }}">
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Username
                            </label>

                            <select name="username"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select User
                                </option>

                                @foreach ($users as $user)
                                    <option value="{{ $user->username }}">
                                        {{ $user->username }} - {{ $user->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="md:col-span-2">

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#createDeptModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-rose-700">

                            Save PIC

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- EDIT DEPARTMENT PIC MODAL --}}
        <div id="editDeptModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Department PIC
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update department PIC assignment.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#editDeptModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="editDeptForm">

                    @csrf
                    @method('PUT')

                    <input type="hidden" id="edit_dept_id">

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Ticket Type
                            </label>

                            <select name="ticket_type" id="edit_dept_ticket_type"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Type
                                </option>

                                @foreach ($types as $type)
                                    <option value="{{ $type->ticket_type }}">
                                        {{ $type->ticket_type_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category
                            </label>

                            <select name="ticket_categoryid" id="edit_dept_category"
                                class="dynamic-select w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Category
                                </option>

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Department
                            </label>

                            <select name="department_id" id="edit_department_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Department
                                </option>

                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->department_id }}">
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Username
                            </label>

                            <select name="username" id="edit_username"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select User
                                </option>

                                @foreach ($users as $user)
                                    <option value="{{ $user->username }}">
                                        {{ $user->username }} - {{ $user->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div class="md:col-span-2">

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status" id="edit_dept_status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">Active</option>
                                <option value="I">Inactive</option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#editDeptModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-rose-700">

                            Update PIC

                        </button>

                    </div>

                </form>

            </div>

        </div>

        {{-- WA SETTING PANEL --}}
        <div id="waSettingPanel" class="hidden">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            WhatsApp Setting
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage WhatsApp notification configuration.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createWaSettingModal', true)"
                        class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700">

                        <span>＋</span>

                        <span>
                            Add Setting
                        </span>

                    </button>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="tableWaSetting" class="display w-full border-collapse text-sm">

                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Company</th>
                                <th>Chat ID</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- CREATE WA SETTING MODAL --}}
        <div id="createWaSettingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add WhatsApp Setting
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Configure WhatsApp destination chat.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#createWaSettingModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="createWaSettingForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Company
                            </label>

                            <select name="cpny_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Company
                                </option>

                                @foreach ($companies as $company)
                                    <option value="{{ $company->cpny_id }}">
                                        {{ $company->cpny_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Chat ID
                            </label>

                            <input type="text" name="chat_id" placeholder="6287875757227@c.us"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">
                                    Active
                                </option>

                                <option value="I">
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#createWaSettingModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-700">

                            Save Setting

                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div id="editWaSettingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit WhatsApp Setting
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update WhatsApp destination chat.
                        </p>

                    </div>

                    <button type="button" onclick="toggleModal('#editWaSettingModal', false)"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="editWaSettingForm">

                    @csrf
                    @method('PUT')

                    <input type="hidden" id="edit_wa_setting_id">

                    <div class="grid grid-cols-1 gap-5 p-6">

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Company
                            </label>

                            <select name="cpny_id" id="edit_cpny_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="">
                                    Select Company
                                </option>

                                @foreach ($companies as $company)
                                    <option value="{{ $company->cpny_id }}">
                                        {{ $company->cpny_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Chat ID
                            </label>

                            <input type="text" name="chat_id" id="edit_chat_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Status
                            </label>

                            <select name="status" id="edit_wa_status"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">

                                <option value="A">
                                    Active
                                </option>

                                <option value="I">
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="toggleModal('#editWaSettingModal', false)"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-green-700">

                            Update Setting

                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>

    <script>
        let tableType;
        let tableCategory;
        let tableSubcategory;
        let tablePriority;
        let tableDept;
        let tableWaSetting;
        $(document).ready(function() {

            tableType = $('#tableType').DataTable(baseTableConfig({

                ajax: routes.type.json,

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'ticket_type',
                        name: 'ticket_type'
                    },
                    {
                        data: 'ticket_type_name',
                        name: 'ticket_type_name'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-right',
                        render: function(data, type, row) {

                            return actionButton(
                                'editType',
                                'deleteType',
                                row.ticket_type
                            );

                        }
                    }
                ],

                searchPlaceholder: 'Search type...'

            }));

            tableCategory = $('#tableCategory').DataTable(baseTableConfig({

                ajax: routes.category.json,

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'ticket_categoryid',
                        name: 'ticket_categoryid'
                    },
                    {
                        data: 'ticket_category_name',
                        name: 'ticket_category_name'
                    },
                    {
                        data: 'ticket_type_name',
                        name: 'ticket_type_name'
                    },
                    {
                        data: 'status_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-right',
                        render: function(data, type, row) {

                            return actionButton(
                                'editCategory',
                                'deleteCategory',
                                row.ticket_categoryid
                            );

                        }
                    }
                ],

                searchPlaceholder: 'Search category...'

            }));

            tableSubcategory = $('#tableSubcategory').DataTable(baseTableConfig({

                ajax: routes.subcategory.json,

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'ticket_subcategoryid',
                        name: 'ticket_subcategoryid'
                    },
                    {
                        data: 'ticket_subcategory_name',
                        name: 'ticket_subcategory_name'
                    },
                    {
                        data: 'ticket_type_name',
                        name: 'ticket_type_name'
                    },
                    {
                        data: 'ticket_category_name',
                        name: 'ticket_category_name'
                    },
                    {
                        data: 'status_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-right',
                        render: function(data, type, row) {

                            return actionButton(
                                'editSubcategory',
                                'deleteSubcategory',
                                row.ticket_subcategoryid
                            );

                        }
                    }
                ],

                searchPlaceholder: 'Search subcategory...'

            }));

            tablePriority = $('#tablePriority').DataTable(baseTableConfig({

                ajax: routes.priority.json,

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'ticket_priority',
                        name: 'ticket_priority'
                    },
                    {
                        data: 'ticket_priority_name',
                        name: 'ticket_priority_name'
                    },
                    {
                        data: 'ticket_type_name',
                        name: 'ticket_type_name'
                    },
                    {
                        data: 'ticket_category_name',
                        name: 'ticket_category_name'
                    },
                    {
                        data: 'ticket_sla_days',
                        name: 'ticket_sla_days'
                    },
                    {
                        data: 'status_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-right',
                        render: function(data, type, row) {

                            return actionButton(
                                'editPriority',
                                'deletePriority',
                                row.id
                            );

                        }
                    }
                ],

                searchPlaceholder: 'Search priority...'

            }));

            tableDept = $('#tableDept').DataTable(baseTableConfig({

                ajax: routes.dept.json,

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'ticket_type_name',
                        name: 'ticket_type_name'
                    },
                    {
                        data: 'ticket_category_name',
                        name: 'ticket_category_name'
                    },
                    {
                        data: 'department_name',
                        name: 'department_name'
                    },
                    {
                        data: 'username',
                        name: 'username'
                    },
                    {
                        data: 'status_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-right',
                        render: function(data, type, row) {

                            return actionButton(
                                'editDept',
                                'deleteDept',
                                row.id
                            );

                        }
                    }
                ],

                searchPlaceholder: 'Search department PIC...'

            }));

            tableWaSetting = $('#tableWaSetting').DataTable(baseTableConfig({

                ajax: routes.waSetting.json,

                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        width: '5%'
                    },
                    {
                        data: 'cpny_name',
                        name: 'cpny_name'
                    },
                    {
                        data: 'chat_id',
                        name: 'chat_id'
                    },
                    {
                        data: 'status_badge',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-right',
                        render: function(data, type, row) {

                            return actionButton(
                                'editWaSetting',
                                'deleteWaSetting',
                                row.id
                            );

                        }
                    }
                ],

                searchPlaceholder: 'Search WhatsApp setting...'

            }));

        });
        $(function() {

            const tabs = {
                tabType: '#typePanel',
                tabCategory: '#categoryPanel',
                tabSubcategory: '#subcategoryPanel',
                tabPriority: '#priorityPanel',
                tabDept: '#deptPanel',
                tabWaSetting: '#waSettingPanel'
            };

            $('.tab-button').on('click', function() {

                $('.tab-button')
                    .removeClass(
                        'active-tab border-blue-200 bg-blue-50 text-blue-700'
                    )
                    .addClass(
                        'border-transparent bg-transparent text-gray-600 dark:text-gray-300'
                    );

                $(this)
                    .removeClass(
                        'border-transparent bg-transparent text-gray-600 dark:text-gray-300'
                    )
                    .addClass(
                        'active-tab border-blue-200 bg-blue-50 text-blue-700'
                    );

                $('#typePanel,#categoryPanel,#subcategoryPanel,#priorityPanel,#deptPanel,#waSettingPanel')
                    .addClass('hidden');

                $(tabs[$(this).attr('id')])
                    .removeClass('hidden');
            });

        });
    </script>
    <script>
        const routes = {

            type: {
                json: "{{ route('ticketsetup.typeJson') }}",
                store: "{{ route('ticketsetup.storeType') }}",
                update: "{{ url('/ticket-setup/update-type') }}",
                delete: "{{ url('/ticket-setup/destroy-type') }}"
            },

            category: {
                json: "{{ route('ticketsetup.categoryJson') }}",
                store: "{{ route('ticketsetup.storeCategory') }}",
                update: "{{ url('/ticket-setup/update-category') }}",
                delete: "{{ url('/ticket-setup/destroy-category') }}"
            },

            subcategory: {
                json: "{{ route('ticketsetup.subcategoryJson') }}",
                store: "{{ route('ticketsetup.storeSubcategory') }}",
                update: "{{ url('/ticket-setup/update-subcategory') }}",
                delete: "{{ url('/ticket-setup/destroy-subcategory') }}"
            },

            priority: {
                json: "{{ route('ticketsetup.priorityJson') }}",
                store: "{{ route('ticketsetup.storePriority') }}",
                update: "{{ url('/ticket-setup/update-priority') }}",
                delete: "{{ url('/ticket-setup/destroy-priority') }}"
            },

            dept: {
                json: "{{ route('ticketsetup.deptJson') }}",
                store: "{{ route('ticketsetup.storeDept') }}",
                update: "{{ url('/ticket-setup/update-dept') }}",
                delete: "{{ url('/ticket-setup/destroy-dept') }}"
            },

            waSetting: {
                json: "{{ route('ticketsetup.waSettingJson') }}",
                store: "{{ route('ticketsetup.storeWaSetting') }}",
                update: "{{ url('/ticket-setup/update-wa-setting') }}",
                delete: "{{ url('/ticket-setup/destroy-wa-setting') }}"
            },

            categoryByType: "{{ url('/ticket-setup/category-by-type') }}"
        };


        function toggleModal(modalId, show = true) {

            const modal = $(modalId);

            if (show) {

                modal
                    .removeClass('hidden')
                    .addClass('flex');

            } else {

                modal
                    .removeClass('flex')
                    .addClass('hidden');

                const form = modal.find('form');

                if (form.length) {

                    form[0].reset();

                    form.find('select').trigger('change');

                }
                modal.find('select.dynamic-select').html(`
                <option value="">
                    Select Category
                </option>
            `);

            }

        }

        function showLoading(title = 'Processing...') {

            Swal.fire({
                title,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

        }

        function showSuccess(message) {

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 1500,
                showConfirmButton: false
            });

        }

        function showError(message = 'Something went wrong') {

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });

        }

        function submitForm({
            form,
            url,
            method = 'POST',
            table = null,
            modal = null,
            loadingText = 'Processing...'
        }) {

            $.ajax({

                url,
                type: method,
                data: form.serialize(),

                beforeSend: function() {

                    form.find('button[type="submit"]')
                        .prop('disabled', true);

                    showLoading(loadingText);

                },

                complete: function() {

                    form.find('button[type="submit"]')
                        .prop('disabled', false);

                },

                success: function(response) {

                    showSuccess(response.message);

                    if (modal) {
                        toggleModal(modal, false);
                    }

                    if (table) {
                        table.ajax.reload(null, false);
                    }

                },

                error: function(xhr) {

                    showError(
                        xhr.responseJSON?.message ??
                        'Something went wrong'
                    );

                }

            });

        }

        function confirmDelete({
            url,
            table,
            title = 'Delete Data?',
            text = 'This data will be deleted.'
        }) {

            Swal.fire({

                title,
                text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Delete'

            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({

                    url,
                    type: 'DELETE',

                    data: {
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(response) {

                        showSuccess(response.message);

                        if (table) {
                            table.ajax.reload(null, false);
                        }

                    },

                    error: function(xhr) {

                        showError(
                            xhr.responseJSON?.message ??
                            'Something went wrong'
                        );

                    }

                });

            });

        }

        function actionButton(editFn, deleteFn, key) {

            const safeKey = String(key).replace(/'/g, "\\'");

            return `
                <div class="flex items-center justify-end gap-2">

                    <button
                        onclick="${editFn}('${safeKey}')"
                        class="rounded-lg bg-amber-500 px-3 py-1 text-xs text-white transition hover:bg-amber-600">

                        Edit

                    </button>

                    <button
                        onclick="${deleteFn}('${safeKey}')"
                        class="rounded-lg bg-red-600 px-3 py-1 text-xs text-white transition hover:bg-red-700">

                        Delete

                    </button>

                </div>
            `;

        }

        function loadCategoryOptions(type, targetSelector, selected = '') {

            if (!type) {

                $(targetSelector).html(`
            <option value="">
                Select Category
            </option>
        `);

                return;
            }

            $(targetSelector).html(`
        <option value="">
            Loading...
        </option>
    `);

            $.ajax({
                url: `${routes.categoryByType}/${type}`,
                type: 'GET',

                success: function(response) {

                    let html = `
                <option value="">
                    Select Category
                </option>
            `;

                    response.forEach(item => {

                        html += `
                    <option value="${item.ticket_categoryid}">
                        ${item.ticket_category_name}
                    </option>
                `;
                    });

                    $(targetSelector).html(html);

                    if (selected) {
                        $(targetSelector).val(selected);
                    }
                },

                error: function() {

                    $(targetSelector).html(`
                <option value="">
                    Select Category
                </option>
            `);

                    showError('Failed to load category data');
                }
            });
        }

        function baseTableConfig({
            ajax,
            columns,
            order = [
                [1, 'asc']
            ],
            searchPlaceholder = 'Search...'
        }) {

            return {

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,

                ajax,
                columns,
                order,

                language: {
                    search: '',
                    searchPlaceholder
                }

            };

        }
    </script>

    <script>
        function editType(ticket_type) {

            let row = tableType.rows().data().toArray().find(x => x.ticket_type == ticket_type);

            if (!row) return;

            $('#edit_ticket_type_old').val(row.ticket_type);
            $('#edit_ticket_type').val(row.ticket_type);
            $('#edit_ticket_type_name').val(row.ticket_type_name);
            $('#edit_type_status').val(row.status);

            toggleModal('#editTypeModal', true);

        }

        function deleteType(ticket_type) {

            confirmDelete({
                url: `${routes.type.delete}/${ticket_type}`,
                table: tableType,
                title: 'Delete Ticket Type?'
            });

        }

        function editCategory(ticket_categoryid) {

            let row = tableCategory.rows().data().toArray().find(x => x.ticket_categoryid == ticket_categoryid);

            if (!row) return;

            $('#edit_ticket_categoryid_old').val(row.ticket_categoryid);
            $('#edit_ticket_categoryid').val(row.ticket_categoryid);
            $('#edit_ticket_category_name').val(row.ticket_category_name);
            $('#edit_category_ticket_type').val(row.ticket_type);
            $('#edit_category_status').val(row.status);

            toggleModal('#editCategoryModal', true);

        }

        function deleteCategory(ticket_categoryid) {

            confirmDelete({
                url: `${routes.category.delete}/${ticket_categoryid}`,
                table: tableCategory,
                title: 'Delete Category?'
            });

        }

        function editSubcategory(ticket_subcategoryid) {

            let row = tableSubcategory.rows().data().toArray()
                .find(x => x.ticket_subcategoryid == ticket_subcategoryid);

            if (!row) return;

            $('#edit_ticket_subcategoryid_old').val(row.ticket_subcategoryid);
            $('#edit_ticket_subcategoryid').val(row.ticket_subcategoryid);
            $('#edit_ticket_subcategory_name').val(row.ticket_subcategory_name);
            $('#edit_subcategory_ticket_type').val(row.ticket_type);
            $('#edit_subcategory_status').val(row.status);

            loadCategoryOptions(
                row.ticket_type,
                '#edit_subcategory_category',
                row.ticket_categoryid
            );

            toggleModal('#editSubcategoryModal', true);

        }

        function deleteSubcategory(ticket_subcategoryid) {

            confirmDelete({
                url: `${routes.subcategory.delete}/${ticket_subcategoryid}`,
                table: tableSubcategory,
                title: 'Delete Subcategory?'
            });

        }

        function editPriority(id) {

            let row = tablePriority.rows().data().toArray()
                .find(x => x.id == id);

            if (!row) return;

            $('#edit_priority_id').val(row.id);
            $('#edit_ticket_priority').val(row.ticket_priority);
            $('#edit_ticket_priority_name').val(row.ticket_priority_name);
            $('#edit_priority_ticket_type').val(row.ticket_type);
            $('#edit_ticket_sla_days').val(row.ticket_sla_days);
            $('#edit_is_default').val(row.is_default);
            $('#edit_priority_status').val(row.status);

            loadCategoryOptions(
                row.ticket_type,
                '#edit_priority_category',
                row.ticket_categoryid
            );

            toggleModal('#editPriorityModal', true);

        }

        function deletePriority(id) {

            confirmDelete({
                url: `${routes.priority.delete}/${id}`,
                table: tablePriority,
                title: 'Delete Priority?'
            });

        }

        function editDept(id) {

            let row = tableDept.rows().data().toArray()
                .find(x => x.id == id);

            if (!row) return;

            $('#edit_dept_id').val(row.id);
            $('#edit_dept_ticket_type').val(row.ticket_type);
            $('#edit_department_id').val(row.department_id);
            $('#edit_username').val(row.username);
            $('#edit_dept_status').val(row.status);

            loadCategoryOptions(
                row.ticket_type,
                '#edit_dept_category',
                row.ticket_categoryid
            );

            toggleModal('#editDeptModal', true);

        }

        function deleteDept(id) {

            confirmDelete({
                url: `${routes.dept.delete}/${id}`,
                table: tableDept,
                title: 'Delete Department PIC?'
            });

        }

        function editWaSetting(id) {

            let row = tableWaSetting.rows().data().toArray()
                .find(x => x.id == id);

            if (!row) return;

            $('#edit_wa_setting_id').val(row.id);
            $('#edit_cpny_id').val(row.cpny_id);
            $('#edit_chat_id').val(row.chat_id);
            $('#edit_wa_status').val(row.status);

            toggleModal('#editWaSettingModal', true);

        }

        function deleteWaSetting(id) {

            confirmDelete({
                url: `${routes.waSetting.delete}/${id}`,
                table: tableWaSetting,
                title: 'Delete WhatsApp Setting?'
            });

        }
        $('#createTypeForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: routes.type.store,
                table: tableType,
                modal: '#createTypeModal',
                loadingText: 'Saving Type...'
            });

        });

        $('#editTypeForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: `${routes.type.update}/${$('#edit_ticket_type_old').val()}`,
                table: tableType,
                modal: '#editTypeModal',
                loadingText: 'Updating Type...'
            });

        });

        $('#createCategoryForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: routes.category.store,
                table: tableCategory,
                modal: '#createCategoryModal',
                loadingText: 'Saving Category...'
            });

        });

        $('#editCategoryForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: `${routes.category.update}/${$('#edit_ticket_categoryid_old').val()}`,
                table: tableCategory,
                modal: '#editCategoryModal',
                loadingText: 'Updating Category...'
            });

        });

        $('#createSubcategoryForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: routes.subcategory.store,
                table: tableSubcategory,
                modal: '#createSubcategoryModal',
                loadingText: 'Saving Subcategory...'
            });

        });

        $('#editSubcategoryForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: `${routes.subcategory.update}/${$('#edit_ticket_subcategoryid_old').val()}`,
                table: tableSubcategory,
                modal: '#editSubcategoryModal',
                loadingText: 'Updating Subcategory...'
            });

        });

        $('#createPriorityForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: routes.priority.store,
                table: tablePriority,
                modal: '#createPriorityModal',
                loadingText: 'Saving Priority...'
            });

        });

        $('#editPriorityForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: `${routes.priority.update}/${$('#edit_priority_id').val()}`,
                table: tablePriority,
                modal: '#editPriorityModal',
                loadingText: 'Updating Priority...'
            });

        });

        $('#createDeptForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: routes.dept.store,
                table: tableDept,
                modal: '#createDeptModal',
                loadingText: 'Saving PIC...'
            });

        });

        $('#editDeptForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: `${routes.dept.update}/${$('#edit_dept_id').val()}`,
                table: tableDept,
                modal: '#editDeptModal',
                loadingText: 'Updating PIC...'
            });

        });


        $('#createWaSettingForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: routes.waSetting.store,
                table: tableWaSetting,
                modal: '#createWaSettingModal',
                loadingText: 'Saving WhatsApp Setting...'
            });

        });

        $('#editWaSettingForm').submit(function(e) {

            e.preventDefault();

            submitForm({
                form: $(this),
                url: `${routes.waSetting.update}/${$('#edit_wa_setting_id').val()}`,
                table: tableWaSetting,
                modal: '#editWaSettingModal',
                loadingText: 'Updating WhatsApp Setting...'
            });

        });
    </script>

</x-app-layout>
