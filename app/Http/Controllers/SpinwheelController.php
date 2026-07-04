<?php

namespace App\Http\Controllers;

use App\Exports\LuckydrawParticipantTemplateExport;
use App\Imports\LuckydrawParticipantImport;
use App\Models\MsLuckydrawEvent;
use App\Models\MsLuckydrawPrize;
use App\Models\TrLuckydraw;
use App\Models\TrLuckydrawWinner;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SpinwheelController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $events = MsLuckydrawEvent::query()
            ->where('status', 'A')
            ->orderByDesc('event_date')
            ->get();

        return view('pages.spinwheel.spinwheel', compact('events'));
    }

    public function downloadTemplate()
    {
        return Excel::download(new LuckydrawParticipantTemplateExport(), 'luckydraw_participant_import_template.xlsx');
    }

    public function prizesByEvent($event_id)
    {
        return MsLuckydrawPrize::query()
            ->where('event_id', $event_id)
            ->where('status', 'A')
            ->orderBy('prize_name')
            ->get([
                'prize_id',
                'prize_name',
            ]);
    }

    public function summary($event_id)
    {
        $wonRefNbrs = TrLuckydrawWinner::query()
            ->where('event_id', $event_id)
            ->where('status', '<>', 'X')
            ->pluck('ref_nbr');

        $totalEntries = TrLuckydraw::query()
            ->where('event_id', $event_id)
            ->where('status', '<>', 'X')
            ->count();

        $eligible = TrLuckydraw::query()
            ->where('event_id', $event_id)
            ->where('status', '<>', 'X')
            ->whereNotIn('ref_nbr', $wonRefNbrs);

        $eligibleParticipants = (clone $eligible)
            ->distinct('ref_nbr')
            ->count('ref_nbr');

        $sampleNames = (clone $eligible)
            ->inRandomOrder()
            ->limit(30)
            ->pluck('customer_name');

        return response()->json([
            'total_entries' => $totalEntries,
            'eligible_participants' => $eligibleParticipants,
            'winners_drawn' => $wonRefNbrs->count(),
            'sample_names' => $sampleNames,
        ]);
    }

    public function winnerJson(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
        ]);

        $data = TrLuckydrawWinner::query()

            ->where('tr_luckydraw_winner.event_id', $request->event_id)
            ->where('tr_luckydraw_winner.status', '<>', 'X')

            ->leftJoin(
                'ms_luckydraw_prize',
                'ms_luckydraw_prize.prize_id',
                '=',
                'tr_luckydraw_winner.prize_id'
            )

            ->select(
                'tr_luckydraw_winner.*',
                'ms_luckydraw_prize.prize_name'
            )

            ->orderByDesc('tr_luckydraw_winner.created_at');

        return DataTables::of($data)

            ->addIndexColumn()

            ->make(true);
    }

    private function parseParticipantFile(UploadedFile $file): array
    {
        $import = new LuckydrawParticipantImport();
        Excel::import($import, $file);

        $rows = $import->getRows()
            ->filter(fn ($r) => $r->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty());

        $errors = [];
        $valid = [];
        $line = 1;

        foreach ($rows as $row) {
            $line++;
            $rowArr = $row->values()->toArray();
            $rowErrors = [];

            // Column order: CUSTOMER_NAME, COMPANY_NAME, REF_NBR
            $customerName = trim((string) ($rowArr[0] ?? ''));
            $companyName = trim((string) ($rowArr[1] ?? ''));
            $refNbr = trim((string) ($rowArr[2] ?? ''));

            if (empty($customerName)) {
                $rowErrors[] = 'CUSTOMER_NAME is required';
            }

            if (empty($refNbr)) {
                $rowErrors[] = 'REF_NBR is required';
            }

            if (!empty($rowErrors)) {
                $errors[] = ['row' => $line, 'errors' => $rowErrors];
            } else {
                $valid[] = [
                    'row' => $line,
                    'customer_name' => $customerName,
                    'company_name' => $companyName,
                    'ref_nbr' => $refNbr,
                ];
            }
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $parsed = $this->parseParticipantFile($request->file('file'));

        if (!empty($parsed['errors'])) {
            return response()->json([
                'success' => false,
                'message' => count($parsed['errors']).' row(s) have errors.',
                'errors' => $parsed['errors'],
            ], 422);
        }

        if (empty($parsed['valid'])) {
            return response()->json([
                'success' => false,
                'message' => 'No data rows found in the file.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'rows' => $parsed['valid'],
            'count' => count($parsed['valid']),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $event = MsLuckydrawEvent::query()
            ->where('event_id', $request->event_id)
            ->firstOrFail();

        $parsed = $this->parseParticipantFile($request->file('file'));

        if (!empty($parsed['errors'])) {
            return response()->json([
                'success' => false,
                'message' => count($parsed['errors']).' row(s) have errors. Please fix and re-upload.',
                'errors' => $parsed['errors'],
            ], 422);
        }

        if (empty($parsed['valid'])) {
            return response()->json([
                'success' => false,
                'message' => 'No data rows found in the file.',
            ], 422);
        }

        $username = auth()->user()->username;

        DB::connection('pgsql5')->transaction(function () use ($parsed, $event, $username) {
            foreach ($parsed['valid'] as $row) {
                TrLuckydraw::create([
                    'event_id' => $event->event_id,
                    'event_date' => $event->event_date,
                    'customer_name' => $row['customer_name'],
                    'company_name' => $row['company_name'],
                    'ref_nbr' => $row['ref_nbr'],
                    'status' => 'A',
                    'created_by' => $username,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => count($parsed['valid']).' participant entries imported successfully',
            'count' => count($parsed['valid']),
        ]);
    }

    public function pickCandidates(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
            'candidate_count' => 'required|integer|min:1|max:100',
        ]);

        $wonRefNbrs = TrLuckydrawWinner::query()
            ->where('event_id', $request->event_id)
            ->where('status', '<>', 'X')
            ->pluck('ref_nbr');

        $pool = TrLuckydraw::query()
            ->where('event_id', $request->event_id)
            ->where('status', '<>', 'X')
            ->whereNotIn('ref_nbr', $wonRefNbrs)
            ->get(['ref_nbr', 'customer_name', 'company_name'])
            ->shuffle();

        if ($pool->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No eligible participants remaining for this event.',
            ], 422);
        }

        $picked = [];
        $pickedRefNbrs = [];

        foreach ($pool as $entry) {
            if (in_array($entry->ref_nbr, $pickedRefNbrs, true)) {
                continue;
            }

            $picked[] = [
                'ref_nbr' => $entry->ref_nbr,
                'customer_name' => $entry->customer_name,
                'company_name' => $entry->company_name,
            ];
            $pickedRefNbrs[] = $entry->ref_nbr;

            if (count($picked) >= (int) $request->candidate_count) {
                break;
            }
        }

        return response()->json([
            'success' => true,
            'candidates' => $picked,
        ]);
    }

    public function confirmWinner(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
            'prize_id' => 'required|string',
            'ref_nbr' => 'required|string',
            'customer_name' => 'required|string',
            'company_name' => 'nullable|string',
        ]);

        $prize = MsLuckydrawPrize::query()
            ->where('prize_id', $request->prize_id)
            ->where('event_id', $request->event_id)
            ->where('status', 'A')
            ->firstOrFail();

        $alreadyWon = TrLuckydrawWinner::query()
            ->where('event_id', $request->event_id)
            ->where('ref_nbr', $request->ref_nbr)
            ->where('status', '<>', 'X')
            ->exists();

        if ($alreadyWon) {
            return response()->json([
                'success' => false,
                'message' => 'This participant has already won a prize in this event.',
            ], 422);
        }

        $username = auth()->user()->username;

        DB::connection('pgsql5')->transaction(function () use ($request, $prize, $username) {
            TrLuckydrawWinner::create([
                'event_id' => $request->event_id,
                'customer_name' => $request->customer_name,
                'company_name' => $request->company_name,
                'ref_nbr' => $request->ref_nbr,
                'prize_id' => $prize->prize_id,
                'status' => 'A',
                'created_by' => $username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Winner saved successfully',
            'prize_name' => $prize->prize_name,
        ]);
    }
}
