<x-app-layout>
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot> --}}

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @php
                        $today = now()->toDateString();
                        $todayAttendance = $attendances->where('date', $today)->first();
                    @endphp

                    @if(!$todayAttendance || !$todayAttendance->check_in_time)
                        <button id="checkInBtn"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Check In
                        </button>
                    @elseif(!$todayAttendance->check_out_time)
                        <button id="checkOutBtn" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Check Out
                        </button>
                    @else
                        <p>You have already checked out for today.</p>
                    @endif

                    <!-- Camera Modal -->
                    <div id="cameraModal"
                        class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                            <div class="mt-3 text-center">
                                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Take Selfie</h3>
                                <div class="mt-2 px-7 py-3">
                                    <video id="video" width="100%" autoplay></video>
                                    <canvas id="canvas" width="320" height="240" class="hidden"></canvas>
                                    <br>
                                    <button id="captureBtn"
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Capture
                                    </button>
                                    <button id="closeModal"
                                        class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded ml-2">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h3 class="mt-6 text-lg font-medium">Attendance History</h3>
                    <table class="min-w-full mt-4">
                        <thead>
                            <tr>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Check In</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Check Out</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Check In Selfie</th>
                                <th
                                    class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Check Out Selfie</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($attendances as $attendance)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $attendance->date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($attendance->check_in_selfie)
                                            <img src="{{ asset('storage/' . $attendance->check_in_selfie) }}"
                                                alt="Check In Selfie" class="w-16 h-16 object-cover">
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($attendance->check_out_selfie)
                                            <img src="{{ asset('storage/' . $attendance->check_out_selfie) }}"
                                                alt="Check Out Selfie" class="w-16 h-16 object-cover">
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let video = document.getElementById('video');
        let canvas = document.getElementById('canvas');
        let modal = document.getElementById('cameraModal');
        let checkInBtn = document.getElementById('checkInBtn');
        let checkOutBtn = document.getElementById('checkOutBtn');
        let captureBtn = document.getElementById('captureBtn');
        let closeModal = document.getElementById('closeModal');
        let action = '';

        if (checkInBtn) {
            checkInBtn.addEventListener('click', () => {
                action = 'check-in';
                openCamera();
            });
        }

        if (checkOutBtn) {
            checkOutBtn.addEventListener('click', () => {
                action = 'check-out';
                openCamera();
            });
        }

        closeModal.addEventListener('click', () => {
            modal.classList.add('hidden');
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });

        captureBtn.addEventListener('click', () => {
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(blob => {
                let formData = new FormData();
                formData.append('selfie', blob, 'selfie.jpg');
                formData.append('_token', '{{ csrf_token() }}');

                fetch('/' + action, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
            modal.classList.add('hidden');
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });

        function openCamera() {
            modal.classList.remove('hidden');
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.srcObject = stream;
                    window.stream = stream;
                })
                .catch(err => {
                    console.error('Error accessing camera:', err);
                });
        }
    </script>
</x-app-layout>
