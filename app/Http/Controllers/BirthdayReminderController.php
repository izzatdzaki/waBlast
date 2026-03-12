<?php

namespace App\Http\Controllers;

use App\Models\BirthdayReminder;
use App\Models\Pasien;
use App\Models\WhatsAppDevice;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BirthdayReminderController extends Controller
{
    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Tampilkan dashboard pengingat ulang tahun
     */
    public function index(Request $request)
    {
        $filter = $request->input('filter', 'today'); // today, week, month, all

        $query = BirthdayReminder::query();

        // Filter berdasarkan tanggal ulang tahun
        if ($filter === 'today') {
            $query->todayBirthday();
        } elseif ($filter === 'week') {
            $query->thisWeekBirthday();
        } elseif ($filter === 'month') {
            $query->whereBetween('birthday_date', [
                now()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ]);
        }

        // Filter berdasarkan status
        $status = $request->input('status');
        if ($status && $status !== '') {
            $query->where('status', $status);
        }

        $reminders = $query->with('patient')
            ->orderBy('birthday_date', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Data untuk chart
        $todayCount = BirthdayReminder::todayBirthday()->count();
        $weekCount = BirthdayReminder::thisWeekBirthday()->count();
        $pendingCount = BirthdayReminder::pending()->count();
        $sentCount = BirthdayReminder::where('status', 'sent')->count();

        return view('dashboard.birthday-reminder.index', compact(
            'reminders',
            'filter',
            'status',
            'todayCount',
            'weekCount',
            'pendingCount',
            'sentCount'
        ));
    }

    /**
     * Tampilkan form untuk membuat pengingat baru
     */
    public function create(Request $request)
    {
        // Get filter query string jika ada
        $filter = $request->input('filter', 'today');
        
        // Ambil pasien yang punya ulang tahun
        $patients = Pasien::where('tgl_lahir', '!=', null)
            ->orderBy('nm_pasien', 'asc')
            ->get();

        return view('dashboard.birthday-reminder.create', compact('patients', 'filter'));
    }

    /**
     * Simpan pengingat ulang tahun baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_rkm_medis' => 'required|exists:pasien,no_rkm_medis',
            'message' => 'required|string|min:5|max:1000',
            'receiver_phone' => 'required|regex:/^62[0-9]{9,12}$/',
            'send_now' => 'boolean',
            'scheduled_date' => 'nullable|date_format:Y-m-d H:i|after:now',
        ]);

        // Ambil data pasien
        $patient = Pasien::findOrFail($validated['no_rkm_medis']);

        // Ambil nomor WA device yang aktif
        $device = WhatsAppDevice::where('status', 'connected')->first();
        if (!$device) {
            return back()->with('error', 'Tidak ada device WhatsApp yang terhubung');
        }

        // Tentukan tanggal ulang tahun
        $birthdayDate = $patient->tgl_lahir;

        // Buat record pengingat
        $reminder = BirthdayReminder::create([
            'no_rkm_medis' => $validated['no_rkm_medis'],
            'message' => $validated['message'],
            'sender_phone' => $device->phone,
            'receiver_phone' => $validated['receiver_phone'],
            'birthday_date' => $birthdayDate,
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'status' => $validated['send_now'] ? 'pending' : 'scheduled',
        ]);

        // Jika send_now, kirim langsung
        if ($validated['send_now'] ?? false) {
            $this->sendReminder($reminder);
        }

        return redirect()->route('dashboard.birthday-reminder.index')
            ->with('success', 'Pengingat ulang tahun berhasil disimpan');
    }

    /**
     * Kirim pengingat secara manual
     */
    public function send(BirthdayReminder $reminder)
    {
        if ($reminder->status === 'sent') {
            return back()->with('warning', 'Pesan pengingat sudah dikirim sebelumnya');
        }

        $this->sendReminder($reminder);

        return back()->with('success', 'Pesan pengingat berhasil dikirim');
    }

    /**
     * Logika pengiriman pesan pengingat
     */
    protected function sendReminder(BirthdayReminder $reminder)
    {
        try {
            $response = $this->whatsappService->sendMessage(
                $reminder->receiver_phone,
                $reminder->message
            );

            // Update status
            $reminder->update([
                'status' => 'sent',
                'response' => json_encode($response),
                'sent_at' => now(),
            ]);

            Log::info("Birthday reminder sent", [
                'reminder_id' => $reminder->id,
                'patient' => $reminder->no_rkm_medis,
                'response' => $response
            ]);
        } catch (\Exception $e) {
            $reminder->update([
                'status' => 'failed',
                'response' => json_encode(['error' => $e->getMessage()])
            ]);

            Log::error("Birthday reminder failed", [
                'reminder_id' => $reminder->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Hapus pengingat ulang tahun
     */
    public function destroy(BirthdayReminder $reminder)
    {
        $reminder->delete();

        return back()->with('success', 'Pengingat ulang tahun berhasil dihapus');
    }

    /**
     * Synchronize birthday data dari tabel pasien
     */
    public function sync(Request $request)
    {
        // Ambil semua pasien yang punya tgl_lahir
        $patients = Pasien::where('tgl_lahir', '!=', null)->get();

        $created = 0;
        $updated = 0;

        foreach ($patients as $patient) {
            // Cek apakah sudah ada reminder untuk pasien ini di tahun ini
            $existingReminder = BirthdayReminder::where('no_rkm_medis', $patient->no_rkm_medis)
                ->whereYear('birthday_date', now()->year)
                ->first();

            if (!$existingReminder) {
                // Ambil nomor WA pertama dari nama/kontak
                $phone = $this->extractPhoneNumber($patient);
                
                if ($phone) {
                    $device = WhatsAppDevice::where('status', 'connected')->first();
                    BirthdayReminder::create([
                        'no_rkm_medis' => $patient->no_rkm_medis,
                        'message' => "Selamat ulang tahun {$patient->nm_pasien}! 🎉 Semoga hari istimewamu dipenuhi berkah dan kebahagiaan. Terima kasih telah mempercayai kami untuk kesehatan Anda.",
                        'sender_phone' => $device ? $device->phone : '',
                        'receiver_phone' => $phone,
                        'birthday_date' => $patient->tgl_lahir->copy()->year(now()->year),
                        'status' => 'pending',
                    ]);
                    $created++;
                }
            }
        }

        return back()->with('success', "Sinkronisasi berhasil! Dibuat: {$created} pengingat baru");
    }

    /**
     * Extract nomor WA dari informasi pasien
     */
    protected function extractPhoneNumber($patient)
    {
        // Coba dari field no_hp jika ada
        if (method_exists($patient, 'no_hp') && !empty($patient->no_hp)) {
            $phone = $this->formatPhoneNumber($patient->no_hp);
            if ($phone) return $phone;
        }

        // Atau dari field no_telp
        if (method_exists($patient, 'no_telp') && !empty($patient->no_telp)) {
            $phone = $this->formatPhoneNumber($patient->no_telp);
            if ($phone) return $phone;
        }

        return null;
    }

    /**
     * Format nomor telepon ke format internasional WhatsApp
     */
    protected function formatPhoneNumber($phone)
    {
        // Hapus karakter non-digit
        $phone = preg_replace('/\D/', '', $phone);

        // Jika dimulai dengan 0, ganti dengan 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // Pastikan dimulai dengan 62
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        // Validasi panjang (62 + 8-12 digit)
        if (strlen($phone) >= 10 && strlen($phone) <= 14) {
            return $phone;
        }

        return null;
    }
}

