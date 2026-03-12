<?php

namespace App\Http\Controllers;

use App\Models\BridgingSuratKontrolBpjs;
use App\Models\BridgingSep;
use App\Models\RegPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardKontrolController extends Controller
{
    public function index(Request $request)
    {
        $query = BridgingSuratKontrolBpjs::query()
            ->select(
                'bridging_surat_kontrol_bpjs.*',
                'pasien.nm_pasien',
                'pasien.no_tlp',
                'pasien.no_rkm_medis',
                'reg_periksa.tgl_registrasi',
                'bridging_sep.nomr'
            )
            ->join('bridging_sep', 'bridging_surat_kontrol_bpjs.no_sep', '=', 'bridging_sep.no_sep')
            ->join('reg_periksa', 'bridging_sep.no_rawat', '=', 'reg_periksa.no_rawat')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis');

        // Search by patient name or medical record
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('pasien.nm_pasien', 'like', "%{$search}%")
                  ->orWhere('pasien.no_rkm_medis', 'like', "%{$search}%");
        }

        // Default filter: today's date if no date range specified
        $start_date = $request->start_date ?? now()->toDateString();
        $end_date = $request->end_date ?? now()->toDateString();

        // Filter by date range
        if ($start_date) {
            $query->whereDate('bridging_surat_kontrol_bpjs.tgl_rencana', '>=', $start_date);
        }

        if ($end_date) {
            $query->whereDate('bridging_surat_kontrol_bpjs.tgl_rencana', '<=', $end_date);
        }

        // Order by scheduled date
        $kontrols = $query->orderBy('bridging_surat_kontrol_bpjs.tgl_rencana', 'desc')
                          ->paginate(15)
                          ->appends($request->query());

        // Get statistics - count total schedules
        $stats = [
            'total' => BridgingSuratKontrolBpjs::count(),
            'upcoming' => BridgingSuratKontrolBpjs::whereDate('tgl_rencana', '>=', now())->count(),
            'past' => BridgingSuratKontrolBpjs::whereDate('tgl_rencana', '<', now())->count(),
        ];

        return view('dashboard.kontrol.index', compact('kontrols', 'stats', 'start_date', 'end_date'));
    }

    public function show($no_surat)
    {
        $kontrol = BridgingSuratKontrolBpjs::with('bridgingSep')
            ->findOrFail($no_surat);

        return view('dashboard.kontrol.show', compact('kontrol'));
    }

    public function sendReminder(Request $request, $no_surat)
    {
        $request->validate([
            'schedule_date' => 'required|date',
            'schedule_time' => 'required|date_format:H:i',
            'pesan' => 'required|string|max:1000',
        ]);

        // Get the kontrol data with relationships
        $kontrol = BridgingSuratKontrolBpjs::with(['bridgingSep' => function($query) {
            $query->with(['regPeriksa' => function($q) {
                $q->with('pasien');
            }]);
        }])->findOrFail($no_surat);

        // Get patient info from the relationship
        $regPeriksa = $kontrol->bridgingSep->first() ? $kontrol->bridgingSep->first()->regPeriksa : null;
        $pasien = $regPeriksa ? $regPeriksa->pasien : null;

        // Combine date and time
        $scheduledDateTime = $request->schedule_date . ' ' . $request->schedule_time;
        
        // Check if this is immediate send (now)
        $now = \Carbon\Carbon::now();
        $scheduledTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $scheduledDateTime);
        $isImmediate = $now->diffInMinutes($scheduledTime) <= 1;

        // Create blast message record
        $blastMessage = \App\Models\BlastMessage::create([
            'no_surat' => $no_surat,
            'no_rkm_medis' => $regPeriksa ? $regPeriksa->no_rkm_medis : null,
            'no_tlp' => $pasien ? $pasien->no_tlp : null,
            'pesan' => $request->pesan,
            'scheduled_at' => $isImmediate ? null : $scheduledDateTime,
            'status' => $isImmediate ? 'pending' : 'scheduled',
        ]);
        
        // If immediate, send now
        if ($isImmediate && $pasien) {
            try {
                $whatsappService = new \App\Services\WhatsAppService();
                $result = $whatsappService->sendMessage(
                    $pasien->no_tlp,
                    $request->pesan
                );
                
                if ($result['success']) {
                    $blastMessage->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                    ]);
                    $message = 'Pesan reminder berhasil dikirim langsung ke pasien';
                } else {
                    $blastMessage->update(['status' => 'failed']);
                    $message = 'Gagal mengirim pesan reminder: ' . ($result['message'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                $blastMessage->update(['status' => 'failed']);
                $message = 'Error mengirim pesan: ' . $e->getMessage();
            }
        } else {
            $message = 'Pesan reminder dijadwalkan berhasil dikirim pada ' . $scheduledDateTime;
        }

        return redirect()->route('dashboard.kontrol.index')
                        ->with('success', $message);
    }
}
