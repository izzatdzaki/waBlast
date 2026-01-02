<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardMobileBpjsController extends Controller
{
    public function index(Request $request)
    {
        // Get date filter or use today
        $date = $request->input('date', now()->toDateString());
        $search = $request->input('search', '');

        // Get pasien yang sudah mengambil antrian di Mobile BPJS
        $mobile_bpjs_query = DB::table('referensi_mobilejkn_bpjs as rmb')
            ->select(
                'rmb.*',
                'p.nm_pasien',
                'p.no_tlp',
                'p.no_ktp',
                'p.no_rkm_medis'
            )
            ->leftJoin('pasien as p', 'rmb.norm', '=', 'p.no_rkm_medis')
            ->whereDate('rmb.tanggalperiksa', $date);

        // Search filter
        if ($search) {
            $mobile_bpjs_query->where(function ($q) use ($search) {
                $q->where('p.nm_pasien', 'like', '%' . $search . '%')
                  ->orWhere('p.no_ktp', 'like', '%' . $search . '%')
                  ->orWhere('p.no_rkm_medis', 'like', '%' . $search . '%')
                  ->orWhere('rmb.nomorreferensi', 'like', '%' . $search . '%');
            });
        }

        $mobile_bpjs_data = $mobile_bpjs_query
            ->orderBy('rmb.tanggalperiksa', 'asc')
            ->paginate(20)
            ->appends($request->query());

        // Calculate statistics
        $total_today = DB::table('referensi_mobilejkn_bpjs')
            ->whereDate('tanggalperiksa', $date)
            ->count();

        $stats = [
            'total' => $total_today,
            'dengan_pasien' => $mobile_bpjs_data->count(),
        ];

        return view('dashboard.mobile_bpjs.index', compact('mobile_bpjs_data', 'stats', 'date', 'search'));
    }

    public function show($nomorreferensi)
    {
        // Get detail dari Mobile BPJS
        $mobile_bpjs = DB::table('referensi_mobilejkn_bpjs as rmb')
            ->select(
                'rmb.*',
                'p.nm_pasien',
                'p.no_tlp',
                'p.no_ktp',
                'p.no_rkm_medis',
                'p.alamat'
            )
            ->leftJoin('pasien as p', 'rmb.norm', '=', 'p.no_rkm_medis')
            ->where('rmb.nomorreferensi', $nomorreferensi)
            ->first();

        if (!$mobile_bpjs) {
            abort(404, 'Data Mobile BPJS tidak ditemukan');
        }

        // Get jadwal kontrol terkait (bridging_surat_kontrol_bpjs)
        $jadwal_terkait = DB::table('bridging_surat_kontrol_bpjs as bskb')
            ->select(
                'bskb.*',
                'p.nm_pasien',
                'rp.stts'
            )
            ->leftJoin('bridging_sep as bs', 'bskb.no_sep', '=', 'bs.no_sep')
            ->leftJoin('reg_periksa as rp', 'bs.no_rawat', '=', 'rp.no_rawat')
            ->leftJoin('pasien as p', 'rp.no_rkm_medis', '=', 'p.no_rkm_medis')
            ->where('bskb.no_surat', $nomorreferensi)
            ->orderBy('bskb.tgl_rencana', 'desc')
            ->get();

        return view('dashboard.mobile_bpjs.show', compact('mobile_bpjs', 'jadwal_terkait'));
    }
}
