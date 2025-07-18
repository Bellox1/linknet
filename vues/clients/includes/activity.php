<?php
// Fichier d'activité sidebar prêt à être inclus dynamiquement
?>
<!-- Font Awesome 6 Free -->
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="sidebar-container" id="sidebar-activity">
  <!-- Activités -->
  <div class="sidebar-section">
    <h3 class="sidebar-title" onclick="toggleSection('activity-menu', 'activity-chevron')" style="cursor:pointer;">
      <i class="fa-solid fa-bell"></i> Vos activités
      <i class="fa fa-chevron-down" id="activity-chevron" style="margin-left:auto;transition:transform 0.2s;"></i>
    </h3>
    <div class="sidebar-items" id="activity-menu">
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-purple">
          <i class="fa-solid fa-inbox"></i>
        </div>
        <span>Nouveaux messages</span>
      </a>
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-blue">
          <i class="fa-solid fa-user"></i>
        </div>
        <span>Amis en ligne</span>
      </a>
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-orange">
          <i class="fa-solid fa-calendar"></i>
        </div>
        <span>Événements</span>
      </a>
    </div>
  </div>

  <!-- Section Jeux -->
  <div class="sidebar-section">
    <h3 class="sidebar-title" onclick="toggleSection('games-menu', 'games-chevron')" style="cursor:pointer;">
      <i class="fa-solid fa-gamepad"></i> Jeux populaires
      <i class="fa fa-chevron-down" id="games-chevron" style="margin-left:auto;transition:transform 0.2s;transform:rotate(-90deg);"></i>
    </h3>
    <div class="sidebar-items" id="games-menu" style="display:none;">
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-purple">
          <i class="fa-solid fa-gamepad"></i>
        </div>
        <span>Fortnite</span>
      </a>
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-blue">
          <i class="fa-solid fa-crosshairs"></i>
        </div>
        <span>Call of Duty</span>
      </a>
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-orange">
          <i class="fa-solid fa-user-ninja"></i>
        </div>
        <span>Among Us</span>
      </a>
    </div>
  </div>

  <!-- Section Sponsors -->
  <div class="sidebar-section">
    <h3 class="sidebar-title" onclick="toggleSection('sponsors-menu', 'sponsors-chevron')" style="cursor:pointer;">
      <i class="fa-solid fa-bullhorn"></i> Sponsors
      <i class="fa fa-chevron-down" id="sponsors-chevron" style="margin-left:auto;transition:transform 0.2s;transform:rotate(-90deg);"></i>
    </h3>
    <div class="sidebar-items" id="sponsors-menu" style="display:none;">
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-black">
          <i class="fa-solid fa-running"></i>
        </div>
        <span>Nike</span>
      </a>
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-gray">
          <i class="fa-solid fa-apple-whole"></i>
        </div>
        <span>Apple</span>
      </a>
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-blue">
          <i class="fa-solid fa-mobile-screen"></i>
        </div>
        <span>Samsung</span>
      </a>
    </div>
  </div>

  <!-- Section Mini-galerie -->
  <div class="sidebar-section">
    <h3 class="sidebar-title" onclick="toggleSection('gallery-menu', 'gallery-chevron')" style="cursor:pointer;">
      <i class="fa-solid fa-image"></i> Mini-galerie
      <i class="fa fa-chevron-down" id="gallery-chevron" style="margin-left:auto;transition:transform 0.2s;transform:rotate(-90deg);"></i>
    </h3>
    <div class="sidebar-items" id="gallery-menu" style="display:none;">
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-purple">
          <i class="fa-solid fa-car"></i>
        </div>
        <span>Voiture</span>
      </a>
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-blue">
          <i class="fa-solid fa-mountain"></i>
        </div>
        <span>Montagne</span>
      </a>
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-orange">
          <i class="fa-solid fa-cat"></i>
        </div>
        <span>Chat</span>
      </a>
      <a href="#" class="sidebar-item">
        <div class="icon-container bg-teal">
          <i class="fa-solid fa-dog"></i>
        </div>
        <span>Chien</span>
      </a>
    </div>
  </div>
</div>

<!-- Bouton flottant pour mobile -->
<button id="sidebar-toggle-btn" style="display:none;position:fixed;bottom:24px;right:24px;z-index:10001;background:#7c3aed;color:#fff;border:none;border-radius:50%;width:56px;height:56px;box-shadow:0 2px 8px rgba(124,58,237,0.15);font-size:28px;align-items:center;justify-content:center;cursor:pointer;">
  <i class="fa fa-bars"></i>
</button>

<style>
.sidebar-container {
  width: 300px;
  padding: 15px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  margin-left: auto;
  margin-right: 20px;
}

.sidebar-section {
  margin-bottom: 25px;
}

.sidebar-section:last-child {
  margin-bottom: 0;
}

.sidebar-title {
  font-size: 16px;
  color: #333;
  margin: 0 0 15px 0;
  padding-bottom: 8px;
  border-bottom: 1px solid #eee;
  display: flex;
  align-items: center;
  gap: 8px;
}

.sidebar-title i {
  color: #7c3aed;
}

.sidebar-items {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.sidebar-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px;
  border-radius: 8px;
  transition: all 0.2s ease;
  text-decoration: none;
  color: #333;
}

.sidebar-item:hover {
  background: #f5f3ff;
  transform: translateX(3px);
}

.icon-container {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 18px;
}

/* Couleurs */
.bg-purple { background-color: #805ad5; }
.bg-blue   { background-color: #3182ce; }
.bg-orange { background-color: #dd6b20; }
.bg-teal   { background-color: #319795; }
.bg-red    { background-color: #e53e3e; }
.bg-yellow { background-color: #d69e2e; }

/* Images */
.game-icon, .sponsor-icon, .thumb-item img {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  object-fit: cover;
}

.sidebar-item span {
  font-size: 14px;
  font-weight: 500;
}

.thumbs {
  flex-direction: row;
  gap: 8px;
}
.thumb-item {
  display: block;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar-container {
    width: 100%;
    margin-right: 0;
    margin-bottom: 20px;
  }
  
  .sidebar-items {
    flex-direction: row;
    flex-wrap: wrap;
  }
  
  .sidebar-item {
    flex-direction: column;
    text-align: center;
    width: calc(33% - 10px);
  }
}

@media (max-width: 480px) {
  .sidebar-item {
    width: calc(50% - 10px);
  }
}
.bg-black  { background-color: #000; }
.bg-gray   { background-color: #777; }

@media (max-width: 900px) {
  #sidebar-activity {
    display: none !important;
  }
  #sidebar-toggle-btn {
    display: flex !important;
  }
}
</style>

<script>
function toggleSection(menuId, chevronId) {
  const menu = document.getElementById(menuId);
  const chevron = document.getElementById(chevronId);
  if (menu.style.display === 'none') {
    menu.style.display = '';
    chevron.style.transform = 'rotate(0deg)';
  } else {
    menu.style.display = 'none';
    chevron.style.transform = 'rotate(-90deg)';
  }
}

// Mobile : ouvrir/fermer toute la sidebar
const sidebar = document.getElementById('sidebar-activity');
const toggleBtn = document.getElementById('sidebar-toggle-btn');
if (toggleBtn) {
  toggleBtn.addEventListener('click', function() {
    if (sidebar.style.display === 'block') {
      sidebar.style.display = 'none';
    } else {
      sidebar.style.display = 'block';
      sidebar.style.position = 'fixed';
      sidebar.style.top = '0';
      sidebar.style.right = '0';
      sidebar.style.height = '100vh';
      sidebar.style.zIndex = '10000';
      sidebar.style.background = '#fff';
      sidebar.style.boxShadow = '0 2px 16px rgba(124,58,237,0.10)';
      sidebar.style.overflowY = 'auto';
      sidebar.style.maxWidth = '90vw';
    }
  });
}
// Fermer la sidebar si on clique en dehors (mobile)
document.addEventListener('click', function(e) {
  if (window.innerWidth <= 900 && sidebar && sidebar.style.display === 'block') {
    if (!sidebar.contains(e.target) && e.target !== toggleBtn) {
      sidebar.style.display = 'none';
    }
  }
});
</script>
