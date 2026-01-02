<?php

namespace App\Http\Controllers;

use App\Models\BridgingSuratKontrolBpjs;
use App\Models\AttendanceTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceTrackingController extends Controller
{
    public function index(Request $request)
    {
        // Get date filter or use today
        $date = $request->input('date', now()->toDateString());

        // Get jadwal kontrol dari tanggal yang diminta
        $jadwal_list = BridgingSuratKontrolBpjs::query()
            ->select(
                'bridging_surat_kontrol_bpjs.*',
                'pasien.nm_pasien',
                'pasien.no_tlp',
                'pasien.no_rkm_medis',
                'reg_periksa.tgl_registrasi'
            )
            ->join('bridging_sep', 'bridging_surat_kontrol_bpjs.no_sep', '=', 'bridging_sep.no_sep')
            ->join('reg_periksa', 'bridging_sep.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->whereDate('bridging_surat_kontrol_bpjs.tgl_rencana', $date)
            ->orderBy('bridging_surat_kontrol_bpjs.tgl_rencana', 'asc')
            ->get();

        // Proses setiap jadwal untuk tentukan status kehadiran berdasarkan 3 tabel
        $attendance_data = $jadwal_list->map(function ($jadwal) use ($date) {
            // Check apakah no_surat cocok dengan nomorreferensi di referensi_mobilejkn_bpjs
            $ada_di_mobilejkn = DB::table('referensi_mobilejkn_bpjs')
                ->where('nomorreferensi', $jadwal->no_surat)
                ->first();

            // Check status di reg_periksa pada tanggal yang sama
            $reg_periksa_data = DB::table('reg_periksa')
                ->where('no_rkm_medis', $jadwal->no_rkm_medis)
                ->whereDate('tgl_registrasi', $date)
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

            return [
                'no_surat' => $jadwal->no_surat,
                'no_rkm_medis' => $jadwal->no_rkm_medis,
                'nama_pasien' => $jadwal->nm_pasien,
                'no_tlp' => $jadwal->no_tlp,
                'tgl_rencana' => $jadwal->tgl_rencana,
                'status_kehadiran' => $status_kehadiran,
                'waktu_datang' => $waktu_datang,
                'asal_kehadiran' => $asal_kehadiran,
            ];
        });

        // Hitung statistik
        $stats = [
            'total' => count($attendance_data),
            'sudah_datang' => count($attendance_data->where('status_kehadiran', 'sudah_datang')),
            'belum_datang' => count($attendance_data->where('status_kehadiran', 'belum_datang')),
        ];

        return view('attendance.tracking', compact('attendance_data', 'stats', 'date'));
    }

    public function export(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        // Get jadwal kontrol dari tanggal yang diminta
        $jadwal_list = BridgingSuratKontrolBpjs::query()
            ->select(
                'bridging_surat_kontrol_bpjs.*',
                'pasien.nm_pasien',
                'pasien.no_rkm_medis'
            )
            ->join('bridging_sep', 'bridging_surat_kontrol_bpjs.no_sep', '=', 'bridging_sep.no_sep')
            ->join('reg_periksa', 'bridging_sep.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->whereDate('bridging_surat_kontrol_bpjs.tgl_rencana', $date)
            ->get();

        // Proses untuk export
        $attendance_data = $jadwal_list->map(function ($jadwal) use ($date) {
            // Check apakah no_surat cocok dengan nomorreferensi di referensi_mobilejkn_bpjs
            $ada_di_mobilejkn = DB::table('referensi_mobilejkn_bpjs')
                ->where('nomorreferensi', $jadwal->no_surat)
                ->first();

            // Check status di reg_periksa
            $reg_periksa_data = DB::table('reg_periksa')
                ->where('no_rkm_medis', $jadwal->no_rkm_medis)
                ->whereDate('tgl_registrasi', $date)
                ->first();

            // Status kehadiran dari stts di reg_periksa
            $status_kehadiran = ($reg_periksa_data && $reg_periksa_data->stts === 'Sudah') ? 'Sudah Datang' : 'Belum Datang';
            $waktu_datang = $reg_periksa_data ? $reg_periksa_data->jam_reg : '-';
            
            // Asal kehadiran:
            // Mobile JKN jika no_surat cocok dengan nomorreferensi
            // ONSITE jika tidak cocok
            if ($ada_di_mobilejkn) {
                $asal_kehadiran = 'Mobile JKN';
            } elseif ($reg_periksa_data) {
                $asal_kehadiran = 'ONSITE';
            } else {
                $asal_kehadiran = '-';
            }

            return [
                $jadwal->no_surat,
                $jadwal->no_rkm_medis,
                $jadwal->nm_pasien,
                $status_kehadiran,
                $asal_kehadiran,
                $waktu_datang,
            ];
        });

        // Simple CSV export
        $filename = 'attendance_' . $date . '.csv';
        $handle = fopen('php://memory', 'r+');

        // Header
        fputcsv($handle, ['No. Surat', 'No. RM', 'Nama Pasien', 'Status Kehadiran', 'Asal Kehadiran', 'Waktu Datang']);

        foreach ($attendance_data as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
