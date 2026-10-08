<x-app-layout>
    @php $currentPage = 'Update Data User'; @endphp

    <div class="max-w-9xl mx-auto w-full p-2" x-data="userDataSync()" x-init="init()">
        <div class="mb-3">
            <h1 class="text-lg font-extrabold text-gray-800 dark:text-white">Update Data User</h1>
            <p class="text-sm text-gray-500 dark:text-gray-300">
                Pick a Talenta employee on the left and the ms_user on the right, then apply Talenta's NPK, Name,
                Company and Department to the user. Selecting a user auto-picks the Talenta row with the same NPK (or name).
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- LEFT: Talenta --}}
            <div class="rounded-xl bg-white p-3 shadow-sm dark:bg-gray-800">
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white">Talenta (view_users_talenta)</h2>
                    <span class="text-xs text-gray-500" x-text="filteredTalenta.length + ' rows'"></span>
                </div>
                <input type="text" x-model.debounce.200ms="qTalenta" placeholder="Search NPK, name, company, department..."
                    class="form-input mb-2 w-full text-sm">
                <div class="mb-2 grid grid-cols-3 gap-2">
                    <select x-model="fT.company" class="form-select w-full text-xs">
                        <option value="">All Company</option>
                        <template x-for="v in talentaCompanies" :key="v"><option :value="v" x-text="v"></option></template>
                    </select>
                    <select x-model="fT.department" class="form-select w-full text-xs">
                        <option value="">All Department</option>
                        <template x-for="v in talentaDepartments" :key="v"><option :value="v" x-text="v"></option></template>
                    </select>
                    <select x-model="fT.status" class="form-select w-full text-xs">
                        <option value="">All Status</option>
                        <template x-for="v in talentaStatuses" :key="v"><option :value="v" x-text="v"></option></template>
                    </select>
                </div>
                <div class="max-h-[60vh] overflow-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-xs">
                        <thead class="sticky top-0 bg-gray-50 text-left dark:bg-gray-900">
                            <tr>
                                <th class="px-2 py-2">NPK</th>
                                <th class="px-2 py-2">Name</th>
                                <th class="px-2 py-2">Company</th>
                                <th class="px-2 py-2">Department</th>
                                <th class="px-2 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="t in filteredTalenta.slice(0, 300)" :key="t.npk">
                                <tr @click="pickTalenta(t)" class="cursor-pointer border-t border-gray-100 dark:border-gray-700"
                                    :class="selTalenta && selTalenta.npk === t.npk ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'hover:bg-gray-50 dark:hover:bg-gray-700/40'">
                                    <td class="px-2 py-1.5 font-mono" x-text="t.npk"></td>
                                    <td class="px-2 py-1.5" x-text="t.name"></td>
                                    <td class="px-2 py-1.5" x-text="t.company"></td>
                                    <td class="px-2 py-1.5" x-text="t.department"></td>
                                    <td class="px-2 py-1.5" x-text="t.status"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <p x-show="filteredTalenta.length > 300" class="p-2 text-center text-xs text-gray-500">Showing first 300 — refine your search.</p>
                </div>
            </div>

            {{-- RIGHT: ms_user --}}
            <div class="rounded-xl bg-white p-3 shadow-sm dark:bg-gray-800">
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white">Users (ms_user)</h2>
                    <span class="text-xs text-gray-500" x-text="filteredUsers.length + ' rows'"></span>
                </div>
                <div class="mb-2 flex gap-2">
                    <input type="text" x-model.debounce.200ms="qUsers" placeholder="Search username, name, NPK..."
                        class="form-input w-full text-sm">
                    <label class="flex items-center gap-1 whitespace-nowrap text-xs">
                        <input type="checkbox" x-model="onlyMismatch" class="form-checkbox"> Mismatch only
                    </label>
                </div>
                <div class="mb-2 grid grid-cols-3 gap-2">
                    <select x-model="fU.company" class="form-select w-full text-xs">
                        <option value="">All Company</option>
                        <template x-for="v in userCompanies" :key="v"><option :value="v" x-text="v"></option></template>
                    </select>
                    <select x-model="fU.department" class="form-select w-full text-xs">
                        <option value="">All Department</option>
                        <template x-for="v in userDepartments" :key="v"><option :value="v" x-text="v"></option></template>
                    </select>
                    <select x-model="fU.status" class="form-select w-full text-xs">
                        <option value="">All Talenta Status</option>
                        <option value="Active">Active</option>
                        <option value="Resigned">Resigned</option>
                        <option value="__none">No Talenta match</option>
                    </select>
                </div>
                <div class="max-h-[60vh] overflow-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-xs">
                        <thead class="sticky top-0 bg-gray-50 text-left dark:bg-gray-900">
                            <tr>
                                <th class="px-2 py-2">Username</th>
                                <th class="px-2 py-2">NPK</th>
                                <th class="px-2 py-2">Name</th>
                                <th class="px-2 py-2">Company</th>
                                <th class="px-2 py-2">Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="u in filteredUsers.slice(0, 300)" :key="u.id">
                                <tr @click="pickUser(u)" class="cursor-pointer border-t border-gray-100 dark:border-gray-700"
                                    :class="selUser && selUser.id === u.id ? 'bg-indigo-50 dark:bg-indigo-900/30' : 'hover:bg-gray-50 dark:hover:bg-gray-700/40'">
                                    <td class="px-2 py-1.5" x-text="u.username"></td>
                                    <td class="px-2 py-1.5 font-mono" x-text="u.npk || '-'"></td>
                                    <td class="px-2 py-1.5" x-text="u.name"></td>
                                    <td class="px-2 py-1.5" x-text="u.origin_cpny_id || '-'"></td>
                                    <td class="px-2 py-1.5" x-text="u.origin_department_id || '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <p x-show="filteredUsers.length > 300" class="p-2 text-center text-xs text-gray-500">Showing first 300 — refine your search.</p>
                </div>
            </div>
        </div>

        {{-- Compare / apply --}}
        <div class="mt-4 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800" x-show="selUser" x-cloak>
            <h2 class="mb-1 text-sm font-bold text-gray-800 dark:text-white">
                Update <span x-text="selUser?.username"></span>
            </h2>
            <p x-show="!selTalenta" class="mb-2 text-xs text-amber-600">No Talenta row selected — click one on the left.</p>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-4" x-show="selTalenta">
                <div>
                    <label class="mb-1 block text-xs font-semibold">NPK / Employee ID</label>
                    <input type="text" x-model="form.npk" class="form-input w-full text-sm">
                    <p class="mt-1 text-xs text-gray-500">Now: <span x-text="selUser?.npk || '-'"></span></p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold">Name</label>
                    <input type="text" x-model="form.name" class="form-input w-full text-sm uppercase">
                    <p class="mt-1 text-xs text-gray-500">Now: <span x-text="selUser?.name"></span></p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold">Origin Company</label>
                    <select x-model="form.origin_cpny_id" class="form-select w-full text-sm">
                        <option value="">-- none --</option>
                        <template x-for="c in companies" :key="c.cpny_id">
                            <option :value="c.cpny_id" x-text="c.cpny_id + ' — ' + c.cpny_name"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Talenta: <b x-text="selTalenta?.company"></b>
                        <span x-show="!suggest.cpny" class="text-amber-600">(no match, choose manually)</span></p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold">Origin Department</label>
                    <select x-model="form.origin_department_id" class="form-select w-full text-sm">
                        <option value="">-- none --</option>
                        <template x-for="d in departments" :key="d.department_id">
                            <option :value="d.department_id" x-text="d.department_id"></option>
                        </template>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Talenta: <b x-text="selTalenta?.department"></b>
                        <span x-show="!suggest.dept" class="text-amber-600">(no match, choose manually)</span></p>
                </div>
            </div>

            <div class="mt-3 flex items-center gap-3" x-show="selTalenta">
                <button type="button" @click="save()" :disabled="saving"
                    class="btn bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50">
                    <span x-text="saving ? 'Saving...' : 'Update ms_user'"></span>
                </button>
                <span class="text-xs" :class="msgError ? 'text-red-600' : 'text-green-600'" x-text="msg"></span>
            </div>
        </div>
    </div>

    <script>
        function userDataSync() {
            const norm = s => String(s || '').toLowerCase().replace(/\s+/g, ' ').trim();
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            return {
                talenta: [], users: [], companies: [], departments: [],
                qTalenta: '', qUsers: '', onlyMismatch: false,
                fT: { company: '', department: '', status: '' },
                fU: { company: '', department: '', status: '' },
                selTalenta: null, selUser: null,
                form: { npk: '', name: '', origin_cpny_id: '', origin_department_id: '' },
                suggest: { cpny: false, dept: false },
                saving: false, msg: '', msgError: false,

                async init() {
                    const get = u => fetch(u, { headers: { Accept: 'application/json' } }).then(r => r.json());
                    const [t, u, o] = await Promise.all([
                        get('{{ route('user-data-sync.talenta') }}'),
                        get('{{ route('user-data-sync.users') }}'),
                        get('{{ route('user-data-sync.options') }}'),
                    ]);
                    this.talenta = t.data;
                    this.users = u.data;
                    this.companies = o.companies;
                    this.departments = o.departments;
                },

                talentaByNpk(npk) {
                    return this.talenta.find(t => t.npk === String(npk || '').trim());
                },
                isMismatch(u) {
                    const t = this.talentaByNpk(u.npk);
                    if (!t) return true;
                    return norm(t.name) !== norm(u.name);
                },

                uniq(arr) { return [...new Set(arr.filter(Boolean))].sort(); },
                get talentaCompanies() { return this.uniq(this.talenta.map(t => t.company)); },
                get talentaDepartments() { return this.uniq(this.talenta.map(t => t.department)); },
                get talentaStatuses() { return this.uniq(this.talenta.map(t => t.status)); },
                get userCompanies() { return this.uniq(this.users.map(u => u.origin_cpny_id)); },
                get userDepartments() { return this.uniq(this.users.map(u => u.origin_department_id)); },

                get filteredTalenta() {
                    const q = norm(this.qTalenta), f = this.fT;
                    return this.talenta.filter(t =>
                        (!q || norm(t.npk + ' ' + t.name + ' ' + t.organization).includes(q)) &&
                        (!f.company || t.company === f.company) &&
                        (!f.department || t.department === f.department) &&
                        (!f.status || t.status === f.status));
                },
                get filteredUsers() {
                    const q = norm(this.qUsers), f = this.fU;
                    return this.users.filter(u => {
                        const t = this.talentaByNpk(u.npk);
                        return (!q || norm(u.username + ' ' + u.name + ' ' + u.npk).includes(q)) &&
                            (!this.onlyMismatch || this.isMismatch(u)) &&
                            (!f.company || u.origin_cpny_id === f.company) &&
                            (!f.department || u.origin_department_id === f.department) &&
                            (!f.status || (f.status === '__none' ? !t : (t && t.status === f.status)));
                    });
                },

                pickUser(u) {
                    this.selUser = u;
                    this.msg = '';
                    // auto-pick Talenta by NPK, else by exact name
                    const t = this.talentaByNpk(u.npk) || this.talenta.find(x => norm(x.name) === norm(u.name));
                    if (t) { this.pickTalenta(t); this.qTalenta = t.npk; } else { this.selTalenta = null; }
                },
                pickTalenta(t) {
                    this.selTalenta = t;
                    this.msg = '';
                    const co = this.companies.find(c => norm(c.cpny_id) === norm(t.company.split(' ')[0]));
                    const dp = this.departments.find(d => norm(d.department_id) === norm(t.department) ||
                        norm(d.department_name) === norm(t.department));
                    this.suggest = { cpny: !!co, dept: !!dp };
                    this.form = {
                        npk: t.npk,
                        name: t.name.toUpperCase(),
                        origin_cpny_id: co ? co.cpny_id : (this.selUser?.origin_cpny_id || ''),
                        origin_department_id: dp ? dp.department_id : (this.selUser?.origin_department_id || ''),
                    };
                },

                async save() {
                    if (!this.selUser) return;
                    this.saving = true; this.msg = '';
                    try {
                        const r = await fetch('{{ url('/user-data-sync') }}/' + this.selUser.id, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify(this.form),
                        });
                        const j = await r.json();
                        if (!r.ok) {
                            this.msgError = true;
                            this.msg = j.message || Object.values(j.errors || {}).flat().join(' ') || 'Failed.';
                        } else {
                            this.msgError = false; this.msg = j.message;
                            Object.assign(this.selUser, j.user);
                        }
                    } catch (e) {
                        this.msgError = true; this.msg = 'Request failed.';
                    }
                    this.saving = false;
                },
            };
        }
    </script>
</x-app-layout>
