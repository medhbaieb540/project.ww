// assets/js/tournaments.js

document.addEventListener("DOMContentLoaded", function () {

  // ========== COMMON ELEMENTS ==========
  var cards = document.querySelectorAll(".tournament-card");

  // ===== DETAILS MODAL =====
  var detailsModal    = document.getElementById("detailsModal");
  var closeDetailsBtn = document.getElementById("closeDetails");

  var detailsImage     = document.getElementById("detailsImage");
  var detailsTitle     = document.getElementById("detailsTitle");
  var detailsStatus    = document.getElementById("detailsStatus");
  var detailsCountdown = document.getElementById("detailsCountdown");
  var detailsPrize     = document.getElementById("detailsPrize");
  var detailsPlayers   = document.getElementById("detailsPlayers");

  var joinBtnPopup     = document.getElementById("joinBtnPopup");
  var leaveBtnPopup    = document.getElementById("leaveBtnPopup");
  var spectateBtnPopup = document.getElementById("spectateBtnPopup");

  var actionForm       = document.getElementById("actionForm");
  var actionTypeInput  = document.getElementById("actionType");
  var actionTidInput   = document.getElementById("actionTournamentId");

  var activeCard = null;
  var popupCountdownInterval = null;

  // ====== INIT CARDS ======
  cards.forEach(function (card) {
    var status = card.getAttribute("data-status");

    if (status === "upcoming") {
      initCardCountdown(card);
    }

    var checkBtn = card.querySelector(".check-btn");
    if (checkBtn) {
      checkBtn.addEventListener("click", function () {
        activeCard = card;
        openDetails(card);
      });
    }
  });

  // ====== CARD COUNTDOWN ======
  function initCardCountdown(card) {
    var countdownEl = card.querySelector(".countdown");
    var startStr    = card.getAttribute("data-start");
    if (!countdownEl || !startStr) return;

    var target = new Date(startStr).getTime();
    if (isNaN(target)) {
      countdownEl.textContent = "Starts in: --h --m --s";
      return;
    }

    function update() {
      var now  = Date.now();
      var diff = target - now;

      if (diff <= 0) {
        countdownEl.textContent = "Starts soon...";
        return;
      }

      var sec = Math.floor(diff / 1000);
      var h   = Math.floor(sec / 3600);
      sec    -= h * 3600;
      var m   = Math.floor(sec / 60);
      sec    -= m * 60;

      countdownEl.textContent =
        "Starts in: " + h + "h " + m + "m " + sec + "s";
    }

    update();
    setInterval(update, 1000);
  }

  // ====== PLAYERS PARSE ======
  function parsePlayers(card) {
    var el = card.querySelector(".players");
    if (!el) return { current: 0, max: 0 };

    var text = el.textContent || "";
    var parts = text.split(":");
    if (parts.length < 2) return { current: 0, max: 0 };

    var nums = parts[1].replace(/\s+/g, "").split("/");
    if (nums.length < 2) return { current: 0, max: 0 };

    var current = parseInt(nums[0], 10);
    var max     = parseInt(nums[1], 10);
    if (isNaN(current)) current = 0;
    if (isNaN(max))     max     = 0;

    return { current: current, max: max };
  }

  function setPlayers(card, current, max) {
    var el = card.querySelector(".players");
    if (!el) return;
    el.textContent = "Players: " + current + " / " + max;
  }

  // ====== UI HELPERS (FIX JOIN/LEAVE) ======
  function setJoinedOnCard(card, joined) {
    card.setAttribute("data-joined", joined ? "1" : "0");
  }

  function updateModalButtonsForUpcoming(card) {
    var joined = card.getAttribute("data-joined") === "1";
    var players = parsePlayers(card);

    spectateBtnPopup.style.display = "none";

    if (joined) {
      joinBtnPopup.style.display  = "none";
      leaveBtnPopup.style.display = "inline-block";
    } else {
      // if full, hide join
      if (players.max > 0 && players.current >= players.max) {
        joinBtnPopup.style.display = "none";
      } else {
        joinBtnPopup.style.display = "inline-block";
      }
      leaveBtnPopup.style.display = "none";
    }
  }

  // ====== OPEN DETAILS POPUP ======
  function openDetails(card) {
    if (!detailsModal) return;

    var imgEl     = card.querySelector("img");
    var titleEl   = card.querySelector("h3");
    var badgeEl   = card.querySelector(".badge");
    var prizeEl   = card.querySelector(".prize");
    var playersEl = card.querySelector(".players");

    var status  = card.getAttribute("data-status") || "";
    var start   = card.getAttribute("data-start") || "";
    var players = parsePlayers(card);

    detailsImage.src          = imgEl ? imgEl.src : "";
    detailsTitle.textContent  = titleEl ? titleEl.textContent : "Tournament";
    detailsStatus.textContent = "Status: " + (badgeEl ? badgeEl.textContent : status);
    detailsPrize.textContent  = prizeEl ? prizeEl.textContent : "Prize: -";

    if (playersEl && status !== "finished") {
      detailsPlayers.textContent = "Players: " + players.current + " / " + players.max;
    } else {
      detailsPlayers.textContent = "Players: - / -";
    }

    if (status === "upcoming") {
      updateModalButtonsForUpcoming(card);

    } else if (status === "live") {
      joinBtnPopup.style.display     = "none";
      leaveBtnPopup.style.display    = "none";
      spectateBtnPopup.style.display = "inline-block";

    } else {
      joinBtnPopup.style.display     = "none";
      leaveBtnPopup.style.display    = "none";
      spectateBtnPopup.style.display = "none";
    }

    if (popupCountdownInterval) {
      clearInterval(popupCountdownInterval);
      popupCountdownInterval = null;
    }

    if (status === "upcoming" && start) {
      initPopupCountdown(start);
    } else {
      detailsCountdown.textContent = "";
    }

    detailsModal.style.display = "flex";
  }

  function initPopupCountdown(startStr) {
    var target = new Date(startStr).getTime();
    if (isNaN(target)) {
      detailsCountdown.textContent = "Starts in: --h --m --s";
      return;
    }

    function update() {
      var now  = Date.now();
      var diff = target - now;

      if (diff <= 0) {
        detailsCountdown.textContent = "Starts soon...";
        return;
      }

      var sec = Math.floor(diff / 1000);
      var h   = Math.floor(sec / 3600);
      sec    -= h * 3600;
      var m   = Math.floor(sec / 60);
      sec    -= m * 60;

      detailsCountdown.textContent =
        "Starts in: " + h + "h " + m + "m " + sec + "s";
    }

    update();
    popupCountdownInterval = setInterval(update, 1000);
  }

  // ====== CLOSE DETAILS MODAL ======
  if (closeDetailsBtn) {
    closeDetailsBtn.addEventListener("click", function () {
      detailsModal.style.display = "none";
    });
  }

  window.addEventListener("click", function (e) {
    if (e.target === detailsModal) {
      detailsModal.style.display = "none";
    }
  });

  // ====== JOIN / LEAVE (FIXED) ======
  if (joinBtnPopup) {
    joinBtnPopup.addEventListener("click", function () {
      if (!activeCard || !actionForm) return;

      var status = activeCard.getAttribute("data-status");
      if (status !== "upcoming") return;

      var players = parsePlayers(activeCard);
      if (players.max > 0 && players.current >= players.max) {
        alert("This tournament is full.");
        return;
      }

      // ✅ update UI instantly
      setJoinedOnCard(activeCard, true);
      setPlayers(activeCard, players.current + 1, players.max);
      detailsPlayers.textContent = "Players: " + (players.current + 1) + " / " + players.max;
      updateModalButtonsForUpcoming(activeCard);

      // submit to PHP
      actionTypeInput.value = "join";
      actionTidInput.value  = activeCard.getAttribute("data-id");
      actionForm.submit();
    });
  }

  if (leaveBtnPopup) {
    leaveBtnPopup.addEventListener("click", function () {
      if (!activeCard || !actionForm) return;

      var status = activeCard.getAttribute("data-status");
      if (status !== "upcoming") return;

      var players = parsePlayers(activeCard);

      // ✅ update UI instantly
      setJoinedOnCard(activeCard, false);
      setPlayers(activeCard, Math.max(0, players.current - 1), players.max);
      detailsPlayers.textContent =
        "Players: " + Math.max(0, players.current - 1) + " / " + players.max;
      updateModalButtonsForUpcoming(activeCard);

      // submit to PHP
      actionTypeInput.value = "leave";
      actionTidInput.value  = activeCard.getAttribute("data-id");
      actionForm.submit();
    });
  }

  if (spectateBtnPopup) {
    spectateBtnPopup.addEventListener("click", function () {
      alert("Spectate mode is only visual in this project.");
    });
  }

  // ====== ADD MODAL OPEN/CLOSE ======
  var addModal    = document.getElementById("addModal");
  var openAddBtn  = document.getElementById("openAddModal");
  var closeAddBtn = document.getElementById("closeAdd");

  if (openAddBtn && addModal) {
    openAddBtn.addEventListener("click", function () {
      addModal.style.display = "flex";
    });
  }
  if (closeAddBtn && addModal) {
    closeAddBtn.addEventListener("click", function () {
      addModal.style.display = "none";
    });
  }
  window.addEventListener("click", function (e) {
    if (e.target === addModal) {
      addModal.style.display = "none";
    }
  });

  /* ===========================
     FRONT SEARCH + FILTER
  ============================ */
  var searchInput  = document.getElementById("searchTournaments");
  var statusSelect = document.getElementById("statusFilter");

  function applyFrontFilters() {
    var text   = searchInput ? searchInput.value.toLowerCase() : "";
    var status = statusSelect ? statusSelect.value : "all";

    cards.forEach(function (card) {
      var nameEl = card.querySelector("h3");
      var name   = nameEl ? nameEl.textContent.toLowerCase() : "";
      var cardStatus = card.getAttribute("data-status") || "";

      var ok = true;

      if (text && !name.includes(text)) ok = false;
      if (status !== "all" && cardStatus !== status) ok = false;

      card.style.display = ok ? "" : "none";
    });
  }

  if (searchInput)  searchInput.addEventListener("input", applyFrontFilters);
  if (statusSelect) statusSelect.addEventListener("change", applyFrontFilters);

  // ====== ADD FORM VALIDATION ======
  var addForm  = addModal ? addModal.querySelector("form") : null;

  if (addForm) {
    var titleInput = addForm.querySelector("input[name='title']");
    var startInput = addForm.querySelector("input[name='start']");
    var rewardSel  = addForm.querySelector("select[name='reward_id']");
    var maxPlayers = addForm.querySelector("input[name='max_players']");

    addForm.addEventListener("submit", function (e) {
      var errors = [];

      if (!titleInput || titleInput.value.trim() === "") {
        errors.push("Tournament title is required.");
      }

      if (!startInput || startInput.value.trim() === "") {
        errors.push("Start date/time is required.");
      } else {
        var chosen = new Date(startInput.value);
        var now    = new Date();

        if (isNaN(chosen.getTime())) {
          errors.push("Invalid start date/time.");
        } else if (chosen.getTime() <= now.getTime()) {
          errors.push("Start date/time must be in the FUTURE.");
        }
      }

      if (!rewardSel || !rewardSel.value) {
        errors.push("Please select a reward.");
      }

      if (maxPlayers && maxPlayers.value !== "") {
        var mp = parseInt(maxPlayers.value, 10);
        if (isNaN(mp) || mp < 2) {
          errors.push("Max players must be at least 2.");
        }
      }

      if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join("\n"));
      }
    });
  }

});
