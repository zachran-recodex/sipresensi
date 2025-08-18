<?php

namespace App\Livewire\Administrator;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ManageAttendances extends Component
{
    use WithPagination;

    public $selectedAttendanceId;

    public $selectedAttendance;

    // Form fields
    public $userId = '';

    public $locationId = '';

    public $clockInTime = '09:00';

    public $clockOutTime = '17:00';

    public $workDays = [1, 2, 3, 4, 5]; // Default: Monday to Friday

    public $isActive = true;

    // Search and filter
    public $search = '';

    public $locationFilter = '';

    public $statusFilter = '';

    protected $rules = [
        'userId' => 'required|exists:users,id',
        'locationId' => 'required|exists:locations,id',
        'clockInTime' => 'required|date_format:H:i',
        'clockOutTime' => 'required|date_format:H:i|after:clockInTime',
        'workDays' => 'required|array|min:1',
        'isActive' => 'boolean',
    ];

    protected $messages = [
        'userId.required' => 'Pilih pengguna terlebih dahulu.',
        'userId.exists' => 'Pengguna yang dipilih tidak valid.',
        'locationId.required' => 'Pilih lokasi terlebih dahulu.',
        'locationId.exists' => 'Lokasi yang dipilih tidak valid.',
        'clockInTime.required' => 'Jam masuk kerja harus diisi.',
        'clockInTime.date_format' => 'Format jam masuk kerja tidak valid.',
        'clockOutTime.required' => 'Jam keluar kerja harus diisi.',
        'clockOutTime.date_format' => 'Format jam keluar kerja tidak valid.',
        'clockOutTime.after' => 'Jam keluar kerja harus setelah jam masuk kerja.',
        'workDays.required' => 'Pilih minimal satu hari kerja.',
        'workDays.min' => 'Pilih minimal satu hari kerja.',
    ];

    protected $listeners = [
        'attendanceUpdated' => '$refresh',
        'modal.close' => 'onModalClose',
    ];

    public function render()
    {
        $attendances = Attendance::with(['user', 'location'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('username', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->locationFilter, function ($query) {
                $query->where('location_id', $this->locationFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->latest()
            ->paginate(10);

        // Get only karyawan users (employees)
        $users = User::role('karyawan')
            ->whereDoesntHave('attendance')
            ->orWhereHas('attendance', function ($query) {
                $query->where('id', $this->selectedAttendanceId);
            })
            ->orderBy('name')
            ->get();

        // Get active locations
        $locations = Location::active()->orderBy('name')->get();

        return view('livewire.administrator.manage-attendances', compact('attendances', 'users', 'locations'));
    }

    public function setEditAttendance(int $attendanceId): void
    {
        $attendance = Attendance::with('user')->findOrFail($attendanceId);

        $this->resetForm();
        $this->selectedAttendanceId = $attendance->id;
        $this->selectedAttendance = $attendance;
        $this->userId = $attendance->user_id;
        $this->locationId = $attendance->location_id;
        $this->clockInTime = $attendance->getFormattedClockInTime();
        $this->clockOutTime = $attendance->getFormattedClockOutTime();
        $this->workDays = $attendance->work_days;
        $this->isActive = $attendance->is_active;

        // Update validation rules for edit
        $this->rules['userId'] = 'required|exists:users,id';
    }

    public function setDeleteAttendance(int $attendanceId): void
    {
        $attendance = Attendance::with('user')->findOrFail($attendanceId);
        $this->selectedAttendanceId = $attendance->id;
        $this->selectedAttendance = $attendance;
    }

    public function createAttendance(): void
    {
        $this->validate();

        Attendance::create([
            'user_id' => $this->userId,
            'location_id' => $this->locationId,
            'clock_in_time' => $this->clockInTime,
            'clock_out_time' => $this->clockOutTime,
            'work_days' => $this->workDays,
            'is_active' => $this->isActive,
        ]);

        $this->resetForm();
        $this->dispatch('attendanceCreated');
        $this->modal('create-attendance')->close();
        session()->flash('message', 'Pengaturan kehadiran berhasil dibuat.');
    }

    public function updateAttendance(): void
    {
        $this->validate();

        $attendance = Attendance::findOrFail($this->selectedAttendanceId);

        $attendance->update([
            'user_id' => $this->userId,
            'location_id' => $this->locationId,
            'clock_in_time' => $this->clockInTime,
            'clock_out_time' => $this->clockOutTime,
            'work_days' => $this->workDays,
            'is_active' => $this->isActive,
        ]);

        $this->resetForm();
        $this->dispatch('attendanceUpdated');
        $this->modal('edit-attendance')->close();
        session()->flash('message', 'Pengaturan kehadiran berhasil diperbarui.');
    }

    public function deleteAttendance(): void
    {
        $attendance = Attendance::findOrFail($this->selectedAttendanceId);
        $attendance->delete();

        $this->resetForm();
        $this->dispatch('attendanceDeleted');
        $this->modal('delete-attendance')->close();
        session()->flash('message', 'Pengaturan kehadiran berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->selectedAttendanceId = null;
        $this->selectedAttendance = null;
        $this->userId = '';
        $this->locationId = '';
        $this->clockInTime = '09:00';
        $this->clockOutTime = '17:00';
        $this->workDays = [1, 2, 3, 4, 5];
        $this->isActive = true;

        $this->resetErrorBag();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLocationFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function onModalClose(): void
    {
        $this->resetForm();
    }

    public function toggleWorkDay(int $day): void
    {
        if (in_array($day, $this->workDays)) {
            $this->workDays = array_values(array_filter($this->workDays, fn ($d) => $d !== $day));
        } else {
            $this->workDays[] = $day;
            sort($this->workDays);
        }
    }
}
