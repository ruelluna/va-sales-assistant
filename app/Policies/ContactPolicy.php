<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function update(User $user, Contact $contact): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }

    public function edit(User $user, Contact $contact): bool
    {
        return $user->hasAnyRole(['admin', 'manager']);
    }
}
