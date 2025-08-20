<?php

namespace App\Livewire\Administrator;

use App\Models\User;
use Livewire\Component;

class ShowUser extends Component
{
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->load([
            'roles',
            'faceEnrollment',
            'attendance.location',
            'attendanceRecords.location',
        ]);
    }

    public function render()
    {
        return view('livewire.administrator.show-user')
            ->layout('components.layouts.app');
    }
}
