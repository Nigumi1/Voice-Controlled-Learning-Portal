<?php
include('connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
  header('Location: login.html');
  exit();
}

// Fetch data from the database
$stmt_content = $conn->prepare("SELECT * FROM content WHERE deleted = 0 ORDER BY semester ASC, term ASC, lesson_number ASC");
$stmt_content->execute();
$result = $stmt_content->get_result();

// Organize data into a structured array
$lectures = [];
while ($row = $result->fetch_assoc()) {
  $semester = $row['semester'] ?? 'Unknown Semester';
  $term = $row['term'] ?? 'Unknown Term';

  if (!isset($lectures[$semester])) {
    $lectures[$semester] = [];
  }
  if (!isset($lectures[$semester][$term])) {
    $lectures[$semester][$term] = [];
  }

  $lectures[$semester][$term][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Learning Portal - Lectures</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/annyang/2.6.0/annyang.min.js"></script>
</head>

<style>
  .listening {
    animation: pulse 1s infinite;
  }

  @keyframes pulse {
    0% {
      transform: scale(1);
      background-color: #3b82f6;
    }

    50% {
      transform: scale(1.1);
      background-color: #2563eb;
    }

    100% {
      transform: scale(1);
      background-color: #3b82f6;
    }
  }

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

  #floatingMicBtn.listening {
    animation: pulse 1s infinite;
  }
</style>

<body class="flex min-h-screen bg-gray-100">
  <!-- Sidebar -->
  <?php include 'sidebar.php'; ?>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <header class="flex justify-between items-center mb-8">
      <!-- Search Bar with Voice -->
      <div class="relative w-80">
        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <input type="text" id="searchInput" placeholder="Search lectures..." 
               class="w-full pl-12 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
        <button id="voiceSearchBtn" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-500">
          <i class="fas fa-microphone"></i>
        </button>
      </div>

      <!-- User Profile & Logout -->
      <div class="flex items-center gap-4">
        <span class="text-gray-700"><?= htmlspecialchars($_SESSION['fname'] ?? 'User') ?></span>
        <a href="action/logout.php" class="w-10 h-10 bg-blue-500 text-white flex items-center justify-center rounded-full font-semibold hover:bg-blue-600">
          <?= strtoupper(substr($_SESSION['fname'] ?? 'U', 0, 1)) ?>
        </a>
      </div>
    </header>

    <h1 class="text-2xl font-bold text-gray-800 mb-5">My Lectures</h1>

    <!-- Lecture Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php foreach ($lectures as $semester => $terms): ?>
        <?php foreach ($terms as $term => $lectureList): ?>
          <?php foreach ($lectureList as $lecture): ?>
            <a href="index3.php?id=<?= $lecture['id'] ?>" 
               class="lecture-card bg-white rounded-xl overflow-hidden shadow hover:shadow-lg transition"
               data-title="<?= htmlspecialchars($lecture['content_title']); ?>"
               data-content="<?= htmlspecialchars($lecture['contents']); ?>">
              <div class="p-5">
                <span class="text-blue-500 text-sm font-medium">
                  <?= htmlspecialchars($semester) ?> - <?= htmlspecialchars($term) ?>
                </span>
                <h3 class="text-lg font-semibold text-gray-800 my-2">
                  <?= htmlspecialchars($lecture['content_title']) ?>
                </h3>
                <p class="text-gray-600 text-sm">
                  <?= substr(htmlspecialchars($lecture['contents']), 0, 50) ?>...
                </p>
              </div>
            </a>
          <?php endforeach; ?>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </div>

    <!-- Floating Mic Button -->
    <div id="floatingMicBtn" class="fixed bottom-6 right-6 w-14 h-14 bg-blue-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-blue-600 cursor-pointer">
      <i class="fas fa-microphone"></i>
    </div>
  </main>

  <!-- Audio Elements -->
  <audio id="listeningSound" src="listening.mp3"></audio>
  <audio id="pageLoadSound" src="lecture_list.mp3" preload="auto"></audio>

  <script>
    const listeningSound = document.getElementById('listeningSound');
    const pageLoadSound = document.getElementById('pageLoadSound');

    document.addEventListener('DOMContentLoaded', () => {
      pageLoadSound.currentTime = 0;
      pageLoadSound.play().catch(err => {
        console.warn("Auto-play might be blocked by the browser:", err);
      });
    });

    function filterLectures(query) {
      const lectureCards = document.querySelectorAll('.lecture-card');

      lectureCards.forEach(card => {
        const title = card.getAttribute('data-title').toLowerCase();
        const content = card.getAttribute('data-content').toLowerCase();

        if (title.includes(query) || content.includes(query)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    }

    function spokenTextToNumber(spokenText) {
      const numberWordsToDigits = {
        one: '1',
        two: '2',
        three: '3',
        four: '4',
        five: '5',
        six: '6',
        seven: '7',
        eight: '8',
        nine: '9',
        ten: '10'
      };

      return spokenText.replace(/\b(one|two|three|four|five|six|seven|eight|nine|ten)\b/gi, match => numberWordsToDigits[match.toLowerCase()]);
    }

    function navigateToLesson(spokenText) {
      const lectureCards = document.querySelectorAll('.lecture-card');
      spokenText = spokenTextToNumber(spokenText.toLowerCase().trim());
      const numberMatch = spokenText.match(/(?:lesson|unit|chapter)?\s*(\d+)/i);
      const lessonNumber = numberMatch ? numberMatch[1] : null;

      for (const card of lectureCards) {
        const title = card.getAttribute('data-title').toLowerCase();
        const lessonUrl = new URL(card.href);
        const lessonId = lessonUrl.searchParams.get('id');

        if (title === spokenText) {
          Swal.fire({
            toast: true,
            icon: 'success',
            title: `Navigating to ${card.getAttribute('data-title')}`,
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 1000
          });

          setTimeout(() => {
            window.location.href = `index3.php?id=${lessonId}`;
          }, 400);
          return true;
        }
      }
      return false;
    }

    const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    let isListening = false;

    function handleVoiceSearch() {
      if (!isChrome) {
        Swal.fire({
          toast: true,
          icon: 'warning',
          title: "Voice search is only supported in Google Chrome",
          position: 'bottom-end',
          showConfirmButton: false,
          timer: 3000,
        });
        return;
      }

      if (annyang) {
        if (isListening) {
          annyang.abort();
          document.getElementById('voiceSearchBtn').classList.remove('listening');
          isListening = false;
        } else {
          const commands = {
            '*query': function(query) {
              query = query.replace(/\.$/, '');
              document.getElementById('searchInput').value = query.toLowerCase();
              filterLectures(query.toLowerCase());
            }
          };

          annyang.removeCommands();
          annyang.addCommands(commands);
          annyang.start({
            autoRestart: false,
            continuous: true,
            interimResults: true
          });
          document.getElementById('voiceSearchBtn').classList.add('listening');
          isListening = true;

          Swal.fire({
            toast: true,
            icon: 'info',
            title: "Listening...",
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 1000,
          });
        }
      }
    }

    function handleVoiceNavigation() {
      if (!isChrome) {
        Swal.fire({
          toast: true,
          icon: 'warning',
          title: "Voice navigation is only supported in Google Chrome",
          position: 'bottom-end',
          showConfirmButton: false,
          timer: 3000,
        });
        return;
      }

      if (annyang) {
        if (isListening) {
          annyang.abort();
          document.getElementById('floatingMicBtn').classList.remove('listening');
          isListening = false;
        } else {
          listeningSound.currentTime = 0;
          listeningSound.play();
          const commands = {
            'go to *title': function(title) {
              console.log(`Heard command: go to ${title}`);
              navigateToLesson(title);
            },
            'open *title': function(title) {
              console.log(`Heard command: open ${title}`);
              navigateToLesson(title);
            },
            'show *title': function(title) {
              console.log(`Heard command: show ${title}`);
              navigateToLesson(title);
            },
            'navigate to *title': function(title) {
              console.log(`Heard command: navigate to ${title}`);
              navigateToLesson(title);
            },
            'take me to *title': function(title) {
              console.log(`Heard command: take me to ${title}`);
              navigateToLesson(title);
            },
            'lesson *number': function(number) {
              console.log(`Heard command: lesson ${number}`);
              navigateToLesson(`lesson ${number}`);
            }
          };
          
          console.log("Adding commands:", commands);
          annyang.removeCommands();
          annyang.addCommands(commands);
          annyang.start({
            autoRestart: true,
            continuous: true,
            interimResults: true
          });
          document.getElementById('floatingMicBtn').classList.add('listening');
          isListening = true;

          Swal.fire({
            toast: true,
            icon: 'info',
            title: "Listening for navigation...",
            position: 'bottom-end',
            showConfirmButton: false,
            timer: 1000,
          });
        }
      }
    }

    document.getElementById('voiceSearchBtn').addEventListener('click', handleVoiceSearch);
    document.getElementById('floatingMicBtn').addEventListener('click', handleVoiceNavigation);
    document.getElementById('searchInput').addEventListener('input', function() {
      const query = this.value.toLowerCase();
      filterLectures(query);
    });
  </script>
</body>
</html>