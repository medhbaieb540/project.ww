<?php
// FrontOffice event list — render events from database
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Controller/eventController.php';
require_once __DIR__ . '/../../Model/event.php';

$eventC = new eventController();
$list = $eventC->listevent();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $_SESSION['username'] ?? null;

// Ensure $list is an array
if (!is_array($list)) {
  $list = [];
}

// Get participant counts for each event
$participantCounts = [];
try {
  $pdo = config::getConnexion();
  foreach ($list as $event) {
    $eventId = $event['id'] ?? null;
    if ($eventId) {
      $participantCounts[$eventId] = $eventC->getParticipantCount($eventId);
    }
  }
} catch (Exception $e) {
  // Silently fail
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GameBridge | Events</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
  <style>
    /* Smooth modal animations for front office details */
    .modal-front {
      display: none; /* JS will set flex when opening */
      position: fixed;
      inset: 0;
      align-items: center;
      justify-content: center;
      background: rgba(0,0,0,0.6);
      z-index: 1200;
      overflow: auto;
    }

    .modal-front .modal-content-front {
      background: #0b0b0b;
      border-radius: 12px;
      padding: 22px;
      width: 96%;
      max-width: 900px;
      transform: translateY(12px) scale(0.995);
      opacity: 0;
      transition: transform 260ms cubic-bezier(.2,.8,.2,1), opacity 220ms ease;
      box-shadow: 0 10px 30px rgba(0,0,0,0.6);
    }

    .modal-front.open { display: flex; }
    .modal-front.open .modal-content-front {
      transform: translateY(0) scale(1);
      opacity: 1;
    }

    .close-btn-front { background: none; border: none; color: #fff; font-size: 1.4rem; cursor: pointer; }
    .event-details-content { color: #ddd; }
    .detail-section { margin-bottom: 14px; }
    .detail-section-title { color: #1aff87; margin-bottom: 8px; }
    .detail-text { color: #ccc; }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); gap: 10px; }
    .detail-item { background: #0d0d0d; padding: 8px 10px; border-radius:6px; border: 1px solid #1aff8710; }
    .detail-label { display:block; color:#999; font-size:0.8rem; }
    .detail-value { color:#fff; font-weight:600; }
  </style>
  <!-- Chat widget styles -->
  <style>
    .chat-widget {
      position: fixed !important;
      right: 20px !important;
      bottom: 24px !important;
      width: 360px;
      max-width: calc(100% - 48px);
      z-index: 9999 !important;
      font-family: Poppins, Arial, sans-serif;
      pointer-events: auto;
    }

    .chat-toggle-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: #1aff87;
      color: #000;
      border: none;
      box-shadow: 0 8px 20px rgba(0,0,0,0.4);
      cursor: pointer;
      font-size: 20px;
    }

    .chat-panel {
      display: none;
      flex-direction: column;
      background: #0b0b0b;
      border: 1px solid #1aff8711;
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 10px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.6);
      transition: transform 260ms cubic-bezier(.2,.8,.2,1), opacity 220ms ease;
      transform-origin: bottom right;
      width: 360px;
      max-height: 520px;
    }

    .chat-panel.open { display: flex; opacity: 1; transform: translateY(0) scale(1); }

    .chat-header {
      padding: 12px 14px;
      background: linear-gradient(90deg,#08110a,#071215);
      border-bottom: 1px solid #1aff8711;
      color: #fff;
      display:flex; align-items:center; justify-content:space-between;
    }

    .chat-messages { padding: 12px; flex: 1 1 auto; overflow-y: auto; background: #070707; }
    .chat-input-row { display:flex; gap:8px; padding:10px; border-top:1px solid #1aff8711; }
    .chat-input { flex:1; padding:8px 10px; background:#0d0d0d; border:1px solid #222; color:#fff; border-radius:6px; }
    .chat-send { background:#1aff87; color:#000; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; }

    .msg { margin-bottom:10px; display:block; }
    .msg.user { text-align: right; }
    .msg .bubble { display:inline-block; padding:8px 12px; border-radius:12px; max-width:80%; }
    .msg.user .bubble { background:#1aff87; color:#000; border-bottom-right-radius:4px; }
    .msg.bot .bubble { background:#111; color:#fff; border-bottom-left-radius:4px; border:1px solid #1aff8711; }

    .chat-empty { color:#999; padding:14px; text-align:center; }
  </style>
</head>

<body>
  <!-- ===== Header ===== -->
  <header>
    <div class="logo-container">
      <img src="images/logo.png" alt="GameBridge Logo">
    </div>
    <nav>
      <a href="index.html">Home</a>
      <a href="games.html">Games</a>
      <a href="tournaments.html">Tournaments</a>
      <a href="event.php" class="active">Events</a>
      <button class="login-btn">Login</button>
    </nav>

























































































  </header>

  <!-- ===== Hero Section ===== -->
  <section class="hero events-hero">
    <div class="hero-content">
      <h2>Game Events & Community Meetups</h2>
      <p>Welcome to our Events</p>
    </div>
  </section>

  <!-- ===== Events List ===== -->
  <section class="events-section">
    <div class="events-header">
      <span class="tagline">UPCOMING</span>
      <div>
        <h2>All Events</h2>
        <p>Discover and participate in our latest community events</p>
      </div>
    </div>

    <div class="events-grid" id="eventsGrid">
      <?php if (!empty($list)): ?>
        <?php foreach ($list as $event): ?>
          <?php
            $title = $event['title'] ?? 'Untitled Event';
            $desc = $event['description'] ?? '';
            $rawDate = $event['eventdate'] ?? null;
            $dateStr = '';
            if (!empty($rawDate)) {
              try {
                $dt = new DateTime($rawDate);
                $dateStr = $dt->format('F j, Y - g:i A');
              } catch (Exception $e) { $dateStr = htmlspecialchars($rawDate); }
            }
            // Check for AI-generated images (like in back office)
            $eventId = $event['id'] ?? null;
            $img = 'images/event1.jpg'; // Default fallback image
            $assetBaseUrl = 'images/generated/';
            $assetBaseFs = __DIR__ . '/images/generated/';
            
            // Check if AI-generated poster exists
            if ($eventId && file_exists($assetBaseFs . "event-{$eventId}-poster.png")) {
              $img = $assetBaseUrl . "event-{$eventId}-poster.png";
            } elseif ($eventId && file_exists($assetBaseFs . "event-{$eventId}-banner.png")) {
              $img = $assetBaseUrl . "event-{$eventId}-banner.png";
            } elseif ($eventId && file_exists($assetBaseFs . "event-{$eventId}-thumb.png")) {
              $img = $assetBaseUrl . "event-{$eventId}-thumb.png";
            }
            
            // Determine a simple badge type (default)
            $badge = 'Event';
            $badgeClass = 'tournament';
            if (!empty($event['organizer_id'])) {
              $badge = 'Organizer '.htmlspecialchars($event['organizer_id']);
            }
          ?>
          <?php
            $participantCount = isset($participantCounts[$eventId]) ? $participantCounts[$eventId] : 0;
            $maxParticipants = isset($event['max_participent']) && $event['max_participent'] > 0 ? (int)$event['max_participent'] : null;
            $remainingSpots = $maxParticipants !== null ? max(0, $maxParticipants - $participantCount) : null;
            $isFull = $maxParticipants !== null && $remainingSpots === 0;
          ?>
          <div class="event-card" data-id="<?php echo htmlspecialchars($eventId); ?>">
            <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($title); ?>" onerror="this.src='images/event1.jpg'">
            <div class="event-content">
              <span class="event-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($badge); ?></span>
              <h3><?php echo htmlspecialchars($title); ?></h3>
              <p class="event-date"><?php echo $dateStr ? '📅 '.$dateStr : ''; ?></p>
              <p class="event-description"><?php echo nl2br(htmlspecialchars($desc)); ?></p>
              
              <!-- Participant Statistics Section -->
              <div class="participant-chart-section-front">
                <div class="participant-stats-row-front">
                  <div class="participant-stat-front">
                    <span class="stat-label-front">Participants</span>
                    <span class="stat-value-front"><?php echo $participantCount; ?></span>
                  </div>
                  <?php if ($maxParticipants !== null): ?>
                    <div class="participant-stat-front">
                      <span class="stat-label-front">Max</span>
                      <span class="stat-value-front"><?php echo $maxParticipants; ?></span>
                    </div>
                    <div class="participant-stat-front">
                      <span class="stat-label-front">Remaining</span>
                      <span class="stat-value-front <?php echo $isFull ? 'stat-full-front' : ($remainingSpots <= 5 ? 'stat-low-front' : ''); ?>">
                        <?php echo $remainingSpots; ?>
                      </span>
                    </div>
                  <?php else: ?>
                    <div class="participant-stat-front">
                      <span class="stat-label-front">Limit</span>
                      <span class="stat-value-front">Unlimited</span>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              
              <div class="event-actions">
                <button class="details-btn-front" onclick="showEventDetails(<?php echo htmlspecialchars($eventId); ?>, '<?php echo htmlspecialchars(addslashes($title)); ?>', '<?php echo htmlspecialchars(addslashes($desc)); ?>', '<?php echo htmlspecialchars(addslashes($dateStr)); ?>', <?php echo $participantCount; ?>, <?php echo $maxParticipants !== null ? $maxParticipants : 'null'; ?>, <?php echo $remainingSpots !== null ? $remainingSpots : 'null'; ?>, '<?php echo htmlspecialchars(addslashes($badge)); ?>', <?php echo $event['organizer_id'] ?? 'null'; ?>)">
                  ℹ️ More Details
                </button>
                <?php if ($isLoggedIn): ?>
                  <?php if ($isFull): ?>
                    <button class="join-btn" disabled style="opacity: 0.5; cursor: not-allowed;">Event Full</button>
                  <?php else: ?>
                    <button class="join-btn" onclick="joinEvent(<?php echo htmlspecialchars($eventId); ?>)">Join Event</button>
                  <?php endif; ?>
                <?php else: ?>
                  <button class="join-btn" onclick="redirectToLogin()">Log In to Join</button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="color:#ccc; padding: 10px;">No events found.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Event Details Modal -->
  <div id="detailsModalFront" class="modal-front" style="display:none;">
    <div class="modal-content-front" style="max-width: 800px;">
      <div class="modal-header-front">
        <h3 id="detailsModalTitleFront">Event Details</h3>
        <button class="close-btn-front" onclick="closeDetailsModalFront()">×</button>
      </div>
      <div class="details-modal-body-front" id="detailsModalBodyFront">
        <!-- Content will be populated by JavaScript -->
      </div>
    </div>

      <!-- Chat widget -->
      <div class="chat-widget" id="chatWidget">
        <div class="chat-panel" id="chatPanel" role="dialog" aria-hidden="true">
          <div class="chat-header">
            <div>Assistant</div>
            <button class="chat-close" onclick="toggleChat()" aria-label="Close chat">×</button>
          </div>
          <div class="chat-messages" id="chatMessages">
            <div class="chat-empty">Ask about events — e.g., "How many participants for Event 3?"</div>
          </div>
          <div class="chat-input-row">
            <input id="chatInput" class="chat-input" placeholder="Ask about events or get help..." />
            <button id="chatSend" class="chat-send" onclick="sendChat()">Send</button>
          </div>
        </div>
        <button class="chat-toggle-btn" id="chatToggle" onclick="toggleChat()">💬</button>
      </div>
  </div>

  <!-- ===== Footer ===== -->
  <footer>
    © <?php echo date('Y'); ?> GameBridge • Developed by Team UnityForge
  </footer>

  <script>
    // Smooth open/close for the details modal
    (function(){
      const modal = document.getElementById('detailsModalFront');
      const bodyEl = document.getElementById('detailsModalBodyFront');

      function setDetailsHtml(html) {
        bodyEl.innerHTML = html;
      }

      window.showEventDetails = function(eventId, title, description, eventDate, participantCount, maxParticipants, remainingSpots, badge, organizerId) {
        document.getElementById('detailsModalTitleFront').textContent = title;

        const isFull = maxParticipants !== null && remainingSpots === 0;
        const fillPercentage = maxParticipants !== null && maxParticipants > 0 
          ? ((participantCount / maxParticipants) * 100).toFixed(1) 
          : null;

        const detailsHtml = `
          <div class="event-details-content">
            <div class="detail-section">
              <h4 class="detail-section-title">📋 Description</h4>
              <p class="detail-text">${description || 'No description available.'}</p>
            </div>
            
            <div class="detail-section">
              <h4 class="detail-section-title">📅 Event Information</h4>
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="detail-label">Event Date:</span>
                  <span class="detail-value">${eventDate || 'Not specified'}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Event Type:</span>
                  <span class="detail-value">${badge || 'Event'}</span>
                </div>
                ${organizerId !== null && organizerId !== 'null' ? `
                  <div class="detail-item">
                    <span class="detail-label">Organizer ID:</span>
                    <span class="detail-value">#${organizerId}</span>
                  </div>
                ` : ''}
              </div>
            </div>
            
            <div class="detail-section">
              <h4 class="detail-section-title">👥 Participation</h4>
              <div class="detail-grid">
                <div class="detail-item">
                  <span class="detail-label">Current Participants:</span>
                  <span class="detail-value highlight">${participantCount}</span>
                </div>
                ${maxParticipants !== null && maxParticipants !== 'null' ? `
                  <div class="detail-item">
                    <span class="detail-label">Maximum Participants:</span>
                    <span class="detail-value">${maxParticipants}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Remaining Spots:</span>
                    <span class="detail-value ${isFull ? 'detail-full' : (remainingSpots <= 5 ? 'detail-low' : 'detail-available')}">${remainingSpots}</span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Fill Status:</span>
                    <span class="detail-value ${isFull ? 'detail-full' : (fillPercentage >= 80 ? 'detail-low' : 'detail-available')}">${fillPercentage}% Full</span>
                  </div>
                ` : `
                  <div class="detail-item">
                    <span class="detail-label">Participant Limit:</span>
                    <span class="detail-value highlight">Unlimited</span>
                  </div>
                `}
              </div>
              ${maxParticipants !== null && maxParticipants !== 'null' ? `
                <div class="detail-progress-bar">
                  <div class="detail-progress-fill" style="width: ${fillPercentage}%; background: ${isFull ? '#ff6464' : (fillPercentage >= 80 ? '#ffaa00' : '#1aff87')};"></div>
                </div>
              ` : ''}
            </div>
            
            <div class="detail-section">
              <h4 class="detail-section-title">ℹ️ Event Status</h4>
              <div class="status-badge ${isFull ? 'status-full' : (maxParticipants !== null && remainingSpots <= 5 ? 'status-low' : 'status-available')}">
                ${isFull ? '🔴 Event Full' : (maxParticipants !== null && remainingSpots <= 5 ? '🟡 Limited Spots Available' : '🟢 Open for Registration')}
              </div>
            </div>
          </div>
        `;

        setDetailsHtml(detailsHtml);

        // Show and animate
        modal.style.display = 'flex';
        // allow for next frame then add open class to trigger CSS transition
        requestAnimationFrame(() => { modal.classList.add('open'); });
      };

      window.closeDetailsModalFront = function() {
        // animate closing then hide
        modal.classList.remove('open');
        // wait for transition duration before hiding
        const hideAfter = 300; // ms (should be slightly longer than CSS transition)
        setTimeout(() => {
          modal.style.display = 'none';
          // clear content to reduce memory
          setDetailsHtml('');
        }, hideAfter);
      };

      // Close modals when clicking outside
      if (!window.modalClickHandlerFront) {
        window.modalClickHandlerFront = function(event) {
          if (event.target === modal) {
            window.closeDetailsModalFront();
          }
        };
        window.addEventListener('click', window.modalClickHandlerFront);
      }
    })();
  </script>

  <script>
    // Join event via AJAX
    function joinEvent(eventId) {
      var formData = new FormData();
      formData.append('event_id', eventId);
      
      fetch('join_event.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('✓ ' + data.message);
        } else {
          alert('✗ ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while joining the event.');
      });
    }
    
    function redirectToLogin() {
      var returnTo = window.location.pathname + window.location.search;
      window.location.href = 'log in module/login.php?return_to=' + encodeURIComponent(returnTo);
    }
  </script>
  <script>
    // Chat widget logic
    let chatOpen = false;
    const chatPanel = document.getElementById('chatPanel');
    const chatToggle = document.getElementById('chatToggle');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const chatSend = document.getElementById('chatSend');

    function toggleChat() {
      chatOpen = !chatOpen;
      if (chatOpen) {
        chatPanel.classList.add('open');
        chatPanel.setAttribute('aria-hidden','false');
        // focus input on open
        setTimeout(() => chatInput.focus(), 200);
      } else {
        chatPanel.classList.remove('open');
        chatPanel.setAttribute('aria-hidden','true');
      }
    }

    function appendMessage(text, who='bot') {
      // remove empty placeholder
      const empty = chatMessages.querySelector('.chat-empty');
      if (empty) empty.remove();
      const wrapper = document.createElement('div');
      wrapper.className = 'msg ' + (who==='user' ? 'user' : 'bot');
      const bubble = document.createElement('span');
      bubble.className = 'bubble';
      bubble.textContent = text;
      wrapper.appendChild(bubble);
      chatMessages.appendChild(wrapper);
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function setLoading(loading) {
      chatSend.disabled = loading;
      chatInput.disabled = loading;
      chatSend.textContent = loading ? '...' : 'Send';
    }

    function sendChat() {
      const text = chatInput.value.trim();
      if (!text) return;
      appendMessage(text, 'user');
      chatInput.value = '';
      setLoading(true);

      fetch('../backofice/api_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text })
      })
      .then(r => r.json())
      .then(data => {
        // api_chat.php proxies OpenAI response raw — show the response
        if (typeof data === 'string') {
          appendMessage(data, 'bot');
        } else if (data && data.choices && data.choices[0] && data.choices[0].message) {
          appendMessage(data.choices[0].message.content || JSON.stringify(data), 'bot');
        } else {
          // if response is JSON message or other
          appendMessage(JSON.stringify(data), 'bot');
        }
      })
      .catch(err => {
        console.error(err);
        appendMessage('Error: could not reach assistant.', 'bot');
      })
      .finally(() => setLoading(false));
    }

    // allow Enter to send
    chatInput.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); sendChat(); } });
  </script>
  <script src="nav.js"></script>
</body>
</html>
