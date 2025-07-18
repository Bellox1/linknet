<?php require_once "../includes/header.php"; ?>

<div class="fb-profile-container">
  <!-- Cover Photo Section -->
  <div class="fb-cover-container">
    <div class="fb-cover-photo">
      <img id="cover-photo" src="/vues/back-office/uploads/default_banner.jpg" alt="Cover Photo">
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
        <button class="fb-btn fb-btn-primary">
          <i class="fas fa-pencil-alt"></i> Modifier le profil
        </button>
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
        <button class="fb-edit-intro">
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
      <!-- Create Post -->
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
        renderPosts(data.data.posts);
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

  function renderProfile(user, friends, followers) {
    document.getElementById('profile-username').textContent = user.username;
    document.getElementById('profile-bio').textContent = user.bio || '';
    document.getElementById('profile-picture').src = '/vues/back-office/uploads/' + (user.profile_picture || 'default_profile.jpg');
    document.getElementById('create-post-picture').src = '/vues/back-office/uploads/' + (user.profile_picture || 'default_profile.jpg');
    document.getElementById('create-post-input').placeholder = 'Quoi de neuf, ' + (user.username || '') + ' ?';

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

  function renderFriends(friends) {
    const friendsGrid = document.getElementById('friends-grid');
    friendsGrid.innerHTML = '';
    if (!friends || friends.length === 0) {
      friendsGrid.innerHTML = '<p class="fb-no-friends">Aucun ami pour le moment</p>';
      return;
    }
    // Show max 9 friends
    const friendsToShow = friends.slice(0, 9);
    friendsToShow.forEach(friend => {
      const friendElement = document.createElement('div');
      friendElement.className = 'fb-friend-item';
      friendElement.innerHTML = `
        <img src="/vues/back-office/uploads/${friend.profile_picture || 'default_profile.jpg'}" alt="${friend.username}">
        <span style="display:block;text-align:center;font-size:0.98rem;color:#333;font-weight:500;margin-top:4px;">${friend.username}</span>
      `;
      friendsGrid.appendChild(friendElement);
    });
  }

  function renderFollowers(followers) {
    const followersGrid = document.getElementById('followers-grid');
    followersGrid.innerHTML = '';
    if (!followers || followers.length === 0) {
      followersGrid.innerHTML = '<p class="fb-no-followers">Aucun abonné pour le moment</p>';
      return;
    }
    // Show max 9 followers
    const followersToShow = followers.slice(0, 9);
    followersToShow.forEach(follower => {
      const followerElement = document.createElement('div');
      followerElement.className = 'fb-follower-item';
      followerElement.innerHTML = `
        <img src="/vues/back-office/uploads/${follower.profile_picture || 'default_profile.jpg'}" alt="${follower.username}">
        <span style="display:block;text-align:center;font-size:0.98rem;color:#333;font-weight:500;margin-top:4px;">${follower.username}</span>
      `;
      followersGrid.appendChild(followerElement);
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
      const postDate = new Date(post.created_at);
      const dateStr = postDate.toLocaleDateString('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        hour: '2-digit',
        minute: '2-digit'
      });
      // Media content
      let mediaContent = '';
      if (post.media) {
        if (/\.(mp4|mov|avi)$/i.test(post.media)) {
          mediaContent = `
            <div class="fb-post-media">
              <video controls>
                <source src="${post.media}" type="video/mp4">
              </video>
            </div>
          `;
        } else {
          mediaContent = `
            <div class="fb-post-media">
              <img src="${post.media}" alt="Post image">
            </div>
          `;
        }
      }
      postElement.innerHTML = `
        <div class="fb-post-header">
          <img src="/vues/back-office/uploads/${post.profile_picture || 'default_profile.jpg'}" alt="${post.username}">
          <div class="fb-post-author">
            <strong>${post.username}</strong>
            <span>${dateStr}</span>
          </div>
          <button class="fb-post-options">
            <i class="fas fa-ellipsis-h"></i>
          </button>
        </div>
        <div class="fb-post-content">
          <p>${post.content}</p>
          ${mediaContent}
        </div>
        <div class="fb-post-actions">
          <button class="fb-post-action">
            <i class="far fa-thumbs-up"></i> J'aime
          </button>
          <button class="fb-post-action">
            <i class="far fa-comment"></i> Commenter
          </button>
          <button class="fb-post-action">
            <i class="fas fa-share"></i> Partager
          </button>
        </div>
      `;
      postsContainer.appendChild(postElement);
    });
  }
});
</script>