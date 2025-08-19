<div class="space-y-6">
    @if (session()->has('message'))
        <flux:callout variant="success" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if ($errorMessage)
        <flux:callout variant="danger" class="mb-4">
            {{ $errorMessage }}
        </flux:callout>
    @endif

    <div class="text-center">
        <flux:heading size="lg" class="mb-2">Daftarkan Wajah</flux:heading>
        <flux:text class="text-gray-600 mb-4">
            Ambil foto wajah Anda untuk sistem absensi face recognition
        </flux:text>

        <!-- Instructions -->
        <div class="bg-blue-50 p-4 rounded-lg text-left space-y-2 mb-6">
            <flux:heading size="sm" class="text-blue-800 mb-2">Tips untuk foto yang baik:</flux:heading>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• Pastikan wajah terlihat jelas dan tidak ada bayangan</li>
                <li>• Tatap langsung ke kamera</li>
                <li>• Lepas kacamata, topi, atau masker jika memungkinkan</li>
                <li>• Pastikan pencahayaan cukup terang</li>
                <li>• Jangan bergerak saat mengambil foto</li>
            </ul>
        </div>
    </div>

    @if ($enrollmentStatus === 'already_enrolled')
        <div class="text-center space-y-4">
            <flux:icon name="check-circle" class="w-16 h-16 text-green-500 mx-auto" />
            <flux:heading size="md" class="text-green-600">
                Wajah Sudah Terdaftar
            </flux:heading>
            <flux:text class="text-gray-600">
                Wajah Anda sudah terdaftar dalam sistem. Anda dapat menggunakan fitur absensi face recognition.
            </flux:text>

            <!-- Re-enrollment Information -->
            <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                <div class="flex flex-col sm:flex-row items-start space-y-3 sm:space-y-0 sm:space-x-3">
                    <flux:icon name="information-circle" class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" />
                    <div class="text-left">
                        <flux:heading size="sm" class="text-orange-800 mb-2">
                            Ingin Mendaftarkan Ulang Wajah?
                        </flux:heading>
                        <flux:text class="text-sm text-orange-700">
                            Jika Anda ingin mendaftarkan ulang wajah karena perubahan penampilan atau masalah pengenalan, 
                            silahkan hubungi administrator sistem untuk bantuan.
                        </flux:text>
                        <div class="mt-3 pt-2 border-t border-orange-200">
                            <flux:text class="text-sm font-medium text-orange-800">
                                Kontak {{ config('services.admin_contact.name') }}:
                            </flux:text>
                            <ul class="text-sm text-orange-700 mt-1 space-y-1">
                                @if (config('services.admin_contact.email'))
                                    <li>📧 Email: 
                                        <a href="mailto:{{ config('services.admin_contact.email') }}" 
                                           class="underline hover:text-orange-900 break-words">
                                            {{ config('services.admin_contact.email') }}
                                        </a>
                                    </li>
                                @endif
                                @if (config('services.admin_contact.whatsapp') ?: config('services.admin_contact.phone'))
                                    <li>📱 WhatsApp: 
                                        <a href="https://wa.me/{{ str_replace(['+', ' ', '-'], '', config('services.admin_contact.whatsapp') ?: config('services.admin_contact.phone')) }}" 
                                           target="_blank" 
                                           class="underline hover:text-orange-900 break-words">
                                            {{ config('services.admin_contact.whatsapp') ?: config('services.admin_contact.phone') }}
                                        </a>
                                    </li>
                                @endif
                                @if (config('services.admin_contact.department'))
                                    <li>🏢 Bagian: {{ config('services.admin_contact.department') }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Start Button -->
            <div class="flex justify-center">
                <flux:button :href="route('attendance.check-in')" variant="primary" wire:navigate class="w-full sm:w-auto">
                    <flux:icon name="arrow-right-on-rectangle" class="mr-2" />
                    Mulai Absensi
                </flux:button>
            </div>
        </div>
    @endif

    @if ($enrollmentStatus === 'idle')
        <div class="text-center">
            <flux:button wire:click="startCamera" variant="primary" icon="camera" class="w-full sm:w-auto">
                Mulai Kamera
            </flux:button>
        </div>
    @endif

    @if ($showCamera && in_array($enrollmentStatus, ['capturing', 'captured']))
        <div class="space-y-4">
            <div class="relative mx-auto w-full max-w-md h-64 sm:w-96 sm:h-72 bg-gray-200 rounded-lg overflow-hidden">
                @if ($enrollmentStatus === 'capturing')
                    <video id="camera-preview" class="w-full h-full object-cover" autoplay muted playsinline></video>
                    <div class="absolute inset-0 border-4 border-dashed border-blue-500 rounded-lg pointer-events-none"></div>

                    <!-- Face guide overlay -->
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-36 h-48 sm:w-48 sm:h-64 border-2 border-white rounded-full opacity-50"></div>
                    </div>

                    <!-- Live feedback -->
                    <div class="absolute top-4 left-4 right-4">
                        <div class="bg-black bg-opacity-50 text-white text-sm rounded px-3 py-2 text-center">
                            <span id="face-feedback">Posisikan wajah dalam lingkaran</span>
                        </div>
                    </div>
                @endif

                @if ($enrollmentStatus === 'captured' && $capturedImage)
                    <img src="data:image/jpeg;base64,{{ $capturedImage }}" class="w-full h-full object-cover" alt="Captured image">

                    <!-- Image quality check -->
                    <div class="absolute top-4 left-4 right-4">
                        <div class="bg-green-500 bg-opacity-90 text-white text-sm rounded px-3 py-2 text-center">
                            ✓ Foto berhasil diambil
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                @if ($enrollmentStatus === 'capturing')
                    <flux:button id="capture-btn" variant="primary" icon="camera" onclick="captureImage()" class="w-full sm:w-auto">
                        Ambil Foto
                    </flux:button>
                    <flux:button wire:click="startCamera" variant="outline" icon="arrow-path" class="w-full sm:w-auto">
                        Mulai Ulang
                    </flux:button>
                @endif

                @if ($enrollmentStatus === 'captured')
                    <flux:button wire:click="retake" variant="outline" icon="arrow-path" class="w-full sm:w-auto">
                        Ambil Ulang
                    </flux:button>

                    <flux:button
                        wire:click="enrollFace"
                        variant="primary"
                        :disabled="$enrollmentStatus === 'enrolling'"
                        class="w-full sm:w-auto"
                    >
                        <span wire:loading.remove wire:target="enrollFace" icon="check">
                            Daftarkan Wajah
                        </span>
                        <span wire:loading wire:target="enrollFace" icon="arrow-path">
                            Mendaftarkan...
                        </span>
                    </flux:button>
                @endif
            </div>
        </div>
    @endif

    @if ($enrollmentStatus === 'success')
        <div class="text-center space-y-4">
            <flux:icon name="check-circle" class="w-16 h-16 text-green-500 mx-auto" />
            <flux:heading size="md" class="text-green-600">
                Wajah Berhasil Didaftarkan!
            </flux:heading>
            <flux:text class="text-gray-600">
                Sekarang Anda dapat menggunakan face recognition untuk absensi.
            </flux:text>

            <!-- Re-enrollment Information -->
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 max-w-md mx-auto">
                <flux:text class="text-sm text-blue-700">
                    💡 <strong>Tips:</strong> Jika suatu saat wajah tidak bisa dikenali atau Anda ingin mendaftarkan ulang, 
                    hubungi <strong>{{ config('services.admin_contact.name') }}</strong> untuk bantuan.
                </flux:text>
            </div>

            <div class="flex justify-center mt-6">
                <flux:button :href="route('attendance.check-in')" variant="primary" wire:navigate icon="arrow-right-circle" class="w-full sm:w-auto">
                    Mulai Absensi
                </flux:button>
            </div>
        </div>
    @endif
</div>

@script
<script>
    let stream = null;
    let video = null;
    let canvas = null;

    // Make captureImage globally accessible
    window.captureImage = function() {
        console.log('Global captureImage function called');
        captureImageInternal();
    };

    function startCamera() {
        console.log('startCamera function called');
        video = document.getElementById('camera-preview');

        if (!video) {
            console.error('Camera preview element not found');
            return;
        }

        console.log('Camera preview element found, requesting media access...');

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
            })
            .then(function(mediaStream) {
                console.log('Camera access granted, setting up stream');
                stream = mediaStream;
                video.srcObject = stream;

                // Add event listeners for video
                video.addEventListener('loadedmetadata', () => {
                    console.log('Video metadata loaded');
                    video.play().catch(e => console.error('Error playing video:', e));
                });

                video.addEventListener('playing', () => {
                    console.log('Video is now playing');
                });

                video.addEventListener('error', (e) => {
                    console.error('Video error:', e);
                });

                // Force video to start
                setTimeout(() => {
                    console.log('Video dimensions:', video.videoWidth, 'x', video.videoHeight);
                    console.log('Video readyState:', video.readyState);
                    console.log('Video paused:', video.paused);

                    video.play().catch(e => console.error('Error playing video:', e));
                }, 100);
            })
            .catch(function(error) {
                console.error('Error accessing camera:', error);
                alert('Tidak dapat mengakses kamera: ' + error.message);
            });
        } else {
            console.error('getUserMedia not supported');
            alert('Browser Anda tidak mendukung akses kamera');
        }
    }

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    }

    function captureImageInternal() {
        console.log('captureImageInternal function called');
        console.log('Video object:', video);
        console.log('Video playing:', !video?.paused);

        if (!video) {
            console.error('No video element found');
            return;
        }

        console.log('Creating canvas and capturing image...');
        canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        console.log('Canvas dimensions:', canvas.width, 'x', canvas.height);

        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        const imageData = canvas.toDataURL('image/jpeg', 0.9);
        const base64Data = imageData.split(',')[1];

        console.log('Image captured, base64 length:', base64Data.length);

        stopCamera();
        console.log('Calling Livewire captureImage method...');
        $wire.call('captureImage', base64Data);
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, setting up listeners');

        // Handle capture button click using event delegation
        document.addEventListener('click', function(e) {
            console.log('Click detected on:', e.target);
            console.log('Target ID:', e.target.id);
            console.log('Target closest:', e.target.closest('#capture-btn'));

            if (e.target && (e.target.id === 'capture-btn' || e.target.closest('#capture-btn'))) {
                e.preventDefault();
                console.log('Capture button clicked - calling captureImageInternal()');
                captureImageInternal();
            }
        });
    });

    // Listen for camera-started event from Livewire
    $wire.on('camera-started', () => {
        console.log('Camera-started event received');
        setTimeout(() => {
            console.log('Starting camera...');
            startCamera();
        }, 200);
    });

    // Also try to start camera when page refreshes and conditions are met
    document.addEventListener('livewire:init', () => {
        console.log('Livewire initialized');
        // Check if camera should be shown on initial load
        if (document.getElementById('camera-preview')) {
            setTimeout(startCamera, 300);
        }
    });


    // Clean up when component is destroyed
    document.addEventListener('livewire:navigating', () => {
        stopCamera();
    });
</script>
@endscript
