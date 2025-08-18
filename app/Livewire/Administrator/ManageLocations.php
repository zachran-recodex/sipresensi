<?php

namespace App\Livewire\Administrator;

use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;

class ManageLocations extends Component
{
    use WithPagination;

    public $selectedLocationId;

    public $selectedLocation;

    // Form fields
    public $name = '';

    public $address = '';

    public $latitude = '';

    public $longitude = '';

    public $radius_meters = 100;

    public $is_active = true;

    // Search and filter
    public $search = '';

    public $statusFilter = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:500',
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
        'radius_meters' => 'required|integer|min:10|max:10000',
        'is_active' => 'boolean',
    ];

    protected $listeners = [
        'locationUpdated' => '$refresh',
        'modal.close' => 'onModalClose',
    ];

    public function render()
    {
        $locations = Location::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('address', 'like', '%'.$this->search.'%');
            })
            ->when($this->statusFilter !== '', function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('is_active', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->latest()
            ->paginate(10);

        return view('livewire.administrator.manage-locations', compact('locations'));
    }

    public function setEditLocation(int $locationId): void
    {
        $location = Location::findOrFail($locationId);

        $this->resetForm();
        $this->selectedLocationId = $location->id;
        $this->selectedLocation = $location;
        $this->name = $location->name;
        $this->address = $location->address;
        $this->latitude = $location->latitude;
        $this->longitude = $location->longitude;
        $this->radius_meters = $location->radius_meters;
        $this->is_active = $location->is_active;
    }

    public function setDeleteLocation(int $locationId): void
    {
        $location = Location::findOrFail($locationId);

        $this->selectedLocationId = $location->id;
        $this->selectedLocation = $location;
    }

    public function createLocation(): void
    {
        try {
            $this->validate();

            Location::create([
                'name' => $this->name,
                'address' => $this->address,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'radius_meters' => $this->radius_meters,
                'is_active' => $this->is_active,
            ]);

            $this->resetForm();
            $this->dispatch('locationCreated');
            $this->modal('create-location')->close();
            session()->flash('message', 'Lokasi berhasil dibuat.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat membuat lokasi: '.$e->getMessage());
        }
    }

    public function updateLocation(): void
    {
        try {
            $this->validate();

            $location = Location::findOrFail($this->selectedLocationId);

            $location->update([
                'name' => $this->name,
                'address' => $this->address,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'radius_meters' => $this->radius_meters,
                'is_active' => $this->is_active,
            ]);

            $this->resetForm();
            $this->dispatch('locationUpdated');
            $this->modal('edit-location')->close();
            session()->flash('message', 'Lokasi berhasil diperbarui.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat memperbarui lokasi: '.$e->getMessage());
        }
    }

    public function deleteLocation(): void
    {
        try {
            $location = Location::findOrFail($this->selectedLocationId);

            $location->delete();

            $this->resetForm();
            $this->dispatch('locationDeleted');
            $this->modal('delete-location')->close();
            session()->flash('message', 'Lokasi berhasil dihapus.');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat menghapus lokasi: '.$e->getMessage());
        }
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->address = '';
        $this->latitude = '';
        $this->longitude = '';
        $this->radius_meters = 100;
        $this->is_active = true;
        $this->selectedLocationId = null;
        $this->selectedLocation = null;

        $this->resetErrorBag();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function onModalClose(): void
    {
        // Reset form when modal is closed
        $this->resetForm();
    }
}
