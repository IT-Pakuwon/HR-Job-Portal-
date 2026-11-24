<x-app-layout>

    <div class="max-w-9xl mx-auto w-full p-2" x-data="{ activeType: 'SUPERADMIN' }">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div class="flex flex-row items-start justify-between gap-4 rounded-t-xl border-b border-gray-100 bg-gray-50/60 px-5 py-4 dark:border-white/[0.06] dark:bg-white/[0.02] sm:flex-row sm:items-center">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                        <i class="fas fa-users text-sm"></i>
                    </span>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">User List</h2>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-4 text-sm font-medium text-indigo-700 dark:border-indigo-400/20 dark:bg-indigo-500/10 dark:text-indigo-300">
                        <i class="fas fa-users text-xs"></i>
                        Total Users <span id="totalUsers" class="font-semibold">-</span>
                    </span>
                    <div class="relative">
                        <i class="fas fa-map-marker-alt pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-indigo-400 dark:text-indigo-400/70"></i>
                        <select id="filterSiteId"
                            class="h-10 w-55 appearance-none rounded-full border border-gray-200 bg-gray-50 pl-8 pr-9 text-sm font-medium text-gray-700 shadow-sm transition hover:border-indigo-200 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:border-indigo-400/40 dark:hover:bg-white/10 dark:focus:bg-white/10">
                            <option value="ALL">All Sites</option>
                        </select>
                        <i class="fas fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 z-10 -translate-y-1/2 text-[10px] text-indigo-400"></i>
                    </div>
                    <button id="copyMsUserBtn" type="button"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-500 hover:shadow-md hover:shadow-indigo-600/30 active:bg-indigo-700">
                        <i class="fas fa-copy text-xs"></i>
                        Copy from ms_user
                    </button>
                </div>
            </div>

            <nav class="flex flex-wrap border-b border-gray-100 dark:border-white/[0.06]">
                <button type="button" @click="activeType = 'SUPERADMIN'; setUserTypeFilter('SUPERADMIN')"
                    :class="activeType === 'SUPERADMIN' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 min-w-25 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Superadmin <span id="countSUPERADMIN" class="ml-1 text-xs text-gray-400"></span>
                </button>
                <button type="button" @click="activeType = 'ADMIN'; setUserTypeFilter('ADMIN')"
                    :class="activeType === 'ADMIN' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 min-w-25 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Admin <span id="countADMIN" class="ml-1 text-xs text-gray-400"></span>
                </button>
                <button type="button" @click="activeType = 'USER'; setUserTypeFilter('USER')"
                    :class="activeType === 'USER' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 min-w-25 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    User <span id="countUSER" class="ml-1 text-xs text-gray-400"></span>
                </button>
                <button type="button" @click="activeType = 'CHECKER'; setUserTypeFilter('CHECKER')"
                    :class="activeType === 'CHECKER' ?
                        'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400' :
                        'border-b-2 border-transparent text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100'"
                    class="flex-1 min-w-25 px-4 py-3 text-center text-sm font-medium transition-colors duration-200">
                    Checker <span id="countCHECKER" class="ml-1 text-xs text-gray-400"></span>
                </button>
            </nav>

            <div class="relative overflow-hidden p-2">
                <table id="usersTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-32 px-4 py-3 text-left font-medium">Actions</th>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">Username</th>
                            <th class="px-4 py-3 text-left font-medium">Email</th>
                            <th class="px-4 py-3 text-left font-medium">User Type</th>
                            <th class="px-4 py-3 text-left font-medium">Site</th>
                            <th class="w-28 px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- COPY FROM MS_USER MODAL -->
        <div id="copyMsUserModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-2xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4 dark:border-white/[0.06] dark:from-indigo-500/10 dark:to-transparent">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <i class="fas fa-user-plus"></i>
                        </span>
                        <div>
                            <h2 class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-100">Copy from ms_user</h2>
                            <p class="mt-0.5 text-xs leading-relaxed text-gray-500 dark:text-gray-400"><span id="copyMsUserHint">Superadmin / Admin / Checker: people with the <b>ACCESSTS</b> role who aren't in tenancy yet.</span></p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200" data-modal="copyMsUserModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>

                <div class="space-y-3 border-b border-gray-100 px-6 py-4 dark:border-white/[0.06]">
                    <div>
                        <p class="mb-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Create as <span class="text-red-500">*</span></p>
                        <input type="hidden" id="copyMsUserType" value="">
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" data-type="USER" class="copyTypeBtn rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 transition hover:border-indigo-300 hover:bg-indigo-50/60 dark:border-white/10 dark:text-gray-300 dark:hover:bg-indigo-500/10">User</button>
                            <button type="button" data-type="SUPERADMIN" class="copyTypeBtn rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 transition hover:border-indigo-300 hover:bg-indigo-50/60 dark:border-white/10 dark:text-gray-300 dark:hover:bg-indigo-500/10">Superadmin</button>
                            <button type="button" data-type="ADMIN" class="copyTypeBtn rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 transition hover:border-indigo-300 hover:bg-indigo-50/60 dark:border-white/10 dark:text-gray-300 dark:hover:bg-indigo-500/10">Admin</button>
                            <button type="button" data-type="CHECKER" class="copyTypeBtn rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-600 transition hover:border-indigo-300 hover:bg-indigo-50/60 dark:border-white/10 dark:text-gray-300 dark:hover:bg-indigo-500/10">Checker</button>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                            <input id="copyMsUserSearch" type="text" placeholder="Search name, username or email"
                                class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 pl-9 pr-3 text-sm text-gray-700 placeholder-gray-400 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                        </div>
                        <label class="flex shrink-0 cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5">
                            <input id="copyMsUserAll" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"> Select all
                        </label>
                    </div>
                </div>

                <p class="border-b border-gray-100 bg-amber-50/60 px-6 py-2 text-[11px] leading-relaxed text-amber-800 dark:border-white/[0.06] dark:bg-amber-500/10 dark:text-amber-300">
                    <b>Auto-filled:</b> Site &amp; Company from origin company (AW&rarr;GC, EP&rarr;KK, GPS&rarr;PMB, PSA&rarr;PBM). Superadmin gets all sites. <b>Department is left empty</b> &mdash; fill it in the User List (marked &ldquo;Fill department&rdquo;).
                </p>
                <div id="copyMsUserList" class="flex-1 space-y-1.5 overflow-y-auto bg-gray-50/50 px-4 py-3 text-sm dark:bg-transparent"></div>
                <div id="copyMsUserPager" class="hidden items-center justify-between gap-3 border-t border-gray-100 px-6 py-2.5 dark:border-white/[0.06]"></div>

                <div class="flex items-center justify-between gap-3 border-t border-gray-100 px-6 py-4 dark:border-white/[0.06]">
                    <span id="copyMsUserCount" class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300">0 selected</span>
                    <div class="flex gap-2">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5" data-modal="copyMsUserModal">Cancel</button>
                        <button type="button" id="copyMsUserSubmit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm shadow-indigo-600/20 transition hover:bg-indigo-500 disabled:opacity-60"><i class="fas fa-copy text-xs"></i> Copy selected</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- USER MODAL -->
        <div id="userModal" class="tsModalBackdrop fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200">
            <div class="ts-modal-panel relative flex max-h-[90vh] w-full max-w-2xl scale-95 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">
                <div class="flex items-center justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50/80 to-white px-6 py-4 dark:border-white/[0.06] dark:from-indigo-500/10 dark:to-transparent">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <i class="fas fa-user"></i>
                        </span>
                        <div>
                            <h2 id="userModalTitle" class="text-base font-semibold leading-tight text-gray-800 dark:text-gray-100">Add User</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">System user account &amp; access</p>
                        </div>
                    </div>
                    <button type="button" class="tsModalClose flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/5 dark:hover:text-gray-200"
                        data-modal="userModal" aria-label="Close"><i class="fas fa-times"></i></button>
                </div>
                <form id="userForm" class="flex flex-1 flex-col overflow-hidden">
                    <input type="hidden" id="usr_id" name="id">
                    <div class="flex-1 space-y-6 overflow-y-auto px-6 py-5">

                        <div>
                            <p class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500 before:h-3.5 before:w-1 before:rounded-full before:bg-indigo-500 dark:text-gray-400">Profile</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Name <span class="text-red-500">*</span></label>
                                    <div class="relative"><i class="fas fa-user pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
<input type="text" id="usr_name" name="name" placeholder="e.g. John Doe"
class="w-full rounded-xl border border-gray-200 bg-gray-50/60 py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 read-only:cursor-not-allowed read-only:bg-gray-100 read-only:text-gray-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-gray-500 dark:read-only:bg-white/[0.03] dark:disabled:bg-white/[0.03]" required></div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Company Name</label>
                                    <div class="relative"><i class="fas fa-building pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
<input type="text" id="usr_companyname" name="companyname" placeholder="e.g. PT Maju Jaya"
class="w-full rounded-xl border border-gray-200 bg-gray-50/60 py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 read-only:cursor-not-allowed read-only:bg-gray-100 read-only:text-gray-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-gray-500 dark:read-only:bg-white/[0.03] dark:disabled:bg-white/[0.03]"></div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-5 dark:border-white/[0.06]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500 before:h-3.5 before:w-1 before:rounded-full before:bg-indigo-500 dark:text-gray-400">Contact &amp; Login</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Email <span class="text-red-500">*</span></label>
                                    <div class="relative"><i class="fas fa-envelope pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
<input type="email" id="usr_email" name="email" placeholder="name@company.com"
class="w-full rounded-xl border border-gray-200 bg-gray-50/60 py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 read-only:cursor-not-allowed read-only:bg-gray-100 read-only:text-gray-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-gray-500 dark:read-only:bg-white/[0.03] dark:disabled:bg-white/[0.03]" required></div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Phone</label>
                                    <div class="relative"><i class="fas fa-phone pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
<input type="text" id="usr_phone" name="phone" placeholder="e.g. 0812 3456 7890"
class="w-full rounded-xl border border-gray-200 bg-gray-50/60 py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 read-only:cursor-not-allowed read-only:bg-gray-100 read-only:text-gray-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-gray-500 dark:read-only:bg-white/[0.03] dark:disabled:bg-white/[0.03]"></div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Username <span class="text-red-500">*</span></label>
                                    <div class="relative"><i class="fas fa-at pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
<input type="text" id="usr_username" name="username" autocomplete="off" placeholder="Login username"
class="w-full rounded-xl border border-gray-200 bg-gray-50/60 py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 read-only:cursor-not-allowed read-only:bg-gray-100 read-only:text-gray-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-gray-500 dark:read-only:bg-white/[0.03] dark:disabled:bg-white/[0.03]" required></div>
                                </div>
                                <div>
                                    <div class="mb-1.5 flex items-center justify-between">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                            Password <span id="usr_password_hint" class="text-xs font-normal text-gray-400"></span>
                                        </label>
                                        <button type="button" id="usr_reset_password_btn"
                                            class="hidden text-xs font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            <i class="fas fa-key"></i> Reset Password
                                        </button>
                                    </div>
                                    <div class="relative"><i class="fas fa-lock pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
<input type="password" id="usr_password" name="password" autocomplete="new-password" placeholder="Default password will be applied"
class="w-full rounded-xl border border-gray-200 bg-gray-50/60 py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition hover:border-indigo-300 hover:bg-white focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/15 read-only:cursor-not-allowed read-only:bg-gray-100 read-only:text-gray-500 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-gray-500 dark:read-only:bg-white/[0.03] dark:disabled:bg-white/[0.03]"></div>
                                </div>
                            </div>
                            <p id="usr_password_note" class="mt-3 flex items-start gap-2 rounded-lg bg-indigo-50 px-3 py-2 text-xs text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"><i class="fas fa-info-circle mt-0.5"></i><span>New users are created with the default password and can change it after first login.</span></p>
                        </div>

                        <div class="border-t border-gray-100 pt-5 dark:border-white/[0.06]">
                            <p class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500 before:h-3.5 before:w-1 before:rounded-full before:bg-indigo-500 dark:text-gray-400">Access</p>
                            <div id="usr_access_grid" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">User Type <span class="text-red-500">*</span></label>
                                    <select id="usr_usertype" name="usertype" class="ts-select2 w-full" required>
                                        <option value="">Select user type</option>
                                        <option value="SUPERADMIN">SUPERADMIN</option>
                                        <option value="ADMIN">ADMIN</option>
                                        <option value="USER">USER</option>
                                        <option value="CHECKER">CHECKER</option>
                                        <option value="TENANT">TENANT</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Site</label>
                                    <div id="usr_site_single_wrap">
                                        <select id="usr_siteid" name="siteid" class="ts-select2 w-full">
                                            <option value="">Select site</option>
                                        </select>
                                    </div>
                                    <div id="usr_site_multi_wrap" class="hidden">
                                        <select id="usr_siteids" name="siteids[]" multiple class="ts-select2 w-full"></select>
                                        <p class="mt-1 text-[11px] text-gray-400">Superadmin can access multiple sites. The first one is the default.</p>
                                    </div>
                                </div>
                                <div id="usr_departmentid_wrap">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-200">Department</label>
                                    <select id="usr_departmentid" name="departmentid" class="ts-select2 w-full">
                                        <option value="">Select department</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-white/[0.06] dark:bg-white/[0.02]">
                        <button type="button" class="tsModalClose rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-200 dark:hover:bg-white/5"
                            data-modal="userModal">Cancel</button>
                        <button type="submit" id="userSaveBtn"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-500 hover:shadow focus:outline-none focus:ring-2 focus:ring-indigo-500/50"><i class="fas fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
            <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
                <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Processing...</span>
            </div>
        </div>
    </div>

    <style>
        .ts-select2-container.select2-container--default .select2-selection--single {
            height: 38px !important;
            display: flex;
            align-items: center;
            border-radius: 9999px !important;
            border: 1px solid rgb(226 232 240) !important;
            padding: 0 !important;
            background-color: rgb(248 250 252) !important;
            box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.04);
            transition: border-color .15s ease, box-shadow .15s ease, background-color .15s ease;
        }
        .ts-select2-container.select2-container--default .select2-selection--multiple {
            min-height: 38px;
            border-radius: 1.25rem !important;
            border: 1px solid rgb(226 232 240) !important;
            background-color: rgb(248 250 252) !important;
            padding: 2px 6px !important;
            box-shadow: 0 1px 2px 0 rgba(15, 23, 42, 0.04);
        }
        .ts-select2-container.select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: rgb(99 102 241) !important;
        }
        .ts-select2-container.select2-container--default .select2-selection--multiple .select2-selection__choice {
            border-radius: 9999px;
            background: rgb(238 242 255);
            border: 1px solid rgb(199 210 254);
            color: rgb(67 56 202);
            font-size: 12px;
            padding: 1px 8px;
        }
        .ts-select2-container.select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color: rgb(129 140 248); margin-right: 4px; }
        .ts-select2-container.select2-container--default .select2-selection--single:hover {
            border-color: rgb(199 210 254) !important;
            background-color: #fff !important;
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            padding-left: 0.75rem !important;
            padding-right: 1.75rem !important;
            color: rgb(51 65 85);
            font-weight: 500;
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 10px !important;
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #7bc8ff transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
        }
        .ts-select2-container.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #7bc8ff transparent !important;
            border-width: 0 4px 5px 4px !important;
        }
        .ts-select2-container.select2-container--default.select2-container--focus .select2-selection--single,
        .ts-select2-container.select2-container--default.select2-container--open .select2-selection--single {
            border-color: #56b1f3 !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 3px rgba(86, 177, 243, 0.15);
        }
        .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: rgb(148 163 184);
            font-weight: 400;
        }
        html.dark .ts-select2-container.select2-container--default .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            box-shadow: none;
        }
        html.dark .ts-select2-container.select2-container--default .select2-selection--single:hover {
            border-color: rgba(123, 200, 255, 0.4) !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
        html.dark .ts-select2-container.select2-container--default.select2-container--focus .select2-selection--single,
        html.dark .ts-select2-container.select2-container--default.select2-container--open .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.08) !important;
        }
        html.dark .ts-select2-container.select2-container--default .select2-selection--single .select2-selection__rendered {
            color: rgb(226 232 240) !important;
        }
        .ts-select2-dropdown.select2-dropdown {
            border-radius: 0.75rem !important;
            border-color: rgb(226 232 240) !important;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.1) !important;
            overflow: hidden;
            padding: 4px;
        }
        .ts-select2-dropdown .select2-results__option {
            border-radius: 0.5rem !important;
        }
        .ts-select2-dropdown .select2-results__option--highlighted[aria-selected] {
            background-color: #e3f3ff !important;
            color: #1c71ae !important;
        }
        .ts-select2-dropdown .select2-results__option[aria-selected="true"] {
            background-color: #e3f3ff !important;
            color: #1c71ae !important;
            font-weight: 600;
        }
        html.dark .ts-select2-dropdown.select2-dropdown {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
        html.dark .ts-select2-dropdown .select2-search--dropdown .select2-search__field {
            background-color: #0b1220 !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            border-radius: 0.5rem !important;
            color: #e5e7eb !important;
        }
        html.dark .ts-select2-dropdown .select2-results__option {
            color: #e5e7eb !important;
        }
        html.dark .ts-select2-dropdown .select2-results__option--highlighted[aria-selected],
        html.dark .ts-select2-dropdown .select2-results__option[aria-selected="true"] {
            background-color: #3193da !important;
            color: #fff !important;
        }
        .ts-select2-container.select2-container--disabled .select2-selection--single {
            background-color: rgb(243 244 246) !important;
            cursor: not-allowed;
        }
        html.dark .ts-select2-container.select2-container--disabled .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function showLoading() { $('#loadingOverlay').removeClass('hidden'); }
        function hideLoading() { $('#loadingOverlay').addClass('hidden'); }

        function toastError(text) {
            Swal.fire({ icon: 'error', title: 'Error', text: text || 'Something went wrong' });
        }

        function toastSuccess(text) {
            Swal.fire({ icon: 'success', title: 'Success', text: text, timer: 1500, showConfirmButton: false });
        }

        function statusBadge(data) {
            return data === 'A' ?
                '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>' :
                '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
        }

        function tsAutoSelect($select, selectedValue, values) {
            if (selectedValue) {
                $select.val(selectedValue);
            } else if (values.length === 1) {
                $select.val(values[0]).trigger('change');
            }
        }

        function tsOpenModal(id) {
            let $modal = $('#' + id);
            $modal.removeClass('hidden').addClass('flex');
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    $modal.removeClass('opacity-0').addClass('opacity-100');
                    $modal.find('.ts-modal-panel').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                });
            });
        }

        function tsCloseModal(id) {
            let $modal = $('#' + id);
            $modal.removeClass('opacity-100').addClass('opacity-0');
            $modal.find('.ts-modal-panel').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
            setTimeout(function() {
                $modal.addClass('hidden').removeClass('flex');
            }, 200);
        }

        // Declared at top level (not inside $(document).ready) because Alpine's @click
        // expressions only resolve against the global scope — a function scoped inside the
        // ready callback is invisible to Alpine and the click silently no-ops.
        let userTable;
        let activeUserTypeFilter = 'SUPERADMIN';
        let activeSiteFilter = 'ALL';

        function setUserTypeFilter(type) {
            activeUserTypeFilter = type;
            if (userTable) userTable.draw();
        }

        function rowHasSite(row, siteid) {
            return row.siteid === siteid || (Array.isArray(row.sites) && row.sites.includes(siteid));
        }

        function updateTotalUsers() {
            let rows = (userTable && userTable.ajax.json() || {}).data || [];
            let total = rows.filter(function(row) {
                return row.usertype !== 'TENANT' && (activeSiteFilter === 'ALL' || rowHasSite(row, activeSiteFilter));
            }).length;
            $('#totalUsers').text(total.toLocaleString());
        }

        function setSiteFilter(siteid) {
            activeSiteFilter = siteid;
            if (userTable) { userTable.draw(); updateTotalUsers(); }
        }

        $(document).ready(function() {

            // ---- Copy from ms_user ----
            let msUserTimer = null;
            function escHtml(s) { return $('<div>').text(s ?? '').html(); }
            const MS_PER_PAGE = 10;
            let msRows = [];          // filtered result from the server
            let msPage = 1;
            const msSel = new Set();  // selected usernames, kept across pages and searches
            function updateCopyCount() {
                const n = msSel.size;
                $('#copyMsUserCount').text(n + ' selected')
                    .toggleClass('bg-indigo-50 text-indigo-700', n > 0)
                    .toggleClass('bg-gray-100 text-gray-600', n === 0);
                const selectable = msRows.filter(r => !r.problem);
                $('#copyMsUserAll').prop('checked', selectable.length > 0 && selectable.every(r => msSel.has(r.username)));
            }
            function setCopyType(type) {
                $('#copyMsUserType').val(type);
                $('.copyTypeBtn').each(function() {
                    const on = $(this).data('type') === type;
                    $(this).toggleClass('border-indigo-500 bg-indigo-50 text-indigo-700 ring-2 ring-indigo-500/20', on)
                           .toggleClass('border-gray-200 text-gray-600', !on);
                });
            }
            function initials(name) {
                const p = String(name || '?').trim().split(/\s+/);
                return ((p[0] || '')[0] + (p.length > 1 ? p[p.length - 1][0] : '')).toUpperCase();
            }
            function renderMsPage() {
                const totalPages = Math.max(1, Math.ceil(msRows.length / MS_PER_PAGE));
                msPage = Math.min(Math.max(1, msPage), totalPages);

                if (!msRows.length) {
                    $('#copyMsUserList').html('<div class="py-12 text-center text-gray-400"><i class="fas fa-check-circle mb-2 text-2xl text-emerald-400"></i><p>Nobody left to copy.</p></div>');
                    $('#copyMsUserPager').addClass('hidden').removeClass('flex');
                    updateCopyCount();
                    return;
                }

                const start = (msPage - 1) * MS_PER_PAGE;
                $('#copyMsUserList').html(msRows.slice(start, start + MS_PER_PAGE).map(r => `
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-white px-3 py-2.5 transition hover:border-indigo-300 has-[:checked]:border-indigo-400 has-[:checked]:bg-indigo-50/70 dark:border-white/10 dark:bg-white/[0.03] dark:has-[:checked]:bg-indigo-500/10 ${r.problem ? 'cursor-not-allowed opacity-50' : ''}">
                        <input type="checkbox" class="msUserChk h-4 w-4 shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="${escHtml(r.username)}" ${r.problem ? 'disabled' : ''} ${msSel.has(r.username) ? 'checked' : ''}>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300">${escHtml(initials(r.name))}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium text-gray-800 dark:text-gray-100">${escHtml(r.name)}</span>
                            <span class="block truncate text-xs text-gray-500 dark:text-gray-400">${escHtml(r.username)} &middot; ${escHtml(r.email)}</span>
                        </span>
                        ${r.problem ? `<span class="shrink-0 rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-medium text-red-600 dark:bg-red-500/10 dark:text-red-400">${escHtml(r.problem)}</span>` : ''}
                    </label>`).join(''));
                $('#copyMsUserList').scrollTop(0);

                // pager: first, last, and a window around the current page
                const pages = [];
                for (let p = 1; p <= totalPages; p++) {
                    if (p === 1 || p === totalPages || Math.abs(p - msPage) <= 1) pages.push(p);
                    else if (pages[pages.length - 1] !== '...') pages.push('...');
                }
                const btn = 'msPageBtn min-w-8 rounded-lg border px-2.5 py-1 text-xs font-medium transition';
                const idle = 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5';
                const on = 'border-indigo-500 bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300';
                $('#copyMsUserPager').removeClass('hidden').addClass('flex').html(`
                    <span class="text-xs text-gray-500 dark:text-gray-400">${start + 1}&ndash;${Math.min(start + MS_PER_PAGE, msRows.length)} of ${msRows.length}</span>
                    <div class="flex items-center gap-1">
                        <button type="button" class="${btn} ${idle} disabled:opacity-40" data-page="${msPage - 1}" ${msPage === 1 ? 'disabled' : ''}><i class="fas fa-chevron-left text-[10px]"></i></button>
                        ${pages.map(p => p === '...'
                            ? '<span class="px-1 text-xs text-gray-400">&hellip;</span>'
                            : `<button type="button" class="${btn} ${p === msPage ? on : idle}" data-page="${p}">${p}</button>`).join('')}
                        <button type="button" class="${btn} ${idle} disabled:opacity-40" data-page="${msPage + 1}" ${msPage === totalPages ? 'disabled' : ''}><i class="fas fa-chevron-right text-[10px]"></i></button>
                    </div>`);
                updateCopyCount();
            }
            function loadMsUsers() {
                const q = $('#copyMsUserSearch').val();
                const type = $('#copyMsUserType').val();
                $('#copyMsUserList').html('<p class="py-10 text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i>Loading...</p>');
                $('#copyMsUserPager').addClass('hidden').removeClass('flex');
                $('#copyMsUserHint').html(type === 'USER'
                    ? 'User: <b>any active ms_user</b> who isn\'t in tenancy yet.'
                    : 'Superadmin / Admin / Checker: people with the <b>ACCESSTS</b> role who aren\'t in tenancy yet.');
                $.get("{{ route('tenancy.user.ms-users') }}", { q: q, usertype: type }, function(res) {
                    msRows = res.data || [];
                    msPage = 1;
                    renderMsPage();
                }).fail(function() { toastError('Gagal mengambil data ms_user'); });
            }
            $(document).on('click', '.msPageBtn', function() {
                if ($(this).prop('disabled')) return;
                msPage = parseInt($(this).data('page'), 10);
                renderMsPage();
            });
            $(document).on('click', '.copyTypeBtn', function() {
                const wasUser = $('#copyMsUserType').val() === 'USER';
                setCopyType($(this).data('type'));
                if (wasUser !== ($(this).data('type') === 'USER')) { msSel.clear(); loadMsUsers(); } // list scope changes only between USER and the rest
            });

            $('#copyMsUserBtn').on('click', function() {
                $('#copyMsUserSearch').val('');
                msSel.clear();
                setCopyType('');
                tsOpenModal('copyMsUserModal');
                loadMsUsers();
            });
            $('#copyMsUserSearch').on('input', function() {
                clearTimeout(msUserTimer);
                msUserTimer = setTimeout(loadMsUsers, 300);
            });
            // "Select all" covers every selectable person in the current result, across all pages.
            $('#copyMsUserAll').on('change', function() {
                const on = this.checked;
                msRows.filter(r => !r.problem).forEach(r => on ? msSel.add(r.username) : msSel.delete(r.username));
                renderMsPage();
            });
            $(document).on('change', '.msUserChk', function() {
                this.checked ? msSel.add(this.value) : msSel.delete(this.value);
                updateCopyCount();
            });

            $('#copyMsUserSubmit').on('click', function() {
                const usernames = Array.from(msSel);
                if (!usernames.length) return toastError('Select at least one user');
                const usertype = $('#copyMsUserType').val();
                if (!usertype) return toastError('Select a User Type (Superadmin, Admin or Checker)');
                const $btn = $(this).prop('disabled', true);
                $.ajax({
                    url: "{{ route('tenancy.user.copy-from-ms-user') }}",
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { usernames: usernames, usertype: usertype },
                    success: function(res) {
                        tsCloseModal('copyMsUserModal');
                        userTable.ajax.reload();
                        const skipped = res.skipped || [];
                        Swal.fire({
                            icon: skipped.length ? 'warning' : 'success',
                            title: `${res.created.length} copied` + (skipped.length ? `, ${skipped.length} skipped` : ''),
                            width: 600,
                            html: (skipped.length ? '<p class="mb-2 text-left"><b>Skipped</b></p><div class="text-left text-sm">' + skipped.map(s => `${escHtml(s.username)}: ${escHtml(s.reason)}`).join('<br>') + '</div><br>' : '')
                                + ((res.needs || []).length
                                    ? '<p class="mb-2 text-left"><b>Still to fill in (User List)</b></p><div class="text-left text-sm" style="max-height:220px;overflow:auto">'
                                        + res.needs.map(n => `${escHtml(n.username)}: <b>${escHtml(n.todo.join(', '))}</b>` + (n.todo.length > 1 ? ` <span style="color:#9ca3af">(origin company ${escHtml(n.origin || 'empty')} has no site mapping)</span>` : '')).join('<br>')
                                        + '</div>'
                                    : ''),
                        });
                    },
                    error: function(xhr) { toastError(xhr.responseJSON?.message || 'Gagal copy user'); },
                    complete: function() { $btn.prop('disabled', false); },
                });
            });

            $(document).on('click', '.tsModalClose', function() {
                tsCloseModal($(this).data('modal'));
            });

            $(document).on('click', '.tsModalBackdrop', function(e) {
                if (e.target === this) tsCloseModal(this.id);
            });

            // Reads the row's raw JSON (usertype/siteid) rather than rendered column text/index,
            // so it can't be thrown off by column order or the Actions column's HTML render.
            $.fn.dataTable.ext.search.push(function(settings, searchData, dataIndex) {
                if (settings.nTable.id !== 'usersTable') return true;

                let row = userTable.row(dataIndex).data();
                if (!row) return true;

                if (activeUserTypeFilter !== 'ALL' && row.usertype !== activeUserTypeFilter) return false;
                if (activeSiteFilter !== 'ALL' && !rowHasSite(row, activeSiteFilter)) return false;
                return true;
            });

            function updateUserTypeCounts(rows) {
                let counts = { SUPERADMIN: 0, ADMIN: 0, USER: 0, CHECKER: 0, TENANT: 0 };
                rows.forEach(function(row) {
                    if (Object.prototype.hasOwnProperty.call(counts, row.usertype)) counts[row.usertype]++;
                });
                Object.keys(counts).forEach(function(type) {
                    $('#count' + type).text(counts[type] ? `(${counts[type]})` : '');
                });
            }


            userTable = $('#usersTable').DataTable({
                ajax: "{{ route('tenancy.user.json') }}",
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center items-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleUserStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button type="button" class="viewUserBtn bg-gray-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-eye"></i></button>
                                    <button type="button" class="editUserBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="resetPasswordBtn bg-amber-500 text-white px-2 py-1 rounded" data-id="${data}" title="Reset Password"><i class="fas fa-key"></i></button>
                                </div>
                            `;
                        }
                    },
                    { data: 'name', render: function(d, t, row) {
                        if (t !== 'display' || row.departmentid) return d;
                        return `${escHtml(d)} <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400" title="Department is empty - edit this user and fill it in"><i class="fas fa-exclamation-circle text-[10px]"></i>Fill department</span>`;
                    } },
                    { data: 'username' },
                    { data: 'email' },
                    { data: 'usertype', render: d => d ?? '-' },
                    { data: 'siteid', render: (d, t, row) => (row.sites && row.sites.length > 1) ? row.sites.join(', ') : (d ?? '-') },
                    { data: 'status', render: statusBadge },
                ]
            });

            userTable.on('xhr', function() {
                updateTotalUsers();
                updateUserTypeCounts(userTable.ajax.json().data || []);
            });

            $.get("{{ route('tenancy.master.sites.options') }}", function(res) {
                let $filter = $('#filterSiteId');
                (res.data || []).forEach(function(site) {
                    $filter.append(`<option value="${site.siteid}">${site.sitename} (${site.siteid})</option>`);
                });
            });

            $('#filterSiteId').on('change', function() {
                setSiteFilter($(this).val());
            });

            $('#usr_usertype').select2({
                width: '100%',
                dropdownParent: $('#userModal'),
                placeholder: 'Select user type',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            $('#usr_siteid').select2({
                width: '100%',
                dropdownParent: $('#userModal'),
                placeholder: 'Select site',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            $('#usr_siteids').select2({
                width: '100%',
                dropdownParent: $('#userModal'),
                placeholder: 'Select sites',
                closeOnSelect: false,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            $('#usr_departmentid').select2({
                width: '100%',
                dropdownParent: $('#userModal'),
                placeholder: 'Select department',
                allowClear: true,
                containerCssClass: 'ts-select2-container',
                dropdownCssClass: 'ts-select2-dropdown',
            });

            function toggleTenantOnlyFields() {
                let isTenant = $('#usr_usertype').val() === 'TENANT';
                $('#usr_departmentid_wrap').toggleClass('hidden', isTenant);
                $('#usr_access_grid').toggleClass('md:grid-cols-3', !isTenant).toggleClass('md:grid-cols-2', isTenant);
            }

            function toggleSiteMode() {
                let multi = $('#usr_usertype').val() === 'SUPERADMIN';
                let readOnly = !!$('#userForm').data('ro');
                $('#usr_site_single_wrap').toggleClass('hidden', multi);
                $('#usr_site_multi_wrap').toggleClass('hidden', !multi);
                $('#usr_siteid').prop('disabled', multi || readOnly);
                $('#usr_siteids').prop('disabled', !multi || readOnly);
            }

            $('#usr_usertype').on('change', function() { toggleTenantOnlyFields(); toggleSiteMode(); });

            function loadSiteOptions($select, selectedId) {
                return $.get("{{ route('tenancy.master.sites.options') }}", function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(site) {
                        $select.append(`<option value="${site.siteid}">${site.sitename} (${site.siteid})</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(s => s.siteid));
                    $select.trigger('change');
                });
            }

            function loadSiteMultiOptions(selectedIds) {
                return $.get("{{ route('tenancy.master.sites.options') }}", function(res) {
                    let $m = $('#usr_siteids').empty();
                    (res.data || []).forEach(function(site) {
                        $m.append(`<option value="${site.siteid}">${site.sitename} (${site.siteid})</option>`);
                    });
                    $m.val(selectedIds || []).trigger('change');
                });
            }

            function loadDepartmentOptions($select, selectedId, siteid) {
                return $.get("{{ route('tenancy.organization.departments.options') }}", { siteid: siteid || '' }, function(res) {
                    $select.find('option:not(:first)').remove();
                    (res.data || []).forEach(function(dept) {
                        $select.append(`<option value="${dept.departmentid}">${dept.departmentname} (${dept.departmentid})</option>`);
                    });
                    tsAutoSelect($select, selectedId, (res.data || []).map(d => d.departmentid));
                    $select.trigger('change');
                });
            }

            // Only reacts to genuine user picks/clears (select2:select / select2:clear), never to the
            // programmatic .trigger('change') calls used elsewhere to refresh select2's disabled state
            // or to auto-select a single option — otherwise this would wipe out the department that
            // openUserModal() just pre-selected for an existing user.
            $('#usr_siteid').on('select2:select select2:clear', function() {
                loadDepartmentOptions($('#usr_departmentid'), null, $(this).val());
            });

            // Department follows the primary (first) site.
            $('#usr_siteids').on('select2:select select2:unselect', function() {
                loadDepartmentOptions($('#usr_departmentid'), null, ($(this).val() || [])[0]);
            });

            function openUserModal(mode, data) {
                let readOnly = mode === 'view';
                $('#userForm').data('ro', readOnly);
                $('#userForm')[0].reset();
                $('#usr_id').val('');
                $('#userModalTitle').text(mode === 'add' ? 'Add User' : (mode === 'view' ? 'View User' : 'Edit User'));
                $('#userForm input, #userForm select').prop('disabled', readOnly);
                $('#usr_usertype, #usr_siteid, #usr_siteids, #usr_departmentid').prop('disabled', readOnly).trigger('change');
                $('#userSaveBtn').toggle(!readOnly);
                $('#usr_password').prop('required', false).prop('readonly', mode === 'add').attr('placeholder', mode === 'add' ? 'Default password will be applied' : 'Leave blank to keep current password');
                $('#usr_password_note').toggleClass('hidden', mode !== 'add');
                $('#usr_password_hint').text('');
                $('#usr_reset_password_btn').toggleClass('hidden', mode !== 'edit').data('id', data ? data.id : null);

                $('#usr_departmentid').find('option:not(:first)').remove().end().val('').trigger('change');

                let isSuper = !!data && data.usertype === 'SUPERADMIN';
                let multiIds = isSuper ? ((data.siteids && data.siteids.length) ? data.siteids : (data.siteid ? [data.siteid] : [])) : [];
                $.when(loadSiteOptions($('#usr_siteid'), data ? data.siteid : null), loadSiteMultiOptions(multiIds)).done(function() {
                    loadDepartmentOptions($('#usr_departmentid'), data ? data.departmentid : null, isSuper ? multiIds[0] : $('#usr_siteid').val());
                    toggleSiteMode();
                });

                if (data) {
                    $('#usr_id').val(data.id);
                    $('#usr_name').val(data.name);
                    $('#usr_companyname').val(data.companyname);
                    $('#usr_email').val(data.email);
                    $('#usr_phone').val(data.phone);
                    $('#usr_username').val(data.username);
                    $('#usr_usertype').val(data.usertype).trigger('change');
                }

                toggleSiteMode();
                tsOpenModal('userModal');
            }

            $('#addUserBtn').click(function() {
                openUserModal('add', null);
            });

            $(document).on('click', '.viewUserBtn, .editUserBtn', function() {
                let id = $(this).data('id');
                let mode = $(this).hasClass('viewUserBtn') ? 'view' : 'edit';

                showLoading();
                $.get(`/tenancy/user/${id}/edit`, function(d) {
                    hideLoading();
                    openUserModal(mode, d);
                }).fail(function(xhr) {
                    hideLoading();
                    toastError('Gagal mengambil data user');
                    console.error(xhr.responseText);
                });
            });

            $('#userForm').submit(function(e) {
                e.preventDefault();
                let id = $('#usr_id').val();
                let url = id ? `/tenancy/user/${id}` : "{{ route('tenancy.user.store') }}";
                let formData = new FormData(document.getElementById('userForm'));
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        hideLoading();
                        tsCloseModal('userModal');
                        userTable.ajax.reload(null, false);
                        toastSuccess('User saved successfully');
                    },
                    error: function(xhr) {
                        hideLoading();
                        toastError(xhr.responseJSON?.message || 'Gagal menyimpan user');
                        console.error(xhr.responseText);
                    }
                });
            });

            $(document).on('change', '.toggleUserStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                $.ajax({
                    url: `/tenancy/user/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() { userTable.ajax.reload(null, false); }
                });
            });

            function resetUserPassword(id) {
                if (!id) return;

                Swal.fire({
                    icon: 'warning',
                    title: 'Reset Password?',
                    text: 'Password will be reset to the default password.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, reset it',
                    confirmButtonColor: '#3193da',
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    showLoading();
                    $.ajax({
                        url: `/tenancy/user/${id}/reset-password`,
                        type: 'PUT',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: function() {
                            hideLoading();
                            toastSuccess('Password has been reset successfully');
                        },
                        error: function(xhr) {
                            hideLoading();
                            toastError(xhr.responseJSON?.message || 'Gagal mereset password');
                            console.error(xhr.responseText);
                        }
                    });
                });
            }

            $('#usr_reset_password_btn').on('click', function() {
                resetUserPassword($(this).data('id'));
            });

            $(document).on('click', '.resetPasswordBtn', function() {
                resetUserPassword($(this).data('id'));
            });

        });
    </script>
</x-app-layout>
