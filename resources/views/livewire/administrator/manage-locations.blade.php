<div x-data="{}">
	<div class="space-y-6">
		<!-- Header -->
		<div class="flex justify-between items-center">
			<div>
				<flux:heading size="xl">Kelola Lokasi</flux:heading>
				<p class="text-zinc-500">Buat, edit, hapus lokasi dan kelola status mereka</p>
			</div>
			<flux:button x-on:click="$wire.resetForm(); $flux.modal('create-location').show()" variant="primary" icon="plus">
				Tambah
			</flux:button>
		</div>

		<!-- Success/Error Messages -->
		@if (session('message'))
			<flux:callout variant="success" dismissible>
				{{ session('message') }}
			</flux:callout>
		@endif

		@if (session('error'))
			<flux:callout variant="danger" dismissible>
				{{ session('error') }}
			</flux:callout>
		@endif

		<!-- Filters -->
		<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
			<flux:input
				wire:model.live="search"
				placeholder="Cari lokasi berdasarkan nama atau alamat..."
				icon="magnifying-glass"
			/>
			<flux:select wire:model.live="statusFilter" placeholder="Filter berdasarkan status">
				<flux:select.option value="">Semua Status</flux:select.option>
				<flux:select.option value="active">Aktif</flux:select.option>
				<flux:select.option value="inactive">Tidak Aktif</flux:select.option>
			</flux:select>
		</div>

		<!-- Locations Table -->
		<div class="overflow-hidden bg-white shadow sm:rounded-lg border-zinc-200">
			<table class="min-w-full divide-y divide-zinc-200">
				<thead class="bg-zinc-50">
					<tr>
						<th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
							Lokasi
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
							Koordinat
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
							Radius
						</th>
						<th class="px-6 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">
							Status
						</th>
						<th class="px-6 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">
							Aksi
						</th>
					</tr>
				</thead>
				<tbody class="bg-white divide-y divide-zinc-200">
					@forelse($locations as $location)
						<tr wire:key="location-{{ $location->id }}">
							<td class="px-6 py-4 whitespace-nowrap">
								<div class="flex items-center">
									<div>
										<div class="text-sm font-medium text-zinc-900">
											{{ $location->name }}
										</div>
										<div class="text-sm text-zinc-500">
											{{ Str::limit($location->address, 50) }}
										</div>
									</div>
								</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900">
								<div class="space-y-1">
									<div>Lat: {{ number_format($location->latitude, 6) }}</div>
									<div>Lng: {{ number_format($location->longitude, 6) }}</div>
								</div>
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900">
								{{ $location->radius_meters }}m
							</td>
							<td class="px-6 py-4 whitespace-nowrap">
								<flux:badge
									size="sm"
									color="{{ $location->is_active ? 'green' : 'yellow' }}"
								>
									{{ $location->is_active ? 'Aktif' : 'Tidak Aktif' }}
								</flux:badge>
							</td>
							<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
								<div class="flex items-center gap-2 justify-end">
									<flux:button x-on:click="$wire.setEditLocation({{ $location->id }}); $flux.modal('edit-location').show()" size="sm" variant="ghost" icon="pencil">
										Edit
									</flux:button>
									<flux:button x-on:click="$wire.setDeleteLocation({{ $location->id }}); $flux.modal('delete-location').show()" size="sm" variant="danger" icon="trash">
										Hapus
									</flux:button>
								</div>
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="5" class="px-6 py-12 text-center">
								<div class="text-zinc-500">
									<flux:icon.map-pin class="mx-auto size-12 mb-4 text-zinc-300" />
									<h3 class="text-sm font-medium">Tidak ada lokasi ditemukan</h3>
									<p class="text-sm">Coba sesuaikan kriteria pencarian atau filter Anda.</p>
								</div>
							</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		<!-- Pagination -->
		<div>
			{{ $locations->links() }}
		</div>
	</div>

	<!-- Create Location Modal -->
	<flux:modal name="create-location" class="min-w-4xl">
		<form wire:submit.prevent="createLocation" class="space-y-6">
			<div>
				<flux:heading size="lg">Buat Lokasi Baru</flux:heading>
				<flux:subheading>Tambahkan lokasi baru ke sistem</flux:subheading>
			</div>

			<div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
				<!-- Left Column: Form Fields -->
				<div class="space-y-6">
					<flux:input
						wire:model="name"
						label="Nama Lokasi"
						placeholder="Masukkan nama lokasi"
						required
					/>

					<flux:textarea
						wire:model="address"
						label="Alamat Lengkap"
						placeholder="Masukkan alamat lengkap lokasi"
						rows="3"
						required
					/>

					<div class="space-y-4">
						<div class="flex items-center justify-between">
							<label class="text-sm font-medium text-zinc-700">Koordinat Lokasi</label>
							<flux:button 
								x-on:click="getCurrentLocation()" 
								size="sm" 
								variant="outline" 
								icon="map-pin"
								title="Gunakan lokasi saat ini"
							>
								Gunakan Lokasi Disini
							</flux:button>
						</div>
						<div class="grid grid-cols-2 gap-4">
							<flux:input
								wire:model="latitude"
								label="Latitude"
								type="number"
								step="0.000001"
								placeholder="-6.2088"
								required
							/>

							<flux:input
								wire:model="longitude"
								label="Longitude"
								type="number"
								step="0.000001"
								placeholder="106.8456"
								required
							/>
						</div>
					</div>

					<flux:input
						wire:model="radius_meters"
						label="Radius (meter)"
						type="number"
						min="10"
						max="10000"
						placeholder="100"
						required
					/>

					<flux:switch
						wire:model="is_active"
						label="Status Aktif"
						description="Lokasi aktif dapat digunakan untuk presensi"
					/>
				</div>

				<!-- Right Column: Map -->
				<div class="space-y-4">
					<flux:heading size="md">Pilih Lokasi di Peta</flux:heading>
					<div
						class="h-[500px] rounded border border-zinc-200 overflow-hidden"
						x-data="leafletMap({ lat: {{ $latitude ? $latitude : '-6.2088' }}, lng: {{ $longitude ? $longitude : '106.8456' }}, bindTo: $wire })"
						x-init="init()"
					></div>
					<p class="text-sm text-zinc-500">
						Klik pada peta atau drag marker untuk menentukan lokasi yang tepat
					</p>
				</div>
			</div>

			<div class="flex gap-3">
				<flux:spacer />

				<flux:button type="submit" variant="primary" wire:loading.attr="disabled">
					<span wire:loading.remove>Buat</span>
					<span wire:loading>Memproses...</span>
				</flux:button>
				<flux:modal.close>
					<flux:button variant="ghost">Batal</flux:button>
				</flux:modal.close>
			</div>
		</form>
	</flux:modal>

	<!-- Edit Location Modal -->
	<flux:modal name="edit-location" class="min-w-4xl">
		<form wire:submit.prevent="updateLocation" class="space-y-6">
			<div>
				<flux:heading size="lg">Edit Lokasi</flux:heading>
				<flux:subheading>Perbarui informasi lokasi</flux:subheading>
			</div>

			<div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
				<!-- Left Column: Form Fields -->
				<div class="space-y-6">
					<flux:input
						wire:model="name"
						label="Nama Lokasi"
						placeholder="Masukkan nama lokasi"
						required
					/>

					<flux:textarea
						wire:model="address"
						label="Alamat Lengkap"
						placeholder="Masukkan alamat lengkap lokasi"
						rows="3"
						required
					/>

					<div class="space-y-4">
						<div class="flex items-center justify-between">
							<label class="text-sm font-medium text-zinc-700">Koordinat Lokasi</label>
							<flux:button 
								x-on:click="getCurrentLocation()" 
								size="sm" 
								variant="outline" 
								icon="map-pin"
								title="Gunakan lokasi saat ini"
							>
								Gunakan Lokasi Disini
							</flux:button>
						</div>
						<div class="grid grid-cols-2 gap-4">
							<flux:input
								wire:model="latitude"
								label="Latitude"
								type="number"
								step="0.000001"
								placeholder="-6.2088"
								required
							/>

							<flux:input
								wire:model="longitude"
								label="Longitude"
								type="number"
								step="0.000001"
								placeholder="106.8456"
								required
							/>
						</div>
					</div>

					<flux:input
						wire:model="radius_meters"
						label="Radius (meter)"
						type="number"
						min="10"
						max="10000"
						placeholder="100"
						required
					/>

					<flux:switch
						wire:model="is_active"
						label="Status Aktif"
						description="Lokasi aktif dapat digunakan untuk presensi"
					/>
				</div>

				<!-- Right Column: Map -->
				<div class="space-y-4">
					<flux:heading size="md">Edit Lokasi di Peta</flux:heading>
					<div
						class="h-[500px] rounded border border-zinc-200 overflow-hidden"
						x-data="leafletMap({ lat: {{ $latitude ? $latitude : '-6.2088' }}, lng: {{ $longitude ? $longitude : '106.8456' }}, bindTo: $wire })"
						x-init="init()"
					></div>
					<p class="text-sm text-zinc-500">
						Klik pada peta atau drag marker untuk mengubah lokasi
					</p>
				</div>
			</div>

			<div class="flex gap-3">
				<flux:spacer />

				<flux:button type="submit" variant="primary" wire:loading.attr="disabled">
					<span wire:loading.remove>Perbarui</span>
					<span wire:loading>Memproses...</span>
				</flux:button>
				<flux:modal.close>
					<flux:button variant="ghost">Batal</flux:button>
				</flux:modal.close>
			</div>
		</form>
	</flux:modal>

	<!-- Delete Location Modal -->
	<flux:modal name="delete-location" class="md:w-[32rem]">
		<div class="space-y-6">
			<div>
				<flux:heading size="lg">Hapus Lokasi</flux:heading>
				<flux:subheading>Apakah Anda yakin ingin menghapus lokasi ini?</flux:subheading>
			</div>

			@if($selectedLocation)
				<div class="bg-zinc-50 p-4 rounded-lg">
					<div class="text-sm text-zinc-900">
						<strong>Nama:</strong> {{ $selectedLocation->name }}
					</div>
					<div class="text-sm text-zinc-500 mt-1">
						<strong>Alamat:</strong> {{ $selectedLocation->address }}
					</div>
					<div class="text-sm text-zinc-500 mt-1">
						<strong>Koordinat:</strong> {{ $selectedLocation->latitude }}, {{ $selectedLocation->longitude }}
					</div>
				</div>

				<div class="text-sm text-zinc-600">
					<p><strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan. Semua data terkait lokasi ini akan dihapus secara permanen.</p>
				</div>
			@endif

			<div class="flex gap-3">
				<flux:spacer />

				<flux:button wire:click="deleteLocation" variant="danger" wire:loading.attr="disabled">
					<span wire:loading.remove>Hapus</span>
					<span wire:loading>Memproses...</span>
				</flux:button>
				<flux:modal.close>
					<flux:button variant="ghost">Batal</flux:button>
				</flux:modal.close>
			</div>
		</div>
	</flux:modal>

	@once
		<script>
			// Geolocation function
			function getCurrentLocation() {
				if (!navigator.geolocation) {
					alert('Geolocation tidak didukung oleh browser ini.');
					return;
				}

				// Show loading state
				const button = event.target.closest('button');
				const originalText = button.innerHTML;
				button.innerHTML = '<span class="flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Mendapatkan Lokasi...</span>';
				button.disabled = true;

				navigator.geolocation.getCurrentPosition(
					function(position) {
						const latitude = position.coords.latitude;
						const longitude = position.coords.longitude;
						
						// Update Livewire properties
						Livewire.find(button.closest('[wire\\:id]').getAttribute('wire:id'))?.call('setCurrentLocation', latitude, longitude);
						
						// Restore button
						button.innerHTML = originalText;
						button.disabled = false;
					},
					function(error) {
						let message = 'Gagal mendapatkan lokasi: ';
						switch(error.code) {
							case error.PERMISSION_DENIED:
								message += 'Akses lokasi ditolak oleh pengguna.';
								break;
							case error.POSITION_UNAVAILABLE:
								message += 'Informasi lokasi tidak tersedia.';
								break;
							case error.TIMEOUT:
								message += 'Permintaan lokasi timeout.';
								break;
							default:
								message += 'Error tidak diketahui.';
								break;
						}
						alert(message);
						
						// Restore button
						button.innerHTML = originalText;
						button.disabled = false;
					},
					{
						enableHighAccuracy: true,
						timeout: 10000,
						maximumAge: 0
					}
				);
			}

			// Make function globally available
			window.getCurrentLocation = getCurrentLocation;

			document.addEventListener('alpine:init', () => {
				window.leafletMap = ({ lat, lng, bindTo }) => ({
					map: null,
					marker: null,
					circle: null,
					init() {
						// Wait for modal to be fully rendered
						this.$nextTick(() => {
							this.initMap();
						});

						// Listen for modal show/hide events
						this.$el.addEventListener('flux:modal:shown', () => {
							if (this.map) {
								setTimeout(() => {
									this.map.invalidateSize();
									const center = [Number(this.$wire.latitude || lat), Number(this.$wire.longitude || lng)];
									this.map.setView(center, this.map.getZoom());
								}, 50);
							}
						});

						this.$el.addEventListener('flux:modal:hidden', () => {
							if (this.map) {
								this.destroy();
							}
						});
					},
					initMap() {
						const el = this.$el;
						const center = [Number(lat || -6.2088), Number(lng || 106.8456)];

						// Initialize map
						this.map = L.map(el, {
							zoomControl: true,
							scrollWheelZoom: true,
							doubleClickZoom: true,
							boxZoom: true,
							keyboard: true,
							dragging: true,
							attributionControl: true
						}).setView(center, 15);

						// Add tile layer
						L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
							maxZoom: 19,
							attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
						}).addTo(this.map);

						// Add marker
						this.marker = L.marker(center, {
							draggable: true,
							title: 'Drag untuk mengubah posisi'
						}).addTo(this.map);

						// Add radius circle
						this.updateRadiusCircle();

						// Add click handler for map
						this.map.on('click', (e) => {
							this.updateMarkerPosition(e.latlng);
						});

						// Add drag end handler for marker
						this.marker.on('dragend', (e) => {
							this.updateMarkerPosition(e.target.getLatLng());
						});

						// Force map resize after modal is shown
						setTimeout(() => {
							this.map.invalidateSize();
							this.map.setView(center, 15);
						}, 100);

						// Listen for coordinate changes
						this.$watch('$wire.latitude', (value) => {
							if (value && this.marker) {
								const newLatLng = [Number(value), Number(this.$wire.longitude || lng)];
								this.marker.setLatLng(newLatLng);
								this.updateRadiusCircle();
								this.map.setView(newLatLng, this.map.getZoom());
							}
						});

						this.$watch('$wire.longitude', (value) => {
							if (value && this.marker) {
								const newLatLng = [Number(this.$wire.latitude || lat), Number(value)];
								this.marker.setLatLng(newLatLng);
								this.updateRadiusCircle();
								this.map.setView(newLatLng, this.map.getZoom());
							}
						});

						// Listen for radius changes
						this.$watch('$wire.radius_meters', () => {
							this.updateRadiusCircle();
						});
					},
					updateMarkerPosition(latLng) {
						if (this.marker) {
							this.marker.setLatLng(latLng);
							// Update Livewire properties
							bindTo.set('latitude', Number(latLng.lat.toFixed(6)));
							bindTo.set('longitude', Number(latLng.lng.toFixed(6)));
							// Update radius circle
							this.updateRadiusCircle();
						}
					},
					updateRadiusCircle() {
						if (this.circle) {
							this.map.removeLayer(this.circle);
						}

						const radius = Number(this.$wire.radius_meters || 100);
						const center = this.marker ? this.marker.getLatLng() : [Number(this.$wire.latitude || -6.2088), Number(this.$wire.longitude || 106.8456)];

						this.circle = L.circle(center, {
							radius: radius,
							color: '#3b82f6',
							fillColor: '#3b82f6',
							fillOpacity: 0.2,
							weight: 2
						}).addTo(this.map);
					},
					destroy() {
						if (this.map) {
							this.map.remove();
							this.map = null;
							this.marker = null;
							this.circle = null;
						}
					}
				});
			});
		</script>
	@endonce
</div>
