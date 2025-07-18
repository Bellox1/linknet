<?php require_once "../includes/header.php"; ?>

<div class="fb-profile-container">
  <!-- Cover Photo Section -->
  <div class="fb-cover-container">
    <div class="fb-cover-photo">
      <img id="cover-photo" src="/assets/images/default_banner.jpg" alt="Cover Photo">
      <div class="fb-cover-actions">
        <button class="fb-btn fb-btn-secondary">
          <i class="fas fa-camera"></i> Ajouter une photo de couverture
        </button>
      </div>
    </div>
  </div>

  <!-- Profile Info Section -->
  <div class="fb-profile-info">
    <div class="fb-profile-picture-container">
      <div class="fb-profile-picture">
        <img id="profile-picture" src="/vues/back-office/uploads/default_profile.jpg" alt="Profile Picture">
        <button class="fb-profile-picture-edit">
          <i class="fas fa-camera"></i>
        </button>
      </div>
      <div class="fb-profile-edit-btn" style="text-align:center;margin-top:10px;">
        <button id="editProfileBtn" class="fb-btn fb-btn-primary">
          <i class="fas fa-pencil-alt"></i> Modifier le profil
        </button>
      </div>
    </div>
    <div class="fb-profile-details">
      <h1 class="fb-profile-name" id="profile-username"></h1>
      <p class="fb-profile-bio" id="profile-bio"></p>
      <div class="fb-profile-stats">
        <div class="fb-stat-item">
          <i class="fas fa-user-friends"></i>
          <span id="friends-count"></span> amis
        </div>
        <div class="fb-stat-item">
          <i class="fas fa-users"></i>
          <span id="followers-count"></span> abonnés
        </div>
        <div class="fb-stat-item">
          <i class="fas fa-user-plus"></i>
          <span id="following-count"></span> abonnements
        </div>
        <div class="fb-stat-item">
          <i class="fas fa-clock"></i>
          <span id="member-since"></span>
        </div>
      </div>
      <div class="fb-profile-actions">
        <button class="fb-btn fb-btn-secondary">
          <i class="fas fa-ellipsis-h"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Profile Navigation -->
  <nav class="fb-profile-nav">
    <ul>
      <li class="active"><a href="#"><i class="fas fa-newspaper"></i> Publications</a></li>
      <li><a href="#"><i class="fas fa-info-circle"></i> À propos</a></li>
      <li><a href="#"><i class="fas fa-users"></i> Amis</a></li>
      <li><a href="#"><i class="fas fa-images"></i> Photos</a></li>
      <li><a href="#"><i class="fas fa-video"></i> Vidéos</a></li>
      <li><a href="#"><i class="fas fa-check-circle"></i> Évènements</a></li>
    </ul>
  </nav>

  <!-- Main Content -->
  <div class="fb-profile-content">
    <!-- Left Sidebar -->
    <aside class="fb-profile-sidebar">
      <div class="fb-sidebar-card">
        <h3><i class="fas fa-user-circle"></i> Intro</h3>
        <div class="fb-intro-item" id="profile-birthday">
          <i class="fas fa-birthday-cake"></i>
          <span>Date de naissance non renseignée</span>
        </div>
        <div class="fb-intro-item">
          <i class="fas fa-history"></i>
          <span id="member-since-sidebar"></span>
        </div>
        <button id="editInfoBtn" class="fb-edit-intro">
          <i class="fas fa-pencil-alt"></i> Modifier les infos
        </button>
      </div>
      <div class="fb-sidebar-card">
        <div class="fb-friends-header">
          <h3><i class="fas fa-user-friends"></i> Amis</h3>
          <a href="/vues/clients/friends/followers.php" class="fb-see-all">Tout voir</a>
        </div>
        <div class="fb-friends-grid" id="friends-grid"></div>
      </div>
      <div class="fb-sidebar-card">
        <div class="fb-followers-header">
          <h3><i class="fas fa-users"></i> Abonnés</h3>
        </div>
        <div class="fb-followers-grid" id="followers-grid"></div>
      </div>
    </aside>
    <!-- Main Posts Area -->
    <main class="fb-profile-posts">
      <!-- Create Post (optionnel pour le propriétaire du profil) -->
      <div class="fb-create-post">
        <div class="fb-post-header">
          <img id="create-post-picture" src="/vues/back-office/uploads/default_profile.jpg" alt="You">
          <input id="create-post-input" type="text" placeholder="Quoi de neuf ?">
        </div>
        <div class="fb-post-actions">
          <button class="fb-post-action">
            <i class="fas fa-video" style="color:#f3425f;"></i> Vidéo en direct
          </button>
          <button class="fb-post-action">
            <i class="fas fa-images" style="color:#45bd62;"></i> Photo/vidéo
          </button>
          <button class="fb-post-action">
            <i class="fas fa-smile" style="color:#f7b928;"></i> Humeur/activité
          </button>
        </div>
      </div>
      <!-- Posts Container -->
      <div id="posts-container"></div>
    </main>
  </div>
</div>

<!-- Modal d'édition du profil (bio, photo) -->
<div id="editProfileModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Modifier le profil</h2>
      <button id="closeEditModal" class="modal-close">&times;</button>
    </div>
    <form id="editProfileForm">
      <div class="form-group">
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" placeholder="Votre bio..."></textarea>
      </div>
      <div class="form-group">
        <label for="profile_picture">Photo de profil</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
      </div>
      <div class="form-actions">
        <button type="button" class="btn-cancel" id="cancelEdit">Annuler</button>
        <button type="submit" class="btn-save">Enregistrer</button>
      </div>
      <div id="editProfileMsg"></div>
    </form>
  </div>
</div>

<!-- Modal d'édition des infos principales -->
<div id="editInfoModal" class="modal-overlay" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Modifier les infos</h2>
      <button id="closeInfoModal" class="modal-close">&times;</button>
    </div>
    <form id="editInfoForm">
      <div class="form-group">
        <label for="username">Nom d'utilisateur</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="birthday">Date de naissance</label>
        <input type="date" id="birthday" name="birthday">
      </div>
      <div class="form-group">
        <label for="bio-info">Bio</label>
        <textarea id="bio-info" name="bio"></textarea>
      </div>
      <div class="form-actions">
        <button type="button" class="btn-cancel" id="cancelInfo">Annuler</button>
        <button type="submit" class="btn-save">Enregistrer</button>
      </div>
      <div id="editInfoMsg"></div>
    </form>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/profile.css">
<script>
document.addEventListener('DOMContentLoaded', function() {
  fetch('https://linknet.wuaze.com/api/profile.php')
    .then(response => response.json())
    .then(data => {
      if (data && data.data && data.data.user) {
        renderProfile(data.data.user, data.data.friends, data.data.followers);
        renderFriends(data.data.friends);
        renderFollowers(data.data.followers);
        renderPosts(data.data.posts); // Les posts sont déjà enrichis
      } else {
        showError('Erreur lors du chargement du profil');
      }
    })
    .catch(() => {
      showError('Impossible de charger le profil');
    });

  function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'fb-error-message';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    document.querySelector('.fb-profile-content').prepend(errorDiv);
  }

  function renderProfile(user, friends, followers, cacheBuster = null) {
    const bust = cacheBuster ? `?v=${cacheBuster}` : '';
    document.getElementById('profile-username').textContent = capitalizeFirst(user.username);
    document.getElementById('profile-bio').textContent = user.bio || '';
    document.getElementById('profile-picture').src = '/vues/back-office/uploads/' + (user.profile_picture || 'default_profile.jpg');
    document.getElementById('create-post-picture').src = '/vues/back-office/uploads/' + (user.profile_picture || 'default_profile.jpg');
    document.getElementById('create-post-input').placeholder = 'Quoi de neuf, ' + (capitalizeFirst(user.username) || '') + ' ?';

    const createdAt = new Date(user.created_at);
    const memberSince = `Membre depuis ${createdAt.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long' })}`;
    document.getElementById('member-since').textContent = memberSince;
    document.getElementById('member-since-sidebar').textContent = memberSince;

    // Stats
    document.getElementById('friends-count').textContent = friends ? friends.length : 0;
    document.getElementById('followers-count').textContent = user.followers_count !== undefined ? user.followers_count : (followers ? followers.length : 0);
    document.getElementById('following-count').textContent = user.following_count !== undefined ? user.following_count : 0;

    // Birthday
    if (user.birthday) {
      const birthday = new Date(user.birthday);
      const birthdayStr = birthday.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });
      document.getElementById('profile-birthday').innerHTML = `
        <i class="fas fa-birthday-cake"></i>
        <span>Né(e) le ${birthdayStr}</span>
      `;
    }
  }

  function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  function renderFriends(friends) {
    const friendsGrid = document.getElementById('friends-grid');
    friendsGrid.innerHTML = '';
    if (!friends || friends.length === 0) {
      friendsGrid.innerHTML = '<p class="fb-no-friends">Aucun ami pour le moment</p>';
      return;
    }
    const friendsToShow = friends.slice(0, 9);
    friendsToShow.forEach(friend => {
      const friendElement = document.createElement('div');
      friendElement.className = 'fb-friend-item';
      const friendName = capitalizeFirst(friend.username);
      friendElement.innerHTML = `
        <a href="#" data-user-id="${friend.id}">
          <img src="/vues/back-office/uploads/${friend.profile_picture || 'default_profile.jpg'}" alt="${friendName}">
          <span>${friendName}</span>
        </a>
      `;
      friendsGrid.appendChild(friendElement);
    });
    // Ajoute l'event pour rediriger vers le profil de l'ami
    friendsGrid.querySelectorAll('a[data-user-id]').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const userId = this.getAttribute('data-user-id');
        const currentUserId = <?php echo $_SESSION['user']; ?>;
        // Rediriger vers le bon profil selon l'utilisateur
        const profileLink = (currentUserId == userId) 
          ? '/vues/clients/user/profile.php'
          : '/vues/clients/user/users_profile.php?user_id=' + userId;
        window.location.href = profileLink;
      });
    });
  }

  function renderFollowers(followers) {
    const followersGrid = document.getElementById('followers-grid');
    followersGrid.innerHTML = '';
    if (!followers || followers.length === 0) {
      followersGrid.innerHTML = '<p class="fb-no-followers">Aucun abonné pour le moment</p>';
      return;
    }
    const followersToShow = followers.slice(0, 9);
    followersToShow.forEach(follower => {
      const followerElement = document.createElement('div');
      followerElement.className = 'fb-follower-item';
      const followerName = capitalizeFirst(follower.username);
      followerElement.innerHTML = `
        <a href="#" data-user-id="${follower.id}">
          <img src="/vues/back-office/uploads/${follower.profile_picture || 'default_profile.jpg'}" alt="${followerName}">
          <span>${followerName}</span>
        </a>
      `;
      followersGrid.appendChild(followerElement);
    });
    // Ajoute l'event pour rediriger vers le profil du follower
    followersGrid.querySelectorAll('a[data-user-id]').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        const userId = this.getAttribute('data-user-id');
        const currentUserId = <?php echo $_SESSION['user']; ?>;
        // Rediriger vers le bon profil selon l'utilisateur
        const profileLink = (currentUserId == userId) 
          ? '/vues/clients/user/profile.php'
          : '/vues/clients/user/users_profile.php?user_id=' + userId;
        window.location.href = profileLink;
      });
    });
  }

  function renderPosts(posts) {
    const postsContainer = document.getElementById('posts-container');
    postsContainer.innerHTML = '';
    if (!posts || posts.length === 0) {
      postsContainer.innerHTML = `
        <div class="fb-no-posts">
          <i class="fas fa-newspaper"></i>
          <p>Aucune publication pour le moment</p>
        </div>
      `;
      return;
    }
    posts.forEach(post => {
      const postElement = document.createElement('div');
      postElement.className = 'fb-post';
      // Format date
      const postDate = new Date(post.post_created_at);
      const dateStr = postDate.toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        hour: '2-digit',
        minute: '2-digit'
      });
      // Media content
      let mediaContent = '';
      if (post.post_media) {
        const mediaPath = post.post_media.includes('Posts/') ? post.post_media.substring(post.post_media.indexOf('Posts/')) : post.post_media;
        if (/\.(mp4|mov|avi)$/i.test(post.post_media)) {
          mediaContent = `
            <div class="fb-post-media">
              <video controls>
                <source src="/vues/back-office/uploads/${mediaPath}" type="video/mp4">
              </video>
            </div>
          `;
        } else {
          mediaContent = `
            <div class="fb-post-media">
              <img src="/vues/back-office/uploads/${mediaPath}" alt="Post image">
            </div>
          `;
        }
      }
      // Hashtags
      let hashtagsHtml = '';
      if (post.hashtags && post.hashtags.length > 0) {
        hashtagsHtml = '<div class="fb-post-hashtags">' + post.hashtags.map(h => `<span class="hashtag">#${h.hashtag_name}</span>`).join(' ') + '</div>';
      }
      // Déterminer le bon lien selon l'utilisateur
      const postUserId = post.user_id;
      const currentUserId = <?php echo $_SESSION['user']; ?>;
      const postLink = (currentUserId == postUserId) 
        ? '/vues/clients/user/profile.php'
        : `/vues/clients/user/users_profile.php?user_id=${postUserId}`;
      postElement.innerHTML = `
        <div class="fb-post-header">
          <a href="${postLink}" style="text-decoration: none; color: inherit;">
            <img src="/vues/back-office/uploads/${post.user_profile_picture || 'default_profile.jpg'}" alt="${capitalizeFirst(post.user_username)}" style="cursor: pointer;">
          </a>
          <div class="fb-post-author">
            <a href="${postLink}" style="text-decoration: none; color: inherit;">
              <strong>${capitalizeFirst(post.user_username)}</strong>
            </a>
            <span>${dateStr}</span>
          </div>
          <button class="fb-post-options">
            <i class="fas fa-ellipsis-h"></i>
          </button>
        </div>
        <div class="fb-post-content">
          <p>${post.post_content}</p>
          ${hashtagsHtml}
          ${mediaContent}
        </div>
        <div class="fb-post-actions">
          <button class="fb-post-action">
            <i class="far fa-thumbs-up"></i> J'aime (${post.likes_count})
          </button>
          <button class="fb-post-action">
            <i class="far fa-comment"></i> Commenter (${post.comments_count})
          </button>
          <button class="fb-post-action">
            <i class="fas fa-share"></i> Partager
          </button>
        </div>
      `;
      postsContainer.appendChild(postElement);
    });
  }

  // Gestion des modals d'édition
  const modal = document.getElementById('editProfileModal');
  const openBtn = document.getElementById('editProfileBtn');
  const closeBtn = document.getElementById('closeEditModal');
  const cancelBtn = document.getElementById('cancelEdit');
  const form = document.getElementById('editProfileForm');
  
  // Modal d'édition des infos
  const infoModal = document.getElementById('editInfoModal');
  const infoOpenBtn = document.getElementById('editInfoBtn');
  const infoCloseBtn = document.getElementById('closeInfoModal');
  const infoCancelBtn = document.getElementById('cancelInfo');
  const infoForm = document.getElementById('editInfoForm');
  
  // Ouverture au clic sur le bouton
  if (openBtn) {
    openBtn.addEventListener('click', function() {
        modal.style.display = 'flex';
        loadUserData();
    });
  }
  
  // Fermeture
  function closeModal() {
      modal.style.display = 'none';
      form.reset();
      document.getElementById('editProfileMsg').innerHTML = '';
  }
  
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
  
  // Fermer en cliquant à l'extérieur
  modal.addEventListener('click', function(e) {
      if (e.target === modal) {
          closeModal();
      }
  });
  
  // Charger les données utilisateur pour la modal profil
  function loadUserData() {
      fetch('https://linknet.wuaze.com/api/profile_update.php', {
          method: 'GET'
      })
      .then(response => response.json())
      .then(data => {
          if (data.status === 'success' && data.user) {
              document.getElementById('bio').value = data.user.bio || '';
          }
      })
      .catch(error => {
          console.error('Erreur lors de la récupération des données:', error);
      });
  }
  
  // === MODAL D'ÉDITION DES INFOS ===
  
  // Ouvrir la modal des infos
  if (infoOpenBtn) {
    infoOpenBtn.addEventListener('click', function() {
        infoModal.style.display = 'flex';
        loadUserInfoData();
    });
  }
  
  // Fermer la modal des infos
  function closeInfoModal() {
      infoModal.style.display = 'none';
      infoForm.reset();
      document.getElementById('editInfoMsg').innerHTML = '';
  }
  
  if (infoCloseBtn) infoCloseBtn.addEventListener('click', closeInfoModal);
  if (infoCancelBtn) infoCancelBtn.addEventListener('click', closeInfoModal);
  
  // Fermer en cliquant à l'extérieur
  infoModal.addEventListener('click', function(e) {
      if (e.target === infoModal) {
          closeInfoModal();
      }
  });
  
  // Charger toutes les données utilisateur pour la modal infos
  function loadUserInfoData() {
      fetch('https://linknet.wuaze.com/api/profile.php')
      .then(response => response.json())
      .then(data => {
          if (data && data.data && data.data.user) {
              const user = data.data.user;
              document.getElementById('username').value = user.username || '';
              document.getElementById('email').value = user.email || '';
              document.getElementById('birthday').value = user.birthday || '';
              document.getElementById('bio-info').value = user.bio || '';
          }
      })
      .catch(error => {
          console.error('Erreur lors de la récupération des données:', error);
      });
  }
  
  // Gestion de la soumission du formulaire
  if (form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const saveBtn = form.querySelector('.btn-save');
        const msgDiv = document.getElementById('editProfileMsg');
        
        // Désactiver le bouton et afficher loading
        saveBtn.disabled = true;
        saveBtn.textContent = 'Enregistrement...';

        fetch('https://linknet.wuaze.com/api/profile_update.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                msgDiv.innerHTML = '<span class="success-msg">' + data.message + '</span>';
                const cacheBuster = Date.now();
                setTimeout(() => {
                    fetch('https://linknet.wuaze.com/api/profile.php')
                      .then(response => response.json())
                      .then(data => {
                          if (data && data.data && data.data.user) {
                              renderProfile(data.data.user, data.data.friends, data.data.followers, cacheBuster);
                          }
                      });
                    closeModal();
                }, 800);
            } else {
                msgDiv.innerHTML = '<span class="error-msg">' + (data.message || 'Erreur inconnue') + '</span>';
            }
        })
        .catch(() => {
            msgDiv.innerHTML = '<span class="error-msg">Erreur réseau</span>';
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Enregistrer';
        });
    });
  }
  
  // Gestion de la soumission du formulaire des infos
  if (infoForm) {
    infoForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const saveBtn = infoForm.querySelector('.btn-save');
        const msgDiv = document.getElementById('editInfoMsg');
        
        // Désactiver le bouton et afficher loading
        saveBtn.disabled = true;
        saveBtn.textContent = 'Enregistrement...';

        fetch('https://linknet.wuaze.com/api/profile_update.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                msgDiv.innerHTML = '<span class="success-msg">' + data.message + '</span>';
                // Recharge dynamiquement les infos utilisateur
                fetch('https://linknet.wuaze.com/api/profile.php')
                  .then(response => response.json())
                  .then(data => {
                      if (data && data.data && data.data.user) {
                          renderProfile(data.data.user, data.data.friends, data.data.followers);
                      }
                  });
                // Ferme la modal après un court délai
                setTimeout(() => {
                    closeInfoModal();
                }, 1000);
            } else {
                msgDiv.innerHTML = '<span class="error-msg">' + (data.message || 'Erreur inconnue') + '</span>';
            }
        })
        .catch(() => {
            msgDiv.innerHTML = '<span class="error-msg">Erreur réseau</span>';
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Enregistrer';
        });
    });
  }
});
</script>