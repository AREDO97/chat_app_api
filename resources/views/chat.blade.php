<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PulseChat - Live Voice & API Messaging</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        whatsapp: {
                            bg: '#0b141a',
                            panel: '#111b21',
                            bubbleOut: '#005c4b',
                            bubbleIn: '#202c33',
                            accent: '#00a884',
                            accentHover: '#029071',
                            inputBg: '#2a3942'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome Icons & Inter Font -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.25); }

        @keyframes recordPulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1.08); box-shadow: 0 0 0 12px rgba(239, 68, 68, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .animate-record-pulse { animation: recordPulse 1.5s infinite; }

        .wave-bar {
            display: inline-block;
            width: 3px;
            height: 100%;
            background-color: #ef4444;
            border-radius: 9999px;
            animation: waveBounce 1.2s ease-in-out infinite alternate;
        }
        .wave-bar:nth-child(1) { animation-delay: 0.10s; }
        .wave-bar:nth-child(2) { animation-delay: 0.30s; }
        .wave-bar:nth-child(3) { animation-delay: 0.20s; }
        .wave-bar:nth-child(4) { animation-delay: 0.40s; }
        .wave-bar:nth-child(5) { animation-delay: 0.15s; }
        .wave-bar:nth-child(6) { animation-delay: 0.35s; }
        .wave-bar:nth-child(7) { animation-delay: 0.25s; }
        .wave-bar:nth-child(8) { animation-delay: 0.45s; }

        @keyframes waveBounce {
            0% { height: 15%; }
            100% { height: 100%; }
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 flex flex-col justify-center items-center p-2 sm:p-4 md:p-6 overflow-hidden">

    <div class="w-full max-w-4xl h-[94vh] bg-whatsapp-panel border border-slate-800 rounded-2xl shadow-2xl flex flex-col overflow-hidden relative">
        
        <!-- Chat Header -->
        <header class="bg-slate-900 border-b border-slate-800 p-3.5 sm:p-4 flex items-center justify-between shrink-0 z-10">
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <img id="receiverAvatar" src="https://placehold.co/100x100/00a884/ffffff?text=User" alt="Receiver Avatar" class="w-10 h-10 sm:w-11 sm:h-11 rounded-full object-cover border-2 border-whatsapp-accent shadow">
                    <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-slate-900 rounded-full"></span>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <label for="receiver_id" class="text-xs text-slate-400 font-medium hidden sm:inline">Receiver ID:</label>
                        <select id="receiver_id" class="bg-slate-800 text-slate-200 text-xs sm:text-sm font-semibold rounded-lg px-2.5 py-1 border border-slate-700 focus:outline-none focus:border-whatsapp-accent transition">
                            <option value="2" selected>User #2</option>
                            <option value="3">User #3</option>
                            <option value="4">User #4</option>
                            <option value="5">User #5</option>
                        </select>
                    </div>
                    <p class="text-[11px] text-emerald-400 mt-0.5 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full inline-block"></span> Posting to <code class="font-mono bg-slate-800 px-1 rounded text-[10px]">/api/messages</code>
                    </p>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center space-x-2">
                <button title="Sanctum Bearer Token Setup" onclick="toggleTokenModal()" class="p-2 sm:p-2.5 rounded-full hover:bg-slate-800 text-slate-400 hover:text-whatsapp-accent transition">
                    <i class="fa-solid fa-key text-base sm:text-lg"></i>
                </button>
                <button title="Clear Chat Feed" onclick="clearChatFeed()" class="p-2 sm:p-2.5 rounded-full hover:bg-slate-800 text-slate-400 hover:text-red-400 transition">
                    <i class="fa-solid fa-trash-can text-base sm:text-lg"></i>
                </button>
            </div>
        </header>

        <main id="chatFeed" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 bg-whatsapp-bg relative bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:16px_16px]">
            
            <div class="flex justify-center my-2">
                <span class="bg-slate-800/90 text-slate-400 text-[11px] px-3 py-1 rounded-full border border-slate-700/50 backdrop-blur-sm shadow-sm">
                    Live Session
                </span>
            </div>

            <!-- Welcome Bubble -->
            <div class="flex items-start space-x-2 max-w-md">
                <div class="bg-whatsapp-bubbleIn text-slate-200 p-3.5 rounded-2xl rounded-tl-none shadow-md border border-slate-700/40 text-xs sm:text-sm">
                    <p>💬 Hold or click the microphone to record live audio notes (<code class="text-emerald-400 font-mono">audio_record</code>), attach files (<code class="text-emerald-400 font-mono">image</code>, <code class="text-emerald-400 font-mono">audio</code>, <code class="text-emerald-400 font-mono">video</code>, <code class="text-emerald-400 font-mono">document</code>), or type text messages (<code class="text-emerald-400 font-mono">body</code>).</p>
                    <span class="text-[10px] text-slate-400 block text-right mt-1">System</span>
                </div>
            </div>

        </main>

        <div id="attachmentTray" class="hidden bg-slate-900 border-t border-slate-800 p-2.5 px-4 flex items-center justify-between z-10 transition-all">
            <div class="flex items-center space-x-3 overflow-x-auto py-1">
                <!-- Image Preview -->
                <div id="imagePreviewBox" class="hidden relative">
                    <img id="imagePreview" src="" alt="Selected Preview" class="h-14 w-14 object-cover rounded-lg border border-slate-700 shadow">
                    <button onclick="removeFile('image')" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center shadow hover:bg-red-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <!-- Audio File Preview -->
                <div id="audioPreviewBox" class="hidden relative flex items-center gap-2 bg-slate-800 p-2 rounded-lg border border-slate-700 text-xs">
                    <i class="fa-solid fa-music text-whatsapp-accent text-base"></i>
                    <span id="audioFileName" class="max-w-[150px] truncate text-slate-300">file.mp3</span>
                    <button onclick="removeFile('audio')" class="text-red-400 hover:text-red-300 ml-2">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <!-- Generic File Preview (Video/Document) -->
                <div id="filePreviewBox" class="hidden relative flex items-center gap-2 bg-slate-800 p-2 rounded-lg border border-slate-700 text-xs">
                    <i class="fa-solid fa-file-arrow-up text-whatsapp-accent text-base"></i>
                    <span id="genericFileName" class="max-w-[150px] truncate text-slate-300">document.pdf</span>
                    <button onclick="removeFile('file')" class="text-red-400 hover:text-red-300 ml-2">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <span class="text-xs text-slate-400 italic">Ready to send</span>
        </div>

        <footer class="bg-slate-900 border-t border-slate-800 p-3 sm:p-4 relative z-20">
            <form id="chatForm" onsubmit="handleFormSubmit(event)" class="flex items-center space-x-2">
                
                <!-- Hidden Real File Inputs -->
                <input type="file" id="imageInput" accept="image/*" class="hidden" onchange="onFileSelected(event, 'image')">
                <input type="file" id="audioInput" accept="audio/mp3,audio/wav,audio/m4a,audio/ogg" class="hidden" onchange="onFileSelected(event, 'audio')">
                <input type="file" id="genericFileInput" accept="video/*,.pdf,.doc,.docx,.txt" class="hidden" onchange="onFileSelected(event, 'file')">

                <!-- Standard Form Controls Bar -->
                <div id="standardBar" class="flex-1 flex items-center space-x-2">
                    
                    <div class="flex items-center space-x-1">
                        <button type="button" onclick="document.getElementById('imageInput').click()" title="Attach Image" class="p-2.5 text-slate-400 hover:text-whatsapp-accent hover:bg-slate-800 rounded-full transition">
                            <i class="fa-solid fa-image text-lg"></i>
                        </button>
                        <button type="button" onclick="document.getElementById('audioInput').click()" title="Attach Audio File" class="p-2.5 text-slate-400 hover:text-whatsapp-accent hover:bg-slate-800 rounded-full transition">
                            <i class="fa-solid fa-music text-lg"></i>
                        </button>
                        <button type="button" onclick="document.getElementById('genericFileInput').click()" title="Attach Video/Document" class="p-2.5 text-slate-400 hover:text-whatsapp-accent hover:bg-slate-800 rounded-full transition">
                            <i class="fa-solid fa-paperclip text-lg"></i>
                        </button>
                    </div>

                    <!-- Text Message Body Input -->
                    <div class="flex-1 relative">
                        <input type="text" id="body" placeholder="Type a message..." 
                            class="w-full bg-whatsapp-inputBg text-slate-100 placeholder-slate-400 text-sm rounded-full py-2.5 pl-4 pr-10 border border-transparent focus:outline-none focus:border-whatsapp-accent transition">
                    </div>

                    <!-- Voice Recorder Toggle Button -->
                    <button type="button" id="startMicBtn" onclick="startRecording()" title="Record Voice Note" 
                        class="p-3 bg-slate-800 text-emerald-400 hover:bg-whatsapp-accent hover:text-slate-950 rounded-full transition-all duration-200 active:scale-95 shadow">
                        <i class="fa-solid fa-microphone text-lg"></i>
                    </button>

                    <!-- Send Message Button -->
                    <button type="submit" id="sendBtn" title="Send Message" 
                        class="p-3 bg-whatsapp-accent text-slate-950 font-semibold rounded-full hover:bg-whatsapp-accentHover transition-all duration-200 active:scale-95 shadow flex items-center justify-center">
                        <i class="fa-solid fa-paper-plane text-base"></i>
                    </button>
                </div>

                <!-- Live Voice Recorder Active Bar -->
                <div id="recorderBar" class="hidden flex-1 flex items-center justify-between bg-slate-950 rounded-full px-4 py-1.5 border border-red-500/30 shadow-inner">
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-3.5 h-3.5 rounded-full bg-red-500 animate-record-pulse"></div>
                        <span id="recordTimer" class="font-mono text-sm font-semibold text-red-400">00:00</span>
                    </div>

                    <!-- Waveform Animation -->
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

                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="cancelRecording()" title="Cancel Recording" class="p-2 text-slate-400 hover:text-red-400 rounded-full hover:bg-slate-800 transition">
                            <i class="fa-solid fa-trash-can text-base"></i>
                        </button>
                        
                        <button type="button" onclick="stopAndSubmitRecording()" title="Send Voice Note" class="p-2.5 bg-red-500 hover:bg-red-600 text-white rounded-full transition shadow flex items-center justify-center">
                            <i class="fa-solid fa-circle-check text-lg"></i>
                        </button>
                    </div>
                </div>

            </form>
        </footer>
    </div>

    <div id="tokenModal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl">
            <h3 class="text-lg font-bold text-slate-100 mb-2 flex items-center gap-2">
                <i class="fa-solid fa-key text-whatsapp-accent"></i> API Bearer Token
            </h3>
            <p class="text-xs text-slate-400 mb-4">Paste your Sanctum Personal Access Token below if using Bearer auth. Leave empty if using web session authentication.</p>
            
            <input type="text" id="sanctumTokenInput" placeholder="1|laravel_sanctum_token..." class="w-full bg-slate-800 text-slate-200 text-xs rounded-xl p-3 border border-slate-700 focus:outline-none focus:border-whatsapp-accent mb-4 font-mono">
            
            <div class="flex justify-end space-x-2">
                <button onclick="toggleTokenModal()" class="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-slate-200">Cancel</button>
                <button onclick="saveSanctumToken()" class="px-4 py-2 bg-whatsapp-accent hover:bg-whatsapp-accentHover text-slate-950 text-xs font-bold rounded-xl shadow transition">Save Token</button>
            </div>
        </div>
    </div>

    <script>
        let mediaRecorder = null;
        let audioChunks = [];
        let recordTimerInterval = null;
        let recordStartTime = null;
        let activeRecordBlob = null;
        let recordMimeType = 'audio/webm';
        
        let attachments = {
            image: null,
            audio: null,
            file: null
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let bearerToken = localStorage.getItem('pulse_bearer_token') || '';

        document.addEventListener('DOMContentLoaded', () => {
            if (bearerToken) {
                document.getElementById('sanctumTokenInput').value = bearerToken;
            }
        });

        function toggleTokenModal() {
            document.getElementById('tokenModal').classList.toggle('hidden');
        }

        function saveSanctumToken() {
            bearerToken = document.getElementById('sanctumTokenInput').value.trim();
            localStorage.setItem('pulse_bearer_token', bearerToken);
            toggleTokenModal();
        }

        async function startRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                audioChunks = [];

                if (MediaRecorder.isTypeSupported('audio/webm')) {
                    recordMimeType = 'audio/webm';
                } else if (MediaRecorder.isTypeSupported('audio/mp4')) {
                    recordMimeType = 'audio/mp4';
                } else if (MediaRecorder.isTypeSupported('audio/ogg')) {
                    recordMimeType = 'audio/ogg';
                } else {
                    recordMimeType = 'audio/wav';
                }

                mediaRecorder = new MediaRecorder(stream, { mimeType: recordMimeType });

                mediaRecorder.ondataavailable = event => {
                    if (event.data.size > 0) {
                        audioChunks.push(event.data);
                    }
                };

                mediaRecorder.start(100);

                document.getElementById('standardBar').classList.add('hidden');
                document.getElementById('recorderBar').classList.remove('hidden');

                recordStartTime = Date.now();
                document.getElementById('recordTimer').innerText = "00:00";
                recordTimerInterval = setInterval(() => {
                    const elapsedSeconds = Math.floor((Date.now() - recordStartTime) / 1000);
                    const minutes = String(Math.floor(elapsedSeconds / 60)).padStart(2, '0');
                    const seconds = String(elapsedSeconds % 60).padStart(2, '0');
                    document.getElementById('recordTimer').innerText = `${minutes}:${seconds}`;
                }, 1000);

            } catch (err) {
                console.error("Microphone access error:", err);
                alert("Could not access microphone: " + err.message);
            }
        }

        function cancelRecording() {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                mediaRecorder.stream.getTracks().forEach(track => track.stop());
            }
            audioChunks = [];
            activeRecordBlob = null;
            clearTimer();
            restoreStandardBar();
        }

        async function stopAndSubmitRecording() {
            if (!mediaRecorder || mediaRecorder.state === 'inactive') return;

            mediaRecorder.onstop = async () => {
                activeRecordBlob = new Blob(audioChunks, { type: recordMimeType });
                mediaRecorder.stream.getTracks().forEach(track => track.stop());
                clearTimer();
                restoreStandardBar();
                
                await dispatchApiRequest({ isLiveVoice: true });
            };

            mediaRecorder.stop();
        }

        function clearTimer() {
            if (recordTimerInterval) {
                clearInterval(recordTimerInterval);
                recordTimerInterval = null;
            }
        }

        function restoreStandardBar() {
            document.getElementById('recorderBar').classList.add('hidden');
            document.getElementById('standardBar').classList.remove('hidden');
        }

        function onFileSelected(event, type) {
            const file = event.target.files[0];
            if (!file) return;

            attachments[type] = file;
            document.getElementById('attachmentTray').classList.remove('hidden');

            if (type === 'image') {
                document.getElementById('imagePreview').src = URL.createObjectURL(file);
                document.getElementById('imagePreviewBox').classList.remove('hidden');
            } else if (type === 'audio') {
                document.getElementById('audioFileName').innerText = file.name;
                document.getElementById('audioPreviewBox').classList.remove('hidden');
            } else if (type === 'file') {
                document.getElementById('genericFileName').innerText = file.name;
                document.getElementById('filePreviewBox').classList.remove('hidden');
            }
        }

        function removeFile(type) {
            attachments[type] = null;
            if (type === 'image') {
                document.getElementById('imageInput').value = '';
                document.getElementById('imagePreviewBox').classList.add('hidden');
            } else if (type === 'audio') {
                document.getElementById('audioInput').value = '';
                document.getElementById('audioPreviewBox').classList.add('hidden');
            } else if (type === 'file') {
                document.getElementById('genericFileInput').value = '';
                document.getElementById('filePreviewBox').classList.add('hidden');
            }

            if (!attachments.image && !attachments.audio && !attachments.file) {
                document.getElementById('attachmentTray').classList.add('hidden');
            }
        }

        async function handleFormSubmit(event) {
            event.preventDefault();
            await dispatchApiRequest({ isLiveVoice: false });
        }

        async function dispatchApiRequest({ isLiveVoice }) {
            const receiverId = document.getElementById('receiver_id').value;
            const bodyValue = document.getElementById('body').value.trim();

            if (!isLiveVoice && !bodyValue && !attachments.image && !attachments.audio && !attachments.file) {
                return;
            }

            const formData = new FormData();
            formData.append('receiver_id', receiverId);

            if (bodyValue) {
                formData.append('body', bodyValue);
            }

            // Append live voice recording
            if (isLiveVoice && activeRecordBlob) {
                const ext = recordMimeType.includes('webm') ? 'webm' : (recordMimeType.includes('mp4') ? 'mp4' : 'wav');
                formData.append('audio_record', activeRecordBlob, `recording.${ext}`);
            }

            // Append optional attachment files
            if (attachments.image) {
                formData.append('image', attachments.image);
            }
            if (attachments.audio) {
                formData.append('audio', attachments.audio);
            }
            if (attachments.file) {
                const fileType = attachments.file.type;
                if (fileType.startsWith('video/')) {
                    formData.append('video', attachments.file);
                } else {
                    formData.append('document', attachments.file);
                }
            }

            const localMsgId = Date.now();
            const tempMessage = {
                id: localMsgId,
                body: bodyValue,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                voiceUrl: isLiveVoice && activeRecordBlob ? URL.createObjectURL(activeRecordBlob) : null,
                imageUrl: attachments.image ? URL.createObjectURL(attachments.image) : null,
                audioUrl: attachments.audio ? URL.createObjectURL(attachments.audio) : null
            };

            renderBubble(tempMessage);

            // Reset Form Inputs
            document.getElementById('body').value = '';
            removeFile('image');
            removeFile('audio');
            removeFile('file');

            try {
                const headers = {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || ''
                };

                if (bearerToken) {
                    headers['Authorization'] = `Bearer ${bearerToken}`;
                }

                // POST directly to /api/messages
                const response = await fetch('/api/messages', {
                    method: 'POST',
                    headers: headers,
                    body: formData
                });

                const json = await response.json();
                console.log("POST /api/messages Response:", json);

                if (response.ok || response.status === 201) {
                    markStatus(localMsgId, 'sent');
                } else {
                    console.error("API Error Payload:", json);
                    const errorText = json.message || Object.values(json.errors || {}).flat().join(', ') || 'Validation error';
                    markStatus(localMsgId, 'failed', errorText);
                    alert("Message failed: " + errorText);
                }

            } catch (err) {
                console.error("Network Request Error:", err);
                markStatus(localMsgId, 'failed', 'Network request failed');
                alert("Network error calling /api/messages");
            } finally {
                activeRecordBlob = null;
            }
        }

        function renderBubble(msg) {
            const chatFeed = document.getElementById('chatFeed');
            const wrapper = document.createElement('div');
            wrapper.id = `msg-${msg.id}`;
            wrapper.className = "flex items-end justify-end space-x-2";

            let contentHtml = '';

            if (msg.imageUrl) {
                contentHtml += `<img src="${msg.imageUrl}" class="max-w-xs rounded-lg mb-2 border border-slate-700 max-h-52 object-cover" />`;
            }

            if (msg.body) {
                contentHtml += `<p class="text-xs sm:text-sm text-slate-100 break-words leading-relaxed">${escapeHtml(msg.body)}</p>`;
            }

            if (msg.voiceUrl || msg.audioUrl) {
                const targetUrl = msg.voiceUrl || msg.audioUrl;
                contentHtml += `
                    <div class="mt-2 bg-slate-900/70 p-2 rounded-xl border border-slate-700/50 flex flex-col gap-1 min-w-[210px]">
                        <div class="flex items-center gap-2 text-xs font-medium text-emerald-400">
                            <i class="fa-solid fa-microphone text-xs"></i> ${msg.voiceUrl ? 'Live Voice Note' : 'Audio File'}
                        </div>
                        <audio controls class="w-full h-8 mt-1 rounded focus:outline-none">
                            <source src="${targetUrl}">
                        </audio>
                    </div>
                `;
            }

            wrapper.innerHTML = `
                <div class="bg-whatsapp-bubbleOut text-slate-100 p-3 rounded-2xl rounded-br-none max-w-sm sm:max-w-md shadow border border-slate-700/30">
                    ${contentHtml}
                    <div class="flex items-center justify-end space-x-1 mt-1 text-[10px] text-slate-300">
                        <span>${msg.time}</span>
                        <span id="status-${msg.id}">
                            <i class="fa-solid fa-clock text-slate-400 animate-spin"></i>
                        </span>
                    </div>
                </div>
            `;

            chatFeed.appendChild(wrapper);
            chatFeed.scrollTop = chatFeed.scrollHeight;
        }

        function markStatus(id, state, message = '') {
            const statusContainer = document.getElementById(`status-${id}`);
            if (!statusContainer) return;

            if (state === 'sent') {
                statusContainer.innerHTML = `<i class="fa-solid fa-check-double text-emerald-300"></i>`;
            } else {
                statusContainer.innerHTML = `<i class="fa-solid fa-circle-exclamation text-red-400" title="${message}"></i>`;
            }
        }

        function clearChatFeed() {
            document.getElementById('chatFeed').innerHTML = `
                <div class="flex justify-center my-2">
                    <span class="bg-slate-800/90 text-slate-400 text-[11px] px-3 py-1 rounded-full border border-slate-700/50">
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
    </script>
</body>
</html>