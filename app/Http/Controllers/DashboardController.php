<?php

namespace App\Http\Controllers;

use App\Models\BridgingSuratKontrolBpjs;
use App\Models\Pasien;
use App\Models\RegPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Calculate pasien yang sudah datang hari ini
        // Logic: Ambil dari jadwal kontrol hari ini yang memiliki stts = 'Sudah' di reg_periksa
        $pasien_datang = DB::table('bridging_surat_kontrol_bpjs as bskb')
            ->join('bridging_sep as bs', 'bskb.no_sep', '=', 'bs.no_sep')
            ->join('reg_periksa as rp', 'bs.no_rawat', '=', 'rp.no_rawat')
            ->whereDate('bskb.tgl_rencana', now()->toDateString())
            ->where('rp.stts', 'Sudah')
            ->selectRaw('DISTINCT rp.no_rkm_medis')
            ->get()
            ->count();

        // Total statistics
        $stats = [
            'total_pasien' => Pasien::count(),
            'total_jadwal_kontrol' => BridgingSuratKontrolBpjs::count(),
            'jadwal_hari_ini' => BridgingSuratKontrolBpjs::whereDate('tgl_rencana', now()->toDateString())->count(),
            'jadwal_mendatang' => BridgingSuratKontrolBpjs::whereDate('tgl_rencana', '>', now()->toDateString())->count(),
            'pesan_terkirim' => 0, // Placeholder - akan diupdate saat integrasi WhatsApp
            'pasien_datang' => $pasien_datang,
        ];

        // Jadwal kontrol hari ini dengan status kehadiran
        $jadwal_hari_ini_raw = BridgingSuratKontrolBpjs::query()
            ->select(
                'bridging_surat_kontrol_bpjs.*',
                'pasien.nm_pasien',
                'pasien.no_rkm_medis',
                'reg_periksa.stts',
                'reg_periksa.jam_reg'
            )
            ->join('bridging_sep', 'bridging_surat_kontrol_bpjs.no_sep', '=', 'bridging_sep.no_sep')
            ->join('reg_periksa', 'bridging_sep.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->whereDate('bridging_surat_kontrol_bpjs.tgl_rencana', now()->toDateString())
            ->orderBy('bridging_surat_kontrol_bpjs.tgl_rencana', 'asc')
            ->limit(5)
            ->get();

        // Proses untuk tambah info asal kehadiran dan status kehadiran
        $jadwal_hari_ini = $jadwal_hari_ini_raw->map(function ($jadwal) {
            // Check apakah no_surat cocok dengan nomorreferensi di referensi_mobilejkn_bpjs
            $ada_di_mobilejkn = DB::table('referensi_mobilejkn_bpjs')
                ->where('nomorreferensi', $jadwal->no_surat)
                ->first();

            // Check status di reg_periksa pada tanggal yang sama (sesuai logic AttendanceTracking)
            $reg_periksa_data = DB::table('reg_periksa')
                ->where('no_rkm_medis', $jadwal->no_rkm_medis)
                ->whereDate('tgl_registrasi', now()->toDateString())
                ->first();

            // Tentukan status kehadiran dari stts di reg_periksa
            $status_kehadiran = ($reg_periksa_data && $reg_periksa_data->stts === 'Sudah') ? 'sudah_datang' : 'belum_datang';
            $waktu_datang = $reg_periksa_data ? $reg_periksa_data->jam_reg : null;
            
            // Tentukan asal kehadiran:
            // Mobile JKN jika no_surat cocok dengan nomorreferensi
            // ONSITE jika tidak cocok
            $asal_kehadiran = null;
            if ($ada_di_mobilejkn) {
                $asal_kehadiran = 'Mobile JKN';
            } elseif ($reg_periksa_data) {
                $asal_kehadiran = 'ONSITE';
            }

            $jadwal->status_kehadiran = $status_kehadiran;
            $jadwal->asal_kehadiran = $asal_kehadiran;
            $jadwal->waktu_datang = $waktu_datang;

            return $jadwal;
        });

        // Jadwal kontrol mendatang (7 hari ke depan)
        $jadwal_minggu_depan = BridgingSuratKontrolBpjs::with('bridgingSep')
            ->whereDate('tgl_rencana', '>', now()->toDateString())
            ->whereDate('tgl_rencana', '<=', now()->addDays(7)->toDateString())
            ->orderBy('tgl_rencana', 'asc')
            ->limit(5)
            ->get();

        return view('dashboard.main', compact('stats', 'jadwal_hari_ini', 'jadwal_minggu_depan'));
    }
}
