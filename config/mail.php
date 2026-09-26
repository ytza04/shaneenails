<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Función para crear el objeto de correo
// Las credenciales ahora vienen del archivo .env

function crearMailer() {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = env('MAIL_HOST');
    $mail->SMTPAuth   = true;
    $mail->Username   = env('MAIL_USUARIO');
    $mail->Password   = env('MAIL_CLAVE');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = env('MAIL_PORT');
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(env('MAIL_USUARIO'), env('MAIL_NOMBRE'));

    return $mail;
}

// Funciones de envío

// Email cuando se agenda una cita

function emailCitaAgendada($email_cliente, $nombre_cliente, $datos_cita) {
    try {
        $mail = crearMailer();
        $mail->addAddress($email_cliente, $nombre_cliente);
        $mail->addAddress(env('MAIL_USUARIO'), env('MAIL_NOMBRE'));

        $mail->isHTML(true);
        $mail->Subject = 'Tu cita en ShaneeNails fue recibida';
        $mail->Body    = "
            <div style='font-family: Georgia, serif; max-width: 560px; margin: 0 auto; color: #3a3a3a;'>
                <div style='background-color: #F9D5E5; padding: 2rem; text-align: center;'>
                    <h1 style='color: #C9A96E; font-size: 2rem; margin: 0;'>ShaneeNails</h1>
                </div>
                <div style='padding: 2rem;'>
                    <p>Hola <strong>{$nombre_cliente}</strong>,</p>
                    <p>Tu solicitud de cita fue recibida correctamente. 
                       Shaneel la revisará y te enviará una cotización pronto.</p>

                    <div style='background-color: #F5ECD7; padding: 1.5rem; 
                                border-radius: 8px; margin: 1.5rem 0;'>
                        <p style='margin: 0.4rem 0;'>
                            <strong>Servicio:</strong> {$datos_cita['servicio']}
                        </p>
                        <p style='margin: 0.4rem 0;'>
                            <strong>Fecha:</strong> {$datos_cita['fecha']}
                        </p>
                        <p style='margin: 0.4rem 0;'>
                            <strong>Hora:</strong> {$datos_cita['hora']}
                        </p>
                        <p style='margin: 0.4rem 0;'>
                            <strong>Modalidad:</strong> {$datos_cita['modalidad']}
                        </p>
                    </div>

                    <p>Puedes revisar el estado de tu cita en tu portal.</p>
                    <a href='" . env('SITE_URL') . "/cliente/dashboard.php'
                       style='display: inline-block; background-color: #C9A96E; 
                              color: white; padding: 0.8rem 2rem; border-radius: 50px;
                              text-decoration: none; font-size: 0.9rem;'>
                        Ver mi portal
                    </a>
                </div>
                <div style='background-color: #F9D5E5; padding: 1rem; 
                            text-align: center; font-size: 0.8rem; color: #7a7a7a;'>
                    ShaneeNails — Todos los derechos reservados
                </div>
            </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Error al enviar email: ' . $e->getMessage());
        return false;
    }
}

// Email cuando Shaneel envía una cotización

function emailCotizacionEnviada($email_cliente, $nombre_cliente, $precio, $mensaje) {
    try {
        $mail = crearMailer();
        $mail->addAddress($email_cliente, $nombre_cliente);

        $mail->isHTML(true);
        $mail->Subject = 'Shaneel te envió una cotización';
        $mail->Body    = "
            <div style='font-family: Georgia, serif; max-width: 560px; margin: 0 auto; color: #3a3a3a;'>
                <div style='background-color: #F9D5E5; padding: 2rem; text-align: center;'>
                    <h1 style='color: #C9A96E; font-size: 2rem; margin: 0;'>ShaneeNails</h1>
                </div>
                <div style='padding: 2rem;'>
                    <p>Hola <strong>{$nombre_cliente}</strong>,</p>
                    <p>Shaneel revisó tu solicitud y te envió una cotización.</p>

                    <div style='background-color: #F5ECD7; padding: 1.5rem;
                                border-radius: 8px; margin: 1.5rem 0; text-align: center;'>
                        <p style='font-size: 0.8rem; color: #7a7a7a; margin: 0 0 0.5rem;'>
                            PRECIO
                        </p>
                        <p style='font-size: 2.5rem; color: #C9A96E; margin: 0;'>
                            \${$precio}
                        </p>
                        " . (!empty($mensaje) ? "
                        <p style='margin: 1rem 0 0; font-style: italic; color: #7a7a7a;'>
                            \"{$mensaje}\"
                        </p>" : "") . "
                    </div>

                    <p>Entra a tu portal para aceptar o rechazar la cotización.</p>
                    <a href='" . env('SITE_URL') . "/cliente/dashboard.php'
                       style='display: inline-block; background-color: #C9A96E;
                              color: white; padding: 0.8rem 2rem; border-radius: 50px;
                              text-decoration: none; font-size: 0.9rem;'>
                        Ver cotización
                    </a>
                </div>
                <div style='background-color: #F9D5E5; padding: 1rem;
                            text-align: center; font-size: 0.8rem; color: #7a7a7a;'>
                    ShaneeNails — Todos los derechos reservados
                </div>
            </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Error al enviar email: ' . $e->getMessage());
        return false;
    }
}

// Email cuando la clienta acepta dicha cotización

function emailCitaConfirmada($email_cliente, $nombre_cliente, $datos_cita) {
    try {
        $mail = crearMailer();
        $mail->addAddress($email_cliente, $nombre_cliente);
        $mail->addAddress(env('MAIL_USUARIO'), env('MAIL_NOMBRE'));

        $mail->isHTML(true);
        $mail->Subject = 'Cita confirmada en ShaneeNails';
        $mail->Body    = "
            <div style='font-family: Georgia, serif; max-width: 560px; margin: 0 auto; color: #3a3a3a;'>
                <div style='background-color: #F9D5E5; padding: 2rem; text-align: center;'>
                    <h1 style='color: #C9A96E; font-size: 2rem; margin: 0;'>ShaneeNails</h1>
                </div>
                <div style='padding: 2rem;'>
                    <p>Hola <strong>{$nombre_cliente}</strong>,</p>
                    <p>Tu cita está confirmada. Te esperamos.</p>

                    <div style='background-color: #F5ECD7; padding: 1.5rem;
                                border-radius: 8px; margin: 1.5rem 0;'>
                        <p style='margin: 0.4rem 0;'>
                            <strong>Servicio:</strong> {$datos_cita['servicio']}
                        </p>
                        <p style='margin: 0.4rem 0;'>
                            <strong>Fecha:</strong> {$datos_cita['fecha']}
                        </p>
                        <p style='margin: 0.4rem 0;'>
                            <strong>Hora:</strong> {$datos_cita['hora']}
                        </p>
                        <p style='margin: 0.4rem 0;'>
                            <strong>Modalidad:</strong> {$datos_cita['modalidad']}
                        </p>
                    </div>
                </div>
                <div style='background-color: #F9D5E5; padding: 1rem;
                            text-align: center; font-size: 0.8rem; color: #7a7a7a;'>
                    ShaneeNails — Todos los derechos reservados
                </div>
            </div>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Error al enviar email: ' . $e->getMessage());
        return false;
    }
}