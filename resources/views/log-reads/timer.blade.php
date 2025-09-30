<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reading Timer</title>
    <script src="{{url('lib/tailwind.js')}}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
        <h1 class="text-2xl font-bold mb-6 text-center">Reading Timer</h1>

        <div class="space-y-4">
            <button id="startBtn" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded transition duration-200">
                Start Reading
            </button>

            <button id="endBtn" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-4 rounded transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                End Reading
            </button>
        </div>

        <div id="result" class="mt-6 p-4 rounded hidden">
            <!-- Results will be displayed here -->
        </div>

        <div id="error" class="mt-6 p-4 bg-red-100 text-red-700 rounded hidden">
            <!-- Errors will be displayed here -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startBtn = document.getElementById('startBtn');
            const endBtn = document.getElementById('endBtn');
            const resultDiv = document.getElementById('result');
            const errorDiv = document.getElementById('error');
            let startTime = null;
            let timerInterval = null;

            // Hide result and error divs initially
            resultDiv.classList.add('hidden');
            errorDiv.classList.add('hidden');

            startBtn.addEventListener('click', function() {
                // Clear any existing timer
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
                fetch('/log-read/start', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        startTime = new Date();
                        resultDiv.innerHTML = `
                            <div class="text-green-600">
                                <p class="font-semibold">Reading session started!</p>
                                <p class="text-sm mt-1">Started at: ${data.timestamp}</p>
                                 <p class="mt-2"><strong>Time since completion:</strong> <span id="elapsedTime">0</span> seconds</p>
                            </div>
                        `;
                        resultDiv.classList.remove('hidden');
                        errorDiv.classList.add('hidden');
                        startBtn.disabled = true;
                        endBtn.disabled = false;

                        // Start timer to show elapsed time
                        timerInterval = setInterval(() => {
                            const now = new Date();
                            const elapsed = Math.floor((now - startTime) / 1000);
                            document.getElementById('elapsedTime').textContent = elapsed;
                        }, 1000);
                    } else {
                        errorDiv.innerHTML = `<p>Error: ${data.error}</p>`;
                        errorDiv.classList.remove('hidden');
                        resultDiv.classList.add('hidden');
                    }
                })
                .catch(error => {
                    errorDiv.innerHTML = `<p>Network error. Please try again.</p>`;
                    errorDiv.classList.remove('hidden');
                    resultDiv.classList.add('hidden');
                });
            });

            endBtn.addEventListener('click', function() {
                fetch('/log-read/end', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="text-green-600">
                                <p class="font-semibold">Reading session completed!</p>
                                <p class="mt-2"><strong>Start:</strong> ${data.start.start}</p>
                                <p><strong>End:</strong> ${data.end.end}</p>
                                <p class="mt-2"><strong>Total reading time:</strong> ${data.start.time} minutes</p>

                            </div>
                        `;
                        resultDiv.classList.remove('hidden');
                        errorDiv.classList.add('hidden');
                        startBtn.disabled = false;
                        endBtn.disabled = true;
                    } else {
                        errorDiv.innerHTML = `<p>Error: ${data.error}</p>`;
                        errorDiv.classList.remove('hidden');
                        resultDiv.classList.add('hidden');
                    }
                })
                .catch(error => {
                    errorDiv.innerHTML = `<p>Network error. Please try again.</p>`;
                    errorDiv.classList.remove('hidden');
                    resultDiv.classList.add('hidden');
                });
            });
        });
    </script>
</body>
</html>
