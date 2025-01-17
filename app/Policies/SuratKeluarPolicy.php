<?php

namespace App\Policies;

use App\Models\SuratKeluar;
use App\Models\User;
use App\Models\Bagian;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuratKeluarPolicy
{
    use HandlesAuthorization;

    /**
     * Check if user is Sekdes
     */
    private function isSekdes(User $user): bool
    {
        return Bagian::where('user_id', $user->id)
                ->where('nama_bagian', 'sekdes')
                ->exists() && $user->level === 'admin';
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Everyone can view list
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SuratKeluar $suratKeluar): bool
    {
        // Sekdes can view all
        // Others can only view their own
        return $this->isSekdes($user) || $suratKeluar->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isSekdes($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SuratKeluar $suratKeluar): bool
    {
        return $this->isSekdes($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SuratKeluar $suratKeluar): bool
    {
        return $this->isSekdes($user);
    }

    /**
     * Determine whether the user can manage disposisi.
     */
    public function manageDisposisi(User $user, SuratKeluar $suratKeluar): bool
    {
        return $this->isSekdes($user);
    }

    /**
     * Determine whether the user can download attachments.
     */
    public function downloadLampiran(User $user, SuratKeluar $suratKeluar): bool
    {
        return $this->isSekdes($user) || $suratKeluar->user_id === $user->id;
    }
}
