<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\Lampiran;
use App\Models\User;
use App\Models\Bagian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SuratMasukController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Check if current user is Sekretaris Desa.
     * This checks both the bagian table and admin level.
     */
    private function isSekdes(): bool
    {
        return Bagian::where('user_id', Auth::id())
            ->where('nama_bagian', 'sekdes')
            ->exists() && Auth::user()->level === 'admin';
    }

    /**
     * Display a listing of incoming letters.
     * Shows different views based on user role.
     */
    public function index()
    {
        $this->authorize('viewAny', SuratMasuk::class);

        $user = Auth::user();
        $users = User::orderBy('nama_lengkap')->get();

        // Query surat masuk with relationships
        $suratMasuk = SuratMasuk::with(['user', 'lampiran'])
            ->orderBy('id', 'DESC')
            ->get();

        // Check sekdes permissions for UI controls
        $isSekdes = $this->isSekdes();

        return Inertia::render('SuratMasuk/Index', [
            'suratMasuk' => $suratMasuk,
            'users' => $users,
            'permissions' => [
                'canCreate' => $isSekdes,
                'canEdit' => $isSekdes,
                'canDelete' => $isSekdes,
                'canPrint' => true,
                'canSearch' => true,
            ]
        ]);
    }

    /**
     * Show the form for creating a new incoming letter.
     */
    public function create()
    {
        $this->authorize('create', SuratMasuk::class);

        $users = User::orderBy('nama_lengkap')->get();

        return Inertia::render('SuratMasuk/Create', [
            'users' => $users
        ]);
    }

    /**
     * Store a newly created incoming letter.
     * Handles both letter data and file attachments.
     */
    public function store(Request $request)
    {
        $this->authorize('create', SuratMasuk::class);

        // Validate the incoming request
        $request->validate([
            'no_asal' => 'required',
            'tgl_no_asal' => 'required|date',
            'penerima' => 'required|exists:users,id',
            'perihal' => 'required',
            'lampiran' => 'required|file|max:10240' // 10MB max size
        ]);

        // Generate unique token for attachments
        $token = Str::random(40);

        // Create new surat masuk record
        $suratMasuk = SuratMasuk::create([
            'no_surat' => $request->no_asal,
            'tgl_ns' => $request->tgl_no_asal,
            'no_asal' => $request->no_asal,
            'tgl_no_asal' => $request->tgl_no_asal,
            'pengirim' => Auth::user()->nama_lengkap,
            'penerima' => $request->penerima,
            'perihal' => $request->perihal,
            'token_lampiran' => $token,
            'user_id' => $request->penerima,
            'dibaca' => 0,
            'tgl_sm' => now()->format('d-m-Y')
        ]);

        // Handle file upload if present
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $fileName = $file->getClientOriginalName();

            // Store file and create lampiran record
            $file->storeAs('lampiran', $fileName);
            Lampiran::create([
                'nama_berkas' => $fileName,
                'ukuran' => $file->getSize(),
                'token_lampiran' => $token
            ]);
        }

        return redirect()->route('surat-masuk.index')
            ->with('message', 'Surat masuk berhasil ditambahkan');
    }

    /**
     * Display the specified incoming letter.
     * Marks letter as read for the recipient.
     */
    public function show(SuratMasuk $suratMasuk)
    {
        $this->authorize('view', $suratMasuk);

        // Mark as read if user is recipient
        if (Auth::id() === $suratMasuk->user_id) {
            $suratMasuk->update(['dibaca' => 1]);
        }

        return Inertia::render('SuratMasuk/Show', [
            'suratMasuk' => $suratMasuk->load(['user', 'lampiran']),
            'canEdit' => $this->isSekdes()
        ]);
    }

    /**
     * Show the form for editing an incoming letter.
     */
    public function edit(SuratMasuk $suratMasuk)
    {
        $this->authorize('update', $suratMasuk);

        $users = User::orderBy('nama_lengkap')->get();

        return Inertia::render('SuratMasuk/Edit', [
            'suratMasuk' => $suratMasuk->load(['user', 'lampiran']),
            'users' => $users
        ]);
    }

    /**
     * Update the specified incoming letter.
     */
    public function update(Request $request, SuratMasuk $suratMasuk)
    {
        $this->authorize('update', $suratMasuk);

        $request->validate([
            'tgl_no_asal' => 'required|date',
            'penerima' => 'required|exists:users,id',
            'perihal' => 'required'
        ]);

        $suratMasuk->update([
            'tgl_no_asal' => $request->tgl_no_asal,
            'penerima' => $request->penerima,
            'perihal' => $request->perihal,
            'user_id' => $request->penerima
        ]);

        return redirect()->route('surat-masuk.index')
            ->with('message', 'Surat masuk berhasil diupdate');
    }

    /**
     * Remove the specified incoming letter and its attachments.
     */
    public function destroy(SuratMasuk $suratMasuk)
    {
        $this->authorize('delete', $suratMasuk);

        // Delete associated attachments if they exist
        if ($suratMasuk->token_lampiran) {
            $lampiran = Lampiran::where('token_lampiran', $suratMasuk->token_lampiran)->get();

            foreach ($lampiran as $file) {
                Storage::delete('lampiran/' . $file->nama_berkas);
                $file->delete();
            }
        }

        $suratMasuk->delete();

        return redirect()->route('surat-masuk.index')
            ->with('message', 'Surat masuk berhasil dihapus');
    }

    /**
     * Toggle the disposisi status of an incoming letter.
     */
    public function toggleDisposisi(SuratMasuk $suratMasuk)
    {
        $this->authorize('manageDisposisi', $suratMasuk);

        $suratMasuk->update([
            'disposisi' => !$suratMasuk->disposisi
        ]);

        return redirect()->back();
    }

    /**
     * Download the attachment of an incoming letter.
     */
    public function downloadLampiran(SuratMasuk $suratMasuk)
    {
        $this->authorize('view', $suratMasuk);

        $lampiran = Lampiran::where('token_lampiran', $suratMasuk->token_lampiran)->first();

        if (!$lampiran) {
            return redirect()->back()
                ->with('error', 'Lampiran tidak ditemukan');
        }

        $path = 'lampiran/' . $lampiran->nama_berkas;

        if (!Storage::exists($path)) {
            return redirect()->back()
                ->with('error', 'File tidak ditemukan');
        }

        return Storage::download($path);
    }
}
