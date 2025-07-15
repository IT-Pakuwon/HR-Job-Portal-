<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\CompanyEng;
use App\Models\DepartmentEng;
use App\Models\CompanyEngRole;
use App\Models\PositionEng;
use App\Models\WorksCategory;
use App\Models\FloorBuilding;
use App\Models\CompanyBuilding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;



class WorksCategoryController extends Controller
{
    public function index()
    {      
        $floorbuilding = FloorBuilding::with('building')->get();
        // dd($floorbuilding);        
        return view('engineering.workscategory.workscategory', compact('floorbuilding'));
    }

    public function treeJson()
    {
        $categories = WorksCategory::where('company_id', 1)
            ->where('active_status', '1')->get();
        // $categories = WorksCategory::all(); // Hapus where active_status
        $tree = $categories->map(function ($cat) {
            return [
                'id'    => $cat->id,
                'parent'=> $cat->Work_Category_Parent == 0 ? '#' : $cat->Work_Category_Parent,
                'text'  => $cat->Work_Category_Name,
            ];
        });

        return response()->json($tree);
    }

   public function store(Request $request)
    {
        $parentId = $request->parent === '#' ? 0 : $request->parent;
        $parent = WorksCategory::find($parentId);

        $level = $parent ? $parent->Work_Category_Level + 1 : 1;

        // Ambil jumlah anak dari parent
        $siblingCount = WorksCategory::where('Work_Category_Parent', $parentId)->count();

        // Generate kode pendek
        $prefix = $parent && $parent->Work_Category_Code
            ? substr($parent->Work_Category_Code, 0, 2)
            : 'A';

        $number = str_pad($siblingCount + 1, 2, '0', STR_PAD_LEFT); // misal: 01, 02, dst
        $newCode = $prefix . $number; // hasil: A01, A02, CM03, dst

        if (strlen($newCode) > 5) {
            return response()->json([
                'success' => false,
                'message' => 'Kode terlalu panjang untuk varchar(5)'
            ], 422);
        }

        $category = new WorksCategory();
        $category->Work_Category_Name = $request->text ?? 'New Node';
        $category->Work_Category_Parent = $parentId;
        $category->Work_Category_Level = $level;
        $category->Work_Category_Code = $newCode;
        $category->company_id = 1;
        $category->active_status = '1';
        $category->save();

        return response()->json(['success' => true, 'id' => $category->id]);
    }


    public function update(Request $request)
    {
        // dd($request->all());
       
        $category = WorksCategory::findOrFail($request->id);
        $category->Work_Category_Name = $request->text;        
        $category->save();

        return response()->json(['success' => true, 'message' => 'Kategori berhasil diperbarui.']);
    }


    public function delete($id)
    {
        $category = WorksCategory::findOrFail($id);
        $category->delete();

        return response()->json(['success' => true, 'message' => 'Node deleted']);
    }





}
