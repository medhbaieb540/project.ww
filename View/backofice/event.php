<?php
// event.php - server-side processing placed before any HTML output
include __DIR__ . '/../../Controller/eventController.php';
require_once __DIR__ . '/../../Model/event.php';

$eventC = new eventController();
$error = '';
$generatedImagesDir = __DIR__ . '/../FrontOffice/images/generated';

function ensureGeneratedDir($dir)
{
  if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
  }
}

function saveDataUriImage($dataUri, $destinationPath)
{
  if (empty($dataUri)) {
    return false;
  }
  // Accept either data URI or remote URL
  if (strpos($dataUri, 'data:image') === 0) {
    $base64Pos = strpos($dataUri, 'base64,');
    if ($base64Pos === false) return false;
    $base64 = substr($dataUri, $base64Pos + 7);
    $binary = base64_decode($base64);
  } else {
    // Try to download remote image
    $binary = @file_get_contents($dataUri);
  }
  if ($binary === false) return false;
  ensureGeneratedDir(dirname($destinationPath));
  file_put_contents($destinationPath, $binary);
  return true;
}

function persistAiAssets($eventId, $posterData, $bannerData, $thumbData, $dir)
{
  if (!$eventId) return;
  $basePath = rtrim($dir, '/\\');
  saveDataUriImage($posterData, $basePath . "/event-{$eventId}-poster.png");
  saveDataUriImage($bannerData, $basePath . "/event-{$eventId}-banner.png");
  saveDataUriImage($thumbData, $basePath . "/event-{$eventId}-thumb.png");
}

// Delete via GET
if (isset($_GET['delete_id'])) {
  $idToDelete = (int)$_GET['delete_id'];
  if ($idToDelete > 0) {
    $eventC->deleteevent($idToDelete);
  }
  header('Location: event.php');
  exit();
}

// Create / Update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $organizer_id = (int)($_POST['organizer_id'] ?? 0);
  $eventdate = $_POST['eventdate'] ?? null;
  $max_participants = !empty($_POST['max_participent']) ? (int)$_POST['max_participent'] : null;
  $posterData = $_POST['ai_poster_data'] ?? '';
  $bannerData = $_POST['ai_banner_data'] ?? '';
  $thumbData = $_POST['ai_thumbnail_data'] ?? '';

  $dt = null;
  if (!empty($eventdate)) {
    try {
      $dt = new DateTime($eventdate);
    } catch (Exception $e) {
      $dt = null;
    }
  }

  if ($action === 'create') {
    $evt = new event(null, $title, $description, $organizer_id, $dt, $max_participants);
    $res = $eventC->addevent($evt);
    if (is_array($res) ? ($res['ok'] ?? false) : $res) {
      $newId = is_array($res) ? ($res['id'] ?? null) : null;
      if ($newId) {
        persistAiAssets($newId, $posterData, $bannerData, $thumbData, $generatedImagesDir);
      }
      header('Location: event.php');
      exit();
    } else {
      $error = 'Failed to create event: ' . (is_array($res) ? ($res['msg'] ?? 'unknown') : 'unknown');
    }
  }

  if ($action === 'update') {
    $id = (int)($_POST['id'] ?? 0);
    $evt = new event($id, $title, $description, $organizer_id, $dt, $max_participants);
    $res = $eventC->updatevent($evt, $id);
    if (is_array($res) ? ($res['ok'] ?? false) : $res) {
      if ($id) {
        persistAiAssets($id, $posterData, $bannerData, $thumbData, $generatedImagesDir);
      }
      header('Location: event.php');
      exit();
    } else {
      $error = 'Failed to update event: ' . (is_array($res) ? ($res['msg'] ?? 'unknown') : 'unknown');
    }
  }
}

// If edit requested, load event to prefill form
$editEvent = null;
if (isset($_GET['edit_id'])) {
  $idToEdit = (int)$_GET['edit_id'];
  if ($idToEdit > 0) {
    $editEvent = $eventC->showevent($idToEdit);
  }
}
$existingAssets = ['poster' => '', 'banner' => '', 'thumb' => ''];
if ($editEvent && isset($editEvent['id'])) {
  $assetBase = __DIR__ . '/../FrontOffice/images/generated/';
  $idAsset = $editEvent['id'];
  if (file_exists($assetBase . "event-{$idAsset}-poster.png")) {
    $existingAssets['poster'] = "../FrontOffice/images/generated/event-{$idAsset}-poster.png";
  }
  if (file_exists($assetBase . "event-{$idAsset}-banner.png")) {
    $existingAssets['banner'] = "../FrontOffice/images/generated/event-{$idAsset}-banner.png";
  }
  if (file_exists($assetBase . "event-{$idAsset}-thumb.png")) {
    $existingAssets['thumb'] = "../FrontOffice/images/generated/event-{$idAsset}-thumb.png";
  }
}

$list = $eventC->listevent();

// Load participants grouped by event
$participants = [];
$participantsByEvent = [];
try {
  $pdo = config::getConnexion();
  $stmt = $pdo->prepare('
    SELECT ep.id, ep.event_id, ep.user_id, NOW() as joined_at, u.username, u.email, e.title as event_title
    FROM event_participation ep
    JOIN users u ON ep.user_id = u.id
    JOIN events e ON ep.event_id = e.id
    ORDER BY ep.event_id, ep.id DESC
  ');
  $stmt->execute();
  $participants = $stmt->fetchAll();
  
  // Group participants by event_id
  foreach ($participants as $p) {
    $event_id = $p['event_id'];
    if (!isset($participantsByEvent[$event_id])) {
      $participantsByEvent[$event_id] = [];
    }
    $participantsByEvent[$event_id][] = $p;
  }
} catch (Exception $e) {
  // Silently fail if table doesn't exist yet
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GameBridge | Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ===== ADMIN DASHBOARD ===== */
    body {
      background: #0c0c0c;
      color: var(--text);
      font-family: 'Poppins', sans-serif;
      margin: 0;
      display: flex;
      min-height: 100vh;
    }

    /* ===== Sidebar ===== */
    .sidebar {
      width: 250px;
      background: #0f0f0f;
      border-right: 1px solid #1aff8715;
      display: flex;
      flex-direction: column;
      padding: 30px 20px;
    }

    .sidebar h2 {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      margin-bottom: 40px;
      text-align: center;
    }

    .sidebar a {
      color: #bbb;
      text-decoration: none;
      padding: 10px 15px;
      margin-bottom: 10px;
      border-radius: 6px;
      transition: 0.3s;
      font-weight: 500;
    }

    .sidebar a:hover, .sidebar a.active {
      background: var(--accent);
      color: #000;
    }

    /* ===== Main Content ===== */
    .main-content {
      flex: 1;
      padding: 40px 60px;
      overflow-y: auto;
    }

    .main-content h1 {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      margin-bottom: 40px;
      text-align: center;
    }

    /* ===== Stats Grid ===== */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 25px;
      margin-bottom: 60px;
    }

    .stat-card {
      background: var(--bg-card);
      border: 1px solid #1aff8720;
      border-radius: 12px;
      padding: 25px;
      text-align: center;
      transition: 0.3s;
      box-shadow: 0 0 20px #00000055;
    }

    .stat-card:hover {
      border-color: var(--accent);
      box-shadow: 0 0 25px #1aff8733;
      transform: translateY(-4px);
    }

    .stat-card h3 {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      font-size: 1.3rem;
      margin-bottom: 8px;
    }

    .stat-card p {
      color: #ccc;
      font-size: 1rem;
    }

    /* ===== Tables ===== */
    h2.section-title {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      margin-bottom: 15px;
      border-left: 4px solid var(--accent);
      padding-left: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: var(--bg-card);
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 40px;
    }

    th, td {
      padding: 14px 18px;
      text-align: left;
      font-size: 0.9rem;
    }

    th {
      background: #111;
      color: var(--accent);
      text-transform: uppercase;
      letter-spacing: 1px;
      border-bottom: 1px solid #1aff8711;
    }

    tr:nth-child(even) {
      background: #121212;
    }

    tr:hover {
      background: #1aff870f;
    }

    td button {
      background: transparent;
      border: 2px solid var(--accent);
      color: var(--accent);
      border-radius: 6px;
      padding: 4px 10px;
      font-size: 0.8rem;
      cursor: pointer;
      transition: 0.3s;
      text-transform: uppercase;
    }

    td button:hover {
      background: var(--accent);
      color: #000;
    }

    /* ===== Events Management Styles ===== */
    .events-management {
      background: var(--bg-card);
      border-radius: 12px;
      padding: 30px;
      margin-bottom: 40px;
      border: 1px solid #1aff8720;
    }

    .events-header {
      display: flex;
      justify-content: between;
      align-items: center;
      margin-bottom: 25px;
    }

    .events-header h2 {
      font-family: 'Orbitron', sans-serif;
      color: var(--accent);
      margin: 0;
    }

    .add-event-btn {
      background: var(--accent);
      color: #000;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-family: 'Orbitron', sans-serif;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    .add-event-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px #1aff8755;
    }

    .events-grid-admin {
      display: grid;
      /* slightly larger cards so content breathes */
      grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
      gap: 28px;
    }

    .event-card-admin {
      background: #111;
      border-radius: 12px;
      border: 1px solid #1aff8715;
      padding: 28px;
      min-height: 220px;
      transition: 0.3s;
    }

    .event-asset-visual {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-radius: 10px;
      border: 1px solid #1aff8715;
      margin-bottom: 12px;
      background: #0a0a0a;
    }

    .event-card-admin:hover {
      border-color: var(--accent);
      transform: translateY(-5px);
    }

    .event-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .badge-tournament { background: #1aff87; color: #000; }
    .badge-stream { background: #ff1a6c; color: #fff; }
    .badge-beta { background: #1a8cff; color: #fff; }
    .badge-meetup { background: #ffb31a; color: #000; }

    .event-card-admin h4 {
      color: #fff;
      margin: 12px 0;
      font-size: 1.25rem;
    }

    .event-date {
      color: #bbb;
      font-size: 0.9rem;
      margin-bottom: 10px;
    }

    .event-description {
      color: #ccc;
      font-size: 0.95rem;
      line-height: 1.5;
      margin-bottom: 18px;
    }

    .event-actions-admin {
      display: flex;
      gap: 10px;
    }

    .edit-btn, .delete-btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.8rem;
      transition: 0.3s;
    }

    .edit-btn {
      background: #1a8cff;
      color: white;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .delete-btn {
      background: #ff1a6c;
      color: white;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .edit-btn:hover, .delete-btn:hover {
      transform: translateY(-2px);
    }

    .chart-btn {
      background: #9b59b6;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.8rem;
      transition: 0.3s;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .chart-btn:hover {
      background: #8e44ad;
      transform: translateY(-2px);
    }

    /* ===== Participant Chart Section ===== */
    .participant-chart-section {
      margin-top: 15px;
      padding: 12px;
      background: #0a0a0a;
      border-radius: 8px;
      border: 1px solid #1aff8715;
    }

    .participant-stats-row {
      display: flex;
      justify-content: space-around;
      align-items: center;
      margin-bottom: 12px;
      gap: 10px;
    }

    .participant-stat {
      display: flex;
      flex-direction: column;
      align-items: center;
      flex: 1;
    }

    .participant-stat .stat-label {
      font-size: 0.7rem;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .participant-stat .stat-value {
      font-size: 1.2rem;
      font-weight: 700;
      color: #1aff87;
      font-family: 'Orbitron', sans-serif;
    }

    .participant-stat .stat-value.stat-low {
      color: #ffaa00;
    }

    .participant-stat .stat-value.stat-full {
      color: #ff6464;
    }

    .participant-chart-container {
      margin-top: 10px;
    }

    .participant-chart-bar {
      width: 100%;
      height: 20px;
      background: #1a1a1a;
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid #1aff8720;
      position: relative;
    }

    .participant-chart-fill {
      height: 100%;
      border-radius: 10px;
      transition: width 0.5s ease, background 0.3s ease;
      background: linear-gradient(90deg, #1aff87, #1aff87cc);
    }

    .participant-chart-label {
      text-align: center;
      margin-top: 6px;
      font-size: 0.75rem;
      color: #aaa;
      font-weight: 600;
    }

    /* Chart Modal Styles */
    .chart-modal-body {
      padding: 20px;
    }

    .chart-container-wrapper {
      width: 100%;
      height: 400px;
      margin-bottom: 20px;
      background: #0a0a0a;
      border-radius: 8px;
      padding: 20px;
      border: 1px solid #1aff8715;
    }

    .chart-stats-summary {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
      gap: 15px;
      margin-top: 20px;
    }

    .stat-item {
      background: #0a0a0a;
      border: 1px solid #1aff8715;
      border-radius: 8px;
      padding: 15px;
      text-align: center;
    }

    .stat-label-chart {
      display: block;
      font-size: 0.75rem;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }

    .stat-value-chart {
      display: block;
      font-size: 1.5rem;
      font-weight: 700;
      color: #1aff87;
      font-family: 'Orbitron', sans-serif;
    }

    .stat-value-chart.stat-low-chart {
      color: #ffaa00;
    }

    .stat-value-chart.stat-full-chart {
      color: #ff6464;
    }

    /* ===== Event Participants in Card ===== */
    .event-participants-section {
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid #1aff8715;
    }

    .view-participants-btn {
      background: #1aff87;
      color: #000;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      font-weight: 600;
      cursor: pointer;
      font-size: 0.85rem;
      transition: 0.3s;
      width: 100%;
    }

    .view-participants-btn:hover {
      background: #1aff87e0;
      transform: translateY(-2px);
    }

    .participants-table-container {
      /* start collapsed, animate with max-height + opacity for smooth open/close */
      max-height: 0;
      margin-top: 12px;
      overflow: hidden;
      transition: max-height 360ms cubic-bezier(.2,.8,.2,1), opacity 220ms ease;
      opacity: 0;
    }

    .participants-table-container.active {
      opacity: 1;
      /* max-height is set via JS to the scrollHeight for smooth transitions */
    }

    .participants-table {
      width: 100%;
      border-collapse: collapse;
      background: #0a0a0a;
      border-radius: 4px;
      overflow: hidden;
    }

    .participants-table thead {
      background: #111;
    }

    .participants-table th {
      color: #1aff87;
      padding: 8px 10px;
      text-align: left;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      border-bottom: 1px solid #1aff870a;
    }

    .participants-table td {
      color: #ccc;
      padding: 8px 10px;
      font-size: 0.8rem;
      border-bottom: 1px solid #1aff870a;
    }

    .participants-table tr:hover {
      background: #1aff870a;
    }

    .delete-participant-btn {
      background: #ff1a6c;
      color: white;
      border: none;
      padding: 4px 8px;
      border-radius: 3px;
      cursor: pointer;
      font-size: 0.7rem;
      transition: 0.3s;
    }

    .delete-participant-btn:hover {
      background: #ff1a6ce0;
      transform: scale(1.05);
    }

    .participant-count-badge {
      display: inline-block;
      background: #1aff87;
      color: #000;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
      margin-left: 5px;
    }

    /* ===== Event Form Modal ===== */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.8);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background: #111;
      padding: 30px;
      border-radius: 12px;
      border: 1px solid var(--accent);
      width: 90%;
      max-width: 500px;
    }

    .modal-header {
      display: flex;
      justify-content: between;
      align-items: center;
      margin-bottom: 20px;
    }

    .modal-header h3 {
      color: var(--accent);
      font-family: 'Orbitron', sans-serif;
      margin: 0;
    }

    .close-btn {
      background: none;
      border: none;
      color: #fff;
      font-size: 1.5rem;
      cursor: pointer;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      color: #ccc;
      margin-bottom: 5px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 10px;
      background: #1a1a1a;
      border: 1px solid #333;
      border-radius: 6px;
      color: #fff;
    }

    .form-actions {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
    }

    /* AI generation block */
    .ai-builder {
      background: #0f0f0f;
      border: 1px dashed #1aff8720;
      border-radius: 10px;
      padding: 14px;
      margin-bottom: 16px;
    }

    .ai-builder h4 {
      margin: 0 0 10px 0;
      color: var(--accent);
      font-family: 'Orbitron', sans-serif;
    }

    .ai-builder .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 10px;
    }

    .ai-preview-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 10px;
      margin-top: 10px;
    }

    .ai-preview-card {
      background: #0c0c0c;
      border: 1px solid #1aff8715;
      border-radius: 8px;
      padding: 8px;
    }

    .ai-preview-card img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 6px;
      background: #080808;
    }

    .ai-status {
      color: #9fdca8;
      margin-top: 8px;
      font-size: 0.9rem;
    }

    .secondary-btn {
      background: transparent;
      border: 1px solid var(--accent);
      color: var(--accent);
      padding: 8px 12px;
      border-radius: 6px;
      cursor: pointer;
      transition: 0.2s;
    }

    .secondary-btn:hover {
      background: #1aff8715;
    }

    /* ===== Footer ===== */
    footer {
      text-align: center;
      color: #777;
      font-size: 0.85rem;
      margin-top: 50px;
      border-top: 1px solid #1aff8711;
      padding-top: 15px;
    }

    @media (max-width: 900px) {
      .sidebar {
        display: none;
      }
      body {
        flex-direction: column;
      }
      .main-content {
        padding: 20px;
      }
      .events-grid-admin {
        grid-template-columns: 1fr;
      }
    }
  </style>
  <!-- Chat widget styles -->
  <style>
    .chat-widget {
      position: fixed;
      right: 20px;
      bottom: 24px;
      z-index: 99999;
      font-family: Poppins, Arial, sans-serif;
      /* ensure pointer events reach children */
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
      position: relative;
      z-index: 100001;
    }

    .chat-panel {
      position: absolute;
      right: 0;
      bottom: 72px; /* sits above the toggle button */
      display: none;
      flex-direction: column;
      background: #0b0b0b;
      border: 1px solid #1aff8711;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.6);
      transition: transform 260ms cubic-bezier(.2,.8,.2,1), opacity 220ms ease;
      transform-origin: bottom right;
      width: 360px;
      max-width: calc(100vw - 48px);
      max-height: 520px;
      opacity: 0;
    }

    .chat-panel.open {
      display: flex;
      opacity: 1;
      transform: translateY(0) scale(1);
    }

    .chat-header { padding: 12px 14px; background: linear-gradient(90deg,#08110a,#071215); border-bottom: 1px solid #1aff8711; color: #fff; display:flex; align-items:center; justify-content:space-between; }
    .chat-messages { padding: 12px; flex: 1 1 auto; overflow-y: auto; background: #070707; }
    .chat-input-row { display:flex; gap:8px; padding:10px; border-top:1px solid #1aff8711; }
    .chat-input { flex:1; padding:8px 10px; background:#0d0d0d; border:1px solid #222; color:#fff; border-radius:6px; }
    .chat-send { background:#1aff87; color:#000; border:none; padding:8px 12px; border-radius:6px; cursor:pointer; }
    .msg { margin-bottom:10px; display:block; }
    .msg.user { text-align:right; }
    .msg .bubble { display:inline-block; padding:8px 12px; border-radius:12px; max-width:80%; }
    .msg.user .bubble { background:#1aff87; color:#000; border-bottom-right-radius:4px; }
    .msg.bot .bubble { background:#111; color:#fff; border-bottom-left-radius:4px; border:1px solid #1aff8711; }
    .chat-empty { color:#999; padding:14px; text-align:center; }

    @media (max-width: 480px) {
      .chat-panel { right: 12px; left: 12px; width: auto; bottom: 84px; }
      .chat-toggle-btn { right: 12px; }
    }
  </style>
 </head>
 <body>

  <!-- ===== Sidebar ===== -->
  <div class="sidebar">
    <h2>Admin</h2>
    <a href="#" class="active">Dashboard</a>
    <a href="#">Users</a>
    <a href="#">Games</a>
    <a href="#">Tournaments</a>
    <a href="#">Feedback</a>
    <a href="#">Rewards</a>
    <a href="event.php">Event</a>
  </div>

  <!-- ===== Main Content ===== -->
  <div class="main-content">
    <h1>GameBridge Admin Dashboard</h1>



    <!-- ===== Events Management Section ===== -->
    <section class="events-management">
      <div class="events-header">
        <h2>Events Management</h2>
        <button class="add-event-btn" onclick="openEventModal()">+ Add New Event</button>
      </div>

      <?php if (!empty($error)) : ?>
        <div style="color: #ff6464; margin-bottom: 12px;"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <div class="events-grid-admin">
        <?php if (!empty($list)) : ?>
            <?php foreach ($list as $event) : ?>
                <div class="event-card-admin">
                    <?php 
                      $idAsset = $event['id'] ?? null;
                      $assetBaseUrl = "../FrontOffice/images/generated/";
                      $posterUrl = $idAsset ? $assetBaseUrl . "event-{$idAsset}-poster.png" : '';
                      $posterFs = $idAsset ? __DIR__ . "/../FrontOffice/images/generated/event-{$idAsset}-poster.png" : '';
                    ?>
                    <?php if (!empty($posterUrl) && file_exists($posterFs)) : ?>
                      <img class="event-asset-visual" src="<?php echo htmlspecialchars($posterUrl); ?>" alt="AI poster">
                    <?php endif; ?>
                    <span class="event-badge badge-tournament"><?php echo htmlspecialchars(($event['organizer_id'] ?? null) ? 'Organizer '.($event['organizer_id'] ?? '') : 'Event'); ?></span>
                    <h4><?php echo htmlspecialchars($event['title'] ?? ''); ?></h4>
                    <p class="event-date">📅 <?php echo htmlspecialchars($event['eventdate'] ?? ''); ?></p>
                    <p class="event-description"><?php echo nl2br(htmlspecialchars($event['description'] ?? '')); ?></p>
                    
                    <!-- Participants Section -->
                    <?php 
                      $event_id = $event['id'] ?? null;
                      $eventParticipants = isset($participantsByEvent[$event_id]) ? $participantsByEvent[$event_id] : [];
                      $participantCount = count($eventParticipants);
                      $maxParticipants = isset($event['max_participent']) && $event['max_participent'] > 0 ? (int)$event['max_participent'] : null;
                      $remainingSpots = $maxParticipants !== null ? max(0, $maxParticipants - $participantCount) : null;
                      $percentage = $maxParticipants !== null && $maxParticipants > 0 ? min(100, ($participantCount / $maxParticipants) * 100) : null;
                    ?>
                    
                    <!-- Participant Chart and Stats -->
                    <div class="participant-chart-section">
                      <div class="participant-stats-row">
                        <div class="participant-stat">
                          <span class="stat-label">Participants:</span>
                          <span class="stat-value"><?php echo $participantCount; ?></span>
                        </div>
                        <?php if ($maxParticipants !== null): ?>
                          <div class="participant-stat">
                            <span class="stat-label">Max:</span>
                            <span class="stat-value"><?php echo $maxParticipants; ?></span>
                          </div>
                          <div class="participant-stat">
                            <span class="stat-label">Remaining:</span>
                            <span class="stat-value <?php echo $remainingSpots === 0 ? 'stat-full' : ($remainingSpots <= 5 ? 'stat-low' : ''); ?>">
                              <?php echo $remainingSpots; ?>
                            </span>
                          </div>
                        <?php else: ?>
                          <div class="participant-stat">
                            <span class="stat-label">Limit:</span>
                            <span class="stat-value">Unlimited</span>
                          </div>
                        <?php endif; ?>
                      </div>
                      
                      <?php if ($maxParticipants !== null && $maxParticipants > 0): ?>
                        <div class="participant-chart-container">
                          <div class="participant-chart-bar">
                            <div class="participant-chart-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $percentage >= 100 ? '#ff6464' : ($percentage >= 80 ? '#ffaa00' : '#1aff87'); ?>;"></div>
                          </div>
                          <div class="participant-chart-label">
                            <span><?php echo number_format($percentage, 1); ?>% Full</span>
                          </div>
                        </div>

                        <!-- Chat widget removed from per-event placement; a single global widget will be rendered once per page. -->
                      <?php endif; ?>
                    </div>
                    
                    <div class="event-participants-section">
                      <button class="view-participants-btn" onclick="toggleParticipants(this)">
                        👥 View Participants <span class="participant-count-badge"><?php echo $participantCount; ?></span>
                      </button>
                      
                      <div class="participants-table-container">
                        <?php if (!empty($eventParticipants)) : ?>
                          <table class="participants-table">
                            <thead>
                              <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Action</th>
                              </tr>
                            </thead>
                            <tbody>
                              <?php foreach ($eventParticipants as $p) : ?>
                                <tr>
                                  <td><?php echo htmlspecialchars($p['username'] ?? 'Unknown'); ?></td>
                                  <td><?php echo htmlspecialchars($p['email'] ?? ''); ?></td>
                                  <td>
                                    <button class="delete-participant-btn" onclick="deleteParticipant(<?php echo $p['id'] ?? 0; ?>, this)">Remove</button>
                                  </td>
                                </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        <?php else: ?>
                          <div style="padding: 10px; color: #666; text-align: center; font-size: 0.8rem;">No participants yet</div>
                        <?php endif; ?>
                      </div>
                    </div>
                    
                    <div class="event-actions-admin">
                      <button class="chart-btn" onclick="showEventChart(<?php echo $event['id'] ?? ''; ?>, '<?php echo htmlspecialchars(addslashes($event['title'] ?? 'Event')); ?>', <?php echo $participantCount; ?>, <?php echo $maxParticipants !== null ? $maxParticipants : 'null'; ?>)">
                        📊 View Chart
                      </button>
                      <a class="edit-btn" href="event.php?edit_id=<?php echo $event['id'] ?? ''; ?>">Edit</a>
                      <a class="delete-btn" href="event.php?delete_id=<?php echo $event['id'] ?? ''; ?>" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:#ccc">No events found.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- ===== User Table ===== -->
    
    <?php include_once __DIR__ . '/chat_widget.php'; ?>

    <!-- Chart Modal -->
    <div id="chartModal" class="modal" style="display:none;">
      <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
          <h3 id="chartModalTitle">Event Chart</h3>
          <button class="close-btn" onclick="closeChartModal()">×</button>
        </div>
        <div class="chart-modal-body">
          <div class="chart-container-wrapper">
            <canvas id="participantChart"></canvas>
          </div>
          <div class="chart-stats-summary" id="chartStatsSummary"></div>
        </div>
      </div>
    </div>

    <!-- Modal for create/update -->
    <div id="eventModal" class="modal" style="display:none;">
      <div class="modal-content">
        <div class="modal-header">
          <h3 id="modalTitle"><?php echo $editEvent ? 'Edit Event' : 'Create New Event'; ?></h3>
          <button class="close-btn" onclick="closeEventModal()">×</button>
        </div>
        <form id="eventForm" method="POST" action="">
          <input type="hidden" name="action" id="actionInput" value="<?php echo $editEvent ? 'update' : 'create'; ?>">
          <input type="hidden" name="id" id="eventId" value="<?php echo htmlspecialchars($editEvent['id'] ?? ''); ?>">
          <input type="hidden" name="ai_poster_data" id="ai_poster_data">
          <input type="hidden" name="ai_banner_data" id="ai_banner_data">
          <input type="hidden" name="ai_thumbnail_data" id="ai_thumbnail_data">

          <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($editEvent['title'] ?? ''); ?>">
            <div id="error-title" style="color:#ff6464; font-size:0.9rem; margin-top:6px;"></div>
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($editEvent['description'] ?? ''); ?></textarea>
            <div id="error-description" style="color:#ff6464; font-size:0.9rem; margin-top:6px;"></div>
          </div>

          <div class="ai-builder">
            <h4>Dynamic AI-Generated Event Page</h4>
            <div class="grid">
              <div class="form-group" style="margin-bottom:10px;">
                <label for="game_type">Game Type</label>
                <input type="text" id="game_type" name="game_type" placeholder="e.g., MOBA, FPS, Battle Royale">
              </div>
              <div class="form-group" style="margin-bottom:10px;">
                <label for="tournament_format">Tournament Format</label>
                <input type="text" id="tournament_format" name="tournament_format" placeholder="e.g., Single Elimination">
              </div>
              <div class="form-group" style="margin-bottom:10px;">
                <label for="player_count">Number of Players</label>
                <input type="number" min="2" id="player_count" name="player_count" placeholder="e.g., 16">
              </div>
              <div class="form-group" style="margin-bottom:10px;">
                <label for="theme">Theme</label>
                <input type="text" id="theme" name="theme" placeholder="e.g., Cyberpunk, Fantasy">
              </div>
            </div>
            <button type="button" class="secondary-btn" id="ai-generate-btn">Generate with AI</button>
            <div class="ai-status" id="ai-status"></div>
            <div class="ai-preview-grid">
              <div class="ai-preview-card">
                <strong style="color:#ccc; font-size:0.9rem;">Poster</strong>
                <img id="ai-poster-preview" src="<?php echo htmlspecialchars($existingAssets['poster'] ?: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='); ?>" alt="Poster preview">
              </div>
              <div class="ai-preview-card">
                <strong style="color:#ccc; font-size:0.9rem;">Banner</strong>
                <img id="ai-banner-preview" src="<?php echo htmlspecialchars($existingAssets['banner'] ?: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='); ?>" alt="Banner preview">
              </div>
              <div class="ai-preview-card">
                <strong style="color:#ccc; font-size:0.9rem;">Thumbnail</strong>
                <img id="ai-thumb-preview" src="<?php echo htmlspecialchars($existingAssets['thumb'] ?: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=='); ?>" alt="Thumbnail preview">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="organizer_id">Organizer ID</label>
            <div style="display:flex; align-items:center; gap:8px;">
              <button type="button" id="organizer-decrement" style="padding:6px 8px;">−</button>
              <input type="number" id="organizer_id" name="organizer_id" min="0" value="<?php echo htmlspecialchars($editEvent['organizer_id'] ?? '0'); ?>" style="width:100px;">
              <button type="button" id="organizer-increment" style="padding:6px 8px;">+</button>
            </div>
            <div id="error-organizer" style="color:#ff6464; font-size:0.9rem; margin-top:6px;"></div>
            <div id="organizer-preview" style="color:#9fdca8; font-size:0.9rem; margin-top:6px;"></div>
          </div>

          <div class="form-group">
            <label for="eventdate">Event Date</label>
            <input type="date" id="eventdate" name="eventdate" value="<?php echo htmlspecialchars($editEvent['eventdate'] ?? ''); ?>">
            <div id="error-eventdate" style="color:#ff6464; font-size:0.9rem; margin-top:6px;"></div>
            <div id="date-preview" style="color:#ccc; font-size:0.9rem; margin-top:6px;"></div>
          </div>

          <div class="form-group">
            <label for="max_participent">Maximum Participants</label>
            <input type="number" id="max_participent" name="max_participent" min="1" value="<?php echo htmlspecialchars($editEvent['max_participent'] ?? ''); ?>" placeholder="Leave empty for unlimited">
            <div id="error-max_participent" style="color:#ff6464; font-size:0.9rem; margin-top:6px;"></div>
            <div style="color:#9fdca8; font-size:0.85rem; margin-top:4px;">Set a limit or leave empty for unlimited participants</div>
          </div>

          <div class="form-actions">
            <button type="button" class="add-event-btn" onclick="closeEventModal()">Cancel</button>
            <button type="submit" class="add-event-btn"><?php echo $editEvent ? 'Update Event' : 'Create Event'; ?></button>
          </div>
        </form>
      </div>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script src="event.js"></script>
    <script>
      let participantChartInstance = null;

      // Show event chart modal
      function showEventChart(eventId, eventTitle, participantCount, maxParticipants) {
        document.getElementById('chartModalTitle').textContent = 'Chart: ' + eventTitle;
        document.getElementById('chartModal').style.display = 'flex';
        
        // Destroy existing chart if any
        if (participantChartInstance) {
          participantChartInstance.destroy();
          participantChartInstance = null;
        }
        
        // Prepare data
        const remaining = maxParticipants !== null && maxParticipants > 0 
          ? Math.max(0, maxParticipants - participantCount) 
          : null;
        
        // Update stats summary
        const statsHtml = `
          <div class="stat-item">
            <span class="stat-label-chart">Current Participants</span>
            <span class="stat-value-chart">${participantCount}</span>
          </div>
          ${maxParticipants !== null && maxParticipants > 0 ? `
            <div class="stat-item">
              <span class="stat-label-chart">Maximum</span>
              <span class="stat-value-chart">${maxParticipants}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label-chart">Remaining Spots</span>
              <span class="stat-value-chart ${remaining === 0 ? 'stat-full-chart' : (remaining <= 5 ? 'stat-low-chart' : '')}">${remaining}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label-chart">Fill Percentage</span>
              <span class="stat-value-chart">${((participantCount / maxParticipants) * 100).toFixed(1)}%</span>
            </div>
          ` : `
            <div class="stat-item">
              <span class="stat-label-chart">Limit</span>
              <span class="stat-value-chart">Unlimited</span>
            </div>
          `}
        `;
        document.getElementById('chartStatsSummary').innerHTML = statsHtml;
        
        // Create chart
        const ctx = document.getElementById('participantChart').getContext('2d');
        
        if (maxParticipants !== null && maxParticipants > 0) {
          // Pie chart for limited events
          participantChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
              labels: ['Participants', 'Remaining Spots'],
              datasets: [{
                data: [participantCount, remaining],
                backgroundColor: [
                  participantCount >= maxParticipants ? '#ff6464' : 
                  (participantCount / maxParticipants) >= 0.8 ? '#ffaa00' : '#1aff87',
                  '#2a2a2a'
                ],
                borderColor: '#0c0c0c',
                borderWidth: 2
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: true,
              plugins: {
                legend: {
                  position: 'bottom',
                  labels: {
                    color: '#ccc',
                    font: {
                      family: 'Poppins',
                      size: 12
                    },
                    padding: 15
                  }
                },
                title: {
                  display: true,
                  text: 'Participant Distribution',
                  color: '#1aff87',
                  font: {
                    family: 'Orbitron',
                    size: 18
                  },
                  padding: {
                    top: 10,
                    bottom: 20
                  }
                },
                tooltip: {
                  backgroundColor: '#1a1a1a',
                  titleColor: '#1aff87',
                  bodyColor: '#ccc',
                  borderColor: '#1aff87',
                  borderWidth: 1,
                  padding: 12
                }
              }
            }
          });
        } else {
          // Bar chart for unlimited events
          participantChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: ['Participants'],
              datasets: [{
                label: 'Current Participants',
                data: [participantCount],
                backgroundColor: '#1aff87',
                borderColor: '#1aff87',
                borderWidth: 2
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: true,
              scales: {
                y: {
                  beginAtZero: true,
                  ticks: {
                    color: '#ccc',
                    font: {
                      family: 'Poppins'
                    }
                  },
                  grid: {
                    color: '#1aff8720'
                  }
                },
                x: {
                  ticks: {
                    color: '#ccc',
                    font: {
                      family: 'Poppins'
                    }
                  },
                  grid: {
                    color: '#1aff8720'
                  }
                }
              },
              plugins: {
                legend: {
                  display: false
                },
                title: {
                  display: true,
                  text: 'Participant Count',
                  color: '#1aff87',
                  font: {
                    family: 'Orbitron',
                    size: 18
                  },
                  padding: {
                    top: 10,
                    bottom: 20
                  }
                },
                tooltip: {
                  backgroundColor: '#1a1a1a',
                  titleColor: '#1aff87',
                  bodyColor: '#ccc',
                  borderColor: '#1aff87',
                  borderWidth: 1,
                  padding: 12
                }
              }
            }
          });
        }
      }

      // Close chart modal
      function closeChartModal() {
        document.getElementById('chartModal').style.display = 'none';
        if (participantChartInstance) {
          participantChartInstance.destroy();
          participantChartInstance = null;
        }
      }

      // Close modal when clicking outside
      window.onclick = function(event) {
        const chartModal = document.getElementById('chartModal');
        if (event.target === chartModal) {
          closeChartModal();
        }
      }
    </script>
    <script>
      // Toggle participants table visibility with smooth height/opacity animation
      function toggleParticipants(button) {
        const container = button.nextElementSibling;
        const isOpen = container.classList.contains('active');
        const badgeEl = button.querySelector('.participant-count-badge');
        const badgeText = badgeEl ? badgeEl.textContent : '0';

        if (isOpen) {
          // collapse: set explicit height then animate to 0
          container.style.maxHeight = container.scrollHeight + 'px';
          // force reflow to ensure transition
          container.offsetHeight;
          container.style.maxHeight = '0';
          container.style.opacity = '0';
          container.classList.remove('active');
          button.innerHTML = '👥 View Participants ' + '<span class="participant-count-badge">' + badgeText + '</span>';
        } else {
          // expand: set max-height to measured height
          container.classList.add('active');
          // ensure collapsed start for animation
          container.style.maxHeight = '0';
          container.style.opacity = '0';
          // force reflow
          container.offsetHeight;
          const target = container.scrollHeight + 'px';
          container.style.maxHeight = target;
          container.style.opacity = '1';
          button.innerHTML = '👥 Hide Participants ' + '<span class="participant-count-badge">' + badgeText + '</span>';
          // after transition remove max-height to allow natural resizing
          setTimeout(() => {
            if (container.classList.contains('active')) {
              container.style.maxHeight = 'none';
            }
          }, 400);
        }
      }

      // Delete participant from event
      function deleteParticipant(participationId, button) {
        if (!confirm('Are you sure you want to remove this participant?')) {
          return;
        }
        
        fetch('delete_participant.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            participation_id: participationId
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Remove the row from the table
            const row = button.closest('tr');
            row.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => {
              row.remove();
            }, 300);
            
            // Update the participant count badge
            const card = button.closest('.event-card-admin');
            const badge = card.querySelector('.participant-count-badge');
            if (badge) {
              const count = parseInt(badge.textContent) - 1;
              badge.textContent = count;
            }
          } else {
            alert('Error removing participant: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error removing participant');
        });
      }
    </script>
    <style>
      @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
      }
    </style>
    <script>
      // Backoffice chat widget logic
      (function(){
        const chatPanelAdmin = document.getElementById('chatPanel');
        const chatMessagesAdmin = document.getElementById('chatMessages');
        const chatInputAdmin = document.getElementById('chatInput');
        const chatSendAdmin = document.getElementById('chatSend');
        let chatOpenAdmin = false;

        function toggleChat() {
          if (!chatPanelAdmin) return;
          chatOpenAdmin = !chatOpenAdmin;
          if (chatOpenAdmin) {
            chatPanelAdmin.classList.add('open');
            chatPanelAdmin.setAttribute('aria-hidden','false');
            setTimeout(() => chatInputAdmin && chatInputAdmin.focus(), 200);
          } else {
            chatPanelAdmin.classList.remove('open');
            chatPanelAdmin.setAttribute('aria-hidden','true');
          }
        }

        window.toggleChat = toggleChat;

        function appendMessage(text, who='bot') {
          if (!chatMessagesAdmin) return;
          const empty = chatMessagesAdmin.querySelector('.chat-empty');
          if (empty) empty.remove();
          const wrapper = document.createElement('div');
          wrapper.className = 'msg ' + (who==='user' ? 'user' : 'bot');
          const bubble = document.createElement('span');
          bubble.className = 'bubble';
          bubble.textContent = text;
          wrapper.appendChild(bubble);
          chatMessagesAdmin.appendChild(wrapper);
          chatMessagesAdmin.scrollTop = chatMessagesAdmin.scrollHeight;
        }

        function setLoading(loading) {
          if (!chatSendAdmin || !chatInputAdmin) return;
          chatSendAdmin.disabled = loading;
          chatInputAdmin.disabled = loading;
          chatSendAdmin.textContent = loading ? '...' : 'Send';
        }

        function sendChat() {
          if (!chatInputAdmin) return;
          const text = chatInputAdmin.value.trim();
          if (!text) return;
          appendMessage(text, 'user');
          chatInputAdmin.value = '';
          setLoading(true);

          fetch('api_chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
          })
          .then(r => r.text())
          .then(raw => {
            // try parse JSON, otherwise show raw
            try {
              const data = JSON.parse(raw);
              if (typeof data === 'string') {
                appendMessage(data, 'bot');
              } else if (data && data.choices && data.choices[0] && data.choices[0].message) {
                appendMessage(data.choices[0].message.content || JSON.stringify(data), 'bot');
              } else {
                appendMessage(JSON.stringify(data), 'bot');
              }
            } catch (e) {
              appendMessage(raw, 'bot');
            }
          })
          .catch(err => {
            console.error(err);
            appendMessage('Error: could not reach assistant.', 'bot');
          })
          .finally(() => setLoading(false));
        }

        if (chatInputAdmin) {
          chatInputAdmin.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); sendChat(); } });
        }

        if (chatSendAdmin) {
          chatSendAdmin.addEventListener('click', sendChat);
        }
      })();
    </script>
    
    <?php if ($editEvent) : ?>
      <script>window.onload = function() { openEventModal(false); };</script>
    <?php endif; ?>
</body>
</html>
