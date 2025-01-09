<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bagian;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['login', 'lupaPassword', 'konfirmPassword', 'sendResetLink', 'updateResetPassword']);
    }

    /**
     * Display dashboard/beranda
     */
    public function index()
    {
        // Get authenticated user data
        $user = Auth::user();

        // Get counts based on user level
        $suratMasukCount = SuratMasuk::when($user->level === 'user', function ($query) use ($user) {
            return $query->where('user_id', $user->id);
        })->count();

        $suratKeluarCount = SuratKeluar::when($user->level === 'user', function ($query) use ($user) {
            return $query->where('user_id', $user->id);
        })->count();

        return Inertia::render('Users/Beranda', [
            'suratMasukCount' => $suratMasukCount,
            'suratKeluarCount' => $suratKeluarCount,
        ]);
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        $user = Auth::user();

        return Inertia::render('Users/Profile', [
            'userData' => $user
        ]);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required',
            'email' => 'required|email',
            'alamat' => 'required',
            'telp' => 'required',
            'pengalaman' => 'required'
        ]);

        $user = User::find(Auth::id());


        $user->$user->update([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'telp' => $request->telp,
            'pengalaman' => $request->pengalaman
        ]);

        return redirect()->back()->with('message', 'Profile berhasil diupdate');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::find(Auth::id());
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()->with('message', 'Password berhasil diupdate');
    }

    /**
     * Display list of users (admin only)
     */
    public function pengguna()
    {
        $this->authorize('manage-users');

        $users = User::orderBy('id', 'DESC')->get();

        return Inertia::render('Users/Pengguna/Index', [
            'users' => $users
        ]);
    }

    /**
     * Show form to create new user
     */
    public function createPengguna()
    {
        $this->authorize('manage-users');

        return Inertia::render('Users/Pengguna/Create');
    }

    /**
     * Store new user
     */
    public function storePengguna(Request $request)
    {
        $this->authorize('manage-users');

        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'level' => 'required|in:s_admin,admin,user'
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'nama_lengkap' => $request->username,
            'level' => $request->level,
            'status' => 'aktif',
            'tgl_daftar' => now()->format('d-m-Y H:i:s')
        ]);

        return redirect()->route('users.pengguna')
            ->with('message', 'Pengguna berhasil ditambahkan');
    }

    /**
     * Show user edit form
     */
    public function editPengguna(User $user)
    {
        $this->authorize('manage-users');

        return Inertia::render('Users/Pengguna/Edit', [
            'userData' => $user
        ]);
    }

    /**
     * Update user
     */
    public function updatePengguna(Request $request, User $user)
    {
        $this->authorize('manage-users');

        $request->validate([
            'nama_lengkap' => 'required',
            'email' => 'required|email',
            'alamat' => 'required',
            'telp' => 'required',
            'pengalaman' => 'required',
            'level' => 'required|in:s_admin,admin,user'
        ]);

        $user->update($request->all());

        return redirect()->route('users.pengguna')
            ->with('message', 'Pengguna berhasil diupdate');
    }

    /**
     * Delete user
     */
    public function destroyPengguna(User $user)
    {
        $this->authorize('manage-users');

        if ($user->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'Anda tidak bisa menghapus akun sendiri');
        }

        $user->delete();

        return redirect()->route('users.pengguna')
            ->with('message', 'Pengguna berhasil dihapus');
    }
}
