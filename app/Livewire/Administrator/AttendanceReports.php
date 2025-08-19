<?php

namespace App\Livewire\Administrator;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceReports extends Component
{
    use WithPagination;

    public $selectedUser = '';

    public $startDate = '';

    public $endDate = '';

    public $attendanceType = '';

    public $method = '';

    public $search = '';

    public $perPage = 15;

    public $sortBy = 'recorded_at';

    public $sortDirection = 'desc';

    public function mount(): void
    {
        // Default date range - current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedUser(): void
    {
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->resetPage();
    }

    public function updatedAttendanceType(): void
    {
        $this->resetPage();
    }

    public function updatedMethod(): void
    {
        $this->resetPage();
    }

    public function sortBy($field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->selectedUser = '';
        $this->attendanceType = '';
        $this->method = '';
        $this->search = '';
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    public function setDateRange($range): void
    {
        $today = now();

        switch ($range) {
            case 'today':
                $this->startDate = $today->format('Y-m-d');
                $this->endDate = $today->format('Y-m-d');
                break;
            case 'yesterday':
                $yesterday = $today->subDay();
                $this->startDate = $yesterday->format('Y-m-d');
                $this->endDate = $yesterday->format('Y-m-d');
                break;
            case 'this_week':
                $this->startDate = $today->startOfWeek()->format('Y-m-d');
                $this->endDate = $today->endOfWeek()->format('Y-m-d');
                break;
            case 'last_week':
                $lastWeek = $today->subWeek();
                $this->startDate = $lastWeek->startOfWeek()->format('Y-m-d');
                $this->endDate = $lastWeek->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->startDate = $today->startOfMonth()->format('Y-m-d');
                $this->endDate = $today->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $lastMonth = $today->subMonth();
                $this->startDate = $lastMonth->startOfMonth()->format('Y-m-d');
                $this->endDate = $lastMonth->endOfMonth()->format('Y-m-d');
                break;
        }

        $this->resetPage();
    }

    public function getAttendanceRecordsProperty()
    {
        return AttendanceRecord::query()
            ->with(['user'])
            ->when($this->search, function (Builder $query) {
                $query->whereHas('user', function (Builder $userQuery) {
                    $userQuery->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('username', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->selectedUser, function (Builder $query) {
                $query->where('user_id', $this->selectedUser);
            })
            ->when($this->startDate, function (Builder $query) {
                $query->whereDate('recorded_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function (Builder $query) {
                $query->whereDate('recorded_at', '<=', $this->endDate);
            })
            ->when($this->attendanceType, function (Builder $query) {
                $query->where('type', $this->attendanceType);
            })
            ->when($this->method, function (Builder $query) {
                $query->where('method', $this->method);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function getUsersProperty()
    {
        return User::query()
            ->select(['id', 'name', 'username'])
            ->orderBy('name')
            ->get();
    }

    public function getStatsProperty()
    {
        $baseQuery = AttendanceRecord::query()
            ->when($this->startDate, function (Builder $query) {
                $query->whereDate('recorded_at', '>=', $this->startDate);
            })
            ->when($this->endDate, function (Builder $query) {
                $query->whereDate('recorded_at', '<=', $this->endDate);
            });

        return [
            'total_records' => (clone $baseQuery)->count(),
            'check_ins' => (clone $baseQuery)->where('type', 'check_in')->count(),
            'check_outs' => (clone $baseQuery)->where('type', 'check_out')->count(),
            'face_recognition' => (clone $baseQuery)->where('method', 'face_recognition')->count(),
            'unique_users' => (clone $baseQuery)->distinct('user_id')->count('user_id'),
        ];
    }

    public function render()
    {
        return view('livewire.administrator.attendance-reports', [
            'attendanceRecords' => $this->attendanceRecords,
            'users' => $this->users,
            'stats' => $this->stats,
        ]);
    }
}
