<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PulseChat - Live Voice & Text Messaging</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        whatsapp: {
                            bg: '#0b141a',
                            panel: '#111b21',
                            bubbleOut: '#005c4b',
                            bubbleIn: '#202c33',
                            accent: '#00a884'
                        }
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Custom scrollbar for chat area */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Pulse animation for live recording button */
        @keyframes recordPulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1.08); box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .animate-record-pulse {
            animation: recordPulse 1.5s infinite;
        }

        /* Live Audio Waveform Animation Bars */
        .wave-bar {
            display: inline-block;
            width: 3px;
            height: 100%;
            background-color: #ef4444;
            border-radius: 9999px;
            animation: waveBounce 1.2s ease-in-out infinite alternate;
        }
        .wave-bar:nth-child(1) { animation-delay: 0.1s; }
        .wave-bar:nth-child(2) { animation-delay: 0.3s; }
        .wave-bar:nth-child(3) { animation-delay: 0.2s; }
        .wave-bar:nth-child(4) { animation-delay: 0.4s; }
        .wave-bar:nth-child(5) { animation-delay: 0.15s; }
        .wave-bar:nth-child(6) { animation-delay: 0.35s; }
        .wave-bar:nth-child(7) { animation-delay: 0.25s; }
        .wave-bar:nth-child(8) { animation-delay: 0.45s; }

        @keyframes waveBounce {
            0% { height: 20%; }
            100% { height: 100%; }
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col justify-center items-center p-2 md:p-6 overflow-hidden">

    <!-- Main Container -->
    <div class="w-full max-w-4xl h-[92vh] bg-whatsapp-panel border border-slate-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden relative backdrop-blur-md">
        
        <!-- Chat Header -->
        <header class="bg-slate-900 border-b border-slate-800 p-4 flex items-center justify-between shrink-0 z-10 shadow-md">
            <div class="flex items-center space-x-3.5">
                <div class="relative">
                    <img id="receiverAvatar" src="https://placehold.co/100x100/00a884/ffffff?text=User" alt="Receiver Avatar" class="w-11 h-11 rounded-full object-cover border-2 border-whatsapp-accent shadow">
                    <span class="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-slate-900 rounded-full"></span>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <label for="receiverIdSelect" class="text-xs text-slate-400 font-medium">To ID:</label>
                        <select id="receiverIdSelect" class="bg-slate-800 text-slate-200 text-sm font-semibold rounded-lg px-2.5 py-1 border border-slate-700 focus:outline-none focus:border-whatsapp-accent transition">
                            <option value="2" selected>User #2 (Demo Target)</option>
                            <option value="3">User #3</option>
                            <option value="4">User #4</option>
                            <option value="5">User #5</option>
                        </select>
                    </div>
                    <p class="text-xs text-emerald-400 mt-0.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full inline-block"></span> Online & ready
                    </p>
                </div>
            </div>

            <!-- Action Tools -->
            <div class="flex items-center space-x-2">
                <button title="Auth Bearer Token Setup" id="tokenConfigBtn" onclick="toggleTokenModal()" class="p-2.5 rounded-full hover:bg-slate-800 text-slate-400 hover:text-whatsapp-accent transition">
                    <i class="fa-solid fa-key text-lg"></i>
                </button>
                <button title="Clear Chat Window" onclick="clearLocalFeed()" class="p-2.5 rounded-full hover:bg-slate-800 text-slate-400 hover:text-red-400 transition">
                    <i class="fa-solid fa-trash-can text-lg"></i>
                </button>
            </div>
        </header>

        <!-- Message Feed Area -->
        <main id="chatFeed" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 bg-whatsapp-bg/90 relative bg-[radial-gradient(#1f2937_1px,transparent_1px)] [background-size:16px_16px]">
            
            <!-- Date Divider -->
            <div class="flex justify-center my-2">
                <span class="bg-slate-800/80 text-slate-400 text-xs px-3 py-1 rounded-full border border-slate-700/50 backdrop-blur-sm shadow-sm">
                    Today
                </span>
            </div>

            <!-- Welcome Placeholder Bubble -->
            <div class="flex items-start space-x-2 max-w-lg">
                <div class="bg-whatsapp-bubbleIn text-slate-200 p-3.5 rounded-2xl rounded-tl-xs shadow-md border border-slate-700/40">
                    <p class="text-sm">👋 Welcome! You can type a message, upload images/files, or hold the mic button to record and stream live audio notes directly to your Laravel API.</p>
                    <span class="text-[10px] text-slate-400 block text-right mt-1">System</span>
                </div>
            </div>

        </main>

        <!-- Preview Bar for Attachments (Image & Extra Audio) -->
        <div id="attachmentPreviewTray" class="hidden bg-slate-900/95 border-t border-slate-800 p-2.5 px-4 flex items-center justify-between z-10 transition-all">
            <div class="flex items-center space-x-3 overflow-x-auto py-1">
                <div id="imagePreviewContainer" class="hidden relative group">
                    <img id="imagePreview" src="" alt="Preview" class="h-14 w-14 object-cover rounded-lg border border-slate-700">
                    <button onclick="removeSelectedFile('image')" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center shadow hover:bg-red-600 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="audioPreviewContainer" class="hidden relative flex items-center gap-2 bg-slate-800 p-2 rounded-lg border border-slate-700 text-xs">
                    <i class="fa-solid fa-music text-whatsapp-accent text-lg"></i>
                    <span id="audioFileName" class="max-w-[150px] truncate text-slate-300">audio.mp3</span>
                    <button onclick="removeSelectedFile('audio')" class="text-red-400 hover:text-red-300 ml-2">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <span class="text-xs text-slate-400 italic">Attached & ready to send</span>
        </div>

        <!-- Chat Controls / Recording Panel Container -->
        <footer class="bg-slate-900 border-t border-slate-800 p-3.5 relative z-20">
            <form id="chatForm" onsubmit="handleFormSubmit(event)" class="flex items-center space-x-2">
                
                <!-- Hidden Real Input Elements -->
                <input type="file" id="imageInput" accept="image/*" class="hidden" onchange="handleFileSelect(event, 'image')">
                <input type="file" id="audioInput" accept="audio/*" class="hidden" onchange="handleFileSelect(event, 'audio')">

                <!-- Regular Input View -->
                <div id="standardInputControls" class="flex-1 flex items-center space-x-2">
                    
                    <!-- Attachment Trigger Dropdown / Buttons -->
                    <div class="flex items-center space-x-1">
                        <button type="button" onclick="document.getElementById('imageInput').click()" title="Attach Image" class="p-2.5 text-slate-400 hover:text-whatsapp-accent hover:bg-slate-800 rounded-full transition">
                            <i class="fa-solid fa-image text-lg"></i>
                        </button>
                        <button type="button" onclick="document.getElementById('audioInput').click()" title="Attach Audio File" class="p-2.5 text-slate-400 hover:text-whatsapp-accent hover:bg-slate-800 rounded-full transition">
                            <i class="fa-solid fa-paperclip text-lg"></i>
                        </button>
                    </div>

                    <!-- Text Input -->
                    <div class="flex-1 relative">
                        <input type="text" id="messageBody" placeholder="Type a message..." 
                            class="w-full bg-slate-800 text-slate-100 placeholder-slate-400 text-sm rounded-full py-2.5 pl-4 pr-10 border border-slate-700/80 focus:outline-none focus:border-whatsapp-accent focus:ring-1 focus:ring-whatsapp-accent transition">
                    </div>

                    <!-- Microphone Button (Live Voice Recording Trigger) -->
                    <button type="button" id="startRecordBtn" onclick="startVoiceRecording()" title="Record Voice Note" 
                        class="p-3 bg-slate-800 text-emerald-400 hover:bg-whatsapp-accent hover:text-white rounded-full transition-all duration-200 active:scale-95 shadow">
                        <i class="fa-solid fa-microphone text-lg"></i>
                    </button>

                    <!-- Send Text/Attachment Button -->
                    <button type="submit" id="sendBtn" title="Send Message" 
                        class="p-3 bg-whatsapp-accent text-slate-950 font-semibold rounded-full hover:bg-emerald-500 transition-all duration-200 active:scale-95 shadow flex items-center justify-center">
                        <i class="fa-solid fa-paper-plane text-base"></i>
                    </button>
                </div>

                <!-- Live Recording In-Progress Overlay Control Bar -->
                <div id="recordingControls" class="hidden flex-1 flex items-center justify-between bg-slate-950/90 rounded-full px-4 py-1.5 border border-red-500/30 shadow-inner animate-pulse">
                    
                    <!-- Left: Pulse Dot & Timer -->
                    <div class="flex items-center space-x-3">
                        <div class="w-3.5 h-3.5 rounded-full bg-red-500 animate-record-pulse"></div>
                        <span id="recordingTimer" class="font-mono text-sm font-semibold text-red-400">00:00</span>
                    </div>

                    <!-- Center: Audio Waveform Visualizer simulation -->
                    <div class="flex items-center space-x-1.5 h-6 px-4">
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                        <div class="wave-bar"></div>
                    </div>

                    <!-- Right: Controls (Cancel & Send) -->
                    <div class="flex items-center space-x-2">
                        <!-- Delete / Cancel Recording -->
                        <button type="button" onclick="cancelVoiceRecording()" title="Cancel Recording" class="p-2 text-slate-400 hover:text-red-400 rounded-full hover:bg-slate-800 transition">
                            <i class="fa-solid fa-trash-can text-base"></i>
                        </button>
                        
                        <!-- Stop & Send Voice Note -->
                        <button type="button" onclick="stopAndSendVoiceNote()" title="Send Voice Note" class="p-2.5 bg-red-500 hover:bg-red-600 text-white rounded-full transition shadow flex items-center justify-center">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                        </button>
                    </div>
                </div>

            </form>
        </footer>
    </div>

    <!-- Token Configuration Modal (Optional Sanctum Token setup) -->
    <div id="tokenModal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-100 mb-2 flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-whatsapp-accent"></i> Authentication Token
            </h3>
            <p class="text-xs text-slate-400 mb-4">If your Laravel API requires Sanctum Bearer Auth, paste your API token below. Leave empty if using web session authentication.</p>
            
            <input type="text" id="authTokenInput" placeholder="1|laravel_sanctum_token..." class="w-full bg-slate-800 text-slate-200 text-xs rounded-xl p-3 border border-slate-700 focus:outline-none focus:border-whatsapp-accent mb-4 font-mono">
            
            <div class="flex justify-end space-x-2">
                <button onclick="toggleTokenModal()" class="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-slate-200">Cancel</button>
                <button onclick="saveAuthToken()" class="px-4 py-2 bg-whatsapp-accent hover:bg-emerald-500 text-slate-950 text-xs font-bold rounded-xl shadow transition">Save Token</button>
            </div>
        </div>
    </div>

    <!-- JavaScript Application Logic -->
    <script>
        // Global Application State Variables
        let mediaRecorder = null;
        let audioChunks = [];
        let recordTimerInterval = null;
        let recordStartTime = null;
        let recordedAudioBlob = null;
        
        // Attachments State
        let selectedFiles = {
            image: null,
            audio: null
        };

        // CSRF Token Setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let bearerToken = localStorage.getItem('pulse_bearer_token') || '';

        document.addEventListener('DOMContentLoaded', () => {
            if (bearerToken) {
                document.getElementById('authTokenInput').value = bearerToken;
            }
        });

        // Toggle Auth Modal
        function toggleTokenModal() {
            const modal = document.getElementById('tokenModal');
            modal.classList.toggle('hidden');
        }

        function saveAuthToken() {
            bearerToken = document.getElementById('authTokenInput').value.trim();
            localStorage.setItem('pulse_bearer_token', bearerToken);
            toggleTokenModal();
            showToast('Authentication token saved');
        }

        // ==========================================
        // VOICE RECORDING CONTROLLER (MediaRecorder)
        // ==========================================

        async function startVoiceRecording() {
            try {
                // Request microphone permissions
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                
                audioChunks = [];
                // Support Chrome/Firefox audio mime types fallback
                let options = { mimeType: 'audio/webm' };
                if (!MediaRecorder.isTypeSupported('audio/webm')) {
                    options = { mimeType: 'audio/ogg' };
                }

                mediaRecorder = new MediaRecorder(stream, options);

                mediaRecorder.ondataavailable = event => {
                    if (event.data.size > 0) {
                        audioChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    // Combine chunks into single audio Blob
                    recordedAudioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                };

                mediaRecorder.start();
                
                // Toggle UI View to Recording Mode
                document.getElementById('standardInputControls').classList.add('hidden');
                document.getElementById('recordingControls').classList.remove('hidden');
                
                startTimer();

            } catch (err) {
                console.error("Microphone Access Error:", err);
                alert("Microphone permission denied or unsupported device.");
            }
        }

        function startTimer() {
            recordStartTime = Date.now();
            const timerElement = document.getElementById('recordingTimer');
            timerElement.innerText = "00:00";

            recordTimerInterval = setInterval(() => {
                const elapsedSeconds = Math.floor((Date.now() - recordStartTime) / 1000);
                const minutes = String(Math.floor(elapsedSeconds / 60)).padStart(2, '0');
                const seconds = String(elapsedSeconds % 60).padStart(2, '0');
                timerElement.innerText = `${minutes}:${seconds}`;
            }, 1000);
        }

        function resetTimer() {
            if (recordTimerInterval) {
                clearInterval(recordTimerInterval);
                recordTimerInterval = null;
            }
            document.getElementById('recordingTimer').innerText = "00:00";
        }

        function cancelVoiceRecording() {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                // Stop all tracks to release mic hardware
                mediaRecorder.stream.getTracks().forEach(track => track.stop());
            }
            audioChunks = [];
            recordedAudioBlob = null;
            resetTimer();
            restoreInputBarUI();
        }

        async function stopAndSendVoiceNote() {
            if (!mediaRecorder || mediaRecorder.state === 'inactive') return;

            mediaRecorder.onstop = async () => {
                recordedAudioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                // Stop mic tracks
                mediaRecorder.stream.getTracks().forEach(track => track.stop());
                resetTimer();
                restoreInputBarUI();
                
                // Trigger form submission specifically with voice note payload
                await submitMessageData({ isVoiceNote: true });
            };

            mediaRecorder.stop();
        }

        function restoreInputBarUI() {
            document.getElementById('recordingControls').classList.add('hidden');
            document.getElementById('standardInputControls').classList.remove('hidden');
        }

        // ==========================================
        // FILE ATTACHMENTS HANDLING
        // ==========================================

        function handleFileSelect(event, type) {
            const file = event.target.files[0];
            if (!file) return;

            selectedFiles[type] = file;
            const tray = document.getElementById('attachmentPreviewTray');
            tray.classList.remove('hidden');

            if (type === 'image') {
                const previewImg = document.getElementById('imagePreview');
                previewImg.src = URL.createObjectURL(file);
                document.getElementById('imagePreviewContainer').classList.remove('hidden');
            } else if (type === 'audio') {
                document.getElementById('audioFileName').innerText = file.name;
                document.getElementById('audioPreviewContainer').classList.remove('hidden');
            }
        }

        function removeSelectedFile(type) {
            selectedFiles[type] = null;
            if (type === 'image') {
                document.getElementById('imageInput').value = '';
                document.getElementById('imagePreviewContainer').classList.add('hidden');
            } else if (type === 'audio') {
                document.getElementById('audioInput').value = '';
                document.getElementById('audioPreviewContainer').classList.add('hidden');
            }

            if (!selectedFiles.image && !selectedFiles.audio) {
                document.getElementById('attachmentPreviewTray').classList.add('hidden');
            }
        }

        // ==========================================
        // SUBMIT FORM & API DISPATCH
        // ==========================================

        async function handleFormSubmit(event) {
            event.preventDefault();
            await submitMessageData({ isVoiceNote: false });
        }

        async function submitMessageData({ isVoiceNote }) {
            const receiverId = document.getElementById('receiverIdSelect').value;
            const bodyText = document.getElementById('messageBody').value.trim();

            if (!isVoiceNote && !bodyText && !selectedFiles.image && !selectedFiles.audio) {
                return; // Nothing to send
            }

            // Build FormData payload according to Laravel Controller specifications
            const formData = new FormData();
            formData.append('receiver_id', receiverId);

            if (bodyText) {
                formData.append('body', bodyText);
            }

            // Voice Note payload key: 'audio_record'
            if (isVoiceNote && recordedAudioBlob) {
                // Rename file with appropriate extension expected by backend validator
                formData.append('audio_record', recordedAudioBlob, 'audio_record.wav');
            }

            // Image attachment payload key: 'image'
            if (selectedFiles.image) {
                formData.append('image', selectedFiles.image);
            }

            // Audio attachment payload key: 'audio'
            if (selectedFiles.audio) {
                formData.append('audio', selectedFiles.audio);
            }

            // Optimistic UI Rendering
            const tempMessageObj = {
                id: Date.now(),
                body: bodyText,
                is_outgoing: true,
                created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                audio_url: isVoiceNote && recordedAudioBlob ? URL.createObjectURL(recordedAudioBlob) : null,
                image_url: selectedFiles.image ? URL.createObjectURL(selectedFiles.image) : null,
                attachment_audio_url: selectedFiles.audio ? URL.createObjectURL(selectedFiles.audio) : null,
                status: 'sending'
            };

            appendMessageToFeed(tempMessageObj);

            // Clear inputs early
            document.getElementById('messageBody').value = '';
            removeSelectedFile('image');
            removeSelectedFile('audio');

            try {
                // Construct Headers
                const headers = {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                };

                if (bearerToken) {
                    headers['Authorization'] = `Bearer ${bearerToken}`;
                }

                // API Endpoint: Route::post('/messages')
                const response = await fetch('/messages', {
                    method: 'POST',
                    headers: headers,
                    body: formData
                });

                const json = await response.json();

                if (response.ok) {
                    markMessageSent(tempMessageObj.id, json.data);
                } else {
                    console.error("Server Error:", json);
                    markMessageFailed(tempMessageObj.id, json.message || "Failed to send");
                }

            } catch (err) {
                console.error("Network Error:", err);
                markMessageFailed(tempMessageObj.id, "Connection Error");
            } finally {
                recordedAudioBlob = null;
            }
        }

        // ==========================================
        // UI FEED & BUBBLE DOM MANIPULATION
        // ==========================================

        function appendMessageToFeed(msg) {
            const feed = document.getElementById('chatFeed');
            const wrapper = document.createElement('div');
            wrapper.id = `msg-wrapper-${msg.id}`;
            wrapper.className = `flex items-end space-x-2 ${msg.is_outgoing ? 'justify-end' : 'justify-start'}`;

            let contentHtml = '';

            // Render Attached Image Preview
            if (msg.image_url) {
                contentHtml += `<img src="${msg.image_url}" class="max-w-xs rounded-lg mb-2 border border-slate-700 max-h-52 object-cover" />`;
            }

            // Render Body Text
            if (msg.body) {
                contentHtml += `<p class="text-sm text-slate-100 break-words leading-relaxed">${escapeHtml(msg.body)}</p>`;
            }

            // Render Recorded Voice Note / Audio Player
            if (msg.audio_url || msg.attachment_audio_url) {
                const targetAudio = msg.audio_url || msg.attachment_audio_url;
                contentHtml += `
                    <div class="mt-2 bg-slate-900/60 p-2 rounded-xl border border-slate-700/50 flex flex-col gap-1 min-w-[220px]">
                        <div class="flex items-center gap-2 text-xs font-medium text-emerald-400">
                            <i class="fa-solid fa-microphone text-sm"></i> Voice Note
                        </div>
                        <audio controls class="w-full h-8 mt-1 rounded focus:outline-none">
                            <source src="${targetAudio}" type="audio/wav">
                            <source src="${targetAudio}" type="audio/mp3">
                            Your browser does not support audio element.
                        </audio>
                    </div>
                `;
            }

            const bubbleBg = msg.is_outgoing ? 'bg-whatsapp-bubbleOut' : 'bg-whatsapp-bubbleIn';

            wrapper.innerHTML = `
                <div class="${bubbleBg} text-slate-100 p-3 rounded-2xl ${msg.is_outgoing ? 'rounded-br-xs' : 'rounded-bl-xs'} max-w-sm md:max-w-md shadow-md border border-slate-700/30">
                    ${contentHtml}
                    <div class="flex items-center justify-end space-x-1 mt-1 text-[10px] text-slate-300">
                        <span>${msg.created_at}</span>
                        <span id="status-icon-${msg.id}">
                            <i class="fa-solid fa-clock text-slate-400 animate-spin"></i>
                        </span>
                    </div>
                </div>
            `;

            feed.appendChild(wrapper);
            feed.scrollTop = feed.scrollHeight;
        }

        function markMessageSent(elementId, serverData) {
            const iconSpan = document.getElementById(`status-icon-${elementId}`);
            if (iconSpan) {
                iconSpan.innerHTML = `<i class="fa-solid fa-check-double text-emerald-300"></i>`;
            }
        }

        function markMessageFailed(elementId, errorMsg) {
            const iconSpan = document.getElementById(`status-icon-${elementId}`);
            if (iconSpan) {
                iconSpan.innerHTML = `<i class="fa-solid fa-circle-exclamation text-red-400" title="${errorMsg}"></i>`;
            }
        }

        function clearLocalFeed() {
            const feed = document.getElementById('chatFeed');
            feed.innerHTML = `
                <div class="flex justify-center my-2">
                    <span class="bg-slate-800/80 text-slate-400 text-xs px-3 py-1 rounded-full border border-slate-700/50">
                        Chat Cleared
                    </span>
                </div>
            `;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.innerText = text;
            return div.innerHTML;
        }

        function showToast(msg) {
            console.log("Notification:", msg);
        }
    </script>
</body>
</html>