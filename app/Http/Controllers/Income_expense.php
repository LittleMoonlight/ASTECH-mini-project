<?php

namespace App\Http\Controllers;

use App\Models\category;
use App\Models\Income_expense as Models_Income_expense;
use App\Models\Saldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Income_expense extends Controller
{
    public function index()
    {
        $pemasukan = Models_Income_expense::where('type', 'Pemasukan')
            ->selectRaw('category, SUM(total) as total')
            ->groupBy('category')
            ->get();

        $pengeluaran = Models_Income_expense::where('type', 'Pengeluaran')
            ->selectRaw('category, SUM(total) as total')
            ->groupBy('category')
            ->get();

        $history = Models_Income_expense::orderBy('tanggal', 'desc')->simplePaginate(5);
        $saldo = Saldo::first();
        $category = category::all();

        return view('welcome', compact('pemasukan', 'pengeluaran', 'category', 'saldo', 'history'));
    }
    public function simpan(Request $request)
    {
        $request->validate([
            'type'        => 'required|in:Pemasukan,Pengeluaran',
            'kategori'    => 'nullable|string',
            'newCategory' => 'nullable|string',
            'jumlah'      => 'required|numeric|min:1',
            'keterangan'  => 'nullable|string',
            'tanggal'     => 'required|date',
        ]);

        $kategori = $request->kategori === 'tambah' ? $request->newCategory : $request->kategori;

        $saldo = Saldo::first();
        if (!$saldo) {
            $saldo = Saldo::create(['saldo' => 0]);
        }

        if ($request->type === 'Pengeluaran' && $saldo->saldo < $request->jumlah) {
            return response()->json([
                'status' => 'error',
                'message' => 'Saldo tidak cukup untuk pengeluaran ini!'
            ]);
        }

        DB::transaction(function () use ($request, $kategori, $saldo) {
            Models_Income_expense::create([
                'type'     => $request->type,
                'category' => $kategori,
                'total'    => $request->jumlah,
                'tanggal'  => $request->tanggal,
                'keterangan' => $request->keterangan,
            ]);

            if (!category::where('category', $kategori)->where('type', $request->type)->exists()) {
                category::create([
                    'type'     => $request->type,
                    'category' => $kategori,
                ]);
            }

            if ($request->type === 'Pemasukan') {
                $saldo->saldo += $request->jumlah;
            } else {
                $saldo->saldo -= $request->jumlah;
            }

            $saldo->save();
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi berhasil disimpan!'
        ]);
    }
}
