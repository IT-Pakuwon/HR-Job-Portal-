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

                    <button type="button" onclick="openCreateTypeModal()"
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

                    <button type="button" onclick="closeCreateTypeModal()"
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

                        <button type="button" onclick="closeCreateTypeModal()"
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

                    <button type="button" onclick="closeEditTypeModal()"
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

                        <button type="button" onclick="closeEditTypeModal()"
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

                    <button type="button" onclick="openCreateCategoryModal()"
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

                    <button type="button" onclick="closeCreateCategoryModal()"
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

                        <button type="button" onclick="closeCreateCategoryModal()"
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

                    <button type="button" onclick="closeEditCategoryModal()"
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

                        <button type="button" onclick="closeEditCategoryModal()"
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

                    <button type="button" onclick="openCreateSubcategoryModal()"
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

                    <button type="button" onclick="closeCreateSubcategoryModal()"
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
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

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

                        <button type="button" onclick="closeCreateSubcategoryModal()"
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

                    <button type="button" onclick="closeEditSubcategoryModal()"
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
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

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

                        <button type="button" onclick="closeEditSubcategoryModal()"
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

                    <button type="button" onclick="openCreatePriorityModal()"
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

                    <button type="button" onclick="closeCreatePriorityModal()"
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
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

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

                        <button type="button" onclick="closeCreatePriorityModal()"
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

                    <button type="button" onclick="closeEditPriorityModal()"
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
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

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

                        <button type="button" onclick="closeEditPriorityModal()"
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

                    <button type="button" onclick="openCreateDeptModal()"
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

                    <button type="button" onclick="closeCreateDeptModal()"
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
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

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

                        <button type="button" onclick="closeCreateDeptModal()"
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

                    <button type="button" onclick="closeEditDeptModal()"
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
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

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

                        <button type="button" onclick="closeEditDeptModal()"
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


    </div>

    <script>
        let tableType;
        let tableCategory;
        let tableSubcategory;
        let tablePriority;
        let tableDept;

        $(document).ready(function() {

            tableType = $('#tableType').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,

                ajax: "{{ route('ticketsetup.typeJson') }}",

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

                            return `
                            <div class="flex items-center justify-end gap-2">

                                <button onclick="editType('${row.ticket_type}')"
                                    class="rounded-lg bg-amber-500 px-3 py-1 text-xs text-white transition hover:bg-amber-600">

                                    Edit

                                </button>

                                <button onclick="deleteType('${row.ticket_type}')"
                                    class="rounded-lg bg-red-600 px-3 py-1 text-xs text-white transition hover:bg-red-700">

                                    Delete

                                </button>

                            </div>
                        `;

                        }
                    }
                ],

                order: [
                    [1, 'asc']
                ],

                language: {
                    search: '',
                    searchPlaceholder: 'Search type...'
                }

            });

            tableCategory = $('#tableCategory').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,

                ajax: "{{ route('ticketsetup.categoryJson') }}",

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

                            return `
                            <div class="flex items-center justify-end gap-2">

                                <button onclick="editCategory('${row.ticket_categoryid}')"
                                    class="rounded-lg bg-amber-500 px-3 py-1 text-xs text-white transition hover:bg-amber-600">

                                    Edit

                                </button>

                                <button onclick="deleteCategory('${row.ticket_categoryid}')"
                                    class="rounded-lg bg-red-600 px-3 py-1 text-xs text-white transition hover:bg-red-700">

                                    Delete

                                </button>

                            </div>
                        `;

                        }
                    }
                ],

                order: [
                    [1, 'asc']
                ],

                language: {
                    search: '',
                    searchPlaceholder: 'Search category...'
                }

            });

            tableSubcategory = $('#tableSubcategory').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,

                ajax: "{{ route('ticketsetup.subcategoryJson') }}",

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

                            return `
                            <div class="flex items-center justify-end gap-2">

                                <button onclick="editSubcategory('${row.ticket_subcategoryid}')"
                                    class="rounded-lg bg-amber-500 px-3 py-1 text-xs text-white transition hover:bg-amber-600">

                                    Edit

                                </button>

                                <button onclick="deleteSubcategory('${row.ticket_subcategoryid}')"
                                    class="rounded-lg bg-red-600 px-3 py-1 text-xs text-white transition hover:bg-red-700">

                                    Delete

                                </button>

                            </div>
                        `;

                        }
                    }
                ],

                order: [
                    [1, 'asc']
                ],

                language: {
                    search: '',
                    searchPlaceholder: 'Search subcategory...'
                }

            });

            tablePriority = $('#tablePriority').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,

                ajax: "{{ route('ticketsetup.priorityJson') }}",

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

                            return `
                            <div class="flex items-center justify-end gap-2">

                                <button onclick="editPriority('${row.id}')"
                                    class="rounded-lg bg-amber-500 px-3 py-1 text-xs text-white transition hover:bg-amber-600">

                                    Edit

                                </button>

                                <button onclick="deletePriority('${row.id}')"
                                    class="rounded-lg bg-red-600 px-3 py-1 text-xs text-white transition hover:bg-red-700">

                                    Delete

                                </button>

                            </div>
                        `;

                        }
                    }
                ],

                order: [
                    [1, 'asc']
                ],

                language: {
                    search: '',
                    searchPlaceholder: 'Search priority...'
                }

            });

            tableDept = $('#tableDept').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,
                pageLength: 10,

                ajax: "{{ route('ticketsetup.deptJson') }}",

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
                        data: 'department_id',
                        name: 'department_id'
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

                            return `
                            <div class="flex items-center justify-end gap-2">

                                <button onclick="editDept('${row.id}')"
                                    class="rounded-lg bg-amber-500 px-3 py-1 text-xs text-white transition hover:bg-amber-600">

                                    Edit

                                </button>

                                <button onclick="deleteDept('${row.id}')"
                                    class="rounded-lg bg-red-600 px-3 py-1 text-xs text-white transition hover:bg-red-700">

                                    Delete

                                </button>

                            </div>
                        `;

                        }
                    }
                ],

                order: [
                    [1, 'asc']
                ],

                language: {
                    search: '',
                    searchPlaceholder: 'Search department PIC...'
                }

            });

        });
    </script>

    <script>
        function openCreateTypeModal() {

            $('#createTypeModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeCreateTypeModal() {

            $('#createTypeModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createTypeForm')[0].reset();

        }

        function openEditTypeModal() {

            $('#editTypeModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeEditTypeModal() {

            $('#editTypeModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editTypeForm')[0].reset();

        }

        function editType(ticket_type) {

            let row = tableType.rows().data().toArray().find(x => x.ticket_type == ticket_type);

            if (!row) return;

            $('#edit_ticket_type_old').val(row.ticket_type);
            $('#edit_ticket_type').val(row.ticket_type);
            $('#edit_ticket_type_name').val(row.ticket_type_name);
            $('#edit_type_status').val(row.status);

            openEditTypeModal();

        }

        function deleteType(ticket_type) {

            Swal.fire({

                title: 'Delete Ticket Type?',
                text: 'This data will be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Delete'

            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({

                    url: `/ticket-setup/type/delete/${ticket_type}`,
                    type: 'DELETE',

                    data: {
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        tableType.ajax.reload(null, false);

                    },

                    error: function(xhr) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ?? 'Something went wrong'
                        });

                    }

                });

            });

        }

        function openCreateCategoryModal() {

            $('#createCategoryModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeCreateCategoryModal() {

            $('#createCategoryModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createCategoryForm')[0].reset();

        }

        function openEditCategoryModal() {

            $('#editCategoryModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeEditCategoryModal() {

            $('#editCategoryModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editCategoryForm')[0].reset();

        }

        function editCategory(ticket_categoryid) {

            let row = tableCategory.rows().data().toArray().find(x => x.ticket_categoryid == ticket_categoryid);

            if (!row) return;

            $('#edit_ticket_categoryid_old').val(row.ticket_categoryid);
            $('#edit_ticket_categoryid').val(row.ticket_categoryid);
            $('#edit_ticket_category_name').val(row.ticket_category_name);
            $('#edit_category_ticket_type').val(row.ticket_type);
            $('#edit_category_status').val(row.status);

            openEditCategoryModal();

        }

        function deleteCategory(ticket_categoryid) {

            Swal.fire({

                title: 'Delete Category?',
                text: 'This data will be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Delete'

            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({

                    url: `/ticket-setup/category/delete/${ticket_categoryid}`,
                    type: 'DELETE',

                    data: {
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        tableCategory.ajax.reload(null, false);

                    },

                    error: function(xhr) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ?? 'Something went wrong'
                        });

                    }

                });

            });

        }

        function openCreateSubcategoryModal() {

            $('#createSubcategoryModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeCreateSubcategoryModal() {

            $('#createSubcategoryModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createSubcategoryForm')[0].reset();

            $('#subcategory_category').html(`
                <option value="">
                    Select Category
                </option>
            `);

        }

        function openEditSubcategoryModal() {

            $('#editSubcategoryModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeEditSubcategoryModal() {

            $('#editSubcategoryModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editSubcategoryForm')[0].reset();

            $('#edit_subcategory_category').html(`
                <option value="">
                    Select Category
                </option>
            `);

        }

        function editSubcategory(ticket_subcategoryid) {

            let row = tableSubcategory.rows().data().toArray().find(x => x.ticket_subcategoryid == ticket_subcategoryid);

            if (!row) return;

            $('#edit_ticket_subcategoryid_old').val(row.ticket_subcategoryid);
            $('#edit_ticket_subcategoryid').val(row.ticket_subcategoryid);
            $('#edit_ticket_subcategory_name').val(row.ticket_subcategory_name);
            $('#edit_subcategory_ticket_type').val(row.ticket_type);
            $('#edit_subcategory_status').val(row.status);

            $.get(`/ticket-setup/category-by-type/${row.ticket_type}`, function(response) {

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

                $('#edit_subcategory_category').html(html);
                $('#edit_subcategory_category').val(row.ticket_categoryid);

                openEditSubcategoryModal();

            });

        }

        function deleteSubcategory(ticket_subcategoryid) {

            Swal.fire({

                title: 'Delete Subcategory?',
                text: 'This data will be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Delete'

            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({

                    url: `/ticket-setup/subcategory/delete/${ticket_subcategoryid}`,
                    type: 'DELETE',

                    data: {
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        tableSubcategory.ajax.reload(null, false);

                    },

                    error: function(xhr) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ?? 'Something went wrong'
                        });

                    }

                });

            });

        }

        function openCreatePriorityModal() {

            $('#createPriorityModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeCreatePriorityModal() {

            $('#createPriorityModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createPriorityForm')[0].reset();

            $('#priority_category').html(`
                <option value="">
                    Select Category
                </option>
            `);

        }

        function openEditPriorityModal() {

            $('#editPriorityModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeEditPriorityModal() {

            $('#editPriorityModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editPriorityForm')[0].reset();

            $('#edit_priority_category').html(`
                <option value="">
                    Select Category
                </option>
            `);

        }

        function editPriority(id) {

            let row = tablePriority.rows().data().toArray().find(x => x.id == id);

            if (!row) return;

            $('#edit_priority_id').val(row.id);
            $('#edit_ticket_priority').val(row.ticket_priority);
            $('#edit_ticket_priority_name').val(row.ticket_priority_name);
            $('#edit_priority_ticket_type').val(row.ticket_type);
            $('#edit_ticket_sla_days').val(row.ticket_sla_days);
            $('#edit_priority_status').val(row.status);

            $.get(`/ticket-setup/category-by-type/${row.ticket_type}`, function(response) {

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

                $('#edit_priority_category').html(html);
                $('#edit_priority_category').val(row.ticket_categoryid);

                openEditPriorityModal();

            });

        }

        function deletePriority(id) {

            Swal.fire({

                title: 'Delete Priority?',
                text: 'This data will be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Delete'

            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({

                    url: `/ticket-setup/priority/delete/${id}`,
                    type: 'DELETE',

                    data: {
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        tablePriority.ajax.reload(null, false);

                    },

                    error: function(xhr) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ?? 'Something went wrong'
                        });

                    }

                });

            });

        }

        function openCreateDeptModal() {

            $('#createDeptModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeCreateDeptModal() {

            $('#createDeptModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createDeptForm')[0].reset();

            $('#dept_category').html(`
                <option value="">
                    Select Category
                </option>
            `);

        }

        function openEditDeptModal() {

            $('#editDeptModal')
                .removeClass('hidden')
                .addClass('flex');

        }

        function closeEditDeptModal() {

            $('#editDeptModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editDeptForm')[0].reset();

            $('#edit_dept_category').html(`
                <option value="">
                    Select Category
                </option>
            `);

        }

        function editDept(id) {

            let row = tableDept.rows().data().toArray().find(x => x.id == id);

            if (!row) return;

            $('#edit_dept_id').val(row.id);
            $('#edit_dept_ticket_type').val(row.ticket_type);
            $('#edit_department_id').val(row.department_id);
            $('#edit_username').val(row.username);
            $('#edit_dept_status').val(row.status);

            $.get(`/ticket-setup/category-by-type/${row.ticket_type}`, function(response) {

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

                $('#edit_dept_category').html(html);
                $('#edit_dept_category').val(row.ticket_categoryid);

                openEditDeptModal();

            });

        }

        function deleteDept(id) {

            Swal.fire({

                title: 'Delete Department PIC?',
                text: 'This data will be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Delete'

            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({

                    url: `/ticket-setup/dept/delete/${id}`,
                    type: 'DELETE',

                    data: {
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        tableDept.ajax.reload(null, false);

                    },

                    error: function(xhr) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ?? 'Something went wrong'
                        });

                    }

                });

            });

        }
    </script>

    <script>
        $('#createTypeForm').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "{{ route('ticketsetup.storeType') }}",
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    $('#createTypeForm button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: 'Saving...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    $('#createTypeForm button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeCreateTypeModal();

                    tableType.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        });

        $('#editTypeForm').submit(function(e) {

            e.preventDefault();

            let ticket_type = $('#edit_ticket_type_old').val();

            $.ajax({

                url: `/ticket-setup/type/update/${ticket_type}`,
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    $('#editTypeForm button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: 'Updating...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    $('#editTypeForm button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeEditTypeModal();

                    tableType.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        });

        $('#createCategoryForm').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "{{ route('ticketsetup.storeCategory') }}",
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    $('#createCategoryForm button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: 'Saving...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    $('#createCategoryForm button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeCreateCategoryModal();

                    tableCategory.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        });

        $('#editCategoryForm').submit(function(e) {

            e.preventDefault();

            let ticket_categoryid = $('#edit_ticket_categoryid_old').val();

            $.ajax({

                url: `/ticket-setup/category/update/${ticket_categoryid}`,
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    $('#editCategoryForm button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: 'Updating...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    $('#editCategoryForm button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeEditCategoryModal();

                    tableCategory.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        });
    </script>

    <script>
        $('#createSubcategoryForm').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "{{ route('ticketsetup.storeSubcategory') }}",
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    $('#createSubcategoryForm button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: 'Saving...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    $('#createSubcategoryForm button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeCreateSubcategoryModal();

                    tableSubcategory.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        });

        $('#editSubcategoryForm').submit(function(e) {

            e.preventDefault();

            let ticket_subcategoryid = $('#edit_ticket_subcategoryid_old').val();

            $.ajax({

                url: `/ticket-setup/subcategory/update/${ticket_subcategoryid}`,
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    $('#editSubcategoryForm button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: 'Updating...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    $('#editSubcategoryForm button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeEditSubcategoryModal();

                    tableSubcategory.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        });

        $('#createPriorityForm').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "{{ route('ticketsetup.storePriority') }}",
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    $('#createPriorityForm button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: 'Saving...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    $('#createPriorityForm button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeCreatePriorityModal();

                    tablePriority.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        });

        $('#editPriorityForm').submit(function(e) {

            e.preventDefault();

            let id = $('#edit_priority_id').val();

            $.ajax({

                url: `/ticket-setup/priority/update/${id}`,
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    $('#editPriorityForm button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: 'Updating...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    $('#editPriorityForm button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeEditPriorityModal();

                    tablePriority.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        });


        $('#createDeptForm').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "{{ route('ticketsetup.storeDept') }}",
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    $('#createDeptForm button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: 'Saving...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    $('#createDeptForm button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeCreateDeptModal();

                    tableDept.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        });

        $('#editDeptForm').submit(function(e) {

            e.preventDefault();

            let id = $('#edit_dept_id').val();

            $.ajax({

                url: `/ticket-setup/dept/update/${id}`,
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    $('#editDeptForm button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: 'Updating...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    $('#editDeptForm button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeEditDeptModal();

                    tableDept.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        });
    </script>

    <script>
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

            $.get(`/ticket-setup/category-by-type/${type}`, function(response) {

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

            });

        }

        function actionButton(editFn, deleteFn, key) {

            return `
            <div class="flex items-center justify-end gap-2">

                <button onclick="${editFn}('${key}')"
                    class="rounded-lg bg-amber-500 px-3 py-1 text-xs text-white transition hover:bg-amber-600">

                    Edit

                </button>

                <button onclick="${deleteFn}('${key}')"
                    class="rounded-lg bg-red-600 px-3 py-1 text-xs text-white transition hover:bg-red-700">

                    Delete

                </button>

            </div>
        `;

        }

        function toggleModal(modalId, show = true) {

            if (show) {

                $(modalId)
                    .removeClass('hidden')
                    .addClass('flex');

            } else {

                $(modalId)
                    .removeClass('flex')
                    .addClass('hidden');

                const form = $(modalId).find('form')[0];

                if (form) {
                    form.reset();
                }

            }

        }

        function submitForm(config) {

            $.ajax({

                url: config.url,
                type: config.type ?? 'POST',
                data: config.form.serialize(),

                beforeSend: function() {

                    config.form.find('button[type="submit"]').prop('disabled', true);

                    Swal.fire({
                        title: config.loadingText ?? 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                complete: function() {

                    config.form.find('button[type="submit"]').prop('disabled', false);

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    if (config.closeModal) {
                        config.closeModal();
                    }

                    if (config.reloadTable) {
                        config.reloadTable.ajax.reload(null, false);
                    }

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Something went wrong'
                    });

                }

            });

        }

        function confirmDelete(config) {

            Swal.fire({

                title: config.title ?? 'Delete Data?',
                text: config.text ?? 'This data will be deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, Delete'

            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({

                    url: config.url,
                    type: 'DELETE',

                    data: {
                        _token: '{{ csrf_token() }}'
                    },

                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        config.table.ajax.reload(null, false);

                    },

                    error: function(xhr) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ?? 'Something went wrong'
                        });

                    }

                });

            });

        }

        $('.tab-button').click(function() {

            $('.tab-button')
                .removeClass('border-blue-200 bg-blue-50 text-blue-700')
                .addClass(
                    'border-transparent bg-transparent text-gray-600 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white'
                    );

            $('#typePanel,#categoryPanel,#subcategoryPanel,#priorityPanel,#deptPanel')
                .addClass('hidden');

            $(this)
                .removeClass(
                    'border-transparent bg-transparent text-gray-600 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white'
                    )
                .addClass('border-blue-200 bg-blue-50 text-blue-700');

            const panelMap = {
                tabType: '#typePanel',
                tabCategory: '#categoryPanel',
                tabSubcategory: '#subcategoryPanel',
                tabPriority: '#priorityPanel',
                tabDept: '#deptPanel'
            };

            $(panelMap[this.id]).removeClass('hidden');

        });

        $('input.uppercase').on('input', function() {
            this.value = this.value.toUpperCase();
        });

        $('#subcategory_ticket_type').change(function() {
            loadCategoryOptions($(this).val(), '#subcategory_category');
        });

        $('#priority_ticket_type').change(function() {
            loadCategoryOptions($(this).val(), '#priority_category');
        });

        $('#dept_ticket_type').change(function() {
            loadCategoryOptions($(this).val(), '#dept_category');
        });

        $('#edit_subcategory_ticket_type').change(function() {
            loadCategoryOptions($(this).val(), '#edit_subcategory_category');
        });

        $('#edit_priority_ticket_type').change(function() {
            loadCategoryOptions($(this).val(), '#edit_priority_category');
        });

        $('#edit_dept_ticket_type').change(function() {
            loadCategoryOptions($(this).val(), '#edit_dept_category');
        });
    </script>

</x-app-layout>
