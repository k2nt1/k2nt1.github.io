<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'POST only']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$key   = isset($input['key']) ? trim($input['key']) : '';

if ($key === '') {
    echo json_encode(['success' => false, 'message' => 'No key provided']);
    exit;
}

function kauthPost($fields) {
    $ch = curl_init('https://keyauth.win/api/1.3/');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $res = curl_exec($ch);
    if ($res === false) {
        curl_close($ch);
        return null;
    }
    curl_close($ch);
    return json_decode($res, true);
}

$init = kauthPost([
    'type'    => 'init',
    'name'    => 'NEXUS',
    'ownerid' => 'hdUlov6Z0E',
    'ver'     => '2.3',
    'hash'    => ''
]);

if (!$init || empty($init['success'])) {
    $msg = isset($init['message']) ? $init['message'] : 'KeyAuth init failed';
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$sessionid = isset($init['sessionid']) ? $init['sessionid'] : '';

$lic = kauthPost([
    'type'      => 'license',
    'key'       => $key,
    'sessionid' => $sessionid,
    'name'      => 'NEXUS',
    'ownerid'   => 'hdUlov6Z0E',
    'ver'       => '2.3'
]);

if ($lic && !empty($lic['success'])) {
    echo json_encode(['success' => true, 'message' => 'Valid license']);
} else {
    $msg = isset($lic['message']) ? $lic['message'] : 'Invalid license key';
    echo json_encode(['success' => false, 'message' => $msg]);
}
?>
