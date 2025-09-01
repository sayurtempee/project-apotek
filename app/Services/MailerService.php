<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService
{
    /**
     * Kirim email reset password ke user.
     *
     * @param string $to Email tujuan
     * @param string $resetLink Link reset password
     * @return bool|string True jika berhasil, pesan error jika gagal
     */
    public static function sendResetEmail($to, $resetLink)
    {
        $mail = new PHPMailer(true);

        try {
            // Konfigurasi SMTP
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');   // Email Gmail
            $mail->Password   = env('MAIL_PASSWORD');   // App Password Gmail
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
            $mail->Port       = env('MAIL_PORT', 587);

            // Pengirim & Penerima
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Apotek'));
            $mail->addAddress($to);

            // Konten Email
            $mail->isHTML(true);
            $mail->Subject = '🔑 Reset Password Anda';

            // Template email
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; color: #333; padding: 20px;'>
                    <h2 style='color:#0d6efd;'>Reset Password</h2>
                    <p>Halo,</p>
                    <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
                    <p>Silakan klik tombol di bawah ini untuk membuat password baru:</p>
                    <p style='text-align:center; margin: 20px 0;'>
                        <a href='{$resetLink}'
                           style='background:#0d6efd; color:#fff; padding:12px 20px; text-decoration:none; border-radius:6px;'>
                           Reset Password
                        </a>
                    </p>
                    <p>Jika Anda tidak merasa meminta reset password, abaikan email ini.</p>
                    <br>
                    <small style='color:#777;'>Email ini dikirim otomatis oleh sistem Apotek.</small>
                </div>
            ";

            $mail->AltBody = "Klik link berikut untuk reset password: $resetLink";

            $mail->send();
            return true;

        } catch (Exception $e) {
            return $mail->ErrorInfo;
        }
    }
}
