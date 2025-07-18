<?php include '../includes/header.php'; ?>
<body>
  <div class="followers-page-center">
    <div class="followers-container">
      <h2>Mes amis</h2>
      <div id="friends-list"></div>
    </div>
  </div>
</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
  fetch('https://linknet.wuaze.com/api/friends.php')
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById('friends-list');
      if (data.status === 'success' && Array.isArray(data.data)) {
        if (data.data.length === 0) {
          container.innerHTML = "<p>Aucun ami pour le moment.</p>";
        } else {
          container.innerHTML = data.data.map(friend => `
            <div class="friend-card">
              <img class="friend-avatar" src="/vues/back-office/uploads/${friend.profile_picture || 'default_profile.jpg'}" alt="${friend.username}" data-id="${friend.id}">
              <div class="friend-info">
                <span class="friend-name" data-id="${friend.id}">${friend.username.charAt(0).toUpperCase() + friend.username.slice(1).toLowerCase()}</span>
                <div class="friend-birthday">${friend.birthday ? 'Né(e) le ' + friend.birthday : ''}</div>
              </div>
            </div>
          `).join('');
          // Ajoute l'event sur image et nom
          container.querySelectorAll('.friend-avatar, .friend-name').forEach(el => {
            el.addEventListener('click', function() {
              const id = this.getAttribute('data-id');
              // Rediriger vers le profil de l'utilisateur
              window.location.href = '/vues/clients/user/users_profile.php?user_id=' + id;
            });
          });
        }
      } else {
        container.innerHTML = "<p>Erreur lors du chargement des amis.</p>";
      }
    })
    .catch(() => {
      document.getElementById('friends-list').innerHTML = "<p>Erreur réseau.</p>";
    });
});
</script>
<style>
.followers-page-center {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 100vh;
  width: 100vw;
  background: none;
}
.followers-container {
  width: 100%;
  max-width: 600px;
  margin: 0;
  background: none;
  border-radius: 0;
  box-shadow: none;
  padding: 2.2rem 0 0.5rem 0;
  font-family: 'Segoe UI', Helvetica, Arial, sans-serif;
}

.followers-container h2 {
  color: #7c3aed;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 1.5rem 2vw;
  text-align: left;
  letter-spacing: 0.01em;
}

.friend-card {
  display: flex;
  align-items: center;
  gap: 1.3rem;
  margin-bottom: 0.7rem;
  border-bottom: 1px solid #f3f0fa;
  padding: 0.7rem 2vw 0.7rem 2vw;
  background: none;
  border-radius: 0;
  box-shadow: none;
  transition: background 0.18s;
}
.friend-card:last-child {
  border-bottom: none;
}
.friend-card:hover {
  background: #f6f3fd;
}
.friend-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 2.5px solid #7c3aed;
  background: #f3f0fa;
  cursor: pointer;
  transition: border-color 0.18s, box-shadow 0.18s;
  box-shadow: 0 1px 6px #a78bfa22;
}
.friend-card:hover .friend-avatar {
  border-color: #a78bfa;
  box-shadow: 0 2px 8px #a78bfa33;
}
.friend-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
}
.friend-name {
  font-size: 1.15rem;
  font-weight: 600;
  color: #000;
  margin-bottom: 0.1rem;
  cursor: pointer;
  transition: color 0.18s;
  text-decoration: none;
  display: inline-block;
}
.friend-card:hover .friend-name {
  color: #333;
}
.friend-birthday {
  font-size: 0.97rem;
  color: #65676b;
}
@media (max-width: 700px) {
  .followers-container {
    max-width: 100vw;
    padding: 1.1rem 0 0.5rem 0;
  }
  .followers-page-center {
    align-items: stretch;
  }
  .friend-card {
    gap: 0.7rem;
    padding: 0.7rem 2vw 0.7rem 2vw;
  }
  .friend-avatar {
    width: 44px;
    height: 44px;
  }
  .friend-name {
    font-size: 1.01rem;
  }
}
</style>
