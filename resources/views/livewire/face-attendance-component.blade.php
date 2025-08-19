<div class="space-y-6">
    @if ($successMessage)
        <flux:callout variant="success" class="mb-4">
            {{ $successMessage }}
        </flux:callout>
    @endif

    @if ($errorMessage)
        <flux:callout variant="danger" class="mb-4">
            {{ $errorMessage }}
        </flux:callout>
    @endif

    <div class="text-center">
        <flux:heading size="lg" class="mb-2">
            Absensi {{ $attendanceType === 'check_in' ? 'Masuk' : 'Keluar' }}
        </flux:heading>
        <flux:text class="text-gray-600">
            Gunakan face recognition untuk mencatat absensi {{ $attendanceType === 'check_in' ? 'masuk' : 'keluar' }}
        </flux:text>
    </div>

    {{-- Today's Attendance Summary --}}
    @if (!empty($todaysAttendance) && ($todaysAttendance['check_in'] || $todaysAttendance['check_out']))
        <div class="bg-blue-50 p-4 rounded-lg mb-6">
            <flux:heading size="sm" class="text-blue-800 mb-2">Absensi Hari Ini</flux:heading>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="flex items-center space-x-2">
                    <flux:icon name="arrow-right-on-rectangle" class="w-4 h-4 {{ $todaysAttendance['check_in'] ? ($isLateCheckIn ? 'text-red-600' : 'text-green-600') : 'text-gray-400' }}" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-700">Check In</div>
                        @if ($todaysAttendance['check_in'])
                            <div class="{{ $this->getCheckInTimeClass() }}">
                                {{ $todaysAttendance['check_in']->recorded_at->format('H:i:s') }}
                                <span class="text-xs">{{ $this->getCheckInStatusMessage() }}</span>
                            </div>
                        @else
                            <div class="text-gray-500 text-sm">Belum absen</div>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <flux:icon name="arrow-left-on-rectangle" class="w-4 h-4 {{ $todaysAttendance['check_out'] ? ($isEarlyCheckOut ? 'text-red-600' : 'text-blue-600') : 'text-gray-400' }}" />
                    <div class="flex-1">
                        <div class="font-medium text-gray-700">Check Out</div>
                        @if ($todaysAttendance['check_out'])
                            <div class="{{ $this->getCheckOutTimeClass() }}">
                                {{ $todaysAttendance['check_out']->recorded_at->format('H:i:s') }}
                                <span class="text-xs">{{ $this->getCheckOutStatusMessage() }}</span>
                            </div>
                        @else
                            <div class="text-gray-500 text-sm">Belum absen</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($attendanceStatus === 'complete')
        <div class="text-center space-y-4">
            <flux:icon name="check-circle" class="w-16 h-16 text-green-500 mx-auto" />
            <flux:heading size="md" class="text-green-600">
                Absensi Hari Ini Sudah Lengkap
            </flux:heading>
            <flux:text class="text-gray-600">
                Anda sudah melakukan check in dan check out hari ini. Sampai jumpa besok!
            </flux:text>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Check In:</span>
                        <div class="{{ $this->getCheckInTimeClass() }} text-sm">
                            {{ $todaysAttendance['check_in']->recorded_at->format('H:i:s') }}
                            <span class="text-xs ml-1">{{ $this->getCheckInStatusMessage() }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-gray-700">Check Out:</span>
                        <div class="{{ $this->getCheckOutTimeClass() }} text-sm">
                            {{ $todaysAttendance['check_out']->recorded_at->format('H:i:s') }}
                            <span class="text-xs ml-1">{{ $this->getCheckOutStatusMessage() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($attendanceStatus === 'already_checked_in')
        <div class="text-center space-y-4">
            <flux:icon name="exclamation-triangle" class="w-16 h-16 text-orange-500 mx-auto" />
            <flux:heading size="md" class="text-orange-600">
                Sudah Check In
            </flux:heading>
            <flux:text class="text-gray-600">
                Anda sudah check in hari ini pada 
                <span class="{{ $this->getCheckInTimeClass() }}">
                    {{ $todaysAttendance['check_in']->recorded_at->format('H:i:s') }}
                </span>
                <span class="text-sm">{{ $this->getCheckInStatusMessage() }}</span>.
                <br>Silahkan lakukan check out jika sudah selesai bekerja.
            </flux:text>
            <flux:button :href="route('attendance.check-out')" variant="primary" wire:navigate>
                <flux:icon name="arrow-left-on-rectangle" class="mr-2" />
                Check Out Sekarang
            </flux:button>
        </div>
    @endif

    @if ($attendanceStatus === 'no_check_in')
        <div class="text-center space-y-4">
            <flux:icon name="exclamation-triangle" class="w-16 h-16 text-red-500 mx-auto" />
            <flux:heading size="md" class="text-red-600">
                Tidak Ada Check In
            </flux:heading>
            <flux:text class="text-gray-600">
                Anda belum check in hari ini. Lakukan check in terlebih dahulu sebelum check out.
            </flux:text>
            <flux:button :href="route('attendance.check-in')" variant="primary" wire:navigate>
                <flux:icon name="arrow-right-on-rectangle" class="mr-2" />
                Check In Sekarang
            </flux:button>
        </div>
    @endif

    @if ($attendanceStatus === 'not_enrolled')
        <div class="text-center space-y-4">
            <flux:icon name="exclamation-triangle" class="w-16 h-16 text-orange-500 mx-auto" />
            <flux:heading size="md" class="text-orange-600">
                Wajah Belum Terdaftar
            </flux:heading>
            <flux:text class="text-gray-600">
                Anda harus mendaftarkan wajah terlebih dahulu sebelum dapat menggunakan absensi face recognition.
            </flux:text>
            <flux:button :href="route('face.enrollment')" variant="primary" wire:navigate icon="face-id">
                Daftar Wajah Sekarang
            </flux:button>
        </div>
    @endif

    @if ($attendanceStatus === 'idle')
        <div class="text-center">
            <flux:button wire:click="startCamera" variant="primary" icon="camera">
                Mulai {{ $attendanceType === 'check_in' ? 'Check In' : 'Check Out' }}
            </flux:button>
        </div>
    @endif

    @if ($showCamera && in_array($attendanceStatus, ['capturing', 'captured']))
        <div class="space-y-4">
            <div class="relative mx-auto w-96 h-72 bg-gray-200 rounded-lg overflow-hidden">
                @if ($attendanceStatus === 'capturing')
                    <video id="attendance-camera-preview" class="w-full h-full object-cover" autoplay muted playsinline></video>
                    <div class="absolute inset-0 border-4 border-dashed border-green-500 rounded-lg pointer-events-none"></div>

                    <!-- Face guide overlay -->
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-48 h-64 border-2 border-white rounded-full opacity-50"></div>
                    </div>

                    <!-- Live feedback -->
                    <div class="absolute top-4 left-4 right-4">
                        <div class="bg-black bg-opacity-50 text-white text-sm rounded px-3 py-2 text-center">
                            <span id="attendance-feedback">Posisikan wajah dalam lingkaran</span>
                        </div>
                    </div>
                @endif

                @if ($attendanceStatus === 'captured' && $capturedImage)
                    <img src="data:image/jpeg;base64,{{ $capturedImage }}" class="w-full h-full object-cover" alt="Captured image">
                @endif
            </div>

            <div class="flex justify-center gap-4">
                @if ($attendanceStatus === 'capturing')
                    <flux:button id="attendance-capture-btn" variant="primary" onclick="captureAttendanceImage()" icon="camera">
                        Ambil Foto
                    </flux:button>
                @endif

                @if ($attendanceStatus === 'captured')
                    <flux:button wire:click="retake" variant="outline" icon="arrow-path">
                        Ambil Ulang
                    </flux:button>

                    <flux:button
                        wire:click="processAttendance"
                        variant="primary"
                        :disabled="$attendanceStatus === 'processing'"
                    >
                        <span wire:loading.remove wire:target="processAttendance">
                            Proses Absensi
                        </span>
                        <span wire:loading wire:target="processAttendance">
                            Memproses...
                        </span>
                    </flux:button>
                @endif
            </div>
        </div>
    @endif

    @if ($attendanceStatus === 'success' && $identifiedUser)
        <div class="text-center space-y-4">
            <flux:icon name="check-circle" class="w-16 h-16 text-green-500 mx-auto" />
            <flux:heading size="md" class="text-green-600">
                Absensi Berhasil!
            </flux:heading>

            <div class="bg-gray-50 p-4 rounded-lg space-y-2">
                <div class="text-sm text-gray-600">
                    <strong>Nama:</strong> {{ $identifiedUser->name }}
                </div>
                <div class="text-sm text-gray-600">
                    <strong>Waktu:</strong> {{ now()->format('d/m/Y H:i:s') }}
                </div>
                @if ($confidenceLevel)
                    <div class="text-sm text-gray-600">
                        <strong>Confidence:</strong> {{ number_format($confidenceLevel * 100, 1) }}%
                    </div>
                @endif
                @if ($maskDetected)
                    <div class="text-sm text-orange-600">
                        <flux:icon name="exclamation-triangle" class="w-4 h-4 inline mr-1" />
                        Mengenakan masker
                    </div>
                @endif
            </div>

            <flux:button wire:click="resetComponent" variant="outline" class="mt-4" icon="arrow-path">
                Absensi Lagi
            </flux:button>
        </div>
    @endif
</div>

@script
<script>
    let attendanceStream = null;
    let attendanceVideo = null;
    let attendanceCanvas = null;

    // Make captureAttendanceImage globally accessible
    window.captureAttendanceImage = function() {
        console.log('Global captureAttendanceImage function called');
        captureAttendanceImageInternal();
    };

    function startAttendanceCamera() {
        console.log('startAttendanceCamera function called');
        attendanceVideo = document.getElementById('attendance-camera-preview');

        if (!attendanceVideo) {
            console.error('Attendance camera preview element not found');
            return;
        }

        console.log('Attendance camera preview element found, requesting media access...');

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            })
            .then(function(mediaStream) {
                console.log('Attendance camera access granted, setting up stream');
                attendanceStream = mediaStream;
                attendanceVideo.srcObject = mediaStream;

                // Add event listeners for video
                attendanceVideo.addEventListener('loadedmetadata', () => {
                    console.log('Attendance video metadata loaded');
                    attendanceVideo.play().catch(e => console.error('Error playing attendance video:', e));
                });

                attendanceVideo.addEventListener('playing', () => {
                    console.log('Attendance video is now playing');
                });

                attendanceVideo.addEventListener('error', (e) => {
                    console.error('Attendance video error:', e);
                });

                // Force video to start
                setTimeout(() => {
                    console.log('Attendance video dimensions:', attendanceVideo.videoWidth, 'x', attendanceVideo.videoHeight);
                    console.log('Attendance video readyState:', attendanceVideo.readyState);
                    console.log('Attendance video paused:', attendanceVideo.paused);

                    attendanceVideo.play().catch(e => console.error('Error playing attendance video:', e));
                }, 100);
            })
            .catch(function(error) {
                console.error('Error accessing attendance camera:', error);
                alert('Tidak dapat mengakses kamera: ' + error.message);
            });
        } else {
            console.error('getUserMedia not supported');
            alert('Browser Anda tidak mendukung akses kamera');
        }
    }

    function stopAttendanceCamera() {
        if (attendanceStream) {
            attendanceStream.getTracks().forEach(track => track.stop());
            attendanceStream = null;
        }
    }

    function captureAttendanceImageInternal() {
        console.log('captureAttendanceImageInternal function called');
        console.log('Attendance video object:', attendanceVideo);
        console.log('Attendance video playing:', !attendanceVideo?.paused);

        if (!attendanceVideo) {
            console.error('No attendance video element found');
            return;
        }

        console.log('Creating canvas and capturing attendance image...');
        attendanceCanvas = document.createElement('canvas');
        attendanceCanvas.width = attendanceVideo.videoWidth;
        attendanceCanvas.height = attendanceVideo.videoHeight;

        console.log('Attendance canvas dimensions:', attendanceCanvas.width, 'x', attendanceCanvas.height);

        const context = attendanceCanvas.getContext('2d');
        context.drawImage(attendanceVideo, 0, 0, attendanceCanvas.width, attendanceCanvas.height);

        const imageData = attendanceCanvas.toDataURL('image/jpeg', 0.9);
        const base64Data = imageData.split(',')[1];

        console.log('Attendance image captured, base64 length:', base64Data.length);

        stopAttendanceCamera();
        console.log('Calling Livewire captureImage method...');
        $wire.call('captureImage', base64Data);
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Attendance DOM loaded, setting up listeners');

        // Handle capture button click using event delegation
        document.addEventListener('click', function(e) {
            console.log('Attendance click detected on:', e.target);
            console.log('Attendance target ID:', e.target.id);
            console.log('Attendance target closest:', e.target.closest('#attendance-capture-btn'));

            if (e.target && (e.target.id === 'attendance-capture-btn' || e.target.closest('#attendance-capture-btn'))) {
                e.preventDefault();
                console.log('Attendance capture button clicked - calling captureAttendanceImageInternal()');
                captureAttendanceImageInternal();
            }
        });
    });

    // Get user location
    function getUserLocation() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject('Geolocation tidak didukung browser ini');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                position => {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    console.log('Location obtained:', latitude, longitude);

                    // Send location to Livewire
                    $wire.call('setUserLocation', latitude, longitude);
                    resolve({ latitude, longitude });
                },
                error => {
                    let message = 'Gagal mendapatkan lokasi: ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            message += 'Akses lokasi ditolak';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message += 'Informasi lokasi tidak tersedia';
                            break;
                        case error.TIMEOUT:
                            message += 'Request lokasi timeout';
                            break;
                        default:
                            message += 'Error tidak diketahui';
                            break;
                    }
                    reject(message);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 60000
                }
            );
        });
    }

    // Listen for attendance camera started event
    $wire.on('attendance-camera-started', () => {
        console.log('Attendance camera-started event received');

        // Get location first, then start camera
        getUserLocation()
            .then(() => {
                console.log('Location obtained, starting camera...');
                setTimeout(startAttendanceCamera, 200);
            })
            .catch(error => {
                console.warn('Location error:', error);
                // Start camera anyway, but location validation might fail
                setTimeout(startAttendanceCamera, 200);
            });
    });

    // Also try to start camera when page refreshes and conditions are met
    document.addEventListener('livewire:init', () => {
        console.log('Attendance Livewire initialized');

        // Get location on page load
        getUserLocation().catch(error => {
            console.warn('Initial location detection failed:', error);
        });

        // Check if camera should be shown on initial load
        if (document.getElementById('attendance-camera-preview')) {
            setTimeout(startAttendanceCamera, 300);
        }
    });

    // Clean up when component is destroyed
    document.addEventListener('livewire:navigating', () => {
        stopAttendanceCamera();
    });
</script>
@endscript
