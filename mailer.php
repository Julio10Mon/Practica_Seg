<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function enviarCodigo($correoDestino, $codigo) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'julio.messi.montoya@gmail.com';
        $mail->Password   = 'xrwg cqxv ayur lxot';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('julio.messi.montoya@gmail.com', 'Sistema');

        $mail->ClearReplyTos();
        $mail->addReplyTo($correoDestino);
        $mail->Sender = $correoDestino;

        $mail->addAddress($correoDestino);

        $mail->isHTML(true);
        $mail->Subject = 'Tu código de acceso';
        $mail->Body    = "<h2>Tu código es: <b>$codigo</b></h2>";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
