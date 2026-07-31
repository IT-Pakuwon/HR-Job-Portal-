<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- HEADER TAB --}}
        <div
            class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-white/5">

            <button id="tabRoom"
                class="tab-button active-tab inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition">

                <span class="text-base">🏢</span>

                <span>
                    Meeting Room
                </span>

            </button>

            <button id="tabAccessories"
                class="tab-button inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                <span class="text-base">🖥️</span>

                <span>
                    Accessories
                </span>

            </button>

            <button id="tabRoomAccess"
                class="tab-button inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                <span class="text-base">🔐</span>

                <span>
                    Meeting Room Access
                </span>

            </button>

            <button id="tabDateBlock"
                class="tab-button inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">

                <span class="text-base">⏳</span>

                <span>
                    Date Block
                </span>

            </button>
        </div>

        {{-- ROOM PANEL --}}
        <div id="roomPanel">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                {{-- HEADER --}}
                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Meeting Room List
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage all meeting room master data.
                        </p>

                    </div>

                    <button type="button" onclick="openCreateRoomModal()"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700">

                        <span>＋</span>

                        <span>
                            Add Room
                        </span>

                    </button>

                </div>

                {{-- TABLE --}}
                <div class="overflow-x-auto p-5">

                    <table id="roomTable" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Room ID</th>
                                <th>Room Name</th>
                                <th>Color</th>
                                <th>User Approval</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- ACCESSORIES PANEL --}}
        <div id="accessoriesPanel" class="hidden">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                {{-- HEADER --}}
                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Meeting Accessories
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage meeting accessories and online meeting accounts.
                        </p>

                    </div>

                    <button type="button" onclick="openCreateAccessoriesModal()"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">

                        <span>＋</span>

                        <span>
                            Add Accessories
                        </span>

                    </button>

                </div>

                {{-- TABLE --}}
                <div class="overflow-x-auto p-5">

                    <table id="accessoriesTable" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Accessories ID</th>
                                <th>Room</th>
                                <th>Accessories</th>
                                <th>Qty</th>
                                <th>Zoom</th>
                                <th>MS Teams</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        <div id="roomAccessPanel" class="hidden">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Meeting Room Access
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Configure allowed booking users per room.
                        </p>

                    </div>

                </div>

                <div class="overflow-x-auto p-5">

                    <table id="roomAccessTable" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Room ID</th>
                                <th>Room Name</th>
                                <th>Allowed User</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        {{-- DATE BLOCK PANEL --}}
        <div id="dateBlockPanel" class="hidden">

            <div
                class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                {{-- HEADER --}}
                <div
                    class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">

                    <div>

                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                            Date Block Settings
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Manage booking date limit settings. Only one Active setting is used by the system at a time.
                        </p>

                    </div>

                    <button type="button" onclick="openCreateDateBlockModal()"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-amber-700">

                        <span>＋</span>

                        <span>
                            Add Setting
                        </span>

                    </button>

                </div>

                {{-- TABLE --}}
                <div class="overflow-x-auto p-5">

                    <table id="dateBlockTable" class="display w-full border-collapse text-sm">

                        <thead>

                            <tr>

                                <th>No</th>
                                <th>Setting ID</th>
                                <th>Setting Name</th>
                                <th>Value (String)</th>
                                <th>Value (Int)</th>
                                <th>Value (Datetime)</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>

                            </tr>

                        </thead>

                    </table>

                </div>

            </div>

        </div>

        <div id="createAccessoriesModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Meeting Accessories
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create new meeting accessories master data.
                        </p>

                    </div>

                    <button type="button" onclick="closeCreateAccessoriesModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                {{-- BODY --}}
                <form id="createAccessoriesForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        {{-- ACCESSORIES ID --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Accessories ID
                            </label>

                            <input type="text" name="acc_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="ACC-001" required>

                        </div>

                        {{-- ROOM --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Meeting Room
                            </label>

                            <select name="room_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                                <option value="">
                                    Select Room
                                </option>

                                @foreach ($rooms as $room)
                                    <option value="{{ $room->room_id }}">
                                        {{ $room->room_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        {{-- ACCESSORIES NAME --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Accessories Name
                            </label>

                            <input type="text" name="acc_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="PROJECTOR">

                        </div>

                        {{-- QTY --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Quantity
                            </label>

                            <input type="number" min="0" name="acc_qty"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="1">

                        </div>

                        {{-- ZOOM --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Zoom Account
                            </label>

                            <input type="text" name="userid_zoom"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="zoom@email.com">

                        </div>

                        {{-- MS TEAMS --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                MS Teams Account
                            </label>

                            <input type="text" name="userid_msteams"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="teams@email.com">

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeCreateAccessoriesModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-700">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700">

                            Save Accessories

                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div id="createRoomModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Meeting Room
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create new meeting room master data.
                        </p>

                    </div>

                    <button type="button" onclick="closeCreateRoomModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                {{-- BODY --}}
                <form id="createRoomForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        {{-- ROOM ID --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Room ID
                            </label>

                            <input type="text" name="room_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="RM-01" required>

                        </div>

                        {{-- ROOM NAME --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Room Name
                            </label>

                            <input type="text" name="room_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="MAIN MEETING ROOM" required>

                        </div>

                        {{-- COLOR --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event Color
                            </label>

                            <div class="flex items-center gap-3">

                                <input type="color" name="eventcolor" value="#3B82F6"
                                    class="h-12 w-16 cursor-pointer rounded-xl border border-gray-300 bg-white p-1 dark:border-white/10 dark:bg-white/5">

                                <input type="text" id="roomColorText" value="#3B82F6"
                                    class="flex-1 rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                            </div>

                        </div>

                        {{-- USER APPROVAL --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                User Approval
                            </label>

                            <input type="text" name="user_approval"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="USERNAME">

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeCreateRoomModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-700">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">

                            Save Room

                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div id="editRoomModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Meeting Room
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update meeting room master data.
                        </p>

                    </div>

                    <button type="button" onclick="closeEditRoomModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                {{-- BODY --}}
                <form id="editRoomForm">

                    @csrf

                    <input type="hidden" id="edit_room_id" name="edit_room_id">

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        {{-- ROOM ID --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Room ID
                            </label>

                            <input type="text" name="room_id" id="edit_room_code"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        {{-- ROOM NAME --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Room Name
                            </label>

                            <input type="text" name="room_name" id="edit_room_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        {{-- EVENT COLOR --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Event Color
                            </label>

                            <div class="flex items-center gap-3">

                                <input type="color" name="eventcolor" id="edit_eventcolor_picker"
                                    class="h-12 w-16 cursor-pointer rounded-xl border border-gray-300 bg-white p-1 dark:border-white/10 dark:bg-white/5">

                                <input type="text" id="edit_eventcolor_text"
                                    class="flex-1 rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                            </div>

                        </div>

                        {{-- USER APPROVAL --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                User Approval
                            </label>

                            <input type="text" name="user_approval" id="edit_user_approval"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeEditRoomModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-700">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">

                            Update Room

                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div id="editAccessoriesModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Accessories
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update accessories master data.
                        </p>

                    </div>

                    <button type="button" onclick="closeEditAccessoriesModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                {{-- BODY --}}
                <form id="editAccessoriesForm">

                    @csrf

                    <input type="hidden" id="edit_accessories_id" name="edit_accessories_id">

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        {{-- ACCESSORIES ID --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Accessories ID
                            </label>

                            <input type="text" name="acc_id" id="edit_acc_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        {{-- ROOM --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Meeting Room
                            </label>

                            <select name="room_id" id="edit_room_select"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                                @foreach ($rooms as $room)
                                    <option value="{{ $room->room_id }}">
                                        {{ $room->room_name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        {{-- ACCESSORIES NAME --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Accessories Name
                            </label>

                            <input type="text" name="acc_name" id="edit_acc_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        {{-- QTY --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Quantity
                            </label>

                            <input type="number" min="0" name="acc_qty" id="edit_acc_qty"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        {{-- ZOOM --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Zoom Account
                            </label>

                            <input type="text" name="userid_zoom" id="edit_userid_zoom"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        {{-- MS TEAMS --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                MS Teams Account
                            </label>

                            <input type="text" name="userid_msteams" id="edit_userid_msteams"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeEditAccessoriesModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-700">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700">

                            Update Accessories

                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div id="roomAccessModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Manage Room Access
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Configure allowed booking users per room.
                        </p>

                    </div>

                    <button type="button" onclick="closeRoomAccessModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                <form id="roomAccessForm">

                    @csrf

                    <input type="hidden" id="access_room_id" name="access_room_id">

                    <div class="p-6">

                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Allowed User
                        </label>

                        <select id="room_access_user" name="username[]" multiple
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">

                            @foreach ($users as $user)
                                <option value="{{ $user->username }}">
                                    {{ $user->name }} ({{ $user->username }})
                                </option>
                            @endforeach

                        </select>

                        <p class="mt-2 text-xs text-gray-400">
                            Leave empty if all users are allowed to book.
                        </p>

                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeRoomAccessModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-700">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-violet-700">

                            Save Access

                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div id="createDateBlockModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Add Date Block Setting
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Create new date block setting.
                        </p>

                    </div>

                    <button type="button" onclick="closeCreateDateBlockModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                {{-- BODY --}}
                <form id="createDateBlockForm">

                    @csrf

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        {{-- SETTING ID --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Setting ID
                            </label>

                            <input type="text" name="setting_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="BOOKING_DATE_LIMIT" required>

                        </div>

                        {{-- SETTING NAME --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Setting Name
                            </label>

                            <input type="text" name="setting_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="Booking Date Limit" required>

                        </div>

                        {{-- VALUE STRING --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Value (String)
                            </label>

                            <input type="text" name="setting_value_string"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="15">

                        </div>

                        {{-- VALUE INT --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Value (Int)
                            </label>

                            <input type="number" name="setting_value_int"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                placeholder="15">

                        </div>

                        {{-- VALUE DATETIME --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Value (Datetime)
                            </label>

                            <input type="datetime-local" name="setting_value_datetime"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeCreateDateBlockModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-700">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-amber-700">

                            Save Setting

                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div id="editDateBlockModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

                {{-- HEADER --}}
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">

                    <div>

                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Edit Date Block Setting
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Update date block setting.
                        </p>

                    </div>

                    <button type="button" onclick="closeEditDateBlockModal()"
                        class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">

                        ✕

                    </button>

                </div>

                {{-- BODY --}}
                <form id="editDateBlockForm">

                    @csrf

                    <input type="hidden" id="edit_dateblock_id" name="edit_dateblock_id">

                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                        {{-- SETTING ID --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Setting ID
                            </label>

                            <input type="text" name="setting_id" id="edit_setting_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        {{-- SETTING NAME --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Setting Name
                            </label>

                            <input type="text" name="setting_name" id="edit_setting_name"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                required>

                        </div>

                        {{-- VALUE STRING --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Value (String)
                            </label>

                            <input type="text" name="setting_value_string" id="edit_setting_value_string"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        {{-- VALUE INT --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Value (Int)
                            </label>

                            <input type="number" name="setting_value_int" id="edit_setting_value_int"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                        {{-- VALUE DATETIME --}}
                        <div>

                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Value (Datetime)
                            </label>

                            <input type="datetime-local" name="setting_value_datetime" id="edit_setting_value_datetime"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-white/10 dark:bg-white/5 dark:text-white">

                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div
                        class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">

                        <button type="button" onclick="closeEditDateBlockModal()"
                            class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 dark:border-gray-700">

                            Cancel

                        </button>

                        <button type="submit"
                            class="rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-amber-700">

                            Update Setting

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>




    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        /*
                |--------------------------------------------------------------------------
                | TAB
                |--------------------------------------------------------------------------
                */

        const tabRoom = document.getElementById('tabRoom');
        const tabAccessories = document.getElementById('tabAccessories');
        const tabRoomAccess = document.getElementById('tabRoomAccess');
        const tabDateBlock = document.getElementById('tabDateBlock');

        const roomPanel = document.getElementById('roomPanel');
        const accessoriesPanel = document.getElementById('accessoriesPanel');
        const roomAccessPanel = document.getElementById('roomAccessPanel');
        const dateBlockPanel = document.getElementById('dateBlockPanel');

        function resetTab(btn) {

            btn.classList.remove(
                'border-blue-200',
                'bg-blue-50',
                'text-blue-700'
            );

            btn.classList.add(
                'border-transparent',
                'bg-transparent',
                'text-gray-600'
            );
        }

        function activateCurrentTab(btn) {

            btn.classList.add(
                'border-blue-200',
                'bg-blue-50',
                'text-blue-700'
            );

            btn.classList.remove(
                'border-transparent',
                'bg-transparent',
                'text-gray-600'
            );
        }

        function hideAllPanels() {

            roomPanel.classList.add('hidden');
            accessoriesPanel.classList.add('hidden');
            roomAccessPanel.classList.add('hidden');
            dateBlockPanel.classList.add('hidden');
        }

        tabRoom.addEventListener('click', function() {

            resetTab(tabAccessories);
            resetTab(tabRoomAccess);
            resetTab(tabDateBlock);

            activateCurrentTab(tabRoom);

            hideAllPanels();

            roomPanel.classList.remove('hidden');

        });

        tabAccessories.addEventListener('click', function() {

            resetTab(tabRoom);
            resetTab(tabRoomAccess);
            resetTab(tabDateBlock);

            activateCurrentTab(tabAccessories);

            hideAllPanels();

            accessoriesPanel.classList.remove('hidden');

        });

        tabRoomAccess.addEventListener('click', function() {

            resetTab(tabRoom);
            resetTab(tabAccessories);
            resetTab(tabDateBlock);

            activateCurrentTab(tabRoomAccess);

            hideAllPanels();

            roomAccessPanel.classList.remove('hidden');

        });

        tabDateBlock.addEventListener('click', function() {

            resetTab(tabRoom);
            resetTab(tabAccessories);
            resetTab(tabRoomAccess);

            activateCurrentTab(tabDateBlock);

            hideAllPanels();

            dateBlockPanel.classList.remove('hidden');

        });

        /*
        |--------------------------------------------------------------------------
        | DATATABLE
        |--------------------------------------------------------------------------
        */

        let roomTable;
        let accessoriesTable;
        let roomAccessTable;
        let dateBlockTable;

        $(document).ready(function() {

            roomTable = $('#roomTable').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,

                ajax: "{{ route('meetingroom.setup.room.json') }}",

                dom: '<"dt-toolbar"lBf>rtip',

                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        title: 'Meeting_Room_List',
                        className: 'buttons-excel'
                    },
                    {
                        extend: 'csvHtml5',
                        text: 'Export CSV',
                        title: 'Meeting_Room_List',
                        className: 'buttons-csv'
                    }
                ],

                columns: [{
                        data: 'DT_RowIndex',
                        width: '5%',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'room_id',
                        name: 'room_id',
                        width: '10%'
                    },
                    {
                        data: 'room_name',
                        name: 'room_name'
                    },
                    {
                        data: 'eventcolor',
                        name: 'eventcolor',
                        width: '12%',
                        render: function(data) {

                            return `
                                <div class="flex items-center gap-2">
                                    <div class="h-4 w-4 rounded-full border"
                                        style="background:${data}">
                                    </div>

                                    <span>${data ?? '-'}</span>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'user_approval',
                        name: 'user_approval'
                    },
                    {
                        data: 'status',
                        width: '10%',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'action',
                        width: '16%',
                        orderable: false,
                        searchable: false,
                        className: 'text-right'
                    }
                ],

                order: [
                    [1, 'asc']
                ],

                pageLength: 10,

                language: {
                    search: '',
                    searchPlaceholder: 'Search room...',
                }

            });

            accessoriesTable = $('#accessoriesTable').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,

                ajax: "{{ route('meetingroom.setup.accessories.json') }}",

                dom: '<"dt-toolbar"lBf>rtip',

                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        title: 'Meeting_Accessories_List',
                        className: 'buttons-excel'
                    },
                    {
                        extend: 'csvHtml5',
                        text: 'Export CSV',
                        title: 'Meeting_Accessories_List',
                        className: 'buttons-csv'
                    }
                ],

                columns: [{
                        data: 'DT_RowIndex',
                        width: '5%',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'acc_id',
                        name: 'acc_id',
                        width: '10%'
                    },
                    {
                        data: 'room_name',
                        name: 'room_name'
                    },
                    {
                        data: 'acc_name',
                        name: 'acc_name'
                    },
                    {
                        data: 'acc_qty',
                        name: 'acc_qty',
                        width: '8%'
                    },
                    {
                        data: 'userid_zoom',
                        name: 'userid_zoom'
                    },
                    {
                        data: 'userid_msteams',
                        name: 'userid_msteams'
                    },
                    {
                        data: 'status',
                        width: '10%',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'action',
                        width: '16%',
                        orderable: false,
                        searchable: false,
                        className: 'text-right'
                    }
                ],

                order: [
                    [1, 'asc']
                ],

                pageLength: 10,

                language: {
                    search: '',
                    searchPlaceholder: 'Search accessories...',
                }

            });

            roomAccessTable = $('#roomAccessTable').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,

               ajax: {
                        url: "{{ route('meetingroom.setup.room.json') }}",
                        data: function(d) {
                            d.mode = 'access';
                        }
                    },

                dom: '<"dt-toolbar"lBf>rtip',

                columns: [{
                        data: 'DT_RowIndex',
                        width: '5%',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'room_id',
                        name: 'room_id',
                        width: '12%'
                    },
                    {
                        data: 'room_name',
                        name: 'room_name'
                    },
                    {
                        data: 'restricted_user',
                        name: 'restricted_user',
                        orderable: false,
                        searchable: false,
                        width: '35%'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-right',
                        width: '15%'
                    }
                ],

                order: [
                    [1, 'asc']
                ],

                pageLength: 10,

                language: {
                    search: '',
                    searchPlaceholder: 'Search room access...',
                }

            });

            dateBlockTable = $('#dateBlockTable').DataTable({

                processing: true,
                serverSide: true,
                responsive: true,
                autoWidth: false,

                ajax: "{{ route('meetingroom.setup.dateblock.json') }}",

                dom: '<"dt-toolbar"lBf>rtip',

                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        title: 'Date_Block_Settings',
                        className: 'buttons-excel'
                    },
                    {
                        extend: 'csvHtml5',
                        text: 'Export CSV',
                        title: 'Date_Block_Settings',
                        className: 'buttons-csv'
                    }
                ],

                columns: [{
                        data: 'DT_RowIndex',
                        width: '5%',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'setting_id',
                        name: 'setting_id',
                        width: '12%'
                    },
                    {
                        data: 'setting_name',
                        name: 'setting_name'
                    },
                    {
                        data: 'setting_value_string',
                        name: 'setting_value_string'
                    },
                    {
                        data: 'setting_value_int',
                        name: 'setting_value_int',
                        width: '10%'
                    },
                    {
                        data: 'setting_value_datetime',
                        name: 'setting_value_datetime'
                    },
                    {
                        data: 'status',
                        width: '10%',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'action',
                        width: '16%',
                        orderable: false,
                        searchable: false,
                        className: 'text-right'
                    }
                ],

                order: [
                    [1, 'asc']
                ],

                pageLength: 10,

                language: {
                    search: '',
                    searchPlaceholder: 'Search date block setting...',
                }

            });

        });

        function openCreateRoomModal() {

            $('#createRoomModal')
                .removeClass('hidden')
                .addClass('flex');
        }

        function closeCreateRoomModal() {

            $('#createRoomModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createRoomForm')[0].reset();
        }

        function openCreateAccessoriesModal() {

            $('#createAccessoriesModal')
                .removeClass('hidden')
                .addClass('flex');
        }

        function closeCreateAccessoriesModal() {

            $('#createAccessoriesModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createAccessoriesForm')[0].reset();
        }

        $('input[name="eventcolor"]').on('input', function() {

            $('#roomColorText').val($(this).val().toUpperCase());

        });

        $('#roomColorText').on('input', function() {

            $('input[name="eventcolor"]').val($(this).val());

        });


        $('#createRoomForm').submit(function(e) {

            e.preventDefault();

            $('input[name="eventcolor"]').val($('#roomColorText').val());

            $.ajax({

                url: "{{ route('meetingroom.setup.room.store') }}",
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeCreateRoomModal();

                    roomTable.ajax.reload(null, false);

                },

                error: function(xhr) {

                    let message = 'Something went wrong';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });

                }

            });

        });

        $('#createAccessoriesForm').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "{{ route('meetingroom.setup.accessories.store') }}",
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeCreateAccessoriesModal();

                    accessoriesTable.ajax.reload(null, false);

                },

                error: function(xhr) {

                    let message = 'Something went wrong';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });

                }

            });

        });

        function openEditRoomModal() {

            $('#editRoomModal')
                .removeClass('hidden')
                .addClass('flex');
        }

        function closeEditRoomModal() {

            $('#editRoomModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editRoomForm')[0].reset();
        }

        function openEditAccessoriesModal() {

            $('#editAccessoriesModal')
                .removeClass('hidden')
                .addClass('flex');
        }

        function closeEditAccessoriesModal() {

            $('#editAccessoriesModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editAccessoriesForm')[0].reset();
        }

        function editRoom(id) {

            $.ajax({

                url: `/meetingroom/setup/room/find/${id}`,
                type: 'GET',

                beforeSend: function() {

                    Swal.fire({
                        title: 'Loading...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.close();

                    const data = response.data;

                    $('#edit_room_id').val(data.id);
                    $('#edit_room_code').val(data.room_id);
                    $('#edit_room_name').val(data.room_name);

                    $('#edit_eventcolor_picker').val(data.eventcolor);
                    $('#edit_eventcolor_text').val(data.eventcolor);

                    $('#edit_user_approval').val(data.user_approval);

                    openEditRoomModal();

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Failed to load data'
                    });

                }

            });

        }

        function editAccessories(id) {

            $.ajax({

                url: `/meetingroom/setup/accessories/find/${id}`,
                type: 'GET',

                beforeSend: function() {

                    Swal.fire({
                        title: 'Loading...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.close();

                    const data = response.data;

                    $('#edit_accessories_id').val(data.id);

                    $('#edit_acc_id').val(data.acc_id);
                    $('#edit_room_select')
                        .val(data.room_id)
                        .trigger('change');

                    $('#edit_acc_name').val(data.acc_name);
                    $('#edit_acc_qty').val(data.acc_qty);

                    $('#edit_userid_zoom').val(data.userid_zoom);
                    $('#edit_userid_msteams').val(data.userid_msteams);

                    openEditAccessoriesModal();

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Failed to load data'
                    });

                }

            });

        }

        $('#edit_eventcolor_picker').on('input', function() {

            $('#edit_eventcolor_text').val($(this).val().toUpperCase());

        });

        $('#edit_eventcolor_text').on('input', function() {

            $('#edit_eventcolor_picker').val($(this).val());

        });
        $('#editRoomForm').submit(function(e) {

            e.preventDefault();

            $('#edit_eventcolor_picker').val($('#edit_eventcolor_text').val());

            let id = $('#edit_room_id').val();

            $.ajax({

                url: `/meetingroom/setup/room/update/${id}`,
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeEditRoomModal();

                    roomTable.ajax.reload(null, false);

                },

                error: function(xhr) {

                    let message = 'Something went wrong';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });

                }

            });

        });

        $('#editAccessoriesForm').submit(function(e) {

            e.preventDefault();

            let id = $('#edit_accessories_id').val();

            $.ajax({

                url: `/meetingroom/setup/accessories/update/${id}`,
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeEditAccessoriesModal();

                    accessoriesTable.ajax.reload(null, false);

                },

                error: function(xhr) {

                    let message = 'Something went wrong';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });

                }

            });

        });

        function updateRoomStatus(id, status, el = null) {

            Swal.fire({
                title: 'Are you sure?',
                text: 'Change room status?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, update',
                cancelButtonText: 'Cancel'
            }).then((result) => {

                // ❌ USER CANCEL → revert toggle
                if (!result.isConfirmed) {
                    if (el) el.checked = !el.checked;
                    return;
                }

                $.ajax({
                    url: `/meetingroom/setup/room/status/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status
                    },

                    beforeSend: function() {

                        Swal.fire({
                            title: 'Updating...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                    },

                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // 🔁 reload table without reset paging
                        roomTable.ajax.reload(null, false);

                    },

                    error: function(xhr) {

                        // ❌ revert toggle if failed
                        if (el) el.checked = !el.checked;

                        let message = 'Something went wrong';

                        if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });

                    }

                });

            });

        }

        function updateAccessoriesStatus(id, status, el = null) {

            Swal.fire({
                title: 'Are you sure?',
                text: 'Change accessories status?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, update',
                cancelButtonText: 'Cancel'
            }).then((result) => {

                if (!result.isConfirmed) {
                    if (el) el.checked = !el.checked;
                    return;
                }

                $.ajax({
                    url: `/meetingroom/setup/accessories/status/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status
                    },

                    beforeSend: function() {
                        Swal.fire({
                            title: 'Updating...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    },

                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        accessoriesTable.ajax.reload(null, false);
                    },

                    error: function(xhr) {

                        if (el) el.checked = !el.checked;

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ?? 'Something went wrong'
                        });
                    }

                });

            });

        }

        const roomAccessUserTom = new TomSelect('#room_access_user', {
            plugins: ['remove_button'],
            create: false,
            persist: false,
            placeholder: 'Select allowed users',
            maxOptions: 1000
        });

        function openRoomAccessModal() {

            $('#roomAccessModal')
                .removeClass('hidden')
                .addClass('flex');
        }

        function closeRoomAccessModal() {

            $('#roomAccessModal')
                .removeClass('flex')
                .addClass('hidden');

            roomAccessUserTom.clear(true);
        }

        function manageRoomAccess(roomId) {

            $('#access_room_id').val(roomId);

            $.ajax({

                url: `/meetingroom/setup/room/access/${roomId}`,
                type: 'GET',

                beforeSend: function() {

                    Swal.fire({
                        title: 'Loading...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                },

                success: function(response) {

                    Swal.close();

                    roomAccessUserTom.clear(true);
                    (response.data || []).forEach(function(val) {
                        roomAccessUserTom.addItem(val, true);
                    });

                    openRoomAccessModal();

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Failed to load access data'
                    });

                }

            });
        }

        $('#roomAccessForm').submit(function(e) {

            e.preventDefault();

            let roomId = $('#access_room_id').val();

            $.ajax({

                url: `/meetingroom/setup/room/access/${roomId}`,
                type: 'POST',

                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Saving...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    closeRoomAccessModal();

                    roomAccessTable.ajax.reload(null, false);

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Failed to save access'
                    });

                }

            });

        });

        /*
        |--------------------------------------------------------------------------
        | DATE BLOCK
        |--------------------------------------------------------------------------
        */

        function openCreateDateBlockModal() {

            $('#createDateBlockModal')
                .removeClass('hidden')
                .addClass('flex');
        }

        function closeCreateDateBlockModal() {

            $('#createDateBlockModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#createDateBlockForm')[0].reset();
        }

        function openEditDateBlockModal() {

            $('#editDateBlockModal')
                .removeClass('hidden')
                .addClass('flex');
        }

        function closeEditDateBlockModal() {

            $('#editDateBlockModal')
                .removeClass('flex')
                .addClass('hidden');

            $('#editDateBlockForm')[0].reset();
        }

        $('#createDateBlockForm').submit(function(e) {

            e.preventDefault();

            $.ajax({

                url: "{{ route('meetingroom.setup.dateblock.store') }}",
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Saving...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeCreateDateBlockModal();

                    dateBlockTable.ajax.reload(null, false);

                },

                error: function(xhr) {

                    let message = 'Something went wrong';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });

                }

            });

        });

        function editDateBlock(id) {

            $.ajax({

                url: `/meetingroom/setup/dateblock/find/${id}`,
                type: 'GET',

                beforeSend: function() {

                    Swal.fire({
                        title: 'Loading...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.close();

                    const data = response.data;

                    $('#edit_dateblock_id').val(data.id);
                    $('#edit_setting_id').val(data.setting_id);
                    $('#edit_setting_name').val(data.setting_name);
                    $('#edit_setting_value_string').val(data.setting_value_string);
                    $('#edit_setting_value_int').val(data.setting_value_int);

                    $('#edit_setting_value_datetime').val(
                        data.setting_value_datetime ?
                        data.setting_value_datetime.slice(0, 16).replace(' ', 'T') :
                        ''
                    );

                    openEditDateBlockModal();

                },

                error: function(xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message ?? 'Failed to load data'
                    });

                }

            });

        }

        $('#editDateBlockForm').submit(function(e) {

            e.preventDefault();

            let id = $('#edit_dateblock_id').val();

            $.ajax({

                url: `/meetingroom/setup/dateblock/update/${id}`,
                type: 'POST',
                data: $(this).serialize(),

                beforeSend: function() {

                    Swal.fire({
                        title: 'Updating...',
                        text: 'Please wait',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                },

                success: function(response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    closeEditDateBlockModal();

                    dateBlockTable.ajax.reload(null, false);

                },

                error: function(xhr) {

                    let message = 'Something went wrong';

                    if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });

                }

            });

        });

        function updateDateBlockStatus(id, status, el = null) {

            Swal.fire({
                title: 'Are you sure?',
                text: 'Change date block setting status?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, update',
                cancelButtonText: 'Cancel'
            }).then((result) => {

                if (!result.isConfirmed) {
                    if (el) el.checked = !el.checked;
                    return;
                }

                $.ajax({
                    url: `/meetingroom/setup/dateblock/status/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status
                    },

                    beforeSend: function() {
                        Swal.fire({
                            title: 'Updating...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    },

                    success: function(response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        dateBlockTable.ajax.reload(null, false);
                    },

                    error: function(xhr) {

                        if (el) el.checked = !el.checked;

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
</x-app-layout>
