
<link rel="stylesheet" href="/assets/css/users.css">

<div class="user-sidebar">

  <div class="user-sidebar-header"></div>

  <div class="user-list" id="user-list">

    <div style="text-align:center;color:#aaa;padding:30px 0;">Chargement...</div>

  </div>

</div>



<script>

window.currentUserId = <?php echo isset($_SESSION['user']) ? json_encode((string)$_SESSION['user']) : 'null'; ?>;

const API_URL = 'https://linknet.wuaze.com/api/users.php';

let allUsers = [];

let currentUserId = window.currentUserId;



// Récupérer l'id utilisateur courant (si dispo dans le header ou session)

try {

  currentUserId = window.currentUserId || (typeof CURRENT_USER_ID !== 'undefined' ? CURRENT_USER_ID : null);

} catch (e) { currentUserId = null; }



// Fonction utilitaire pour obtenir le bon lien de profil

function getProfilePageLink(userId) {

  if (!currentUserId || !userId) return '/vues/clients/user/users_profile.php?user_id=' + userId;

  return (userId == currentUserId)

    ? '/vues/clients/user/profile.php'

    : '/vues/clients/user/users_profile.php?user_id=' + userId;

}



// Nouvelle fonction pour obtenir le statut de la relation d'ami

async function getFriendRequestStatus(userId) {

  if (!userId || userId == currentUserId) return 'none';

  try {

    const res = await fetch(`https://linknet.wuaze.com/api/send_request.php?user_id=${userId}`, { credentials: 'include' });

    const data = await res.json();

    if (data.status === 'success' && data.data && data.data.friend_status) {

      return data.data.friend_status;

    }

    return 'none';

  } catch (e) {

    return 'none';

  }

}



function shuffleArray(array) {

  // Durstenfeld shuffle

  for (let i = array.length - 1; i > 0; i--) {

    const j = Math.floor(Math.random() * (i + 1));

    [array[i], array[j]] = [array[j], array[i]];

  }

  return array;

}



async function renderUserList(users) {

  const list = document.getElementById('user-list');

  if (!users.length) {

    list.innerHTML = '<div style="text-align:center;color:#aaa;padding:30px 0;">Aucun utilisateur trouvé</div>';

    return;

  }

  // Affichage loading

  list.innerHTML = users.map(user => {

    console.log('currentUserId:', currentUserId, 'user.id:', user.id, typeof currentUserId, typeof user.id);

    return `
  <div class="user-item" id="user-item-${user.id}">
    <img class="user-avatar" src="${normalizeProfilePicture(user.profile_picture)}" alt="Avatar" data-profile-link="${getProfilePageLink(user.id)}">
    <div class="user-info">
      <a href="${getProfilePageLink(user.id)}" class="user-username user-profile-link">${user.username}</a>
      <a href="${getProfilePageLink(user.id)}" class="user-email-link">${user.bio ? user.bio : user.email}</a>
    </div>
    ${currentUserId && String(user.id) === String(currentUserId) ? '' : '<div class="user-action-placeholder"></div>'}
  </div>
`}).join('');



  // Pour chaque utilisateur (sauf soi-même), interroger l'API pour le statut

  await Promise.all(users.map(async user => {

    if (currentUserId && user.id == currentUserId) return; // Empêche l'affichage du bouton pour l'utilisateur connecté

    const status = await getFriendRequestStatus(user.id);

    const item = document.getElementById(`user-item-${user.id}`);

    if (!item) return;

    const placeholder = item.querySelector('.user-action-placeholder');

    let btn = '';

    if (status === 'pending') {

      btn = `<button class="user-action-btn" data-id="${user.id}" data-action="cancel_friend">Annuler</button>`;

    } else if (status === 'none' || status === 'rejected') {

      btn = `<button class="user-action-btn" data-id="${user.id}" data-action="add_friend">Ajouter</button>`;

    } // accepted: pas de bouton

    placeholder.innerHTML = btn;

  }));



  // Ajout listeners sur les boutons

  document.querySelectorAll('.user-action-btn').forEach(btn => {

    btn.onclick = async function(e) {

      const userId = btn.getAttribute('data-id');

      const action = btn.getAttribute('data-action');

      btn.disabled = true;

      btn.textContent = '...';

      try {

        const res = await fetch('https://linknet.wuaze.com/api/send_request.php', {

          method: 'POST',

          headers: { 'Content-Type': 'application/json' },

          body: JSON.stringify({ action: action === 'add_friend' ? 'add' : (action === 'cancel_friend' ? 'cancel' : action), user_id: userId })

        });

        const data = await res.json();

        if (data.status === 'success') {

          // Rafraîchir le statut du bouton

          const status = await getFriendRequestStatus(userId);

          const item = document.getElementById(`user-item-${userId}`);

          if (item) {

            const placeholder = item.querySelector('.user-action-placeholder');

            let btnHtml = '';

            if (status === 'pending') {

              btnHtml = `<button class="user-action-btn" data-id="${userId}" data-action="cancel_friend">Annuler</button>`;

            } else if (status === 'none' || status === 'rejected') {

              btnHtml = `<button class="user-action-btn" data-id="${userId}" data-action="add_friend">Ajouter</button>`;

            }

            placeholder.innerHTML = btnHtml;

            // Réattacher le listener si bouton présent

            if (btnHtml) {

              const newBtn = placeholder.querySelector('.user-action-btn');

              if (newBtn) newBtn.onclick = btn.onclick;

            }

          }

        } else {

          btn.textContent = data.message || 'Erreur';

          setTimeout(() => renderUserList(users), 1200);

        }

      } catch (err) {

        btn.textContent = 'Erreur réseau';

        setTimeout(() => renderUserList(users), 1200);

      }

    };

  });



  // Ajout listeners sur les avatars et usernames

  setTimeout(() => {

    document.querySelectorAll('.user-avatar, .user-profile-link').forEach(el => {

      el.onclick = function(e) {

        e.preventDefault();

        const link = el.getAttribute('data-profile-link') || el.getAttribute('href');

        if (link) window.location.href = link;

      };

    });

  }, 0);

}



function normalizeProfilePicture(pic) {

  if (!pic) return '/vues/back-office/uploads/default_profile.jpg';

  if (pic.startsWith('http')) return pic;

  if (pic.startsWith('../uploads/') || pic.startsWith('../../back-office/uploads/')) {

    return '/vues/back-office/uploads/' + pic.replace(/^\.\.\//, '').replace(/^\.\.\/\.\.\/back-office\/uploads\//, '');

  }

  return '/vues/back-office/uploads/' + pic.replace(/^.*uploads[\\/]/, '');

}



function goToUserProfile(userId) {

  window.location.href = `users_profile.php?user_id=${userId}`;

}



const globalSearch = document.getElementById('global-user-search');

if (globalSearch) {

  globalSearch.addEventListener('input', function(e) {

    const q = e.target.value.trim().toLowerCase();

    const filtered = allUsers.filter(u =>

      u.username.toLowerCase().includes(q) ||

      (u.bio && u.bio.toLowerCase().includes(q)) ||

      (u.email && u.email.toLowerCase().includes(q))

    );

    renderUserList(filtered);

  });

}



async function loadUsers() {

  try {

    const res = await fetch(API_URL);

    const data = await res.json();

    if (data.status === 'success' && data.data && Array.isArray(data.data.users)) {

      allUsers = shuffleArray(data.data.users);

      await renderUserList(allUsers);

    } else {

      document.getElementById('user-list').innerHTML = '<div style="text-align:center;color:#e74c3c;padding:30px 0;">Erreur de chargement</div>';

    }

  } catch (e) {

    document.getElementById('user-list').innerHTML = '<div style="text-align:center;color:#e74c3c;padding:30px 0;">Erreur réseau</div>';

  }

}



loadUsers();

window.goToUserProfile = goToUserProfile;

</script>

