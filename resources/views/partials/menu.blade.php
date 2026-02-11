<style>
    .shortcut-wrapper {
        display: flex;
        align-items: center;
        gap: 4px;
        position: relative;
    }

    .shortcut-item {
        display: flex;
        align-items: center;
        gap: 6px;
        height: 36px;
        padding: 0 12px;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        font-size: 14px;
        color: #374151;
        transition: background .15s, border .15s;
        white-space: nowrap;
    }

    .shortcut-item:hover {
        background: #f3f4f6;
        border-color: #c7d2fe;
    }

    .shortcut-actions {
        display: none;
        gap: 4px;
    }

    .shortcut-wrapper:hover .shortcut-actions {
        display: flex;
    }

    .shortcut-actions button {
        font-size: 12px;
        opacity: 0.6;
        cursor: pointer;
    }

    .shortcut-actions button:hover {
        opacity: 1;
    }
</style>

{{-- ================= SHORTCUT BAR ================= --}}
<div class="flex flex-wrap items-center gap-2" id="shortcutBar">

    <!-- DEFAULT SHORTCUT: EMAIL -->
    <div class="shortcut-wrapper">
        <a href="https://mail3.pakuwon.com" target="_blank" class="shortcut-item">
            📧 Email
        </a>
        <div class="shortcut-actions">
            <button onclick="editShortcut(this)">✏️</button>
            <button onclick="removeShortcut(this)">❌</button>
        </div>
    </div>

    <!-- DEFAULT SHORTCUT: ISORT -->
    <div class="shortcut-wrapper">
        <a href="https://isort.pakuwon.com" target="_blank" class="shortcut-item">
            📦 ISORT
        </a>
        <div class="shortcut-actions">
            <button onclick="editShortcut(this)">✏️</button>
            <button onclick="removeShortcut(this)">❌</button>
        </div>
    </div>

    <!-- ADD BUTTON -->
    <button onclick="openShortcutModal()"
        class="flex h-9 items-center gap-2 rounded-md border border-dashed border-gray-300 px-3 text-sm text-gray-500 hover:border-indigo-400 hover:text-indigo-600">
        ➕ Add
    </button>

</div>
{{-- ================= MODAL ================= --}}
<div id="shortcutModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">

    <div class="w-full max-w-sm rounded-lg bg-white p-4 shadow-lg">
        <h3 id="modalTitle" class="mb-3 text-sm font-semibold text-gray-800">
            Add Shortcut
        </h3>

        <input id="shortcutTitle" type="text" placeholder="Shortcut title"
            class="mb-2 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none">

        <input id="shortcutUrl" type="url" placeholder="https://example.com"
            class="mb-4 w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none">

        <div class="flex justify-end gap-2">
            <button onclick="closeShortcutModal()" class="text-sm text-gray-500 hover:text-gray-700">
                Cancel
            </button>

            <button onclick="saveShortcut()"
                class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm text-white hover:bg-indigo-700">
                Save
            </button>
        </div>
    </div>
</div>

<script>
    let editingShortcut = null;

    function openShortcutModal() {
        editingShortcut = null;
        document.getElementById('modalTitle').innerText = 'Add Shortcut';
        document.getElementById('shortcutTitle').value = '';
        document.getElementById('shortcutUrl').value = '';
        showModal();
    }

    function editShortcut(button) {
        editingShortcut = button.closest('.shortcut-wrapper');
        const link = editingShortcut.querySelector('a');

        document.getElementById('modalTitle').innerText = 'Edit Shortcut';
        document.getElementById('shortcutTitle').value = link.innerText.replace(/^🔗|📧|📦/, '').trim();
        document.getElementById('shortcutUrl').value = link.href;

        showModal();
    }

    function saveShortcut() {
        const title = document.getElementById('shortcutTitle').value.trim();
        const url = document.getElementById('shortcutUrl').value.trim();

        if (!title || !url) {
            alert('Title and URL are required');
            return;
        }

        if (editingShortcut) {
            const link = editingShortcut.querySelector('a');
            link.innerHTML = `🔗 ${title}`;
            link.href = url;
        } else {
            const wrapper = document.createElement('div');
            wrapper.className = 'shortcut-wrapper';

            wrapper.innerHTML = `
                <a href="${url}" target="_blank" class="shortcut-item">
                    🔗 ${title}
                </a>
                <div class="shortcut-actions">
                    <button onclick="editShortcut(this)">✏️</button>
                    <button onclick="removeShortcut(this)">❌</button>
                </div>
            `;

            const bar = document.getElementById('shortcutBar');
            bar.insertBefore(wrapper, bar.lastElementChild);
        }

        closeShortcutModal();
    }

    function removeShortcut(button) {
        if (!confirm('Remove this shortcut?')) return;
        button.closest('.shortcut-wrapper').remove();
    }

    function showModal() {
        const modal = document.getElementById('shortcutModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeShortcutModal() {
        const modal = document.getElementById('shortcutModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
