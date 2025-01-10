<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\Lampiran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SuratMasukController extends Controller
{
    public function index()
    {
        // Get current user
        $user = Auth::user();

        $users = User::orderBy('nama_lengkap')->get();

        // Query surat masuk with relationships
        $suratMasuk = SuratMasuk::with(['user', 'lampiran'])
            ->when($user->level === 'user', function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->orderBy('id', 'DESC')
            ->get();

        return Inertia::render('SuratMasuk/Index', [
            'suratMasuk' => $suratMasuk,
            'users' => $users
        ]);
    }

    public function create()
    {
        // Get users for recipient dropdown
        $users = User::orderBy('nama_lengkap')->get();

        return Inertia::render('SuratMasuk/Create', [
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_asal' => 'required',
            'tgl_no_asal' => 'required',
            'penerima' => 'required',
            'perihal' => 'required',
            'lampiran' => 'required|file'
        ]);

        // Generate unique token for attachments
        $token = Str::random(40);

        // Create surat masuk
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

        // Handle file upload
        if ($request->hasFile('lampiran')) {
            $file = $request->file('lampiran');
            $fileName = $file->getClientOriginalName();
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

    public function show(SuratMasuk $suratMasuk)
    {
        // Mark as read if user is recipient
        if (Auth::id() === $suratMasuk->user_id) {
            $suratMasuk->update(['dibaca' => 1]);
        }

        return Inertia::render('SuratMasuk/Show', [
            'suratMasuk' => $suratMasuk->load(['user', 'lampiran'])
        ]);
    }

    public function edit(SuratMasuk $suratMasuk)
    {
        $this->authorize('update', $suratMasuk);

        $users = User::orderBy('nama_lengkap')->get();

        return Inertia::render('SuratMasuk/Edit', [
            'suratMasuk' => $suratMasuk->load(['user', 'lampiran']),
            'users' => $users
        ]);
    }

    public function update(Request $request, SuratMasuk $suratMasuk)
    {
        $this->authorize('update', $suratMasuk);

        $request->validate([
            'tgl_no_asal' => 'required',
            'penerima' => 'required',
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

    public function destroy(SuratMasuk $suratMasuk)
    {
        $this->authorize('delete', $suratMasuk);

        // Delete associated attachments
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

    public function toggleDisposisi(SuratMasuk $suratMasuk)
    {
        $this->authorize('update', $suratMasuk);

        $suratMasuk->update([
            'disposisi' => !$suratMasuk->disposisi
        ]);

        return redirect()->back();
    }
}
