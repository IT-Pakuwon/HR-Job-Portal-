<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyEng;
use App\Models\DepartmentEng;
use App\Models\CompanyEngRole;
use App\Models\PositionEng;
use App\Models\AssetsLocation;
use App\Models\FloorBuilding;
use App\Models\CompanyBuilding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;



class AssetsLocationController extends Controller
{
    public function index()
    {
      
        $floorbuilding = FloorBuilding::with('building')->get();

        // dd($floorbuilding);
        
        return view('engineering.assetslocation.assetslocation', compact('floorbuilding'));
    }
  
   
    public function json()
    {
        $locations = AssetsLocation::with([
            'floor.building'  // relasi berantai: AssetsLocation -> FloorBuilding -> CompanyBuilding
        ])
        ->orderBy('location_code')
        ->get();

        $data = $locations->map(function ($loc) {
            return [
                'id'             => $loc->id, 
                'building'       => $loc->floor->building->Building_name ?? '-',
                'floor'          => $loc->floor->Floor_name ?? '-',
                'location_name'  => $loc->location_name,
                'location_code'  => $loc->location_code,
                'active_status'  => $loc->active_status,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'floor_id'       => 'required|integer|exists:mysql4.floorbuilding,id',
            'location_name'  => 'required|string|max:100',
            'location_code'  => 'required|string|max:50|unique:mysql4.asset_location,location_code',
        ]);

        DB::beginTransaction();
        try {
            AssetsLocation::create([
                'floor_id'          => $request->floor_id,
                'location_name'     => $request->location_name,
                'location_code'     => $request->location_code,
                'location_position' => null, // default null, update jika kamu punya koordinat
                'location_img'      => 'default.png',
                'location_tumbnail' => null,
                'position_x'        => null,
                'position_y'        => null,
                'svg_width'         => null,
                'svg_height'        => null,
                'active_status'     => '1', // default aktif
                'Last_update_By'    => auth()->user()->id ?? '',
            ]);

            DB::commit();
            return response()->json(['message' => 'Assets Location created successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
   
    public function edit($id)
    {
        $location = AssetsLocation::with('floor.building')->findOrFail($id);

        return response()->json([
            'id'             => $location->id,
            'floor_id'       => $location->floor_id,
            'location_name'  => $location->location_name,
            'location_code'  => $location->location_code,
            'building_name'  => $location->floor->building->Building_name ?? null,
            'floor_name'     => $location->floor->Floor_name ?? null,
        ]);
    }



    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'floor_id'       => 'required|integer|exists:mysql4.floorbuilding,id',
            'location_name'  => 'required|string|max:100',
            'location_code'  => 'required|string|max:50|unique:mysql4.asset_location,location_code,' . $id,
        ]);

        DB::beginTransaction();
        try {
            $location = AssetsLocation::findOrFail($id);

            $location->update([
                'floor_id'          => $request->floor_id,
                'location_name'     => $request->location_name,
                'location_code'     => $request->location_code,
                'Last_update_By'    => auth()->user()->id ?? 'system',
            ]);

            DB::commit();
            return response()->json(['message' => 'Assets Location updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function toggleStatus($id)
    {
        $screen = AssetsLocation::findOrFail($id);
        $screen->update(['active_status' => request('status')]);

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return response()->json(['message' => 'Current password does not match'], 422);
        }

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

}
