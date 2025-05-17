<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/dao/event.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

header('Content-Type: application/json');

if (!methodIsAllowed('read')) {
    returnError(405, 'Method not allowed');
    return;
}

acceptedTokens(true, false, true, false);

try {
    $stats = getEventsStats();

    $result = [
        'success' => true,
        'stats' => [
            'total' => $stats['total'],
            'upcoming' => $stats['upcoming'],
            'participants' => $stats['participants']
        ]
    ];

    echo json_encode($result);

} catch (Exception $e) {
    returnError(500, 'Erreur lors de la récupération des statistiques: ' . $e->getMessage());
}
?>
