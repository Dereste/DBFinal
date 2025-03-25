<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserList extends Component
{
    public $showPasswordInput = [];
    public $newPasswords = [];

    protected $listeners = ['deleteUser', 'resetPassword'];

    public function deleteUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            session()->flash('error', 'User not found.');
            return;
        }

        // Prevent self-deletion
        if ($userId == auth()->id()) {
            session()->flash('error', 'You cannot delete yourself.');
            return;
        }

        $user->delete();
        session()->flash('success', 'User deleted successfully.');
    }

    public function resetPassword($userId, $newPassword)
    {
        if (empty($newPassword)) {
            session()->flash('error', 'New password cannot be empty.');
            return;
        }

        $user = User::find($userId);

        if (!$user) {
            session()->flash('error', 'User not found.');
            return;
        }

        $user->update(['password' => Hash::make($newPassword)]);
        session()->flash('success', 'Password has been reset successfully.');

        // Hide the input field after reset
        unset($this->showPasswordInput[$userId]);
    }


    public function render()
    {
        $users = User::select('id', 'UserName')
            ->where('id', '!=', 1)
            ->get();

        return view('livewire.user-list', compact('users'))->extends('layouts.app')->section('content');
    }
}
