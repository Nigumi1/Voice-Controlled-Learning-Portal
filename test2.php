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
  <title>Introduction to Calculus - Learning Portal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/annyang/2.6.0/annyang.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<style>
  .floating-mic-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    cursor: pointer;
    transition: background-color 0.3s;
  }

  .floating-mic-btn:hover {
    background-color: #2563eb;
  }

  .floating-mic-btn.listening {
    animation: pulse 1s infinite;
  }

  @keyframes pulse {

    0%,
    100% {
      transform: scale(1);
      background-color: #3b82f6;
    }

    50% {
      transform: scale(1.1);
      background-color: #2563eb;
    }
  }
</style>

<body class="flex min-h-screen bg-gray-100">

  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <!-- Header -->
    <header class="flex justify-between items-center mb-6">
      <div class="flex items-center text-gray-600">
        <a href="test.php" class="text-blue-500">Lectures</a>
        <i class="fas fa-chevron-right mx-2"></i>
        <span><?php echo htmlspecialchars($row['content_title']); ?></span>
      </div>
    </header>

    <!-- Lecture Section -->
    <div class="bg-white rounded-lg overflow-hidden shadow-md">
      <video id="lecture-video" controls class="w-full h-64 md:h-96 rounded-lg shadow-lg">
        <source src="admin/action/uploads/<?php echo htmlspecialchars($row['video']); ?>" type="video/mp4">
        Your browser does not support the video tag.
      </video>
      <div class="p-5">
        <h1 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($row['content_title']); ?></h1>
      </div>
    </div>

    <!-- Tabs -->
    <div class="mt-6">
      <!-- Overview Tab -->
      <div class="tab-content p-5 bg-white rounded-lg mt-4">
        <h2 class="text-lg font-semibold text-gray-800">Lecture Notes</h2>
        <div id="content-to-read">
          <p class="text-gray-600 mt-2"><?php echo nl2br(htmlspecialchars($row['contents'])); ?></p>
        </div>
      </div>

      <div class="mt-6 flex items-center space-x-4" id="tts-controls">
        <label for="voice-select" class="text-gray-800 font-semibold">Select Voice:</label>
        <select id="voice-select" class="p-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
          <option value="">Loading voices...</option>
        </select>

        <button onclick="readContent()" id="readBtn"
          class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 transition duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
          Read Content
        </button>

        <button onclick="stopReading()" id="stopBtn"
          class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 transition duration-300 focus:outline-none focus:ring-2 focus:ring-red-500">
          Stop Reading
        </button>
      </div>
    </div>

    <div id="browser-warning" class="hidden mt-4 p-4 bg-yellow-100 text-yellow-800 rounded-lg">
      This text-to-speech feature is only available in Google Chrome. Please switch to Chrome to use this feature.
    </div>
    </div>

    <div id="floatingMicBtn" class="floating-mic-btn">
      <i class="fa-solid fa-microphone"></i>
    </div>
    <audio id="listeningSound" src="listening.mp3"></audio>
    <audio id="playingVideo" src="play_video.mp3"></audio>
    <audio id="pauseVideo" src="pause_video.mp3"></audio>
    <audio id="readLecture" src="read_lecture.mp3"></audio>
    <audio id="stopReadingLec" src="stop_reading.mp3"></audio>
    <audio id="changeMale" src="change_male.mp3"></audio>
    <audio id="changeFemale" src="change_female.mp3"></audio>
    <audio id="pleaseSabrina" src="please.mp3"></audio>
    <audio id="pageLoadSound" src="navigated_you1.mp3" preload="auto"></audio>
  </main>

</body>

<script>
  const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
  const ttsControls = document.getElementById('tts-controls');
  const browserWarning = document.getElementById('browser-warning');
  const voiceSelect = document.getElementById('voice-select');
  const readBtn = document.getElementById('readBtn');
  const stopBtn = document.getElementById('stopBtn');
  const floatingMicBtn = document.getElementById('floatingMicBtn');
  const video = document.getElementById('lecture-video');
  const listeningSound = document.getElementById('listeningSound');
  const playingVideo = document.getElementById('playingVideo');
  const pauseVideo = document.getElementById('pauseVideo');
  const readLecture = document.getElementById('readLecture');
  const stopReadingLec = document.getElementById('stopReadingLec');
  const changeMale = document.getElementById('changeMale');
  const changeFemale = document.getElementById('changeFemale');
  const pageLoadSound = document.getElementById('pageLoadSound');
  const pleaseSabrina = document.getElementById('pleaseSabrina');
  let voices = [];
  let maleVoice = null;
  let femaleVoice = null;
  let currentUtterance = null;
  let isListening = false;

  if (!isChrome) {
    ttsControls.classList.add('hidden');
    browserWarning.classList.remove('hidden');
    throw new Error('Browser not supported');
  }

  window.onload = () => {
    if (pageLoadSound) {
      pageLoadSound.currentTime = 0;
      pageLoadSound.play().catch(error => {
        console.error('Failed to play page load sound:', error);
      });
    }
    d
    if (isChrome) {
      if (speechSynthesis.onvoiceschanged !== undefined) {
        speechSynthesis.onvoiceschanged = populateVoiceList;
      }
      populateVoiceList();
    }
  };

  function populateVoiceList() {
    const availableVoices = speechSynthesis.getVoices();
    voices = availableVoices.filter(voice => voice.lang.startsWith('en')); // Filter English voices

    // Assign male and female voices
    maleVoice = voices.find(voice =>
      voice.name.toLowerCase().includes('male') ||
      voice.name.toLowerCase().includes('david') ||
      voice.name.toLowerCase().includes('alex')
    );

    femaleVoice = voices.find(voice =>
      voice.name.toLowerCase().includes('female') ||
      voice.name.toLowerCase().includes('samantha') ||
      voice.name.toLowerCase().includes('victoria') ||
      voice.name.toLowerCase().includes('karen')
    );

    // Populate the dropdown
    voiceSelect.innerHTML = '';

    if (maleVoice) {
      const maleOption = document.createElement('option');
      maleOption.value = maleVoice.name;
      maleOption.textContent = `Male (${maleVoice.name})`;
      voiceSelect.appendChild(maleOption);
    }

    if (femaleVoice) {
      const femaleOption = document.createElement('option');
      femaleOption.value = femaleVoice.name;
      femaleOption.textContent = `Female (${femaleVoice.name})`;
      voiceSelect.appendChild(femaleOption);
    }

    // Set default voice selection
    voiceSelect.value = maleVoice ? maleVoice.name : femaleVoice ? femaleVoice.name : '';

    // Enable the "Read" button if voices are available
    if (maleVoice || femaleVoice) {
      readBtn.disabled = false;
      readBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
      readBtn.disabled = true;
      readBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
  }


  function readContent() {
    if (!isChrome) return;
    stopReading();

    const content = document.getElementById('content-to-read').innerText;
    const selectedVoiceName = voiceSelect.value;
    const selectedVoice = voices.find(voice => voice.name === selectedVoiceName);

    currentUtterance = new SpeechSynthesisUtterance(content);

    if (selectedVoice) {
      currentUtterance.voice = selectedVoice;
    }

    currentUtterance.rate = 1;
    currentUtterance.pitch = 1;

    currentUtterance.onerror = (event) => {
      if (event.error !== 'canceled') {
        console.error('Speech synthesis error:', event);
      }
    };

    speechSynthesis.speak(currentUtterance);
    Swal.fire({
      toast: true,
      icon: 'info',
      title: "Reading the lecture...",
      position: 'bottom-end',
      showConfirmButton: false,
      timer: 1500
    });
  }

  function redirectToLectureList() {
    window.location.href = 'index.php';
  }

  function stopReading() {
    if (speechSynthesis.speaking) {
      speechSynthesis.cancel();
      currentUtterance = null;

      Swal.fire({
        toast: true,
        icon: 'info',
        title: "Stopped reading the lecture.",
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 1500
      });
    }
  }

  function handleVoiceCommands() {
    if (annyang) {
      listeningSound.currentTime = 0;
      listeningSound.play();
      const commands = {
        'read the lecture': () => {
          readLecture.currentTime = 0;
          readLecture.play();
          readContent();
          Swal.fire({
            toast: true,
            icon: 'info',
            title: "Reading the lecture...",
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 1500
          });
        },
        'stop reading the lecture': () => {
          stopReadingLec.currentTime = 0;
          stopReadingLec.play();
          stopReading();
          Swal.fire({
            toast: true,
            icon: 'info',
            title: "Stopped reading the lecture.",
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 1500
          });
        },
        'play the video': () => {
          playingVideo.currentTime = 0;
          playingVideo.play();
          video.play();
          Swal.fire({
            toast: true,
            icon: 'info',
            title: "Playing the video...",
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 1500
          });
        },
        'pause the video': () => {
          pauseVideo.currentTime = 0;
          pauseVideo.play();
          video.pause();
          Swal.fire({
            toast: true,
            icon: 'info',
            title: "Video paused.",
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 1500
          });
        },
        'change voice to male': () => {
          if (maleVoice) {
            changeMale.currentTime = 0;
            changeMale.play();
            voiceSelect.value = maleVoice.name;
            Swal.fire({
              toast: true,
              icon: 'success',
              title: "Voice changed to Male.",
              position: 'bottom-end',
              showConfirmButton: false,
              timer: 1500
            });
          }
        },
        'change voice to female': () => {
          if (femaleVoice) {
            changeFemale.currentTime = 0;
            changeFemale.play();
            voiceSelect.value = femaleVoice.name;
            Swal.fire({
              toast: true,
              icon: 'success',
              title: "Voice changed to Female.",
              position: 'bottom-end',
              showConfirmButton: false,
              timer: 1500
            });
          }
        },
        'go to lecture list': () => {
          redirectToLectureList();
        },
        'please please please': () => {
          pleaseSabrina.currentTime = 0;
          pleaseSabrina.play();
          Swal.fire({
            toast: true,
            icon: 'success',
            title: "Playing please please please by sabrina carpenter.",
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 1500
          });
        }
      };

      annyang.removeCommands();
      annyang.addCommands(commands);
      annyang.start({
        autoRestart: true,
        continuous: true
      });

      floatingMicBtn.classList.add('listening');
      isListening = true;

      Swal.fire({
        toast: true,
        icon: 'info',
        title: "Listening for commands...",
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 1000
      });
    } else {
      Swal.fire({
        toast: true,
        icon: 'warning',
        title: "Voice commands not supported.",
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 1500
      });
    }
  }

  floatingMicBtn.addEventListener('click', () => {
    if (isListening) {
      annyang.abort();
      floatingMicBtn.classList.remove('listening');
      isListening = false;
    } else {
      handleVoiceCommands();
    }
  });

  if (isChrome) {
    if (speechSynthesis.onvoiceschanged !== undefined) {
      speechSynthesis.onvoiceschanged = populateVoiceList;
    }
    populateVoiceList();
  }
</script>

</html>