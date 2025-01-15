<?php

namespace App\Policies;

use App\Models\SuratMasuk;
use App\Models\User;
use App\Models\Bagian;
use Illuminate\Auth\Access\HandlesAuthorization;

class SuratMasukPolicy
{
    use HandlesAuthorization;

    /**
     * Check if the user is Sekretaris Desa by querying the bagian table directly
     */
    private function isSekdes(User $user): bool
    {
        // Direct database query to check if user is sekdes and admin
        return Bagian::where('user_id', $user->id)
            ->where('nama_bagian', 'sekdes')
            ->exists() && $user->level === 'admin';
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Everyone can view the list
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SuratMasuk $suratMasuk): bool
    {
        return true;
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
    public function update(User $user, SuratMasuk $suratMasuk): bool
    {
        return $this->isSekdes($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SuratMasuk $suratMasuk): bool
    {
        return $this->isSekdes($user);
    }

    /**
     * Determine whether the user can print documents.
     */
    public function print(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can search documents.
     */
    public function search(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can manage disposisi.
     */
    public function manageDisposisi(User $user, SuratMasuk $suratMasuk): bool
    {
        return $this->isSekdes($user);
    }
}
