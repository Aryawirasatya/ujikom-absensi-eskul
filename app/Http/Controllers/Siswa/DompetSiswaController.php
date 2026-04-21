<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\FlexibilityItem;
use App\Models\PointLedger;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DompetSiswaController extends Controller
{
    /**
     * Dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // 1. Ambil saldo langsung dari kolom (Gak perlu query latest ledger lagi)
        $currentPoints = $user->point_balance;

        // 2. Riwayat transaksi (Ledger)
        $ledgers = $user->pointLedgers()
              ->with(['attendance.activity'])
            ->latest()
            ->limit(50)
            ->get();

        // 3. Item yang tersedia di Marketplace
        $items = FlexibilityItem::latest()->get();

        // 4. Koleksi Token (Ganti INVENTORY ke AVAILABLE sesuai DB)
        $myTokens = $user->tokens()
            ->with('item')
            ->where('status', 'AVAILABLE') // Sesuai ENUM database
            ->latest()
            ->get();

        return view('siswa.dompet.index', compact(
            'currentPoints',
            'ledgers',
            'items',
            'myTokens'
        ));
    }

    /**
     * BUY TOKEN
     */
   public function buyToken(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:flexibility_items,id'
        ]);

        $user = auth()->user();

        try {
            DB::transaction(function () use ($request, $user) {
                // 1. Lock Item untuk keamanan data
                $item = FlexibilityItem::findOrFail($request->item_id);

                // 2. Cek Limit (Gunakan status AVAILABLE & ACTIVE)
                if ($item->stock_limit) {
                    $ownedCount = UserToken::where('user_id', $user->id)
                        ->where('item_id', $item->id)
                        ->whereIn('status', ['AVAILABLE', 'ACTIVE'])
                        ->count();

                    if ($ownedCount >= $item->stock_limit) {
                        throw new \Exception('Batas kepemilikan item ini sudah maksimal.');
                    }
                }

                // 3. Lock baris User agar saldo tidak 'balapan' (Race Condition)
                $userData = User::where('id', $user->id)->lockForUpdate()->first();

                // 4. Validasi kecukupan saldo
                if ($userData->point_balance < $item->point_cost) {
                    throw new \Exception('Saldo poin Anda tidak mencukupi.');
                }

                // 5. Potong Saldo secara Atomic (SQL: SET balance = balance - x)
                $userData->decrement('point_balance', $item->point_cost);

                // 6. Catat ke Riwayat (Audit Trail)
                PointLedger::create([
                    'user_id' => $user->id,
                    'transaction_type' => 'SPEND',
                    'amount' => $item->point_cost,
                    'current_balance' => $userData->point_balance, // Saldo setelah dikurang
                    'description' => 'Membeli ' . $item->item_name,
                ]);

                // 7. Terbitkan Token (Status wajib AVAILABLE sesuai ENUM DB)
                UserToken::create([
                    'user_id' => $user->id,
                    'item_id' => $item->id,
                    'status'  => 'AVAILABLE', 
                ]);
            });

            return back()->with('success', 'Transaksi berhasil! Cek menu Inventory.');

        } catch (\Exception $e) {
            // Jika ada satu saja yang gagal, semua proses di atas dibatalkan (Rollback)
            return back()->with('error', $e->getMessage());
        }
    }
}