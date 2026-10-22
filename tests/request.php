<?php
/* Only served by the disposable test FPM pool. PHP 7.1-compatible syntax. */
$earlyError = error_get_last();
$action = isset($_GET['action']) ? $_GET['action'] : 'ping';
$result = array('pid' => getmypid(), 'php' => PHP_VERSION, 'action' => $action);

if ($action === 'produce') {
    /* Create request-owned values and nested handler stacks, then let normal
     * request shutdown release them. A later early INI warning must never call
     * these previous-request closures or use their captured state. */
    trigger_error('previous-request-error-state', E_USER_NOTICE);
    $captured = str_repeat('request-owned-state-', 128);
    for ($i = 0; $i < 3; $i++) {
        set_error_handler(function ($number, $message) use ($captured) {
            if (strpos($message, 'session.sid_length') !== false) {
                file_put_contents('/tmp/stale-callbacks', 'STALE_HANDLER_CALLED\n', FILE_APPEND);
            }
            return true;
        });
        set_exception_handler(function ($exception) use ($captured) {
            file_put_contents('/tmp/stale-callbacks', 'STALE_EXCEPTION_CALLED\n', FILE_APPEND);
        });
    }
    $result['produced'] = true;
    $result['auth_user'] = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
} elseif ($action === 'diagnostic') {
    $result['early_error'] = $earlyError;
    $result['auth_user'] = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
    $result['stale_callbacks'] = is_file('/tmp/stale-callbacks') ? file_get_contents('/tmp/stale-callbacks') : '';
} elseif ($action === 'session' || $action === 'upload') {
    if (!session_start()) {
        http_response_code(500);
        $result['error'] = 'session_start failed';
    } else {
        if ($action === 'session') {
            $_SESSION['counter'] = isset($_SESSION['counter']) ? $_SESSION['counter'] + 1 : 1;
        } else {
            $file = isset($_FILES['file']) ? $_FILES['file'] : null;
            $result['file'] = $file === null ? null : array(
                'name' => $file['name'], 'error' => $file['error'], 'size' => $file['size'],
                'is_uploaded_file' => is_uploaded_file($file['tmp_name']),
                'sha256' => is_file($file['tmp_name']) ? hash_file('sha256', $file['tmp_name']) : null
            );
            $result['progress_present'] = array_key_exists('upload_progress_probe', $_SESSION);
            $result['progress_ini_enabled'] = ini_get('session.upload_progress.enabled');
            $result['post'] = $_POST;
        }
        $result['counter'] = isset($_SESSION['counter']) ? $_SESSION['counter'] : 0;
        session_write_close();
    }
} elseif ($action === 'fields') {
    $result['post'] = $_POST;
    $result['limit'] = ini_get('max_multipart_body_parts');
}

header('Content-Type: application/json');
echo json_encode($result);
