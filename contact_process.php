<?php
// contact_process.php
// НИЧЕГО не выводим до header() / JSON-ответа

// --- КОНФИГУРАЦИЯ (замените, если нужно) ---
$to = "info@youribouw.nl";                 // приёмная почта
$fallbackFrom = "noreply@nl-bouw.nl";      // адрес FROM вашего домена
$thankyouUrl = "/thankyou.html";           // куда редирект при обычной отправке (без AJAX)
$logo = "https://nl-bouw.nl/img/logo.png"; // рекламный логотип (абсолютный URL)
// -------------------------------------------------

// Получаем поля (безопасно)
$from_raw    = isset($_POST['email'])   ? trim($_POST['email'])   : '';
$name_raw    = isset($_POST['name'])    ? trim($_POST['name'])    : '';
$subject_raw = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$phone_raw   = isset($_POST['phone'])   ? trim($_POST['phone'])   : '';
$msg_raw     = isset($_POST['message']) ? trim($_POST['message']) : '';

// Функция защита от header-injection
function safe_header_value($str) {
    return preg_replace("/[\r\n]/", "", $str);
}

// Определим режим (AJAX если запрошен JSON)
function is_ajax_request() {
    return (
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

// Простая валидация email
if (!filter_var($from_raw, FILTER_VALIDATE_EMAIL)) {
    respond_and_exit(false, 'invalid_email');
}

// Ограничения длины полей
$name    = mb_substr($name_raw, 0, 200);
$subject = mb_substr($subject_raw, 0, 200);
$phone   = mb_substr($phone_raw, 0, 50);
$message = mb_substr($msg_raw, 0, 5000);

// Формируем заголовки — From: ваш домен; Reply-To: фактический отправитель
$headers  = "From: " . $fallbackFrom . "\r\n";
$headers .= "Reply-To: " . safe_header_value($from_raw) . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Тема письма
$mailSubject = "Nieuw bericht via nl-bouw.nl";

// Формируем HTML-тело письма
$body  = "<!DOCTYPE html><html lang='nl'><head><meta charset='UTF-8'><title>Contactformulier</title></head><body>";
$body .= "<table style='width:100%; font-family:Arial, sans-serif; border-collapse:collapse;'>";
if (!empty($logo)) {
    $body .= "<tr><td colspan='2' style='text-align:center; padding-bottom:10px;'><img src='".htmlspecialchars($logo, ENT_QUOTES, 'UTF-8')."' alt='Logo' style='max-width:200px;'></td></tr>";
}
$body .= "<tr><td style='border:none;'><strong>Naam:</strong> ".htmlspecialchars($name, ENT_QUOTES, 'UTF-8')."</td>";
$body .= "<td style='border:none;'><strong>E-mail:</strong> ".htmlspecialchars($from_raw, ENT_QUOTES, 'UTF-8')."</td></tr>";
$body .= "<tr><td style='border:none;'><strong>Onderwerp:</strong> ".htmlspecialchars($subject, ENT_QUOTES, 'UTF-8')."</td>";
$body .= "<td style='border:none;'><strong>Telefoonnummer:</strong> ".htmlspecialchars($phone, ENT_QUOTES, 'UTF-8')."</td></tr>";
$body .= "<tr><td colspan='2' style='border:none; padding-top:10px;'><strong>Bericht:</strong><br>".nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'))."</td></tr>";
$body .= "</table></body></html>";

// Попытка отправки (если функция mail доступна)
$sent = false;
if (function_exists('mail')) {
    // Флаг -f устанавливает envelope-from (может не поддерживаться хостом)
    $flags = '-f' . $fallbackFrom;
    $sent = @mail($to, $mailSubject, $body, $headers, $flags);
}

// Ответ (AJAX -> JSON, иначе редирект на thankyou)
function respond_and_exit($ok, $error_code = '') {
    global $thankyouUrl;
    $isAjax = is_ajax_request();

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'error' => $ok ? null : $error_code]);
        exit;
    } else {
        if ($ok) {
            header('Location: ' . $thankyouUrl);
            exit;
        } else {
            header('Location: /contact.html?error=' . urlencode($error_code));
            exit;
        }
    }
}

if ($sent) respond_and_exit(true);
else respond_and_exit(false, 'send_failed');
