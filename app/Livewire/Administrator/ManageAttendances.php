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

    public $dailySchedules = [];

    public $isActive = true;

    // Search and filter
    public $search = '';

    public $locationFilter = '';

    public $statusFilter = '';

    protected $rules = [
        'userId' => 'required|exists:users,id',
        'locationId' => 'required|exists:locations,id',
        'dailySchedules' => 'required|array|min:1',
        'dailySchedules.*.clock_in' => 'required|date_format:H:i',
        'dailySchedules.*.clock_out' => 'required|date_format:H:i',
        'isActive' => 'boolean',
    ];

    protected $messages = [
        'userId.required' => 'Pilih pengguna terlebih dahulu.',
        'userId.exists' => 'Pengguna yang dipilih tidak valid.',
        'locationId.required' => 'Pilih lokasi terlebih dahulu.',
        'locationId.exists' => 'Lokasi yang dipilih tidak valid.',
        'dailySchedules.required' => 'Atur jadwal minimal untuk satu hari.',
        'dailySchedules.min' => 'Atur jadwal minimal untuk satu hari.',
        'dailySchedules.*.clock_in.required' => 'Jam masuk kerja harus diisi.',
        'dailySchedules.*.clock_in.date_format' => 'Format jam masuk kerja tidak valid.',
        'dailySchedules.*.clock_out.required' => 'Jam keluar kerja harus diisi.',
        'dailySchedules.*.clock_out.date_format' => 'Format jam keluar kerja tidak valid.',
        'dailySchedules.*.clock_out.after' => 'Jam keluar kerja harus setelah jam masuk kerja.',
    ];

    protected $listeners = [
        'attendanceCreated' => '$refresh',
        'attendanceUpdated' => '$refresh',
        'attendanceDeleted' => '$refresh',
        'modal.close' => 'onModalClose',
    ];

    public function setEditAttendance(int $attendanceId): void
    {
        $attendance = Attendance::with('user')->findOrFail($attendanceId);

        $this->resetForm();
        $this->selectedAttendanceId = $attendance->id;
        $this->selectedAttendance = $attendance;
        $this->userId = $attendance->user_id;
        $this->locationId = $attendance->location_id;
        $this->dailySchedules = $attendance->daily_schedules ?? [];
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
        // Custom validation for clock times
        $this->validateClockTimes();

        $this->validate();

        Attendance::create([
            'user_id' => $this->userId,
            'location_id' => $this->locationId,
            'daily_schedules' => $this->dailySchedules,
            'is_active' => $this->isActive,
        ]);

        $this->resetForm();
        $this->dispatch('attendanceCreated');
        $this->modal('create-attendance')->close();
        session()->flash('message', 'Pengaturan kehadiran berhasil dibuat.');
    }

    public function updateAttendance(): void
    {
        // Custom validation for clock times
        $this->validateClockTimes();

        $this->validate();

        $attendance = Attendance::findOrFail($this->selectedAttendanceId);

        $attendance->update([
            'user_id' => $this->userId,
            'location_id' => $this->locationId,
            'daily_schedules' => $this->dailySchedules,
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
        $this->dailySchedules = [];
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
        if (isset($this->dailySchedules[$day])) {
            unset($this->dailySchedules[$day]);
        } else {
            // Set default working hours when toggling a work day
            $this->dailySchedules[$day] = [
                'clock_in' => '09:00',
                'clock_out' => '17:00',
            ];
        }

        // Ensure the component is updated immediately
        $this->dispatch('scheduleUpdated');
    }

    public function updateDaySchedule(int $day, string $field, string $value): void
    {
        if (! isset($this->dailySchedules[$day])) {
            $this->dailySchedules[$day] = [
                'clock_in' => '09:00',
                'clock_out' => '17:00',
            ];
        }

        $this->dailySchedules[$day][$field] = $value;
    }

    protected function validateClockTimes(): void
    {
        foreach ($this->dailySchedules as $day => $schedule) {
            if (isset($schedule['clock_in'], $schedule['clock_out'])) {
                $clockIn = \DateTime::createFromFormat('H:i', $schedule['clock_in']);
                $clockOut = \DateTime::createFromFormat('H:i', $schedule['clock_out']);

                // Allow clock_out to be the next day (e.g., night shift)
                if ($clockOut <= $clockIn) {
                    $clockOut->add(new \DateInterval('P1D')); // Add 1 day
                }

                // Still validate that there's at least 1 hour difference
                $diff = $clockIn->diff($clockOut);
                $totalMinutes = ($diff->h * 60) + $diff->i;

                if ($totalMinutes < 60) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "dailySchedules.{$day}.clock_out" => 'Jam keluar kerja harus minimal 1 jam setelah jam masuk kerja.',
                    ]);
                }
            }
        }
    }

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
}
