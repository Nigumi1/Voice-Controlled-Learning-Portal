<?php
include('connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit();
}

if (isset($_GET['id'])) {
  $id = $_GET['id'];

  $stmt_content = $conn->prepare("SELECT * FROM content WHERE id = ?");
  $stmt_content->bind_param("i", $id);
  $stmt_content->execute();
  $result = $stmt_content->get_result();
  $row = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($row['content_title']) ?> - Learning Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/annyang/2.6.0/annyang.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="flex min-h-screen bg-gray-100">
  <!-- Sidebar Navigation -->
  <nav class="w-64 bg-white p-5 border-r border-gray-300 flex flex-col">
    <div class="flex items-center gap-3 text-lg font-semibold text-blue-500 mb-8">
      <svg width="40" height="40" viewBox="0 0 40 40">
        <circle cx="20" cy="20" r="18" fill="#2196F3"/>
        <path d="M20 10 L28 15 L28 25 L20 30 L12 25 L12 15 Z" fill="white"/>
      </svg>
      <span>Learning Portal</span>
    </div>
    <ul class="space-y-2 flex-1">
      <li class="p-3 rounded-lg cursor-pointer bg-blue-100 text-blue-500">
        <i class="fas fa-play-circle mr-2"></i>Lectures
      </li>
      <li class="p-3 rounded-lg cursor-pointer hover:bg-gray-200 text-gray-600">
        <i class="fas fa-book mr-2"></i>Courses
      </li>
      <li class="p-3 rounded-lg cursor-pointer hover:bg-gray-200 text-gray-600">
        <i class="fas fa-tasks mr-2"></i>Assignments
      </li>
      <li class="p-3 rounded-lg cursor-pointer hover:bg-gray-200 text-gray-600">
        <i class="fas fa-chart-bar mr-2"></i>Progress
      </li>
    </ul>
    <div class="mt-auto">
      <div class="flex items-center gap-3 p-3 hover:bg-gray-100 rounded-lg">
        <div class="w-10 h-10 bg-blue-500 text-white flex items-center justify-center rounded-full font-bold">
          <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
        </div>
        <span class="text-gray-600"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
      </div>
    </div>
  </nav>

  <!-- Main Content Area -->
  <main class="flex-1 p-6 flex flex-col">
    <!-- Video Section -->
    <div class="bg-white rounded-lg overflow-hidden shadow-md flex-1">
      <video id="player" class="w-full h-full" playsinline controls
        data-poster="admin/action/uploads/<?= htmlspecialchars($row['thumbnail'] ?? 'default.jpg') ?>">
        <source src="admin/action/uploads/<?= htmlspecialchars($row['video']) ?>" type="video/mp4">
      </video>
    </div>

    <!-- Content Tabs -->
    <div class="mt-6 flex flex-col flex-1">
      <div class="flex space-x-4 border-b">
        <button data-tab="notes" class="tab-btn active px-4 py-2 border-b-2 border-blue-500 text-blue-500">
          Lecture Notes
        </button>
        <button data-tab="resources" class="tab-btn px-4 py-2 text-gray-600 hover:text-blue-500">
          Resources
        </button>
        <button data-tab="tts" class="tab-btn px-4 py-2 text-gray-600 hover:text-blue-500">
          Audio Controls
        </button>
      </div>

      <!-- Tab Contents -->
      <div class="flex-1 bg-white rounded-b-lg shadow-md overflow-y-auto">
        <!-- Lecture Notes Tab -->
        <div id="notes" class="tab-content p-6">
          <article class="prose max-w-none" id="content-to-read">
            <?= nl2br(htmlspecialchars($row['contents'])) ?>
          </article>
        </div>

        <!-- Resources Tab -->
        <div id="resources" class="tab-content hidden p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
              <div class="flex items-center gap-3">
                <i class="fas fa-file-pdf text-3xl text-red-500"></i>
                <div>
                  <h3 class="font-semibold">Lecture Slides</h3>
                  <p class="text-sm text-gray-500">PDF Presentation</p>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg hover:bg-gray-100 transition-colors">
              <div class="flex items-center gap-3">
                <i class="fas fa-link text-3xl text-blue-500"></i>
                <div>
                  <h3 class="font-semibold">External Resources</h3>
                  <p class="text-sm text-gray-500">Supplementary Materials</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TTS Controls Tab -->
        <div id="tts" class="tab-content hidden p-6">
          <div class="max-w-xl space-y-6">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Voice Settings</label>
              <select id="voice-select" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="">Loading voices...</option>
              </select>
            </div>
            
            <div class="flex gap-3">
              <button onclick="readContent()" class="flex-1 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                <i class="fas fa-play mr-2"></i>Start Reading
              </button>
              <button onclick="stopReading()" class="flex-1 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                <i class="fas fa-stop mr-2"></i>Stop Reading
              </button>
            </div>
            
            <div id="browser-warning" class="hidden p-3 bg-yellow-100 text-yellow-800 rounded-lg">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              Text-to-speech works best in Google Chrome
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Voice Control Fab -->
    <button id="voice-control" class="fixed bottom-6 right-6 w-14 h-14 bg-blue-500 text-white rounded-full shadow-lg hover:bg-blue-600 transition-colors">
      <i class="fa-solid fa-microphone"></i>
    </button>
  </main>

  <!-- Scripts -->
  <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
  <script>
    // Initialize Plyr Video Player
    const player = new Plyr('#player', {
      controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
      settings: ['quality', 'speed'],
      speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] }
    });

    // Tab Functionality
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        // Update Tab Buttons
        document.querySelectorAll('.tab-btn').forEach(t => 
          t.classList.remove('active', 'border-blue-500', 'text-blue-500')
        );
        btn.classList.add('active', 'border-blue-500', 'text-blue-500');
        
        // Update Tab Contents
        const targetTab = btn.dataset.tab;
        document.querySelectorAll('.tab-content').forEach(t => 
          t.classList.toggle('hidden', t.id !== targetTab)
        );
      });
    });

    // TTS Functionality
    let synth = window.speechSynthesis;
    let voices = [];
    let currentUtterance = null;

    function populateVoices() {
      voices = synth.getVoices();
      const voiceSelect = document.getElementById('voice-select');
      voiceSelect.innerHTML = voices
        .map(voice => `<option value="${voice.name}">${voice.name} (${voice.lang})</option>`)
        .join('');
    }

    if (speechSynthesis.onvoiceschanged !== undefined) {
      speechSynthesis.onvoiceschanged = populateVoices;
    }

    function readContent() {
      if (synth.speaking) synth.cancel();
      
      const text = document.getElementById('content-to-read').textContent;
      const selectedVoice = voices.find(v => v.name === document.getElementById('voice-select').value);
      
      currentUtterance = new SpeechSynthesisUtterance(text);
      currentUtterance.voice = selectedVoice;
      synth.speak(currentUtterance);
    }

    function stopReading() {
      if (synth.speaking) synth.cancel();
    }

    // Voice Commands
    if (annyang) {
      const commands = {
        'play video': () => player.play(),
        'pause video': () => player.pause(),
        'skip (forward)': () => player.forward(30),
        'go back': () => player.rewind(30),
        'read content': readContent,
        'stop reading': stopReading
      };

      annyang.addCommands(commands);
      annyang.start({ autoRestart: true, continuous: false });
    }

    // Voice Control Feedback
    document.getElementById('voice-control').addEventListener('click', () => {
      new Audio('listening.mp3').play();
      Swal.fire({
        title: 'Listening...',
        html: 'Available commands:<br>' +
          '• "Play video"<br>' +
          '• "Pause video"<br>' +
          '• "Skip forward"<br>' +
          '• "Go back"<br>' +
          '• "Read content"<br>' +
          '• "Stop reading"',
        timer: 3000,
        position: 'top-end',
        timerProgressBar: true,
        showConfirmButton: false
      });
    });
  </script>
</body>
</html>