<?php require_once "../includes/header.php"; ?>

<div class="fb-profile-container">
  <!-- Cover Photo Section -->
  <div class="fb-cover-container">
    <div class="fb-cover-photo">
      <img id="cover-photo" src="/assets/images/default_banner.jpg" alt="Cover Photo">
    </div>
  </div>

  <!-- Profile Info Section -->
  <div class="fb-profile-info">
    <div class="fb-profile-picture-container">
      <div class="fb-profile-picture">
        <img id="profile-picture" src="/vues/back-office/uploads/default_profile.jpg" alt="Profile Picture">
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
      </div>

      <div class="fb-sidebar-card">
        <div class="fb-friends-header">
          <h3><i class="fas fa-user-friends"></i> Amis</h3>
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
      <!-- Posts Container -->
      <div id="posts-container"></div>
    </main>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="/assets/css/profile.css">
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Récupérer l'ID utilisateur depuis l'URL
  const urlParams = new URLSearchParams(window.location.search);
  const userId = urlParams.get('user_id');
  
  if (!userId) {
    showError('ID utilisateur manquant');
    return;
  }

  // Vérifier si c'est l'utilisateur en session qui accède à son propre profil
  const currentUserId = <?php echo isset($_SESSION['user']) ? $_SESSION['user'] : 'null'; ?>;
  
  // Choisir l'API appropriée
  const apiUrl = (currentUserId && currentUserId == userId) 
    ? 'https://linknet.wuaze.com/api/profile.php'
    : `https://linknet.wuaze.com/api/user_profile.php?user_id=${userId}`;

  // Charger le profil de l'utilisateur
  fetch(apiUrl)
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success' && data.data) {
        const userData = data.data;
        renderProfile(userData.user, userData.friends, userData.followers);
        renderFriends(userData.friends);
        renderFollowers(userData.followers);
        renderPosts(userData.posts);
      } else {
        showError('Utilisateur non trouvé');
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
    document.getElementById('profile-username').textContent = capitalizeFirst(user.username);
    document.getElementById('profile-bio').textContent = user.bio || '';
    document.getElementById('profile-picture').src = '/vues/back-office/uploads/' + (user.profile_picture || 'default_profile.jpg');

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
      
      // Déterminer le bon lien selon l'utilisateur
      const friendLink = (currentUserId && currentUserId == friend.id) 
        ? '/vues/clients/user/profile.php'
        : `/vues/clients/user/users_profile.php?user_id=${friend.id}`;
      
      friendElement.innerHTML = `
        <a href="${friendLink}">
          <img src="/vues/back-office/uploads/${friend.profile_picture || 'default_profile.jpg'}" alt="${friendName}">
          <span>${friendName}</span>
        </a>
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
    const followersToShow = followers.slice(0, 9);
    followersToShow.forEach(follower => {
      const followerElement = document.createElement('div');
      followerElement.className = 'fb-follower-item';
      const followerName = capitalizeFirst(follower.username);
      
      // Déterminer le bon lien selon l'utilisateur
      const followerLink = (currentUserId && currentUserId == follower.id) 
        ? '/vues/clients/user/profile.php'
        : `/vues/clients/user/users_profile.php?user_id=${follower.id}`;
      
      followerElement.innerHTML = `
        <a href="${followerLink}">
          <img src="/vues/back-office/uploads/${follower.profile_picture || 'default_profile.jpg'}" alt="${followerName}">
          <span>${followerName}</span>
        </a>
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
      const postLink = (currentUserId && currentUserId == postUserId) 
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
});
</script> 