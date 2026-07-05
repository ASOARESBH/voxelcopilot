<?php
namespace App\Services;

use App\Core\Logger;

class MailService {

    /**
     * Envia e-mail com estratégia de fallback:
     * 1. SMTP configurado no .env (se MAIL_HOST e MAIL_USERNAME preenchidos)
     * 2. mail() nativo do PHP (funciona no HostGator sem configuração adicional)
     */
    public static function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody
    ): bool {
        $host     = $_ENV['MAIL_HOST']         ?? '';
        $port     = (int)($_ENV['MAIL_PORT']   ?? 587);
        $user     = $_ENV['MAIL_USERNAME']     ?? '';
        $pass     = $_ENV['MAIL_PASSWORD']     ?? '';
        $fromAddr = $_ENV['MAIL_FROM_ADDRESS'] ?? ($user ?: 'noreply@voxelpacs.com.br');
        $fromName = $_ENV['MAIL_FROM_NAME']    ?? 'VOXEL Copilot';
        $enc      = $_ENV['MAIL_ENCRYPTION']   ?? 'tls';

        // Se SMTP não configurado, usa mail() nativo diretamente
        if (empty($user) || empty($host) || in_array($host, ['localhost', '127.0.0.1', ''])) {
            Logger::info('MailService: usando mail() nativo (SMTP não configurado)', ['to' => $toEmail]);
            return self::sendNative($toEmail, $toName, $subject, $htmlBody, $fromAddr, $fromName);
        }

        // Tenta SMTP com timeout reduzido (5s para não travar a requisição)
        try {
            return self::sendSmtp($host, $port, $user, $pass, $fromAddr, $fromName,
                $toEmail, $toName, $subject, $htmlBody, $enc);
        } catch (\Throwable $e) {
            Logger::error('Falha ao enviar e-mail via SMTP — usando fallback nativo', [
                'to'    => $toEmail,
                'error' => $e->getMessage(),
            ]);
            // Fallback para mail() nativo
            return self::sendNative($toEmail, $toName, $subject, $htmlBody, $fromAddr, $fromName);
        }
    }

    /**
     * Envia usando a função mail() nativa do PHP.
     * Funciona no HostGator/cPanel sem configuração adicional.
     */
    private static function sendNative(
        string $toEmail, string $toName,
        string $subject, string $htmlBody,
        string $fromAddr, string $fromName
    ): bool {
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFrom    = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n";
        $headers .= "From: {$encodedFrom} <{$fromAddr}>\r\n";
        $headers .= "Reply-To: {$fromAddr}\r\n";
        $headers .= "X-Mailer: VOXEL-Copilot/1.0\r\n";

        $body   = chunk_split(base64_encode($htmlBody));
        $result = @mail("{$toName} <{$toEmail}>", $encodedSubject, $body, $headers);

        if (!$result) {
            Logger::error('mail() nativo falhou', ['to' => $toEmail]);
        } else {
            Logger::info('E-mail enviado via mail() nativo', ['to' => $toEmail]);
        }

        return (bool) $result;
    }

    /**
     * Envia via SMTP com socket nativo (sem PHPMailer).
     * Timeout reduzido para 5s para não travar a requisição.
     */
    private static function sendSmtp(
        string $host, int $port,
        string $user, string $pass,
        string $fromAddr, string $fromName,
        string $toEmail, string $toName,
        string $subject, string $htmlBody,
        string $enc
    ): bool {
        $prefix = ($enc === 'ssl') ? 'ssl://' : '';
        $socket = @fsockopen("{$prefix}{$host}", $port, $errno, $errstr, 5); // timeout 5s
        if (!$socket) {
            throw new \RuntimeException("Nao foi possivel conectar ao SMTP: {$errstr} ({$errno})");
        }

        stream_set_timeout($socket, 5);

        $read = fgets($socket, 512);
        if (substr($read, 0, 3) !== '220') {
            fclose($socket);
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

        $date = date('r');
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

        Logger::info('E-mail enviado via SMTP', ['to' => $toEmail, 'host' => $host]);
        return true;
    }

    private static function smtpCmd($socket, string $cmd): string {
        fputs($socket, $cmd . "\r\n");
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (strlen($line) >= 4 && substr($line, 3, 1) === ' ') break;
        }
        return $response;
    }

    /**
     * Template HTML de boas-vindas com credenciais
     */
    public static function templateBoasVindas(string $nome, string $email, string $senha): string {
        $appUrl = $_ENV['APP_URL'] ?? 'https://demo.voxelpacs.com.br';
        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Bem-vindo ao VOXEL Copilot</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:40px 20px;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">
      <tr>
        <td style="background:linear-gradient(135deg,#0d2244,#0ea5e9);padding:32px 40px;text-align:center;">
          <div style="font-size:26px;font-weight:800;color:#fff;letter-spacing:-0.02em;">VOXEL Copilot</div>
          <div style="font-size:12px;color:rgba(255,255,255,.75);margin-top:6px;letter-spacing:.12em;text-transform:uppercase;">Inteligencia que acelera o diagnostico por imagem</div>
        </td>
      </tr>
      <tr>
        <td style="padding:40px;">
          <p style="color:#1e293b;font-size:16px;margin:0 0 12px;">Ola, <strong>{$nome}</strong>!</p>
          <p style="color:#64748b;font-size:14px;line-height:1.7;margin:0 0 28px;">
            Seu cadastro no <strong style="color:#0ea5e9;">VOXEL Copilot</strong> foi realizado com sucesso.
            Utilize as credenciais abaixo para acessar o sistema:
          </p>
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;margin-bottom:28px;">
            <tr>
              <td style="padding:20px 24px;">
                <div style="margin-bottom:14px;">
                  <div style="font-size:11px;color:#0284c7;text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px;">E-mail de acesso</div>
                  <div style="font-size:15px;color:#0f172a;font-weight:600;">{$email}</div>
                </div>
                <div>
                  <div style="font-size:11px;color:#0284c7;text-transform:uppercase;letter-spacing:.1em;margin-bottom:4px;">Senha temporaria</div>
                  <div style="font-size:18px;color:#0f172a;font-weight:700;font-family:monospace;background:#e0f2fe;padding:8px 14px;border-radius:8px;display:inline-block;letter-spacing:.08em;">{$senha}</div>
                </div>
              </td>
            </tr>
          </table>
          <p style="color:#64748b;font-size:13px;line-height:1.7;margin:0 0 28px;">
            Por seguranca, recomendamos alterar sua senha apos o primeiro acesso.
          </p>
          <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td align="center">
                <a href="{$appUrl}/login" style="display:inline-block;background:linear-gradient(135deg,#0d2244,#0ea5e9);color:#fff;text-decoration:none;padding:14px 36px;border-radius:10px;font-weight:700;font-size:15px;letter-spacing:.03em;">
                  Acessar o VOXEL Copilot
                </a>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td style="padding:20px 40px;border-top:1px solid #e2e8f0;text-align:center;">
          <p style="color:#94a3b8;font-size:12px;margin:0;">
            &copy; 2026 VOXEL Copilot &middot; Este e-mail e automatico, nao responda.
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
