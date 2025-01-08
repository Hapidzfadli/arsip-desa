<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\Bagian;
use App\Models\Lampiran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SuratKeluarController extends Controller
{
    // Constructor to apply middleware
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of surat keluar
     */
    public function index()
    {
        $user = Auth::user();

        // Query surat keluar with relationships
        // If user level is 'user', only show their own letters
        $suratKeluar = SuratKeluar::with(['user', 'bagian', 'lampiran'])
            ->when($user->level === 'user', function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->orderBy('id', 'DESC')
            ->get();

        // Return view with data
        return Inertia::render('SuratKeluar/Index', [
            'suratKeluar' => $suratKeluar,
            'userLevel' => $user->level
        ]);
    }

    /**
     * Show form for creating new surat keluar
     */
    public function create()
    {
        // Check if user is allowed to create
        if (Auth::user()->level === 's_admin') {
            return redirect()->route('404');
        }

        // Get bagian data for dropdown
        $bagian = Bagian::where('user_id', Auth::id())
            ->orderBy('nama_bagian', 'ASC')
            ->get();

        // Generate next surat number
        $nextNumber = $this->generateNextNumber();

        return Inertia::render('SuratKeluar/Create', [
            'bagian' => $bagian,
            'nextNumber' => $nextNumber,
            'today' => now()->format('d-m-Y')
        ]);
    }

    /**
     * Store a newly created surat keluar
     */
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'no_surat' => 'required',
            'tgl_ns' => 'required|date_format:d-m-Y',
            'bagian_id' => 'required|exists:bagian,id',
            'perihal' => 'required',
            'lampiran' => 'required|file|max:10240' // Max 10MB
        ]);

        // Generate unique token for attachments
        $token = Str::random(40);

        // Create surat keluar
        $suratKeluar = SuratKeluar::create([
            'no_surat' => $request->no_surat,
            'tgl_ns' => $request->tgl_ns,
            'perihal' => $request->perihal,
            'bagian_id' => $request->bagian_id,
            'token_lampiran' => $token,
            'user_id' => Auth::id(),
            'dibaca' => 0,
            'disposisi' => '',
            'peringatan' => 0,
            'tgl_sk' => now()->format('d-m-Y')
        ]);

        // Handle file upload
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $fileName = $file->getClientOriginalName();

            // Store file
            $file->storeAs('lampiran', $fileName);

            // Create lampiran record
            Lampiran::create([
                'nama_berkas' => $fileName,
                'ukuran' => $file->getSize(),
                'token_lampiran' => $token
            ]);
        }

        return redirect()->route('surat-keluar.index')
            ->with('message', 'Surat keluar berhasil ditambahkan');
    }

    /**
     * Display the specified surat keluar
     */
    public function show(SuratKeluar $suratKeluar)
    {
        // Load relationships
        $suratKeluar->load(['user', 'bagian', 'lampiran']);

        // Mark as read if admin
        if (Auth::user()->level === 'admin') {
            $suratKeluar->update(['dibaca' => 1]);
        }

        // Get bagian for disposisi dropdown
        $bagianList = Bagian::orderBy('nama_bagian', 'ASC')->get();

        return Inertia::render('SuratKeluar/Show', [
            'suratKeluar' => $suratKeluar,
            'bagianList' => $bagianList,
            'userLevel' => Auth::user()->level
        ]);
    }

    /**
     * Show form for editing surat keluar
     */
    public function edit(SuratKeluar $suratKeluar)
    {
        // Check authorization
        if (Auth::user()->level === 's_admin' || Auth::user()->level === 'admin') {
            return redirect()->route('404');
        }

        // Check if user owns this surat
        if ($suratKeluar->user_id !== Auth::id()) {
            return redirect()->route('surat-keluar.index')
                ->with('error', 'Anda tidak berhak mengubah surat keluar ini');
        }

        // Get bagian data
        $bagian = Bagian::where('user_id', Auth::id())
            ->orderBy('nama_bagian', 'ASC')
            ->get();

        return Inertia::render('SuratKeluar/Edit', [
            'suratKeluar' => $suratKeluar->load(['lampiran']),
            'bagian' => $bagian
        ]);
    }

    /**
     * Update the specified surat keluar
     */
    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        // Validate request
        $request->validate([
            'tgl_ns' => 'required|date_format:d-m-Y',
            'bagian_id' => 'required|exists:bagian,id',
            'perihal' => 'required'
        ]);

        // Update surat keluar
        $suratKeluar->update([
            'tgl_ns' => $request->tgl_ns,
            'bagian_id' => $request->bagian_id,
            'perihal' => $request->perihal
        ]);

        return redirect()->route('surat-keluar.index')
            ->with('message', 'Surat keluar berhasil diupdate');
    }

    /**
     * Remove the specified surat keluar
     */
    public function destroy(SuratKeluar $suratKeluar)
    {
        // Check if user can delete
        if (Auth::user()->level === 's_admin' || Auth::user()->level === 'admin') {
            return redirect()->route('404');
        }

        // Delete associated files
        if ($suratKeluar->token_lampiran) {
            $lampiran = Lampiran::where('token_lampiran', $suratKeluar->token_lampiran)->get();

            foreach ($lampiran as $file) {
                Storage::delete('lampiran/' . $file->nama_berkas);
                $file->delete();
            }
        }

        $suratKeluar->delete();

        return redirect()->route('surat-keluar.index')
            ->with('message', 'Surat keluar berhasil dihapus');
    }

    /**
     * Toggle disposisi status
     */
    public function toggleDisposisi(Request $request, SuratKeluar $suratKeluar)
    {
        if ($request->disposisi) {
            $suratKeluar->update(['disposisi' => $request->bagian]);
        } else {
            $suratKeluar->update(['disposisi' => '']);
        }

        return redirect()->back();
    }

    /**
     * Toggle peringatan status
     */
    public function togglePeringatan(SuratKeluar $suratKeluar)
    {
        $suratKeluar->update([
            'peringatan' => !$suratKeluar->peringatan
        ]);

        return redirect()->back();
    }

    /**
     * Generate next surat keluar number
     */
    protected function generateNextNumber()
    {
        $lastSurat = SuratKeluar::orderBy('id', 'DESC')->first();

        if (!$lastSurat) {
            return "SKm/001";
        }

        $lastNumber = (int)substr($lastSurat->no_surat, 4);
        $newNumber = $lastNumber + 1;

        return "SKm/" . str_pad($newNumber, 3, "0", STR_PAD_LEFT);
    }
}
