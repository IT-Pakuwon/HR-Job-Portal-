<?php

namespace App\Exports;

use App\Models\MsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CarExpenseExport implements WithMultipleSheets
{
    public function __construct(protected Request $request) {}

    public function sheets(): array
    {
        $query = DB::connection('pgsql5')
            ->table('tr_car_expense')
            ->whereNull('deleted_at')
            ->whereNotNull('cost_type');

        if ($this->request->date_from) {
            $query->whereDate('ref_date', '>=', $this->request->date_from);
        }

        if ($this->request->date_to) {
            $query->whereDate('ref_date', '<=', $this->request->date_to);
        }

        if ($this->request->nopol) {
            $query->where('nopol', $this->request->nopol);
        }

        if ($this->request->driver) {
            $query->where('driver', 'ilike', "%{$this->request->driver}%");
        }

        $costTypeIds = $query->distinct()->orderBy('cost_type')->pluck('cost_type');

        // Resolve IDs → names from ms_categories
        $categoryMap = MsCategory::where('groups', 'CAR COST')
            ->where('status', 'A')
            ->pluck('category_name', 'id');

        return $costTypeIds->map(
            fn ($id) => new CarExpenseSheetExport(
                $this->request,
                (string) $id,
                $categoryMap[$id] ?? (string) $id
            )
        )->toArray();
    }
}
