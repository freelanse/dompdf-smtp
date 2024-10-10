<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
// Подключение Dompdf
require 'dompdf/autoload.inc.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';
use Dompdf\Dompdf;
use Dompdf\Options;
use phpmailer\phpmailer\phpmailer;
use phpmailer\phpmailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');

    // Создаем HTML-содержимое для PD/F
    $html = "
    <html>
    <head>
<meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>PDF вложение</title>
        <style></style>
    </head>
    <body>
        <h1>PDF вложение</h1>
        <p><strong>Имя:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Сообщение:</strong> $message</p>
    </body>
    </html>
    ";

    // Настройки Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true);
    $options->set('tempDir', sys_get_temp_dir());
    $dompdf = new Dompdf($options);

    // Загрузка HTML-содержимого в Dompdf
    $dompdf->loadHtml($html);

    // Установка размера бумаги и ориентации
    $dompdf->setPaper('A4', 'portrait');

    // Рендеринг HTML как PDF
    $dompdf->render();

    // Сохранение PDF во временный файл
    $output = $dompdf->output();
    $pdfFilePath = sys_get_temp_dir() . '/mail.pdf';
    file_put_contents($pdfFilePath, $output);

    // Настройки PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Серверные настройки
        $mail->isSMTP();
        $mail->CharSet = "UTF-8";
        $mail->Host = 'mail.smtp.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'info@smtp.com';
        $mail->Password = '0xy04q';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        // Отправитель и получатель
        $mail->setFrom('info@smtp.com', $name);
        $mail->addAddress($email );
        $mail->addAddress('info@smtp.com');

        $mail->addAttachment($pdfFilePath, 'mail.pdf');
        // Содержимое письма
        $mail->isHTML(true);
        $mail->Subject = 'PDF Договор';
        $mail->Body    = 'Пожалуйста, не отвечайте на это сообщение. Оно сгенерировано автоматически';

        // Отправка письма
        $mail->send();
        $dompdf->stream("mail.pdf", ["Attachment" => 0]);
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }


}
?>
