<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsTicketCategory;
use App\Models\MsTicketCategoryDept;
use App\Models\MsTicketPriority;
use App\Models\MsTicketSubcategory;
use App\Models\MsTicketType;
use App\Models\MsWaSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TicketSetupController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $types = MsTicketType::query()
            ->where('status', 'A')
            ->orderBy('ticket_type_name')
            ->get();

        $users = User::query()
            ->orderBy('username')
            ->get();

        $departments = MsDepartment::query()
            ->orderBy('department_name')
            ->get();

        $companies = MsCompany::query()
            ->where('status', 'A')
            ->orderBy('cpny_name')
            ->get();

        return view('pages.ticket.ticketsetup', compact(
            'types',
            'users',
            'departments',
            'companies'
        ));
    }

    public function typeJson(Request $request)
    {
        $data = MsTicketType::query()
            ->where('status', '<>', 'X');

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('status_badge', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Active</span>'
                    : '<span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Inactive</span>';
            })

            ->rawColumns([
                'status_badge',
            ])

            ->make(true);
    }

    public function categoryJson(Request $request)
    {
        $data = MsTicketCategory::query()

            ->where('ms_ticket_category.status', '<>', 'X')

            ->leftJoin(
                'ms_ticket_type',
                'ms_ticket_type.ticket_type',
                '=',
                'ms_ticket_category.ticket_type'
            )

            ->select(
                'ms_ticket_category.*',
                'ms_ticket_type.ticket_type_name'
            );

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('ticket_type_name', function ($row) {
                return $row->ticket_type_name;
            })

            ->addColumn('status_badge', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Active</span>'
                    : '<span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Inactive</span>';
            })

            ->rawColumns([
                'status_badge',
            ])

            ->make(true);
    }

    public function subcategoryJson(Request $request)
    {
        $data = MsTicketSubcategory::query()

            ->where('ms_ticket_subcategory.status', '<>', 'X')

            ->leftJoin(
                'ms_ticket_type',
                'ms_ticket_type.ticket_type',
                '=',
                'ms_ticket_subcategory.ticket_type'
            )

            ->leftJoin(
                'ms_ticket_category',
                'ms_ticket_category.ticket_categoryid',
                '=',
                'ms_ticket_subcategory.ticket_categoryid'
            )

            ->select(
                'ms_ticket_subcategory.*',
                'ms_ticket_type.ticket_type_name',
                'ms_ticket_category.ticket_category_name'
            );

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('ticket_type_name', function ($row) {
                return $row->ticket_type_name;
            })

            ->addColumn('ticket_category_name', function ($row) {
                return $row->ticket_category_name;
            })

            ->addColumn('status_badge', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Active</span>'
                    : '<span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Inactive</span>';
            })

            ->rawColumns([
                'status_badge',
            ])

            ->make(true);
    }

    public function priorityJson(Request $request)
    {
        $data = MsTicketPriority::query()

            ->where('ms_ticket_priority.status', '<>', 'X')

            ->leftJoin(
                'ms_ticket_type',
                'ms_ticket_type.ticket_type',
                '=',
                'ms_ticket_priority.ticket_type'
            )

            ->leftJoin(
                'ms_ticket_category',
                'ms_ticket_category.ticket_categoryid',
                '=',
                'ms_ticket_priority.ticket_categoryid'
            )

            ->select(
                'ms_ticket_priority.*',
                'ms_ticket_type.ticket_type_name',
                'ms_ticket_category.ticket_category_name'
            );

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('ticket_type_name', function ($row) {
                return $row->ticket_type_name;
            })

            ->addColumn('ticket_category_name', function ($row) {
                return $row->ticket_category_name;
            })

            ->addColumn('status_badge', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Active</span>'
                    : '<span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Inactive</span>';
            })

            ->rawColumns([
                'status_badge',
            ])

            ->make(true);
    }

    public function deptJson(Request $request)
    {
        $departments = MsDepartment::query()
            ->pluck('department_name', 'department_id');

        $data = MsTicketCategoryDept::query()

            ->where('ms_ticket_category_dept.status', '<>', 'X')

            ->leftJoin(
                'ms_ticket_type',
                'ms_ticket_type.ticket_type',
                '=',
                'ms_ticket_category_dept.ticket_type'
            )

            ->leftJoin(
                'ms_ticket_category',
                'ms_ticket_category.ticket_categoryid',
                '=',
                'ms_ticket_category_dept.ticket_categoryid'
            )

            ->select(
                'ms_ticket_category_dept.*',
                'ms_ticket_type.ticket_type_name',
                'ms_ticket_category.ticket_category_name'
            );

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('ticket_type_name', function ($row) {
                return $row->ticket_type_name;
            })

            ->addColumn('ticket_category_name', function ($row) {
                return $row->ticket_category_name;
            })

            ->addColumn('department_name', function ($row) use ($departments) {
                return $departments[$row->department_id] ?? $row->department_id;
            })

            ->addColumn('user_name', function ($row) {
                return $row->username;
            })

            ->addColumn('status_badge', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Active</span>'
                    : '<span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Inactive</span>';
            })

            ->rawColumns([
                'status_badge',
            ])

            ->make(true);
    }

    public function waSettingJson(Request $request)
    {
        $companies = MsCompany::query()
            ->pluck('cpny_name', 'cpny_id');

        $data = MsWaSetting::query()
            ->where('status', '<>', 'X');

        return DataTables::of($data)

            ->addIndexColumn()

            ->addColumn('cpny_name', function ($row) use ($companies) {
                return $companies[$row->cpny_id] ?? $row->cpny_id;
            })

            ->addColumn('status_badge', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Active</span>'
                    : '<span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Inactive</span>';
            })

            ->rawColumns([
                'status_badge',
            ])

            ->make(true);
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'ticket_type' => 'required|string|max:20|unique:pgsql5.ms_ticket_type,ticket_type',
            'ticket_type_name' => 'required|string|max:255',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request) {
            MsTicketType::create([
                'ticket_type' => strtoupper($request->ticket_type),

                'ticket_type_name' => $request->ticket_type_name,

                'status' => $request->status,

                'created_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Ticket Type created successfully',
        ]);
    }

    public function storeWaSetting(Request $request)
    {
        $request->validate([
            'cpny_id' => 'required',
            'chat_id' => 'required|string|max:255',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request) {
            MsWaSetting::create([
                'cpny_id' => $request->cpny_id,

                'chat_id' => trim($request->chat_id),

                'status' => $request->status,

                'created_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp Setting created successfully',
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'ticket_categoryid' => 'required|string|max:20|unique:pgsql5.ms_ticket_category,ticket_categoryid',
            'ticket_category_name' => 'required|string|max:255',
            'ticket_type' => 'required|string|max:20',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request) {
            MsTicketCategory::create([
                'ticket_categoryid' => strtoupper($request->ticket_categoryid),

                'ticket_category_name' => $request->ticket_category_name,

                'ticket_type' => $request->ticket_type,

                'status' => $request->status,

                'created_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
        ]);
    }

    public function storeSubcategory(Request $request)
    {
        $request->validate([
            'ticket_subcategoryid' => 'required|string|max:20|unique:pgsql5.ms_ticket_subcategory,ticket_subcategoryid',
            'ticket_subcategory_name' => 'required|string|max:255',
            'ticket_type' => 'required|string|max:20',
            'ticket_categoryid' => 'required|string|max:20',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request) {
            MsTicketSubcategory::create([
                'ticket_subcategoryid' => strtoupper($request->ticket_subcategoryid),

                'ticket_subcategory_name' => $request->ticket_subcategory_name,

                'ticket_type' => $request->ticket_type,

                'ticket_categoryid' => $request->ticket_categoryid,

                'status' => $request->status,

                'created_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Subcategory created successfully',
        ]);
    }

    public function storePriority(Request $request)
    {
        $request->validate([
            'ticket_type' => 'required',
            'ticket_categoryid' => 'required',
            'ticket_priority' => 'required|string|max:20',
            'ticket_priority_name' => 'required|string|max:255',
            'ticket_sla_days' => 'required|numeric|min:0',
            'is_default' => 'required|in:Y,N',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request) {
            MsTicketPriority::create([
                'ticket_type' => $request->ticket_type,

                'ticket_categoryid' => $request->ticket_categoryid,

                'ticket_priority' => strtoupper($request->ticket_priority),

                'ticket_priority_name' => $request->ticket_priority_name,

                'ticket_sla_days' => $request->ticket_sla_days,

                'is_default' => $request->is_default,

                'status' => $request->status,

                'created_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Priority created successfully',
        ]);
    }

    public function storeDept(Request $request)
    {
        $request->validate([
            'ticket_type' => 'required',
            'ticket_categoryid' => 'required',
            'department_id' => 'required',
            'username' => 'required',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request) {
            MsTicketCategoryDept::create([
                'ticket_type' => $request->ticket_type,

                'ticket_categoryid' => $request->ticket_categoryid,

                'department_id' => $request->department_id,

                'username' => $request->username,

                'status' => $request->status,

                'created_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Department PIC created successfully',
        ]);
    }

    public function updateDept(Request $request, $id)
    {
        $data = MsTicketCategoryDept::findOrFail($id);

        $request->validate([
            'ticket_type' => 'required',
            'ticket_categoryid' => 'required',
            'department_id' => 'required',
            'username' => 'required',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request, $data) {
            $data->update([
                'ticket_type' => $request->ticket_type,

                'ticket_categoryid' => $request->ticket_categoryid,

                'department_id' => $request->department_id,

                'username' => $request->username,

                'status' => $request->status,

                'updated_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Department PIC updated successfully',
        ]);
    }

    public function updatePriority(Request $request, $id)
    {
        $data = MsTicketPriority::findOrFail($id);

        $request->validate([
            'ticket_type' => 'required',
            'ticket_categoryid' => 'required',
            'ticket_priority' => 'required|string|max:20',
            'ticket_priority_name' => 'required|string|max:255',
            'ticket_sla_days' => 'required|numeric|min:0',
            'is_default' => 'required|in:Y,N',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request, $data) {
            $data->update([
                'ticket_type' => $request->ticket_type,

                'ticket_categoryid' => $request->ticket_categoryid,

                'ticket_priority' => strtoupper($request->ticket_priority),

                'ticket_priority_name' => $request->ticket_priority_name,

                'ticket_sla_days' => $request->ticket_sla_days,

                'is_default' => $request->is_default,

                'status' => $request->status,

                'updated_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Priority updated successfully',
        ]);
    }

    public function updateSubcategory(Request $request, $ticket_subcategoryid)
    {
        $data = MsTicketSubcategory::query()
            ->where('ticket_subcategoryid', $ticket_subcategoryid)
            ->firstOrFail();

        $request->validate([
            'ticket_subcategory_name' => 'required|string|max:255',
            'ticket_type' => 'required|string|max:20',
            'ticket_categoryid' => 'required|string|max:20',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request, $data) {
            $data->update([
                'ticket_subcategory_name' => $request->ticket_subcategory_name,

                'ticket_type' => $request->ticket_type,

                'ticket_categoryid' => $request->ticket_categoryid,

                'status' => $request->status,

                'updated_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Subcategory updated successfully',
        ]);
    }

    public function updateCategory(Request $request, $ticket_categoryid)
    {
        $data = MsTicketCategory::query()
            ->where('ticket_categoryid', $ticket_categoryid)
            ->firstOrFail();

        $request->validate([
            'ticket_category_name' => 'required|string|max:255',
            'ticket_type' => 'required|string|max:20',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request, $data) {
            $data->update([
                'ticket_category_name' => $request->ticket_category_name,

                'ticket_type' => $request->ticket_type,

                'status' => $request->status,

                'updated_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
        ]);
    }

    public function updateType(Request $request, $ticket_type)
    {
        $data = MsTicketType::query()
            ->where('ticket_type', $ticket_type)
            ->firstOrFail();

        $request->validate([
            'ticket_type_name' => 'required|string|max:255',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request, $data) {
            $data->update([
                'ticket_type_name' => $request->ticket_type_name,
                'status' => $request->status,
                'updated_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Ticket Type updated successfully',
        ]);
    }

    public function updateWaSetting(
        Request $request,
        $id
    ) {
        $data = MsWaSetting::findOrFail($id);

        $request->validate([
            'cpny_id' => 'required',
            'chat_id' => 'required|string|max:255',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use (
            $request,
            $data
        ) {
            $data->update([
                'cpny_id' => $request->cpny_id,

                'chat_id' => trim($request->chat_id),

                'status' => $request->status,

                'updated_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp Setting updated successfully',
        ]);
    }

    public function destroyDept($id)
    {
        $data = MsTicketCategoryDept::findOrFail($id);

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department PIC deleted successfully',
        ]);
    }

    public function destroyPriority($id)
    {
        $data = MsTicketPriority::findOrFail($id);

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Priority deleted successfully',
        ]);
    }

    public function destroySubcategory($ticket_subcategoryid)
    {
        $data = MsTicketSubcategory::query()
            ->where('ticket_subcategoryid', $ticket_subcategoryid)
            ->firstOrFail();

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subcategory deleted successfully',
        ]);
    }

    public function destroyCategory($ticket_categoryid)
    {
        $data = MsTicketCategory::query()
            ->where('ticket_categoryid', $ticket_categoryid)
            ->firstOrFail();

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    public function destroyType($ticket_type)
    {
        $data = MsTicketType::query()
            ->where('ticket_type', $ticket_type)
            ->firstOrFail();

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket Type deleted successfully',
        ]);
    }

    public function destroyWaSetting($id)
    {
        $data = MsWaSetting::findOrFail($id);

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'WhatsApp Setting deleted successfully',
        ]);
    }

    public function categoryByType($ticket_type)
    {
        return MsTicketCategory::query()
            ->where('ticket_type', $ticket_type)
            ->where('status', 'A')
            ->orderBy('ticket_category_name')
            ->get([
                'ticket_categoryid',
                'ticket_category_name',
            ]);
    }

    public function subcategoryByCategory($ticket_categoryid)
    {
        return MsTicketSubcategory::query()
            ->where('ticket_categoryid', $ticket_categoryid)
            ->where('status', 'A')
            ->orderBy('ticket_subcategory_name')
            ->get([
                'ticket_subcategoryid',
                'ticket_subcategory_name',
            ]);
    }

    public function priorityByCategory($ticket_categoryid)
    {
        return MsTicketPriority::query()
            ->where('ticket_categoryid', $ticket_categoryid)
            ->where('status', 'A')
            ->orderBy('ticket_priority_name')
            ->get([
                'id',
                'ticket_priority',
                'ticket_priority_name',
            ]);
    }
}
