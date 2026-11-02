<?php

namespace App\Http\Controllers\Vms;

use App\Http\Controllers\Controller;
use App\Models\SysMenu;
use App\Models\Vms\VendorBusinessCategory;
use App\Models\Vms\VendorCategory;
use App\Services\Vms\RegistrationEmail;
use App\Services\Vms\RegistrationReview;
use App\Services\Vms\VendorDirectory;
use App\Services\Vms\VendorProfileWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    private function authorizeAccess(Request $request): void
    {
        $user = $request->user();
        abort_unless($user, 403);
        abort_unless(in_array('admin', $user->roles(), true) || SysMenu::allowedScreenKeysForUsername($user->username)->contains('LISTVENDOR|VMS'), 403);
    }

    public function index(Request $request, VendorDirectory $directory)
    {
        $this->authorizeAccess($request);
        $tabs = VendorDirectory::TABS;
        $activeTab = $request->query('tab', 'new');
        abort_unless(is_string($activeTab) && isset($tabs[$activeTab]), 404);
        $counts = $directory->counts();
        $categories = VendorCategory::orderBy('parent_category_name')->get(['id', 'parent_category_name']);
        $businesses = VendorBusinessCategory::orderBy('category_name')->get(['category_id', 'category_name', 'parent_category']);
        $tags = DB::connection('mysql7')->table('master_tag')->orderBy('nama_tag')->get(['id', 'nama_tag']);
        $types = DB::connection('mysql7')->table('vendor')->whereNotNull('type')->distinct()->pluck('type')->filter()->values();

        return response()->view('pages.vms.vendor.index', compact('tabs', 'activeTab', 'counts', 'categories', 'businesses', 'tags', 'types'))->header('Cache-Control', 'no-store, private');
    }

    public function json(Request $request, VendorDirectory $directory, string $tab)
    {
        $this->authorizeAccess($request);
        abort_unless(isset(VendorDirectory::TABS[$tab]), 404);
        $filters = $request->validate(['category' => ['nullable', 'integer', 'min:1'], 'business' => ['nullable', 'integer', 'min:1'], 'tag' => ['nullable', 'integer', 'min:1'], 'type' => ['nullable', 'string', 'max:30']]);

        return response()->json(['data' => $directory->rows($tab, $filters)])->header('Cache-Control', 'no-store, private');
    }

    public function registration(Request $request, VendorDirectory $directory, RegistrationReview $review, int $id)
    {
        $this->authorizeAccess($request);

        return response()->json($review->read($id, $directory) + ['can_decide' => $review->canDecide($request->user()), 'categories' => VendorCategory::where('status', 1)->orderBy('parent_category_name')->get(['id', 'parent_category_name']), 'businesses' => VendorBusinessCategory::where('status', 1)->orderBy('category_name')->get(['category_id', 'category_name', 'parent_category'])])->header('Cache-Control', 'no-store, private');
    }

    public function decideRegistration(Request $request, RegistrationReview $review, RegistrationEmail $email, VendorDirectory $directory, int $id)
    {
        $this->authorizeAccess($request);
        abort_unless($review->canDecide($request->user()), 403);
        $data = $request->validate(['decision' => ['required', Rule::in(['approve', 'reject'])], 'review_token' => ['required', 'string', 'size:64'],
            'vendor_name' => ['required_if:decision,approve', 'nullable', 'string', 'max:50'],
            'vendor_category' => ['required_if:decision,approve', 'nullable', 'integer', 'min:1'],
            'bidang_usaha' => ['required_if:decision,approve', 'nullable', 'integer', 'min:1'],
            'tags' => ['nullable', 'array', 'max:50'], 'tags.*' => ['required', 'string', 'max:255', 'distinct'], 'notes' => ['nullable', 'string', 'max:5000']]);
        $result = $review->decide($id, $data, $request->user());
        $email->deliver($result['delivery_id']);

        return response()->json(['message' => $result['message'], 'counts' => $directory->counts()]);
    }

    public function incomplete(Request $request, VendorDirectory $directory, int $id)
    {
        $this->authorizeAccess($request);

        return response()->json(['html' => view('pages.vms.vendor.incomplete', $directory->incomplete($id))->render()])->header('Cache-Control', 'no-store, private');
    }

    public function profile(Request $request, VendorProfileWorkflow $workflow, VendorDirectory $directory, int $id, string $mode)
    {
        $this->authorizeAccess($request);

        return response()->json(['html' => view('pages.vms.vendor.profile', $workflow->read($id, $mode, $request->user(), $directory))->render()])->header('Cache-Control', 'no-store, private');
    }

    public function profileDecision(Request $request, VendorProfileWorkflow $workflow, RegistrationEmail $email, VendorDirectory $directory, int $id)
    {
        $this->authorizeAccess($request);
        $data = $request->validate(['kind' => ['required', Rule::in(['basic', 'department', 'document', 'npwp', 'bank'])], 'decision' => ['required', Rule::in(['approve', 'reject', 'revise'])], 'record_id' => ['required_if:kind,document,npwp,bank', 'nullable', 'integer', 'min:1'], 'department' => ['required_if:kind,department', 'nullable', Rule::in(array_keys(VendorProfileWorkflow::DEPARTMENTS))], 'token' => ['required', 'string', 'size:64'], 'notes' => ['nullable', 'string', 'max:5000']]);
        $result = $workflow->decide($id, $data, $request->user());
        $email->deliver($result['delivery_id']);
        $mode = $directory->query('master')->where('v.vendor_id', $id)->exists() ? 'master' : ($directory->query('review')->where('v.vendor_id', $id)->exists() ? 'review' : null);

        return response()->json(['message' => $result['message'], 'counts' => $directory->counts(), 'mode' => $mode]);
    }

    public function profileChat(Request $request, VendorProfileWorkflow $workflow, int $id, string $mode)
    {
        $this->authorizeAccess($request);
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);
        $workflow->chat($id, $mode, $data['message'], $request->user(), (string) $request->ip());

        return response()->json(['message' => 'Pesan berhasil disimpan.']);
    }

    public function profilePassword(Request $request, VendorProfileWorkflow $workflow, int $id, string $mode)
    {
        $this->authorizeAccess($request);
        $data = $request->validate(['user_id' => ['required', 'integer', 'min:1'], 'password' => ['required', 'string', 'min:6', 'max:128', 'confirmed']]);
        $workflow->password($id, $mode, $data['user_id'], $data['password'], $request->user());

        return response()->json(['message' => 'Password vendor berhasil diperbarui.']);
    }

    public function show(Request $request, VendorDirectory $directory, int $id)
    {
        $this->authorizeAccess($request);
        $tab = $request->query('tab', 'new');
        abort_unless(is_string($tab) && isset(VendorDirectory::TABS[$tab]), 404);

        return response()->view('pages.vms.vendor.show', $directory->detail($id) + ['tab' => $tab])->header('Cache-Control', 'no-store, private');
    }
}
