<style>
.create-post-container {
  max-width: 600px;
  width: 100%;
  margin: 20px auto 8px auto;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.07);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  position: relative;
}
.create-post-main {
  display: flex;
  align-items: center;
  gap: 10px;
  position: relative;
}
#create-post-text {
  flex: 1;
  border: none;
  background: #f0f2f5;
  border-radius: 18px;
  padding: 12px 16px;
  font-size: 16px;
  outline: none;
  min-width: 0;
  width: 100%;
}
.media-icon {
  cursor: pointer;
  font-size: 20px;
  color: #7c3aed;
  margin-left: 6px;
}
#create-post-btn {
  background: #7c3aed;
  color: #fff;
  border: none;
  border-radius: 18px;
  padding: 8px 18px;
  font-size: 15px;
  cursor: pointer;
  margin-left: 6px;
}
#media-preview img,
#media-preview video {
  width: 100%;
  max-width: 100%;
  max-height: 320px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.07);
  margin-bottom: 10px;
  object-fit: cover;
  display: block;
}
#create-post-error {
  color: #e74c3c;
  margin-top: 8px;
  text-align: center;
}
.hashtag-suggestions {
  position: absolute;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.07);
  z-index: 10;
  margin-top: 2px;
  display: none;
  left: 0;
  right: 0;
  top: 100%;
  max-height: 200px;
  overflow-y: auto;
}
.hashtag-suggestions span {
  display: block;
  padding: 8px 14px;
  cursor: pointer;
  color: #7c3aed;
  font-size: 14px;
}
.hashtag-suggestions span:hover {
  background: #f6f3fd;
}
</style>

<div class="create-post-container">
  <div id="media-preview"></div>
  <div class="create-post-main">
    <input id="create-post-text" type="text" placeholder="Exprime-toi... #hashtag" autocomplete="off" />
    <label for="create-post-media" class="media-icon" title="Ajouter une image ou vidéo">
      <i class="fas fa-image"></i>
      <input type="file" id="create-post-media" accept="image/*,video/*" style="display:none;">
    </label>
    <button id="create-post-btn">Publier</button>
  </div>
  <div id="create-post-error"></div>
  <div class="hashtag-suggestions" id="hashtag-suggestions"></div>
</div>

<script>
window.API_BASE_URL = window.API_BASE_URL || 'https://linknet.wuaze.com/api/';
let hashtagCounts = {};

// Preview image/vidéo dans le formulaire (doit être avant l'envoi du post)
const mediaInput = document.getElementById('create-post-media');
const mediaPreview = document.getElementById('media-preview');
if (mediaInput) {
  mediaInput.addEventListener('change', function(e) {
    mediaPreview.innerHTML = '';
    const file = e.target.files[0];
    if (!file) return;
    const url = URL.createObjectURL(file);
    if (file.type.startsWith('image/')) {
      mediaPreview.innerHTML = `<img src="${url}" alt="Image" class="post-media">`;
    } else if (file.type.startsWith('video/')) {
      mediaPreview.innerHTML = `<video src="${url}" controls autoplay playsinline class="post-media"></video>`;
    }
  });
}

function updateHashtagCountsFromPosts(posts) {
    hashtagCounts = {};
    posts.forEach(post => {
        if (Array.isArray(post.hashtags)) {
            post.hashtags.forEach(tag => {
                if (tag.hashtag_name) {
                    const name = tag.hashtag_name;
                    hashtagCounts[name] = (hashtagCounts[name] || 0) + 1;
                }
            });
        }
    });
}

async function fetchAndPrepareHashtags() {
    try {
        const response = await fetch(window.API_BASE_URL + 'actualite.php');
        const data = await response.json();
        console.log('API hashtags', data);
        if (data.status === 'success' && data.data && Array.isArray(data.data.posts)) {
            updateHashtagCountsFromPosts(data.data.posts);
            console.log('Hashtag counts:', hashtagCounts);
        }
    } catch (e) {
        hashtagCounts = {};
        // Afficher un message discret dans la box de suggestions
        const hashtagBox = document.getElementById('hashtag-suggestions');
        if (hashtagBox) {
            hashtagBox.innerHTML = '<span>Suggestions indisponibles</span>';
            hashtagBox.style.display = 'block';
        }
        // Masquer le log en production
        if (location.hostname === 'localhost' || location.hostname === '127.0.0.1') {
            console.error('Erreur fetch hashtags', e);
        }
    }
}

function setupHashtagSuggestions() {
    const postInput = document.getElementById('create-post-text');
    const hashtagBox = document.getElementById('hashtag-suggestions');
    console.log('setupHashtagSuggestions', {postInput, hashtagBox, hashtagCounts});
    if (!postInput || !hashtagBox) return;

    let hashtagTimeout = null;

    postInput.addEventListener('input', function(e) {
        const cursorPos = postInput.selectionStart;
        const text = postInput.value.slice(0, cursorPos);
        const match = text.match(/#(\w*)$/);

        if (match) {
            const query = match[1].toLowerCase();
            clearTimeout(hashtagTimeout);

            hashtagTimeout = setTimeout(() => {
                let suggestions = Object.entries(hashtagCounts);

                if (query.length > 0) {
                    suggestions = suggestions.filter(([tag]) =>
                        tag.toLowerCase().startsWith(query)
                    );
                }
                // Si query est vide, on garde tous les hashtags (top 5 plus populaires)

                // Trier par popularité (fréquence d'apparition)
                suggestions = suggestions
                    .sort((a, b) => b[1] - a[1])
                    .slice(0, 5)
                    .map(([tag]) => tag);

                if (suggestions.length) {
                    hashtagBox.innerHTML = suggestions.map(tag =>
                        `<span data-hashtag="${tag}">#${tag}</span>`
                    ).join('');
                    hashtagBox.style.display = 'block';
                } else {
                    hashtagBox.style.display = 'none';
                }
            }, 200);
        } else {
            hashtagBox.style.display = 'none';
        }
    });

    hashtagBox.addEventListener('mousedown', function(e) {
        if (e.target.dataset.hashtag) {
            const cursorPos = postInput.selectionStart;
            const text = postInput.value;
            const before = text.slice(0, cursorPos).replace(/#(\w*)$/, '#' + e.target.dataset.hashtag + ' ');
            const after = text.slice(cursorPos);
            postInput.value = before + after;
            hashtagBox.style.display = 'none';
            postInput.focus();
        }
    });

    // Fermer la suggestion quand on clique ailleurs
    document.addEventListener('click', function(e) {
        if (!hashtagBox.contains(e.target)) {
            hashtagBox.style.display = 'none';
        }
    });
}

// Initialisation autonome
document.addEventListener('DOMContentLoaded', function() {
    fetchAndPrepareHashtags().then(setupHashtagSuggestions);
});

// Gestion de l'envoi du post
const postBtn = document.getElementById('create-post-btn');
if (postBtn) {
  postBtn.onclick = async function(e) {
    e.preventDefault();
    const content = document.getElementById('create-post-text').value.trim();
    const mediaInput = document.getElementById('create-post-media');
    const media = mediaInput.files[0];
    const errorBox = document.getElementById('create-post-error');
    errorBox.textContent = '';
    if (!content && !media) {
      errorBox.textContent = "Le post ne peut pas être vide !";
      return;
    }
    const formData = new FormData();
    formData.append('content', content);
    if (media) formData.append('media', media);
    try {
      const response = await fetch(window.API_BASE_URL + 'post.php', {
        method: 'POST',
        body: formData,
        credentials: 'include'
      });
      const data = await response.json();
      if (data.status === 'success') {
        document.getElementById('create-post-text').value = '';
        mediaInput.value = '';
        document.getElementById('media-preview').innerHTML = '';
        errorBox.textContent = '';
        // Optionnel : recharger les hashtags après création
        fetchAndPrepareHashtags();
        // Si la fonction loadPosts existe (dans index.php), l'appeler
        if (typeof loadPosts === 'function') {
          loadPosts();
        }
      } else {
        errorBox.textContent = data.message || "Erreur lors de la création du post";
      }
    } catch (err) {
      errorBox.textContent = "Erreur réseau ou serveur";
      console.error('Post submission error:', err);
    }
  };
}
</script>