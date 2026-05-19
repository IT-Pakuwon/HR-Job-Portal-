<?php

namespace App\Http\Controllers;

use App\Models\MsDepartment;
use App\Models\MsTicketCategory;
use App\Models\MsTicketCategoryDept;
use App\Models\MsTicketPriority;
use App\Models\MsTicketSubcategory;
use App\Models\MsTicketType;
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

        return view('pages.ticket.ticketsetup', compact(
            'types',
            'users',
            'departments'
        ));
    }

    public function typeJson(Request $request)
    {
        $data = MsTicketType::query();

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
        $data = MsTicketCategoryDept::query()

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

            ->addColumn('department_name', function ($row) {

                return $row->department_id;
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

    public function updateType(Request $request, $ticket_type)
    {
        $data = MsTicketType::findOrFail($ticket_type);

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

    public function destroyType($ticket_type)
    {
        $data = MsTicketType::findOrFail($ticket_type);

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket Type deleted successfully',
        ]);
    }
}
