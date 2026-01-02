<?php

namespace App\Http\Controllers;

use App\Models\Pasien;
use Illuminate\Http\Request;

class DashboardPasienController extends Controller
{
    public function index(Request $request)
    {
        $query = Pasien::query()
            ->select('pasien.*', 'penjab.png_jawab')
            ->leftJoin('penjab', 'pasien.kd_pj', '=', 'penjab.kd_pj');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('pasien.nm_pasien', 'like', "%{$search}%")
                  ->orWhere('pasien.no_rkm_medis', 'like', "%{$search}%")
                  ->orWhere('pasien.no_ktp', 'like', "%{$search}%")
                  ->orWhere('pasien.no_tlp', 'like', "%{$search}%");
        }

        // Filter by gender
        if ($request->has('jk') && $request->jk) {
            $query->where('pasien.jk', $request->jk);
        }

        // Filter by marital status
        if ($request->has('stts_nikah') && $request->stts_nikah) {
            $query->where('pasien.stts_nikah', $request->stts_nikah);
        }

        // Pagination
        $pasiens = $query->paginate(15)->appends($request->query());

        return view('dashboard.pasien.index', compact('pasiens'));
    }

    public function show($no_rkm_medis)
    {
        $pasien = Pasien::findOrFail($no_rkm_medis);
        $regPeriksa = $pasien->regPeriksa()->with('bridgingSep', 'poliklinik', 'dokter')->paginate(10);

        return view('dashboard.pasien.show', compact('pasien', 'regPeriksa'));
    }
}
