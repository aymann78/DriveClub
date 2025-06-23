<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'lib/PHPMailer/src/Exception.php';
require 'lib/PHPMailer/src/PHPMailer.php';
require 'lib/PHPMailer/src/SMTP.php';

/**
 * Envía un correo electrónico usando el SMTP de Gmail.
 *
 * @param string $to Dirección de correo del destinatario
 * @param string $subject Asunto del correo
 * @param string $body Cuerpo del correo (HTML o texto plano)
 * @param bool $isHtml Indica si el cuerpo es HTML (true) o texto plano (false)
 * @param string|null $attachmentPath Ruta del archivo adjunto (opcional, usado para el QR)
 * @param string|null $attachmentCid ID para incrustar el archivo en el HTML (opcional)
 * @return bool Verdadero si el correo se envió correctamente, falso en caso contrario
 */
function sendEmail($to, $subject, $body, $isHtml = true, $attachmentPath = null, $attachmentCid = null) {
    $mail = new PHPMailer(true);
    
    try {
        // Configurar depuración SMTP (solo en log, no en pantalla)
        $mail->SMTPDebug = 2; // Nivel de detalle (0 = sin debug, 2 = detallado)
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer [$level]: $str"); // Guardar en el log del servidor
            // Eliminamos el echo para evitar salida en pantalla
        };

        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Remitente y destinatario
        $mail->setFrom('', 'DriveClub');
        $mail->addAddress($to);

        // Opciones SMTP (solo para pruebas locales, quitar en producción)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Contenido del correo
        $mail->CharSet = 'UTF-8';
        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Si hay un archivo adjunto (como el QR)
        if ($attachmentPath && $attachmentCid) {
            if (file_exists($attachmentPath)) {
                $mail->addEmbeddedImage($attachmentPath, $attachmentCid);
            } else {
                error_log("Archivo adjunto no encontrado: $attachmentPath");
            }
        }

        // Enviar el correo
        $mail->send();
        error_log("Correo enviado exitosamente a $to");
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo a $to: {$mail->ErrorInfo}");
        return false;
    }
}
?>