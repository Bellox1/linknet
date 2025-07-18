<?php include 'includes/header.php'; ?>
<div class="main-layout" style="display: flex;">
  <div class="sidebar-left">
    <button class="sidebar-left-close-btn" style="display:none;">&times;</button>
    <?php include 'user/users.php'; ?>
  </div>
  <div id="sidebar-left-overlay"></div>
  <div class="main-content">
    <!-- Posts, etc. -->
  </div>



<div id="global-loader" style="display:flex;justify-content:center;align-items:center;height:60vh;">
  <img src="/assets/images/loading.gif" alt="Chargement..." style="height:64px;">
</div>
<div class="posts-container" style="display:none;">
    <?php include 'posts/create_post.php'; ?>
    <div id="posts-list"></div>
</div>

<script>
// Vérification de session sécurisée
window.currentUserId = <?php echo isset($_SESSION["user"]) ? json_encode($_SESSION["user"]) : 'null'; ?>;
if (!window.currentUserId) {
    window.location.href = 'auth/login.php';
}
</script>

<script>
// Configuration
const API_BASE_URL = 'https://linknet.wuaze.com/api/';
const UPDATE_INTERVAL = 3000; // 3 secondes
const MAX_RETRIES = 3;

// État de l'application
let appState = {
    openCommentsPostId: null,
    commentDrafts: {},
    postsData: [],
    profilesCache: {},
    lastUpdateTime: 0,
    retryCount: 0,
    isUpdating: false
};

// Initialisation
document.addEventListener('DOMContentLoaded', initApp);

function initApp() {
    loadAllDataAndShow();
}

async function loadAllDataAndShow() {
    showLoader();
    try {
        await loadPosts(true);
        hideLoader();
        startPeriodicUpdates();
    } catch (error) {
        handleDataLoadError(error);
    }
}

async function loadPosts(isInitial = false) {
    if (appState.isUpdating) return;
    appState.isUpdating = true;
    
    try {
        const response = await fetchWithRetry(
            `${API_BASE_URL}actualite.php?last_update=${appState.lastUpdateTime}`,
            MAX_RETRIES
        );
        
        const data = await processApiResponse(response);
        
        if (isInitial || !appState.postsData.length) {
            appState.postsData = data.posts || [];
            await cacheProfiles(appState.postsData);
            renderAllPosts(appState.postsData);
        } else {
            updatePostsData(data.posts || []);
        }
        
        appState.retryCount = 0;
    } catch (error) {
        appState.retryCount++;
        throw error;
    } finally {
        appState.isUpdating = false;
    }
}

async function fetchWithRetry(url, maxRetries) {
    let lastError;
    
    for (let i = 0; i <= maxRetries; i++) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response;
        } catch (error) {
            lastError = error;
            if (i < maxRetries) await new Promise(resolve => setTimeout(resolve, 1000 * i));
        }
    }
    
    throw lastError;
}

async function processApiResponse(response) {
    const data = await response.json();
    
    if (!data || data.status !== 'success') {
        throw new Error('Invalid API response');
    }
    
    appState.lastUpdateTime = data.data?.current_time || Date.now();
    return data.data;
}

function updatePostsData(newPosts) {
    newPosts.forEach(newPost => {
        const existingIndex = appState.postsData.findIndex(p => p.post_id === newPost.post_id);
        
        if (existingIndex >= 0) {
            updateExistingPost(existingIndex, newPost);
        } else {
            addNewPost(newPost);
        }
    });
}

function updateExistingPost(index, newData) {
    const existingPost = appState.postsData[index];
    
    // Mise à jour des likes
    if (newData.likes_count !== undefined) {
        existingPost.likes_count = newData.likes_count;
        existingPost.current_user_liked = newData.current_user_liked || 0;
        updatePostLikesUI(existingPost.post_id, existingPost.likes_count, existingPost.current_user_liked);
    }
    
    // Mise à jour des commentaires
    if (newData.comments_count !== undefined) {
        existingPost.comments_count = newData.comments_count;
        updatePostCommentsCountUI(existingPost.post_id, existingPost.comments_count);
    }
    
    // Ajout de nouveaux commentaires
    if (newData.new_comments?.length) {
        existingPost.all_comments = existingPost.all_comments || [];
        newData.new_comments.forEach(comment => {
            if (!existingPost.all_comments.some(c => c.comment_id === comment.comment_id)) {
                existingPost.all_comments.unshift(comment);
                addCommentToUI(existingPost.post_id, comment);
            }
        });
    }
}

async function addNewPost(post) {
    appState.postsData.unshift(post);
    await cacheProfiles([post]);
    renderNewPost(post);
}

async function cacheProfiles(posts) {
    const userIds = [...new Set(posts.map(p => p.user_id))];
    
    await Promise.all(userIds.map(async userId => {
        if (!appState.profilesCache[userId]) {
            appState.profilesCache[userId] = await fetchProfilePicture(userId);
        }
    }));
}

async function fetchProfilePicture(userId) {
    const isCurrentUser = userId == window.currentUserId;
    const url = isCurrentUser 
        ? `${API_BASE_URL}profile.php` 
        : `${API_BASE_URL}user_profile.php?user_id=${userId}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data?.status === 'success' && data.data?.user?.profile_picture) {
            return normalizeProfilePictureUrl(data.data.user.profile_picture);
        }
    } catch (error) {
        console.error('Error fetching profile:', error);
    }
    
    return '/vues/back-office/uploads/default_profile.jpg';
}

function normalizeProfilePictureUrl(pic) {
    if (!pic) return '/vues/back-office/uploads/default_profile.jpg';
    if (pic.startsWith('http')) return pic;
    if (pic.startsWith('../uploads/') || pic.startsWith('../../back-office/uploads/')) {
        return '/vues/back-office/uploads/' + pic.replace(/^\.\.\//, '').replace(/^\.\.\/\.\.\/back-office\/uploads\//, '');
    }
    return '/vues/back-office/uploads/' + pic.replace(/^.*uploads[\\/]/, '');
}

function renderAllPosts(posts) {
    const container = document.getElementById('posts-list');
    container.innerHTML = posts.map(post => renderPost(post)).join('');
}

function renderNewPost(post) {
    const container = document.getElementById('posts-list');
    container.insertAdjacentHTML('afterbegin', renderPost(post));
}

function renderPost(post) {
    const profilePic = appState.profilesCache[post.user_id] || '/vues/back-office/uploads/default_profile.jpg';
    
        return `
    <div class="post" data-post-id="${post.post_id}">
            <div class="post-header">
            <img src="${profilePic}" alt="Avatar" class="post-avatar" onclick="goToProfile(${post.user_id})">
                <div class="post-user-info">
                    <div class="post-username">${post.user_username || 'Utilisateur inconnu'}</div>
                <div class="post-time">${formatDate(post.post_created_at)} <span class="realtime-indicator"></span></div>
            </div>
            </div>
            <div class="post-content">
            ${renderPostContent(post)}
            </div>
        <div class="post-stats" id="post-stats-${post.post_id}">
            ${renderPostStats(post)}
            </div>
            <div class="post-actions">
            ${renderPostActions(post)}
        </div>
        ${renderCommentsSection(post)}
    </div>
    `;
}

function renderPostContent(post) {
    let mediaHtml = '';
    if (post.post_media) {
        const mediaSrc = '/vues/back-office/uploads/' + post.post_media.replace(/^.*uploads[\\/]/, '');
        if (post.post_media.match(/\.(mp4|webm|ogg)$/i)) {
            mediaHtml = `<video src="${mediaSrc}" class="post-media" controls></video>`;
        } else {
            mediaHtml = `<img src="${mediaSrc}" alt="Media" class="post-media">`;
        }
    }
    
    const hashtags = post.hashtags?.length
        ? `<div class="hashtags">${post.hashtags.map(tag => `<span class="hashtag">#${tag.hashtag_name}</span>`).join(' ')}</div>`
        : '';
    
    return `
        <div class="post-text">${post.post_content || ''}</div>
        ${mediaHtml}
        ${hashtags}
    `;
}

function renderPostStats(post) {
    const commentsLink = post.comments_count > 0
        ? ` • <span class="comments-count-link" onclick="toggleAllComments(${post.post_id})">${post.comments_count} commentaire${post.comments_count > 1 ? 's' : ''}</span>`
        : '';
    return `
        ${post.likes_count > 0 ? `${post.likes_count} j'aime` : ''}
        ${commentsLink}
    `;
}

function renderPostActions(post) {
    return `
        <button class="action-btn${post.current_user_liked ? ' liked' : ''}" 
                onclick="handleLike(${post.post_id})">
                    <i class="fas fa-heart"></i> J'aime
                </button>
        <button class="action-btn" onclick="focusCommentInput(${post.post_id})">
                    <i class="fas fa-comment"></i> Commenter
                </button>
    `;
}

function renderCommentsSection(post) {
    const showAll = appState.openCommentsPostId === post.post_id;
    const comments = post.all_comments || [];
    const visibleComments = showAll ? comments : comments.slice(0, 3);
    
    return `
            <div class="comments-section">
        ${comments.length > 3 && !showAll
            ? `<span class="show-comments-btn" onclick="toggleAllComments(${post.post_id})">
                Voir les ${post.comments_count} commentaires
               </span>`
            : ''}
        <div class="comments-list" id="comments-list-${post.post_id}" 
             style="max-height: ${showAll ? '300px' : 'none'}; 
                    overflow-y: ${showAll ? 'auto' : 'visible'}">
            ${visibleComments.map(comment => renderComment(comment)).join('')}
                            </div>
        ${showAll ? renderCommentInput(post.post_id) : ''}
                        </div>
                    `;
}

function renderComment(comment) {
    const commenterPic = appState.profilesCache[comment.commenter_id] || 
                         '/vues/back-office/uploads/default_profile.jpg';
    
    return `
    <div class="comment" data-comment-id="${comment.comment_id}">
        <img src="${commenterPic}" class="comment-avatar" onclick="goToProfile(${comment.commenter_id})">
        <div class="comment-content">
            <div class="comment-header">
                <div class="comment-username" onclick="goToProfile(${comment.commenter_id})">
                    ${comment.commenter_username || 'Utilisateur inconnu'}
                </div>
                <div class="comment-time">${formatDate(comment.comment_created_at)}</div>
            </div>
            <div class="comment-text">${comment.comment_text}</div>
            </div>
        </div>
        `;
}

function renderCommentInput(postId) {
    return `
    <div class="comment-input">
        <input type="text" class="comment-input-field" 
               placeholder="Écrire un commentaire..." 
               id="comment-${postId}"
               value="${appState.commentDrafts[postId] || ''}"
               oninput="saveCommentDraft(${postId}, this.value)"
               onkeypress="handleCommentKeyPress(event, ${postId})">
        <button onclick="submitComment(${postId})">Envoyer</button>
    </div>
    <div class="comment-error" id="comment-error-${postId}"></div>
    `;
}

// Gestion des interactions
async function handleLike(postId) {
    try {
        const response = await fetch(`${API_BASE_URL}actualite.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                action: 'like', 
                post_id: postId 
            })
        });
        
        const data = await response.json();
        
        if (data && data.status === 'success') {
            const postIndex = appState.postsData.findIndex(p => p.post_id == postId);
            if (postIndex !== -1) {
                const post = appState.postsData[postIndex];
                
                if (data.action === 'liked') {
                    post.likes_count = (post.likes_count || 0) + 1;
                    post.current_user_liked = true;
                } else if (data.action === 'unliked') {
                    post.likes_count = Math.max(0, (post.likes_count || 1) - 1);
                    post.current_user_liked = false;
                }
                
                updatePostLikesUI(postId, post.likes_count, post.current_user_liked);
            }
        }
    } catch (error) {
        console.error('Like error:', error);
    }
}

async function submitComment(postId) {
    const input = document.getElementById(`comment-${postId}`);
    const commentText = input.value.trim();
    
    if (!commentText) return;
    
    try {
        const response = await fetch(`${API_BASE_URL}actualite.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'comment',
                post_id: postId,
                comment_text: commentText
            })
        });
        
        const data = await response.json();
        if (data.status === 'success') {
            input.value = '';
            delete appState.commentDrafts[postId];
            document.getElementById(`comment-error-${postId}`).textContent = '';
            
            const postIndex = appState.postsData.findIndex(p => p.post_id == postId);
            if (postIndex !== -1) {
                const post = appState.postsData[postIndex];
                
                const newComment = {
                    comment_id: 'temp-' + Date.now(),
                    comment_text: commentText,
                    comment_created_at: new Date().toISOString(),
                    commenter_id: window.currentUserId,
                    commenter_username: "Vous",
                    commenter_profile_picture: appState.profilesCache[window.currentUserId] || '/vues/back-office/uploads/default_profile.jpg'
                };
                
                if (!post.all_comments) post.all_comments = [];
                post.all_comments.unshift(newComment);
                post.comments_count = (post.comments_count || 0) + 1;
                
                renderPostComments(postId);
                
                setTimeout(async () => {
                    await loadPosts();
                }, 1000);
            }
        }
    } catch (error) {
        document.getElementById(`comment-error-${postId}`).textContent = 'Erreur d\'envoi';
        console.error('Comment error:', error);
    }
}

function renderPostComments(postId) {
    const postElement = document.querySelector(`.post[data-post-id="${postId}"]`);
    if (!postElement) return;
    
    const post = appState.postsData.find(p => p.post_id == postId);
    if (!post) return;
    
    const showAll = appState.openCommentsPostId === postId;
    const commentsSection = postElement.querySelector('.comments-section');
    
    if (commentsSection) {
        commentsSection.innerHTML = `
            ${post.comments_count > 3 && !showAll
                ? `<span class="show-comments-btn" onclick="toggleAllComments(${postId})">
                    Voir les ${post.comments_count} commentaires
                   </span>`
                : ''}
            <div class="comments-list" id="comments-list-${postId}" 
                 style="max-height: ${showAll ? '300px' : 'none'}; 
                        overflow-y: ${showAll ? 'auto' : 'visible'}">
                ${(showAll ? post.all_comments : post.all_comments?.slice(0, 3) || [])
                    .map(comment => renderComment(comment)).join('')}
            </div>
            ${showAll ? renderCommentInput(postId) : ''}
        `;
        
        updatePostCommentsCountUI(postId, post.comments_count);
    }
}

function focusCommentInput(postId) {
    if (appState.openCommentsPostId !== postId) {
        appState.openCommentsPostId = postId;
        renderAllPosts(appState.postsData);
        setTimeout(() => {
            const input = document.getElementById(`comment-${postId}`);
            if (input) input.focus();
        }, 100);
    } else {
    const input = document.getElementById(`comment-${postId}`);
        if (input) input.focus();
    }
}

function toggleAllComments(postId) {
    appState.openCommentsPostId = appState.openCommentsPostId === postId ? null : postId;
    renderAllPosts(appState.postsData);
}

function handleCommentKeyPress(event, postId) {
    if (event.key === 'Enter') {
        submitComment(postId);
    }
}

function saveCommentDraft(postId, value) {
    appState.commentDrafts[postId] = value;
}

function updatePostLikesUI(postId, likesCount, isLiked) {
    const postElement = document.querySelector(`.post[data-post-id="${postId}"]`);
    if (!postElement) return;
    
    const likeBtn = postElement.querySelector('.action-btn');
    if (likeBtn) {
        likeBtn.classList.toggle('liked', isLiked);
    }
    
    const statsElement = postElement.querySelector('.post-stats');
    if (statsElement) {
        const commentsMatch = statsElement.textContent.match(/(\d+) commentaires/);
        const commentsCount = commentsMatch ? parseInt(commentsMatch[1]) : 0;
        statsElement.innerHTML = `
            ${likesCount} j'aime
            ${commentsCount > 0 ? ` • ${commentsCount} commentaires` : ''}
        `;
    }
}

function updatePostCommentsCountUI(postId, commentsCount) {
    const postElement = document.querySelector(`.post[data-post-id="${postId}"]`);
    if (!postElement) return;
    
    const statsElement = postElement.querySelector('.post-stats');
    const showCommentsBtn = postElement.querySelector('.show-comments-btn');
    
    if (statsElement) {
        const likesMatch = statsElement.textContent.match(/(\d+) j'aime/);
        const likesCount = likesMatch ? parseInt(likesMatch[1]) : 0;
        statsElement.innerHTML = `
            ${likesCount} j'aime
            ${commentsCount > 0 ? ` • ${commentsCount} commentaires` : ''}
        `;
    }
    
    if (showCommentsBtn && commentsCount > 3) {
        showCommentsBtn.textContent = `Voir les ${commentsCount} commentaires`;
    }
}

function addCommentToUI(postId, comment) {
    const commentsList = document.getElementById(`comments-list-${postId}`);
    if (!commentsList) return;
    
    if (document.querySelector(`[data-comment-id="${comment.comment_id}"]`)) return;
    
    commentsList.insertAdjacentHTML('afterbegin', renderComment(comment));
    updatePostCommentsCountUI(postId, commentsList.children.length);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffSec = Math.round(diffMs / 1000);
    const diffMin = Math.round(diffSec / 60);
    const diffHrs = Math.round(diffMin / 60);
    const diffDays = Math.round(diffHrs / 24);
    
    if (diffSec < 60) return 'À l\'instant';
    if (diffMin < 60) return `Il y a ${diffMin} min`;
    if (diffHrs < 24) return `Il y a ${diffHrs} h`;
    if (diffDays < 7) return `Il y a ${diffDays} j`;
    return date.toLocaleDateString('fr-FR');
}

function showLoader() {
    document.getElementById('global-loader').style.display = 'flex';
    document.querySelector('.posts-container').style.display = 'none';
}

function hideLoader() {
    document.getElementById('global-loader').style.display = 'none';
    document.querySelector('.posts-container').style.display = '';
}

function showError(message = 'Erreur de chargement, réessayez plus tard') {
    const container = document.getElementById('posts-list');
    container.innerHTML = `<div class="error">${message}</div>`;
    hideLoader();
}

function handleDataLoadError(error) {
    console.error('Data load error:', error);
    showError();
    
    if (appState.retryCount < MAX_RETRIES) {
        setTimeout(loadAllDataAndShow, 3000);
    }
}

function startPeriodicUpdates() {
    setInterval(() => {
        if (!document.hidden && !appState.isUpdating) {
            loadPosts();
        }
    }, UPDATE_INTERVAL);
}

function goToProfile(userId) {
    if (userId == window.currentUserId) {
        window.location.href = 'user/profile.php';
    } else {
        window.location.href = `user/users_profile.php?user_id=${userId}`;
    }
}

// Exposer les fonctions globales
window.handleLike = handleLike;
window.submitComment = submitComment;
window.focusCommentInput = focusCommentInput;
window.toggleAllComments = toggleAllComments;
window.handleCommentKeyPress = handleCommentKeyPress;
window.saveCommentDraft = saveCommentDraft;
window.goToProfile = goToProfile;

// --- Sidebar gauche (amis/utilisateurs) responsive avec overlay et bouton fermer ---
function handleSidebarLeftMobile() {
  const sidebarLeft = document.querySelector('.sidebar-left');
  const searchInput = document.querySelector('.search-form input');
  const overlay = document.getElementById('sidebar-left-overlay');
  const closeBtn = document.querySelector('.sidebar-left-close-btn');
  if (!sidebarLeft || !searchInput || !overlay || !closeBtn) return;

  function openSidebar() {
    sidebarLeft.classList.add('sidebar-left--open');
    overlay.style.display = 'block';
    closeBtn.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    sidebarLeft.classList.remove('sidebar-left--open');
    overlay.style.display = 'none';
    closeBtn.style.display = 'none';
    document.body.style.overflow = '';
  }

  // Ouvre la sidebar quand on focus ou tape dans la recherche
  searchInput.addEventListener('focus', openSidebar);
  searchInput.addEventListener('input', function() {
    if (searchInput.value.trim() !== '') openSidebar();
    else if (document.activeElement !== searchInput) closeSidebar();
  });
  // Ferme la sidebar si on sort du champ et qu'il est vide
  searchInput.addEventListener('blur', function() {
    setTimeout(function() {
      if (searchInput.value.trim() === '') closeSidebar();
    }, 100);
  });
  // Fermer via overlay ou bouton fermer
  overlay.addEventListener('click', closeSidebar);
  closeBtn.addEventListener('click', closeSidebar);
  // Toujours visible sur desktop
  window.addEventListener('resize', function() {
    if (window.innerWidth > 900) {
      sidebarLeft.classList.remove('sidebar-left--open');
      overlay.style.display = 'none';
      closeBtn.style.display = 'none';
      document.body.style.overflow = '';
    }
  });
}
document.addEventListener('DOMContentLoaded', handleSidebarLeftMobile);
</script>

  <div class="sidebar-right">
    <?php include 'includes/activity.php'; ?>
  </div>
</div>