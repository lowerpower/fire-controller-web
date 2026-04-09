<?php
header('Content-Type: application/json');

$web_root = __DIR__;
$personality_dir = $web_root . '/personality';
$uploads_path = $web_root . '/uploads';

function json_response($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function list_personalities($dir)
{
    $out = array();

    if (!is_dir($dir)) {
        return $out;
    }

    foreach (scandir($dir) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        if (is_dir($dir . '/' . $entry)) {
            $out[] = $entry;
        }
    }

    sort($out, SORT_NATURAL | SORT_FLAG_CASE);
    return $out;
}

function current_personality($uploads_path, $personality_dir)
{
    if (!is_link($uploads_path)) {
        return null;
    }

    $target = readlink($uploads_path);
    if ($target === false) {
        return null;
    }

    if ($target[0] !== '/') {
        $target = realpath(dirname($uploads_path) . '/' . $target);
    } else {
        $target = realpath($target);
    }

    $base = realpath($personality_dir);
    if ($target === false || $base === false) {
        return null;
    }

    if (strpos($target, $base . DIRECTORY_SEPARATOR) !== 0) {
        return null;
    }

    return basename($target);
}

function backup_uploads_dir($uploads_path)
{
    if (!is_dir($uploads_path) || is_link($uploads_path)) {
        return null;
    }

    $entries = array_diff(scandir($uploads_path), array('.', '..'));
    if (empty($entries)) {
        if (!rmdir($uploads_path)) {
            return false;
        }
        return 'removed_empty_uploads_dir';
    }

    $backup = dirname($uploads_path) . '/uploads.manual-backup-' . date('Ymd-His');
    if (!rename($uploads_path, $backup)) {
        return false;
    }

    return basename($backup);
}

function set_personality($uploads_path, $personality_dir, $personality)
{
    $target = realpath($personality_dir . '/' . $personality);
    $base = realpath($personality_dir);

    if ($target === false || $base === false || strpos($target, $base . DIRECTORY_SEPARATOR) !== 0) {
        return array('ok' => false, 'error' => 'invalid_personality');
    }

    $current = current_personality($uploads_path, $personality_dir);
    if ($current === $personality) {
        return array(
            'ok' => true,
            'changed' => false,
            'current' => $current,
            'target' => $target,
        );
    }

    if (is_link($uploads_path)) {
        if (!unlink($uploads_path)) {
            return array('ok' => false, 'error' => 'failed_to_remove_existing_link');
        }
    } else {
        $backup_result = backup_uploads_dir($uploads_path);
        if ($backup_result === false) {
            return array('ok' => false, 'error' => 'failed_to_prepare_uploads_path');
        }
    }

    if (!symlink($target, $uploads_path)) {
        return array('ok' => false, 'error' => 'failed_to_create_link');
    }

    return array(
        'ok' => true,
        'changed' => true,
        'current' => basename($target),
        'target' => $target,
    );
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'status';
$personalities = list_personalities($personality_dir);

if ($action === 'set') {
    $personality = isset($_REQUEST['personality']) ? trim($_REQUEST['personality']) : '';
    if ($personality === '' || !in_array($personality, $personalities, true)) {
        json_response(array(
            'ok' => false,
            'error' => 'unknown_personality',
            'personalities' => $personalities,
            'current' => current_personality($uploads_path, $personality_dir),
        ), 400);
    }

    $result = set_personality($uploads_path, $personality_dir, $personality);
    if (!$result['ok']) {
        $result['personalities'] = $personalities;
        $result['current'] = current_personality($uploads_path, $personality_dir);
        json_response($result, 500);
    }

    $result['personalities'] = $personalities;
    json_response($result);
}

json_response(array(
    'ok' => true,
    'current' => current_personality($uploads_path, $personality_dir),
    'personalities' => $personalities,
    'uploads_path' => $uploads_path,
    'uploads_is_link' => is_link($uploads_path),
));
