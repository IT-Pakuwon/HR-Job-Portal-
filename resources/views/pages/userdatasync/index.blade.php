<x-app-layout>
    @php $currentPage = 'Update Data User'; @endphp

    <div class="max-w-9xl mx-auto w-full p-2" x-data="userDataSync()" x-init="init()">
        <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
            <div>
                <h1 class="text-lg font-extrabold text-gray-800 dark:text-white">Update Data User</h1>
                <p class="text-sm text-gray-500 dark:text-gray-300">
                    Every active account is checked against Talenta. Pick a card to see what needs fixing, then act per row or in bulk.
                </p>
            </div>
            <button type="button" @click="load()" :disabled="loading" class="btn border-gray-200 text-xs dark:border-gray-700">
                <span x-text="loading ? 'Loading...' : 'Refresh'"></span>
            </button>
        </div>

        <p x-show="loading" class="mb-2 text-xs text-gray-500">Loading Talenta and user data…</p>

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
            </template>
        </div>

        <div class="rounded-xl bg-white p-3 shadow-sm dark:bg-gray-800">
            {{-- Toolbar --}}
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <input type="text" x-model.debounce.200ms="q" placeholder="Search username, name, NPK, Talenta name/NPK..."
                    class="form-input min-w-56 flex-1 text-sm">
                <select x-model="fCompany" class="form-select text-xs">
                    <option value="">All Company</option>
                    <template x-for="v in companyOptions" :key="v"><option :value="v" x-text="v"></option></template>
                </select>
                <span class="text-xs text-gray-500" x-text="filtered.length + ' accounts'"></span>
            </div>

            {{-- Bulk bar --}}
            <div class="mb-2 flex flex-wrap items-center gap-2 rounded-lg bg-indigo-50 px-3 py-2 text-xs dark:bg-indigo-900/30" x-show="selCount" x-cloak>
                <b x-text="selCount + ' selected'"></b>
                <button type="button" @click="bulkSync()" :disabled="busy || !selBy('mismatch').length"
                    class="btn bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-40">
                    Sync with Talenta (<span x-text="selBy('mismatch').length"></span>)
                </button>
                <button type="button" @click="bulkDeactivate()" :disabled="busy || !selBy('resigned').length"
                    class="btn bg-red-600 text-white hover:bg-red-700 disabled:opacity-40">
                    Set status X — resigned (<span x-text="selBy('resigned').length"></span>)
                </button>
                <span class="text-gray-500">Other issues are skipped here; use the row action.</span>
                <button type="button" @click="sel = {}" class="ml-auto text-indigo-600 hover:underline">Clear</button>
            </div>

            <p class="mb-2 text-xs" :class="msgError ? 'text-red-600' : 'text-green-600'" x-show="msg" x-text="msg"></p>

            {{-- Worklist --}}
            <div class="max-h-[65vh] overflow-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-xs">
                    <thead class="sticky top-0 z-10 bg-gray-50 text-left dark:bg-gray-900">
                        <tr>
                            <th class="w-8 px-2 py-2"><input type="checkbox" class="form-checkbox" @change="selectAll($event.target.checked)" :checked="allSelected"></th>
                            <th class="px-2 py-2">Account</th>
                            <th class="px-2 py-2">NPK</th>
                            <th class="px-2 py-2">Company / Dept</th>
                            <th class="px-2 py-2">Talenta</th>
                            <th class="px-2 py-2">Issue</th>
                            <th class="px-2 py-2 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="r in visible" :key="r.id">
                            <tr class="cursor-pointer border-t border-gray-100 align-top hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40" @click="open(r)">
                                <td class="px-2 py-1.5" @click.stop><input type="checkbox" class="form-checkbox" :checked="!!sel[r.id]" @change="sel[r.id] = $event.target.checked"></td>
                                <td class="px-2 py-1.5">
                                    <div class="font-semibold" x-text="r.username"></div>
                                    <div class="text-gray-500" x-text="r.name"></div>
                                </td>
                                <td class="px-2 py-1.5 font-mono" :class="r.diff && r.diff.npk ? 'text-amber-600' : ''" x-text="r.npk || '-'"></td>
                                <td class="px-2 py-1.5">
                                    <span :class="r.diff && r.diff.company ? 'text-amber-600' : ''" x-text="r.cpny || '-'"></span> /
                                    <span :class="r.diff && r.diff.dept ? 'text-amber-600' : ''" x-text="r.dept || '-'"></span>
                                </td>
                                <td class="px-2 py-1.5">
                                    <template x-if="r.talenta">
                                        <div>
                                            <div><span :class="r.diff.name ? 'text-amber-600' : ''" x-text="r.talenta.name"></span>
                                                · <span class="font-mono" x-text="r.talenta.npk"></span>
                                                <span class="ml-1 rounded px-1.5 py-0.5 text-[10px] font-semibold"
                                                    :class="r.talenta.status === 'Resigned' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                                                    x-text="r.talenta.status"></span></div>
                                            <div class="text-gray-500" x-text="r.talenta.company + ' / ' + r.talenta.department"></div>
                                        </div>
                                    </template>
                                    <span x-show="!r.talenta" class="text-gray-400">No match</span>
                                </td>
                                <td class="px-2 py-1.5">
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold" :class="badge[r.issue].cls" x-text="badge[r.issue].label"></span>
                                    <span x-show="r.dup" class="ml-1 rounded bg-orange-100 px-1.5 py-0.5 text-[10px] font-semibold text-orange-700" x-text="'Dup: ' + r.dup"></span>
                                    <div x-show="r.rehire" class="mt-0.5 text-[11px] text-gray-500"
                                        x-text="r.rehire ? (r.rehire.keep ? 'keep → NPK ' + r.rehire.active_npk : 'will be set to X') : ''"></div>
                                </td>
                                <td class="px-2 py-1.5 text-right whitespace-nowrap" @click.stop>
                                    <button type="button" x-show="r.issue === 'resigned'" @click="deactivateOne(r)" :disabled="busy" class="btn-xs bg-red-600 text-white">Set X</button>
                                    <button type="button" x-show="r.issue === 'rehire'" @click="resolveRehire(r)" :disabled="busy" class="btn-xs bg-purple-600 text-white">Resolve</button>
                                    <button type="button" x-show="r.issue === 'mismatch'" @click="syncOne(r)" :disabled="busy" class="btn-xs bg-indigo-600 text-white">Sync</button>
                                    <button type="button" x-show="r.issue === 'link' || r.issue === 'no_match'" @click="open(r)" class="btn-xs bg-gray-600 text-white">Review</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <p x-show="!filtered.length && !loading" class="p-4 text-center text-xs text-gray-500">Nothing here.</p>
                <p x-show="loading" class="p-4 text-center text-xs text-gray-500">Loading...</p>
                <button type="button" x-show="filtered.length > limit" @click="limit += 200"
                    class="w-full p-2 text-center text-xs text-indigo-600 hover:underline"
                    x-text="'Show more (' + (filtered.length - limit) + ' left)'"></button>
            </div>

            {{-- Talenta-only duplicates --}}
            <details class="mt-3 text-xs" x-show="talentaDups.length">
                <summary class="cursor-pointer font-semibold text-gray-700 dark:text-gray-200">
                    Talenta duplicates (<span x-text="talentaDups.length"></span>) — same name + birth date, not a Resigned/Active pair
                </summary>
                <div class="mt-2 grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                    <template x-for="(g, i) in talentaDups" :key="i">
                        <div class="rounded-lg border border-gray-200 p-2 dark:border-gray-700">
                            <b x-text="g.name"></b> <span class="text-gray-500" x-text="'· BOD ' + g.bod"></span>
                            <template x-for="t in g.rows" :key="t.npk">
                                <div><span class="font-mono" x-text="t.npk"></span> — <span x-text="t.company + ' / ' + t.department"></span> — <span x-text="t.status"></span></div>
                            </template>
                        </div>
                    </template>
                </div>
            </details>
        </div>

        {{-- Detail drawer --}}
        <div x-show="drawer" x-cloak class="fixed inset-0 z-50 flex justify-end bg-black/30" @click.self="drawer = null" @keydown.escape.window="drawer = null">
            <div class="h-full w-full max-w-md overflow-y-auto bg-white p-4 shadow-xl dark:bg-gray-800" x-show="drawer">
                <div class="mb-3 flex items-start justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-gray-800 dark:text-white" x-text="drawer?.username"></h2>
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold" :class="drawer ? badge[drawer.issue].cls : ''" x-text="drawer ? badge[drawer.issue].label : ''"></span>
                    </div>
                    <button type="button" @click="drawer = null" class="text-lg leading-none text-gray-500">&times;</button>
                </div>

                <p x-show="drawer && drawer.via === 'name'" class="mb-2 rounded bg-blue-50 p-2 text-xs text-blue-700">
                    Linked by name only — check this is the same person before saving, the NPK will change.
                </p>

                {{-- Pick another Talenta row --}}
                <label class="mb-1 block text-xs font-semibold">Talenta row</label>
                <div class="mb-1 rounded-lg border border-gray-200 p-2 text-xs dark:border-gray-700" x-show="pick">
                    <b x-text="pick?.name"></b> · <span class="font-mono" x-text="pick?.npk"></span> ·
                    <span x-text="pick?.status"></span><br>
                    <span class="text-gray-500" x-text="pick ? pick.company + ' / ' + pick.department : ''"></span>
                </div>
                <p x-show="!pick" class="mb-1 text-xs text-amber-600">No Talenta row linked — search below.</p>
                <input type="text" x-model.debounce.200ms="tq" @focus="ensureTalenta()" placeholder="Search Talenta by NPK / name..."
                    class="form-input mb-1 w-full text-sm">
                <div class="mb-3 max-h-40 overflow-auto rounded-lg border border-gray-200 dark:border-gray-700" x-show="tq">
                    <template x-for="t in talentaHits" :key="t.npk">
                        <div @click="usePick(t)" class="cursor-pointer border-b border-gray-100 px-2 py-1 text-xs hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40">
                            <span class="font-mono" x-text="t.npk"></span> · <b x-text="t.name"></b> · <span x-text="t.status"></span>
                            <div class="text-gray-500" x-text="t.company + ' / ' + t.department"></div>
                        </div>
                    </template>
                    <p x-show="!talentaHits.length" class="p-2 text-xs text-gray-500">No matches.</p>
                </div>

                {{-- Edit form --}}
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold">NPK</label>
                        <input type="text" x-model="form.npk" class="form-input w-full text-sm">
                        <p class="mt-1 text-xs text-gray-500">Now: <span x-text="drawer?.npk || '-'"></span></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold">Name</label>
                        <input type="text" x-model="form.name" class="form-input w-full text-sm uppercase">
                        <p class="mt-1 text-xs text-gray-500">Now: <span x-text="drawer?.name"></span></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold">Origin Company</label>
                        <select x-model="form.origin_cpny_id" class="form-select w-full text-sm">
                            <option value="">-- none --</option>
                            <template x-for="c in companies" :key="c.cpny_id"><option :value="c.cpny_id" x-text="c.cpny_id + ' — ' + c.cpny_name"></option></template>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Now: <span x-text="drawer?.cpny || '-'"></span></p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold">Origin Department</label>
                        <select x-model="form.origin_department_id" class="form-select w-full text-sm">
                            <option value="">-- none --</option>
                            <template x-for="d in departments" :key="d.department_id"><option :value="d.department_id" x-text="d.department_id"></option></template>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Now: <span x-text="drawer?.dept || '-'"></span></p>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button type="button" @click="saveDrawer()" :disabled="busy" class="btn bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50">Save</button>
                    <button type="button" @click="deactivateOne(drawer)" :disabled="busy" class="btn bg-red-600 text-white hover:bg-red-700 disabled:opacity-50">Set status X</button>
                </div>
                <p class="mt-2 text-xs text-red-600" x-show="drawerErr" x-text="drawerErr"></p>
            </div>
        </div>
    </div>

    <style>
        .btn-xs { border-radius: .375rem; padding: .2rem .6rem; font-size: .7rem; font-weight: 600; }
        .btn-xs:disabled { opacity: .5; }
    </style>

    <script>
        function userDataSync() {
            const norm = s => String(s || '').toLowerCase().replace(/\s+/g, ' ').trim();
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const json = (method, url, body) => fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: body ? JSON.stringify(body) : undefined,
            }).then(async r => ({ ok: r.ok, j: await r.json().catch(() => ({})) }));

            return {
                talenta: [], users: [], companies: [], departments: [],
                loading: true,
                talentaMap: new Map(), talentaSearch: [], userSearch: [],
                filteredTalenta: [], filteredUsers: [],
                talentaCompanies: [], talentaDepartments: [], talentaStatuses: [],
                userCompanies: [], userDepartments: [],
                qTalenta: '', qUsers: '', onlyMismatch: false,
                fT: { company: '', department: '', status: '' },
                fU: { company: '', department: '', status: '' },
                selTalenta: null, selUser: null,
                form: { npk: '', name: '', origin_cpny_id: '', origin_department_id: '' },
                suggest: { cpny: false, dept: false },
                saving: false, msg: '', msgError: false,

                cards: [
                    { key: 'todo', label: 'Needs action', hint: 'everything below', color: 'text-gray-800 dark:text-white' },
                    { key: 'resigned', label: 'Resigned', hint: 'set status X', color: 'text-red-600' },
                    { key: 'rehire', label: 'Rehire', hint: 'same name + BOD', color: 'text-purple-600' },
                    { key: 'unlinked', label: 'Not linked', hint: 'no / name-only match', color: 'text-blue-600' },
                    { key: 'mismatch', label: 'Data differs', hint: 'sync with Talenta', color: 'text-amber-600' },
                    { key: 'dup', label: 'Duplicates', hint: 'same NPK or name', color: 'text-orange-600' },
                    { key: 'ok', label: 'In sync', hint: 'nothing to do', color: 'text-green-600' },
                    { key: 'all', label: 'All', hint: 'active accounts', color: 'text-gray-500' },
                ],
                badge: {
                    resigned: { label: 'Resigned', cls: 'bg-red-100 text-red-700' },
                    rehire: { label: 'Rehire', cls: 'bg-purple-100 text-purple-700' },
                    mismatch: { label: 'Data differs', cls: 'bg-amber-100 text-amber-700' },
                    link: { label: 'Name match only', cls: 'bg-blue-100 text-blue-700' },
                    no_match: { label: 'No Talenta match', cls: 'bg-gray-200 text-gray-700' },
                    ok: { label: 'In sync', cls: 'bg-green-100 text-green-700' },
                },

                async init() { await this.load(); },

                async load() {
                    this.loading = true;
                    try {
                        const r = await fetch('{{ route('user-data-sync.overview') }}', { headers: { Accept: 'application/json' } });
                        const d = await r.json();
                        this.rows = d.rows;
                        this.talentaDups = d.talenta_dups;
                        this.sel = {};
                    } catch (e) { this.flash('Failed to load.', true); }
                    this.loading = false;
                },

                inFilter(r, key) {
                    switch (key) {
                        case 'todo': return r.issue !== 'ok' || !!r.dup;
                        case 'unlinked': return r.issue === 'link' || r.issue === 'no_match';
                        case 'dup': return !!r.dup;
                        case 'all': return true;
                        default: return r.issue === key;
                    }
                },
                get counts() {
                    const c = {};
                    for (const k of this.cards) c[k.key] = 0;
                    for (const r of this.rows) for (const k of this.cards) if (this.inFilter(r, k.key)) c[k.key]++;
                    return c;
                },
                setFilter(k) { this.filter = k; this.limit = 200; this.sel = {}; },
                get companyOptions() { return [...new Set(this.rows.map(r => r.cpny).filter(Boolean))].sort(); },
                get filtered() {
                    const q = norm(this.q);
                    return this.rows.filter(r => this.inFilter(r, this.filter) &&
                        (!this.fCompany || r.cpny === this.fCompany) &&
                        (!q || norm(r.username + ' ' + r.name + ' ' + r.npk + ' ' + (r.talenta ? r.talenta.name + ' ' + r.talenta.npk : '')).includes(q)));
                },
                get visible() { return this.filtered.slice(0, this.limit); },

                get selCount() { return Object.values(this.sel).filter(Boolean).length; },
                selBy(issue) { return this.rows.filter(r => this.sel[r.id] && r.issue === issue); },
                get allSelected() { return this.visible.length > 0 && this.visible.every(r => this.sel[r.id]); },
                selectAll(on) { const s = { ...this.sel }; for (const r of this.visible) s[r.id] = on; this.sel = s; },

                flash(m, err) { this.msg = m; this.msgError = !!err; },

                // Run a request, show its message, and reload the list on success.
                async run(fn) {
                    this.busy = true; this.drawerErr = '';
                    const { ok, j } = await fn();
                    const m = j.message || Object.values(j.errors || {}).flat().join(' ') || (ok ? 'Done.' : 'Failed.');
                    if (ok) { this.drawer = null; this.flash(m); await this.load(); }
                    else { this.flash(m, true); this.drawerErr = m; }
                    this.busy = false;
                    return ok;
                },
                syncOne(r) { return this.run(() => json('PUT', '{{ url('/user-data-sync') }}/' + r.id, r.suggested)); },
                deactivateOne(r) {
                    if (!confirm('Set ' + r.username + ' to status X?')) return;
                    return this.run(() => json('POST', '{{ url('/user-data-sync') }}/' + r.id + '/deactivate'));
                },
                resolveRehire(r) {
                    if (!confirm('Rehire ' + r.name + ': keep one account on NPK ' + r.rehire.active_npk + ' and set the others to X?')) return;
                    return this.run(() => json('POST', '{{ route('user-data-sync.resolve-rehire') }}', { active_npk: r.rehire.active_npk }));
                },
                bulkSync() {
                    const ids = this.selBy('mismatch').map(r => r.id);
                    if (!confirm('Apply Talenta data to ' + ids.length + ' account(s)?')) return;
                    return this.run(() => json('POST', '{{ route('user-data-sync.sync-bulk') }}', { ids }));
                },
                bulkDeactivate() {
                    const ids = this.selBy('resigned').map(r => r.id);
                    if (!confirm('Set ' + ids.length + ' resigned account(s) to status X?')) return;
                    return this.run(() => json('POST', '{{ route('user-data-sync.deactivate-resigned') }}', { ids }));
                },

                // ---- drawer ----
                async ensureTalenta() {
                    if (this.talenta) return;
                    this.talenta = [];
                    const [t, o] = await Promise.all([
                        fetch('{{ route('user-data-sync.talenta') }}', { headers: { Accept: 'application/json' } }).then(r => r.json()),
                        fetch('{{ route('user-data-sync.options') }}', { headers: { Accept: 'application/json' } }).then(r => r.json()),
                    ]);
                    this.talenta = t.data;
                    this.users = u.data;
                    this.companies = o.companies;
                    this.departments = o.departments;
                    this.buildIndex();
                    this.loading = false;

                    // Alpine getters re-run on every access; cache the results and only recompute on change.
                    this.$watch('qTalenta', () => this.refilterTalenta());
                    this.$watch('fT', () => this.refilterTalenta());
                    ['qUsers', 'onlyMismatch', 'fU'].forEach(k => this.$watch(k, () => this.refilterUsers()));
                },

                buildIndex() {
                    this.talentaMap = new Map(this.talenta.map(t => [t.npk, t]));
                    this.talentaSearch = this.talenta.map(t => norm(t.npk + ' ' + t.name + ' ' + t.organization));
                    this.userSearch = this.users.map(u => norm(u.username + ' ' + u.name + ' ' + u.npk));
                    this.talentaCompanies = this.uniq(this.talenta.map(t => t.company));
                    this.talentaDepartments = this.uniq(this.talenta.map(t => t.department));
                    this.talentaStatuses = this.uniq(this.talenta.map(t => t.status));
                    this.userCompanies = this.uniq(this.users.map(u => u.origin_cpny_id));
                    this.userDepartments = this.uniq(this.users.map(u => u.origin_department_id));
                    this.refilterTalenta();
                    this.refilterUsers();
                },

                talentaByNpk(npk) {
                    return this.talentaMap.get(String(npk || '').trim());
                },
                get talentaHits() {
                    const q = norm(this.tq);
                    if (!q || !this.talenta) return [];
                    return this.talenta.filter(t => norm(t.npk + ' ' + t.name).includes(q)).slice(0, 12);
                },

                uniq(arr) { return [...new Set(arr.filter(Boolean))].sort(); },

                refilterTalenta() {
                    const q = norm(this.qTalenta), f = this.fT;
                    this.filteredTalenta = this.talenta.filter((t, i) =>
                        (!q || this.talentaSearch[i].includes(q)) &&
                        (!f.company || t.company === f.company) &&
                        (!f.department || t.department === f.department) &&
                        (!f.status || t.status === f.status));
                },
                refilterUsers() {
                    const q = norm(this.qUsers), f = this.fU;
                    this.filteredUsers = this.users.filter((u, i) => {
                        const t = this.talentaByNpk(u.npk);
                        return (!q || this.userSearch[i].includes(q)) &&
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
                    const dp = this.departments.find(d => norm(d.department_id) === norm(t.department) || norm(d.department_name) === norm(t.department));
                    this.form = {
                        npk: t.npk, name: t.name.toUpperCase(),
                        origin_cpny_id: co ? co.cpny_id : this.form.origin_cpny_id,
                        origin_department_id: dp ? dp.department_id : this.form.origin_department_id,
                    };
                },
                saveDrawer() {
                    return this.run(() => json('PUT', '{{ url('/user-data-sync') }}/' + this.drawer.id, this.form));
                },
            };
        }
    </script>
</x-app-layout>
