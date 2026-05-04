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
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TicketSetupController extends Controller
{
    public function index()
    {
        $types = MsTicketType::where('status', 'A')
            ->orderBy('ticket_type_name')
            ->get();

        $users = User::orderBy('username')->get();

        $departments = MsDepartment::orderBy('department_name')->get();

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
            ->addColumn('status_badge', fn ($row) => $row->status == 'A'
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>')
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function categoryJson(Request $request)
    {
        $data = MsTicketCategory::with('type');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('ticket_type_name', fn ($row) => optional($row->type)->ticket_type_name)
            ->addColumn('status_badge', fn ($row) => $row->status == 'A'
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>')
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function subcategoryJson(Request $request)
    {
        $data = MsTicketSubcategory::with(['type', 'category']);

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('ticket_type_name', fn ($row) => optional($row->type)->ticket_type_name)
            ->addColumn('ticket_category_name', fn ($row) => optional($row->category)->ticket_category_name)
            ->addColumn('status_badge', fn ($row) => $row->status == 'A'
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>')
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function priorityJson(Request $request)
    {
        $data = MsTicketPriority::with(['type', 'category']);

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('ticket_type_name', fn ($row) => optional($row->type)->ticket_type_name)
            ->addColumn('ticket_category_name', fn ($row) => optional($row->category)->ticket_category_name)
            ->addColumn('status_badge', fn ($row) => $row->status == 'A'
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>')
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function deptJson(Request $request)
    {
        $data = MsTicketCategoryDept::with(['type', 'category']);

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('ticket_type_name', fn ($row) => optional($row->type)->ticket_type_name)
            ->addColumn('ticket_category_name', fn ($row) => optional($row->category)->ticket_category_name)
            ->addColumn('status_badge', fn ($row) => $row->status == 'A'
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>')
            ->rawColumns(['status_badge'])
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

    public function storeCategory(Request $request)
    {
        $request->validate([
            'ticket_categoryid' => 'required|string|max:50|unique:pgsql5.ms_ticket_category,ticket_categoryid',
            'ticket_category_name' => 'required|string|max:255',
            'ticket_type' => 'required|exists:pgsql5.ms_ticket_type,ticket_type',
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

    public function updateCategory(Request $request, $ticket_categoryid)
    {
        $data = MsTicketCategory::findOrFail($ticket_categoryid);

        $request->validate([
            'ticket_category_name' => 'required|string|max:255',
            'ticket_type' => 'required|exists:pgsql5.ms_ticket_type,ticket_type',
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

    public function storeSubcategory(Request $request)
    {
        $request->validate([
            'ticket_subcategoryid' => 'required|string|max:50|unique:pgsql5.ms_ticket_subcategory,ticket_subcategoryid',
            'ticket_subcategory_name' => 'required|string|max:255',
            'ticket_type' => 'required|exists:pgsql5.ms_ticket_type,ticket_type',
            'ticket_categoryid' => 'required|exists:pgsql5.ms_ticket_category,ticket_categoryid',
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

    public function updateSubcategory(Request $request, $ticket_subcategoryid)
    {
        $data = MsTicketSubcategory::findOrFail($ticket_subcategoryid);

        $request->validate([
            'ticket_subcategory_name' => 'required|string|max:255',
            'ticket_type' => 'required|exists:pgsql5.ms_ticket_type,ticket_type',
            'ticket_categoryid' => 'required|exists:pgsql5.ms_ticket_category,ticket_categoryid',
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

    public function storePriority(Request $request)
    {
        $request->validate([
            'ticket_type' => 'required|exists:pgsql5.ms_ticket_type,ticket_type',
            'ticket_categoryid' => 'required|exists:pgsql5.ms_ticket_category,ticket_categoryid',
            'ticket_priority' => 'required|string|max:20',
            'ticket_priority_name' => 'required|string|max:255',
            'ticket_sla_days' => 'required|integer|min:0',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request) {
            MsTicketPriority::create([
                'ticket_type' => $request->ticket_type,
                'ticket_categoryid' => $request->ticket_categoryid,
                'ticket_priority' => strtoupper($request->ticket_priority),
                'ticket_priority_name' => $request->ticket_priority_name,
                'ticket_sla_days' => $request->ticket_sla_days,
                'status' => $request->status,
                'created_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Priority created successfully',
        ]);
    }

    public function updatePriority(Request $request, $id)
    {
        $data = MsTicketPriority::findOrFail($id);

        $request->validate([
            'ticket_type' => 'required|exists:pgsql5.ms_ticket_type,ticket_type',
            'ticket_categoryid' => 'required|exists:pgsql5.ms_ticket_category,ticket_categoryid',
            'ticket_priority' => 'required|string|max:20',
            'ticket_priority_name' => 'required|string|max:255',
            'ticket_sla_days' => 'required|integer|min:0',
            'status' => 'required|in:A,I',
        ]);

        DB::connection('pgsql5')->transaction(function () use ($request, $data) {
            $data->update([
                'ticket_type' => $request->ticket_type,
                'ticket_categoryid' => $request->ticket_categoryid,
                'ticket_priority' => strtoupper($request->ticket_priority),
                'ticket_priority_name' => $request->ticket_priority_name,
                'ticket_sla_days' => $request->ticket_sla_days,
                'status' => $request->status,
                'updated_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Priority updated successfully',
        ]);
    }

    public function storeDept(Request $request)
    {
        $request->validate([
            'ticket_type' => 'required|exists:pgsql5.ms_ticket_type,ticket_type',
            'ticket_categoryid' => 'required|exists:pgsql5.ms_ticket_category,ticket_categoryid',
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
            'ticket_type' => 'required|exists:pgsql5.ms_ticket_type,ticket_type',
            'ticket_categoryid' => 'required|exists:pgsql5.ms_ticket_category,ticket_categoryid',
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

    public function categoryByType($ticket_type)
    {
        $data = MsTicketCategory::where('ticket_type', $ticket_type)
            ->where('status', 'A')
            ->orderBy('ticket_category_name')
            ->get();

        return response()->json($data);
    }

    public function subcategoryByCategory($ticket_categoryid)
    {
        $data = MsTicketSubcategory::where('ticket_categoryid', $ticket_categoryid)
            ->where('status', 'A')
            ->orderBy('ticket_subcategory_name')
            ->get();

        return response()->json($data);
    }

    public function priorityByCategory($ticket_categoryid)
    {
        $data = MsTicketPriority::where('ticket_categoryid', $ticket_categoryid)
            ->where('status', 'A')
            ->orderBy('ticket_priority_name')
            ->get();

        return response()->json($data);
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

    public function destroyCategory($ticket_categoryid)
    {
        $data = MsTicketCategory::findOrFail($ticket_categoryid);

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }

    public function destroySubcategory($ticket_subcategoryid)
    {
        $data = MsTicketSubcategory::findOrFail($ticket_subcategoryid);

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subcategory deleted successfully',
        ]);
    }

    public function destroyPriority($id)
    {
        $data = MsTicketPriority::findOrFail($id);

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Priority deleted successfully',
        ]);
    }

    public function destroyDept($id)
    {
        $data = MsTicketCategoryDept::findOrFail($id);

        $data->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
        ]);

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Department PIC deleted successfully',
        ]);
    }
}
