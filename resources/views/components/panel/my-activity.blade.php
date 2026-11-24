@props(['title' => 'My Activity'])
{{-- My Activity as a panel of its own (tile "My Activity" in Dashboard Menu): the user's own documents, messages and files.
     Same widget that used to be pushed into each dashboard's tab bar; here it stays where it is, in the Panel area.
     The widget has fixed element ids, so only one can be on a page: a dashboard that has this tile does not get the old
     injected tab (see multidashboard/index.blade.php). Its script (public/assets/js/multidashboard/my-activity.js) is
     loaded up front by component/menu.blade.php, because inline scripts inside this panel's <template> would not run. --}}
<div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
        <h2 class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $title }}</h2>
    </div>

    @include('components.multidashboard.my-activity', ['combined' => true, 'asPanel' => true])
</div>
