{{-- Winner History panel, identical on the admin and audience screens. --}}

<div id="winnerHistoryPanel" class="lg:col-span-2 rounded-2xl border border-white/10 bg-white/5 p-5 shadow-sm">

    <div class="flex items-center gap-2">
        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-base shadow">📜</span>
        <h3 class="text-base font-bold text-white">
            Winner History
        </h3>
    </div>

    <div class="mt-4 overflow-x-auto">

        <table id="tableWinner" class="display w-full border-collapse text-base">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Ref Nbr</th>
                    <th>Customer</th>
                    <th>Prize</th>
                </tr>
            </thead>

        </table>

    </div>

</div>
