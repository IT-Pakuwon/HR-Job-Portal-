<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MsVendor;
use App\Models\ViewVendorVMS;

class VendorController extends Controller
{
    public function index()
    {
        return view('pages.vendor.vendor');
    }

    public function json()
    {
        $vendors = MsVendor::select([
                'id',
                'vendor_id',
                'vendor_name',
                'vendor_addr1',
                'vendor_addr2',
                'email',
                'contact_person',
                'phone_number',
                'npwp',
                'contact_email',
                'contact_number1',
                'contact_number2',
                'fax_no',
                'post_cd',
                'status',
            ])
            ->orderByDesc('vendor_id')
            ->get();

        return response()->json(['data' => $vendors]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id'        => 'required|string|max:50|unique:pgsql.ms_vendor,vendor_id',
            'vendor_name'      => 'required|string|max:200',
            'vendor_addr1'     => 'nullable|string|max:255',
            'vendor_addr2'     => 'nullable|string|max:255',
            'email'            => 'nullable|email|max:150',
            'contact_person'   => 'nullable|string|max:150',
            'phone_number'     => 'nullable|string|max:50',
            'npwp'             => 'nullable|string|max:100',
            'contact_email'    => 'nullable|email|max:150',
            'contact_number1'  => 'nullable|string|max:50',
            'contact_number2'  => 'nullable|string|max:50',
            'fax_no'           => 'nullable|string|max:50',
            'post_cd'          => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $vendor = MsVendor::create([
                'vendor_id'        => strtoupper(trim($request->vendor_id)),
                'vendor_name'      => strtoupper(trim($request->vendor_name)),
                'vendor_addr1'     => $request->vendor_addr1,
                'vendor_addr2'     => $request->vendor_addr2,
                'email'            => $request->email,
                'contact_person'   => $request->contact_person,
                'phone_number'     => $request->phone_number,
                'npwp'             => $request->npwp,
                'contact_email'    => $request->contact_email,
                'contact_number1'  => $request->contact_number1,
                'contact_number2'  => $request->contact_number2,
                'fax_no'           => $request->fax_no,
                'post_cd'          => $request->post_cd,
                'status'           => 'A',
                'created_by'       => $loginUser->username ?? 'system',
                'created_at'       => now(),
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'vendor'  => $vendor
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal menyimpan vendor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $vendor = MsVendor::findOrFail($id);

        return response()->json([
            'id'               => $vendor->id,
            'vendor_id'        => $vendor->vendor_id,
            'vendor_name'      => $vendor->vendor_name,
            'vendor_addr1'     => $vendor->vendor_addr1,
            'vendor_addr2'     => $vendor->vendor_addr2,
            'email'            => $vendor->email,
            'contact_person'   => $vendor->contact_person,
            'phone_number'     => $vendor->phone_number,
            'npwp'             => $vendor->npwp,
            'contact_email'    => $vendor->contact_email,
            'contact_number1'  => $vendor->contact_number1,
            'contact_number2'  => $vendor->contact_number2,
            'fax_no'           => $vendor->fax_no,
            'post_cd'          => $vendor->post_cd,
            'status'           => $vendor->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $vendor = MsVendor::findOrFail($id);

        $request->validate([
            'vendor_id'        => 'required|string|max:50|unique:pgsql.ms_vendor,vendor_id,' . $vendor->id,
            'vendor_name'      => 'required|string|max:200',
            'vendor_addr1'     => 'nullable|string|max:255',
            'vendor_addr2'     => 'nullable|string|max:255',
            'email'            => 'nullable|email|max:150',
            'contact_person'   => 'nullable|string|max:150',
            'phone_number'     => 'nullable|string|max:50',
            'npwp'             => 'nullable|string|max:100',
            'contact_email'    => 'nullable|email|max:150',
            'contact_number1'  => 'nullable|string|max:50',
            'contact_number2'  => 'nullable|string|max:50',
            'fax_no'           => 'nullable|string|max:50',
            'post_cd'          => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $vendor->update([
                'vendor_id'        => strtoupper(trim($request->vendor_id)),
                'vendor_name'      => strtoupper(trim($request->vendor_name)),
                'vendor_addr1'     => $request->vendor_addr1,
                'vendor_addr2'     => $request->vendor_addr2,
                'email'            => $request->email,
                'contact_person'   => $request->contact_person,
                'phone_number'     => $request->phone_number,
                'npwp'             => $request->npwp,
                'contact_email'    => $request->contact_email,
                'contact_number1'  => $request->contact_number1,
                'contact_number2'  => $request->contact_number2,
                'fax_no'           => $request->fax_no,
                'post_cd'          => $request->post_cd,
                'updated_by'       => $loginUser->username ?? 'system',
                'updated_at'       => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal update vendor',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $vendor = MsVendor::findOrFail($id);
        $newStatus = $request->status;

        $vendor->update([
            'status'     => $newStatus,
            'updated_by' => Auth::check() ? Auth::user()->username : 'system',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated'
        ]);
    }

    public function syncVendor(Request $request)
    {
        DB::beginTransaction();

        try {
            $loginUser = Auth::user();
            $username = $loginUser->username ?? 'system';

            $sourceVendors = ViewVendorVMS::query()
                ->select([
                    'second_vendor_id',
                    'vendor_nama',
                    'address1',
                    'address2',
                    'emailaddr',
                    'attention',
                    'phone',
                    'npwp',
                    'postal_code',
                    'Statuss',
                ])
                ->orderBy('second_vendor_id')
                ->get();

            if ($sourceVendors->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Data vendor dari ViewVendorVMS tidak ditemukan.'
                ], 404);
            }

            $inserted = 0;
            $updated  = 0;
            $skipped  = 0;

            foreach ($sourceVendors as $row) {
                $vendorId = trim((string) $row->second_vendor_id);

                if ($vendorId === '') {
                    $skipped++;
                    continue;
                }

                $firstEmail  = $this->takeFirstEmail($row->emailaddr);
                $phone1      = $this->takePhonePart($row->phone, 1);
                $phone2      = $this->takePhonePart($row->phone, 2);

                $payload = [
                    'vendor_name'     => $this->limitText(strtoupper(trim((string) $row->vendor_nama)), 200),
                    'vendor_addr1'    => $this->limitText($row->address1, 255),
                    'vendor_addr2'    => $this->limitText($row->address2, 255),
                    'email'           => $this->limitText($firstEmail, 150),
                    'contact_person'  => $this->limitText($row->attention, 150),

                    // jangan isi gabungan panjang
                    'phone_number'    => $this->limitText($phone1, 50),

                    'npwp'            => $this->limitText($row->npwp, 100),

                    // simpan email utama saja supaya tidak melebihi limit
                    'contact_email'   => $this->limitText($firstEmail, 150),

                    'contact_number1' => $this->limitText($phone1, 50),
                    'contact_number2' => $this->limitText($phone2, 50),

                    'fax_no'          => null,
                    'post_cd'         => $this->limitText($row->postal_code, 20),
                    'status'          => strtoupper(trim((string) ($row->Statuss ?? 'A'))) === 'A' ? 'A' : 'X',
                ];

                $vendor = MsVendor::where('vendor_id', $vendorId)->first();

                if ($vendor) {
                    $vendor->update(array_merge($payload, [
                        'updated_by' => $username,
                        'updated_at' => now(),
                    ]));
                    $updated++;
                } else {
                    MsVendor::create(array_merge($payload, [
                        'vendor_id'   => $vendorId,
                        'created_by'  => $username,
                        'created_at'  => now(),
                    ]));
                    $inserted++;
                }
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => "Sync vendor berhasil. Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}",
                'inserted' => $inserted,
                'updated'  => $updated,
                'skipped'  => $skipped,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal sync vendor: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function limitText($value, int $max)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $max);
    }

    private function takeFirstEmail($email)
    {
        $email = trim((string) $email);

        if ($email === '') {
            return null;
        }

        $parts = preg_split('/\s*;\s*/', $email);

        return trim($parts[0] ?? '');
    }

    private function takePhonePart($phone, $index = 1)
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return null;
        }

        $parts = preg_split('/\s*,\s*/', $phone);

        if ($index === 1) {
            return trim($parts[0] ?? '');
        }

        if ($index === 2) {
            return trim($parts[1] ?? '');
        }

        return null;
    }
}