<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointRule;
use App\Models\FlexibilityItem;
use App\Models\User;
use App\Models\PointLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DompetAdminController extends Controller
{
    public function index()
    {
        $rules = PointRule::latest()->get();
        $items = FlexibilityItem::latest()->get();

        $baseQuery = User::role('siswa')
            ->select('users.id', 'users.name')
            ->addSelect([
                'points' => PointLedger::select('current_balance')
                    ->whereColumn('user_id', 'users.id')
                    ->latest('id')
                    ->limit(1)
            ]);

        $topStudents = (clone $baseQuery)
            ->orderByRaw('COALESCE(points,0) DESC')
            ->take(10)
            ->get();

        $bottomStudents = (clone $baseQuery)
            ->orderByRaw('COALESCE(points,0) ASC')
            ->take(10)
            ->get();

        return view('admin.dompet.index', compact('rules', 'items', 'topStudents', 'bottomStudents'));
    }

    public function storeRule(Request $request)
    {
        $validated = $this->validateRule($request);

        PointRule::create(array_merge($validated, [
            'target_role' => 'siswa'
        ]));

        return back()->with('success', 'Aturan berhasil dibuat.');
    }

    public function updateRule(Request $request, $id)
    {
        $rule = PointRule::findOrFail($id);
        $rule->update($this->validateRule($request));

        return back()->with('success', 'Aturan diperbarui.');
    }

    private function validateRule(Request $request)
{
    $validated = $request->validate([
        'rule_name'          => 'required|string|max:255',
        'condition_field'    => 'required|in:late_minutes,checkin_time,checkout_time,final_status',
        'condition_operator' => 'required|in:=,<,>,<=,>=,BETWEEN',
        'condition_value'    => 'required|string',
        'condition_value_2'  => 'nullable|string',
        'point_modifier'     => 'required|integer|not_in:0',
    ]);

    $field = $validated['condition_field'];
    $operator = $validated['condition_operator'];
    $val1 = $validated['condition_value'];
    $val2 = $validated['condition_value_2'];

    // 1. TIME VALIDATION
    if (in_array($field, ['checkin_time', 'checkout_time'])) {
        try {
            $t1 = Carbon::createFromFormat('H:i', $val1);
            if ($operator === 'BETWEEN') {
                if (empty($val2)) throw new \Exception("Wajib diisi");
                $t2 = Carbon::createFromFormat('H:i', $val2);
                if ($t1->gt($t2)) {
                    throw ValidationException::withMessages(['condition_value_2' => 'Jam awal harus lebih kecil dari jam akhir']);
                }
            }
        } catch (\Exception $e) {
            $msg = $e instanceof ValidationException ? $e->errors() : ['condition_value' => 'Format jam harus HH:MM'];
            throw ValidationException::withMessages($msg);
        }
    }

    // 2. NUMBER VALIDATION
    if ($field === 'late_minutes') {
        if (!is_numeric($val1)) throw ValidationException::withMessages(['condition_value' => 'Harus angka']);
        if ($operator === 'BETWEEN') {
            if (!is_numeric($val2)) throw ValidationException::withMessages(['condition_value_2' => 'Harus angka']);
            if ((int)$val1 > (int)$val2) throw ValidationException::withMessages(['condition_value_2' => 'Range minimal harus lebih kecil dari maksimal']);
        }
    }

    // 3. GLOBAL BETWEEN CHECK
    if ($operator === 'BETWEEN' && empty($val2)) {
        throw ValidationException::withMessages(['condition_value_2' => 'Nilai kedua wajib diisi untuk operator ANTARA']);
    }

    // 4. STATUS VALIDATION
    if ($field === 'final_status' && $operator !== '=') {
        throw ValidationException::withMessages(['condition_operator' => 'Status hanya boleh menggunakan operator SAMA DENGAN (=)']);
    }

    return $validated;
}

    public function storeItem(Request $request)
    {
        $data = $this->validateItem($request);
        FlexibilityItem::create($data);

        return back()->with('success', 'Item ditambahkan.');
    }

    public function updateItem(Request $request, $id)
    {
        $item = FlexibilityItem::findOrFail($id);
        $item->update($this->validateItem($request));

        return back()->with('success', 'Item diperbarui.');
    }

    private function validateItem(Request $request)
{
    $validated = $request->validate([
        'item_name'    => 'required|string|max:255',
        'token_type'   => 'required|in:late_forgiveness,free_alpha,forget_checkout',
        'effect_value' => 'nullable|integer|min:1',
        'point_cost'   => 'required|integer|min:1',
        'stock_limit'  => 'nullable|integer|min:1',
    ]);

    if ($validated['token_type'] === 'late_forgiveness') {
        if (empty($validated['effect_value'])) {
            throw ValidationException::withMessages(['effect_value' => 'Wajib mengisi menit toleransi untuk tipe ini']);
        }
    } else {
        // PENTING: Set ke 1 agar tidak null di database dan sinkron dengan sistem absensi
        $validated['effect_value'] = 1; 
    }

    return $validated;
}

    public function destroyRule($id)
    {
        PointRule::findOrFail($id)->delete();
        return back()->with('success', 'Rule dihapus.');
    }

    public function destroyItem($id)
    {
        $item = FlexibilityItem::findOrFail($id);
        
        // Kamu tidak perlu lagi cek DB::table('user_tokens')->exists() 
        // karena Soft Delete menjaga integritas relasi.
        $item->delete(); 

        return back()->with('success', 'Item berhasil ditarik dari Marketplace.');
    }
}