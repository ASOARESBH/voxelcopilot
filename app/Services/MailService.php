<?php
namespace App\Services;

use App\Core\Logger;

class MailService {

    /**
     * Envia e-mail via SMTP usando sockets nativos do PHP
     * Compatível com HostGator / cPanel sem extensões adicionais
     */
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody
    ): bool {
        $host     = $_ENV['MAIL_HOST']         ?? 'localhost';
        $port     = (int)($_ENV['MAIL_PORT']   ?? 587);
        $user     = $_ENV['MAIL_USERNAME']     ?? '';
        $pass     = $_ENV['MAIL_PASSWORD']     ?? '';
        $fromAddr = $_ENV['MAIL_FROM_ADDRESS'] ?? $user;
        $fromName = $_ENV['MAIL_FROM_NAME']    ?? 'VOXEL Copilot';
        $enc      = $_ENV['MAIL_ENCRYPTION']   ?? 'tls';

        // Fallback: usa mail() nativo se SMTP não configurado
        if (empty($user) || empty($host) || $host === 'localhost') {
            return self::sendNative($toEmail, $toName, $subject, $htmlBody, $fromAddr, $fromName);
        }

        try {
            return self::sendSmtp($host, $port, $user, $pass, $fromAddr, $fromName,
                $toEmail, $toName, $subject, $htmlBody, $enc);
        } catch (\Throwable $e) {
            Logger::error('Falha ao enviar e-mail via SMTP', [
                'to'    => $toEmail,
                'error' => $e->getMessage(),
            ]);
            // Tenta fallback nativo
            return self::sendNative($toEmail, $toName, $subject, $htmlBody, $fromAddr, $fromName);
        }
    }

    private static function sendNative(
        string $toEmail, string $toName,
        string $subject, string $htmlBody,
        string $fromAddr, string $fromName
    ): bool {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromAddr}>\r\n";
        $headers .= "Reply-To: {$fromAddr}\r\n";
        $headers .= "X-Mailer: VOXEL-Copilot\r\n";

        $result = @mail("{$toName} <{$toEmail}>", $subject, $htmlBody, $headers);
        if (!$result) {
            Logger::error('mail() nativo falhou', ['to' => $toEmail]);
        }
        return (bool) $result;
    }

    private static function sendSmtp(
        string $host, int $port,
        string $user, string $pass,
        string $fromAddr, string $fromName,
        string $toEmail, string $toName,
        string $subject, string $htmlBody,
        string $enc
    ): bool {
        $prefix = ($enc === 'ssl') ? 'ssl://' : '';
        $socket = @fsockopen("{$prefix}{$host}", $port, $errno, $errstr, 10);
        if (!$socket) {
            throw new \RuntimeException("Não foi possível conectar ao SMTP: {$errstr} ({$errno})");
        }

        $read = fgets($socket, 512);
        if (substr($read, 0, 3) !== '220') {
            throw new \RuntimeException("SMTP: resposta inesperada: {$read}");
        }

        $domain = parse_url($_ENV['APP_URL'] ?? 'localhost', PHP_URL_HOST) ?? 'localhost';

        self::smtpCmd($socket, "EHLO {$domain}");

        if ($enc === 'tls') {
            self::smtpCmd($socket, "STARTTLS");
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            self::smtpCmd($socket, "EHLO {$domain}");
        }

        self::smtpCmd($socket, "AUTH LOGIN");
        self::smtpCmd($socket, base64_encode($user));
        self::smtpCmd($socket, base64_encode($pass));
        self::smtpCmd($socket, "MAIL FROM:<{$fromAddr}>");
        self::smtpCmd($socket, "RCPT TO:<{$toEmail}>");
        self::smtpCmd($socket, "DATA");

        $boundary = md5(uniqid());
        $date     = date('r');
        $msg  = "Date: {$date}\r\n";
        $msg .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromAddr}>\r\n";
        $msg .= "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>\r\n";
        $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n";
        $msg .= "\r\n";
        $msg .= chunk_split(base64_encode($htmlBody));
        $msg .= "\r\n.";

        self::smtpCmd($socket, $msg);
        self::smtpCmd($socket, "QUIT");
        fclose($socket);

        return true;
    }

    private static function smtpCmd($socket, string $cmd): string {
        fputs($socket, $cmd . "\r\n");
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $response;
    }

    /**
     * Template de e-mail de boas-vindas com senha
     */
    public static function templateBoasVindas(string $nome, string $email, string $senha): string {
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Bem-vindo ao VOXEL Copilot</title>
</head>
<body style="margin:0;padding:0;background:#020c1b;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#020c1b;padding:40px 20px;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#0a1628;border-radius:16px;border:1px solid rgba(14,165,233,.2);overflow:hidden;">
      <!-- Header -->
      <tr>
        <td style="background:linear-gradient(135deg,#0ea5e9,#06b6d4);padding:32px 40px;text-align:center;">
          <div style="font-size:28px;font-weight:800;color:#fff;letter-spacing:-0.02em;">
            ✦ VOXEL <span style="opacity:.85">Copilot</span>
          </div>
          <div style="font-size:13px;color:rgba(255,255,255,.8);margin-top:6px;letter-spacing:.1em;text-transform:uppercase;">
            Sistema Operacional de Laudos
          </div>
        </td>
      </tr>
      <!-- Body -->
      <tr>
        <td style="padding:40px;">
          <p style="color:#e2e8f0;font-size:16px;margin:0 0 16px;">Olá, <strong>{$nome}</strong>!</p>
          <p style="color:#8fa3bf;font-size:14px;line-height:1.7;margin:0 0 28px;">
            Seu cadastro no <strong style="color:#0ea5e9;">VOXEL Copilot</strong> foi realizado com sucesso.
            Utilize as credenciais abaixo para acessar o sistema:
          </p>
          <!-- Credenciais -->
          <table width="100%" cellpadding="0" cellspacing="0" style="background:rgba(14,165,233,.06);border:1px solid rgba(14,165,233,.2);border-radius:12px;margin-bottom:28px;">
            <tr>
              <td style="padding:20px 24px;">
                <div style="margin-bottom:14px;">
                  <div style="font-size:11px;color:#4fc3f7;text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px;">E-mail de acesso</div>
                  <div style="font-size:15px;color:#e2e8f0;font-weight:600;">{$email}</div>
                </div>
                <div>
                  <div style="font-size:11px;color:#4fc3f7;text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px;">Senha temporária</div>
                  <div style="font-size:18px;color:#fff;font-weight:700;font-family:monospace;background:rgba(14,165,233,.1);padding:8px 14px;border-radius:8px;display:inline-block;letter-spacing:.08em;">{$senha}</div>
                </div>
              </td>
            </tr>
          </table>
          <p style="color:#8fa3bf;font-size:13px;line-height:1.7;margin:0 0 28px;">
            Por segurança, você será solicitado a <strong style="color:#e2e8f0;">alterar sua senha</strong> no primeiro acesso.
          </p>
          <!-- CTA -->
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td align="center">
                <a href="{$_ENV['APP_URL']}/login" style="display:inline-block;background:linear-gradient(135deg,#0ea5e9,#06b6d4);color:#fff;text-decoration:none;padding:14px 36px;border-radius:10px;font-weight:700;font-size:15px;letter-spacing:.03em;">
                  Acessar o VOXEL Copilot →
                </a>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <!-- Footer -->
      <tr>
        <td style="padding:20px 40px;border-top:1px solid rgba(14,165,233,.1);text-align:center;">
          <p style="color:#2d4a6a;font-size:12px;margin:0;">
            © 2026 VOXEL Copilot · Este é um e-mail automático, não responda.
          </p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }
}
