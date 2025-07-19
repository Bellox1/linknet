<?php
header('Content-Type: application/json');
http_response_code(404);

echo json_encode([
    'status' => 'error',
    'message' => 'Endpoint non trouvé',
    'available_endpoints' => [
        'posts' => '/api/posts/',
        'users' => '/api/users/',
        'messages' => '/api/messages/',
        'actualite' => '/api/actualite.php'
    ]
]);
?> 