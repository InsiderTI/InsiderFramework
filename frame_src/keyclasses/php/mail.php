<?php
/**
  Arquivo KeyClass\Mail
*/

// Namespace das KeyClass
namespace KeyClass;

/**
   KeyClass de envio de email

   @package KeyClass\Mail
   @author Marcello Costa
*/
class Mail{
    /**
        Função para enviar emails
     
        @author Marcello Costa
      
        @package KeyClass\Mail
     
        @param  string  $to            Destinatários
        @param  string  $from          Remetente
        @param  string  $frompass      Senha do email do remetente
        @param  string  $subject       Assunto da mensagem
        @param  string  $message       Corpo da mensagem
        @param  string  $messagealt    Mensagem alternativa
        @param  string  $smtpserver    Servidor SMTP que enviará a mensagem
        @param  int     $smtpport      Porta do servidor SMTP que enviará a mensagem
        @param  bool    $smtpauth      Se o servidor SMTP utilizar autenticação
        @param  string  $smtpsecure    Qual é o tipo de autenticacão do servidor SMTP
                                       (valores possíveis: tls ou ssl)
        @param  string  $replyto       Para quem a mensagem será respondida
        @param  array   $ccto          Para quem a mensagem deverá ser enviada em cópia
        @param  array   $bccto         Para quem a mensagem deverá ser enviada em cópia oculta
        @param  array   $attachfiles   Caminho dos arquivos que serão anexados à mensagem
        @param  string  $content_type  Tipo de conteúdo da mensagem
        @param  string  $charset       Codificação da mensagem
     
        @return  bool   Retorno da operação de envio
    */
    public static function sendMail(string $to, string $from, string $frompass, string $subject, string $message, string $messagealt, string $smtpserver, int $smtpport, bool $smtpauth=true, string $smtpsecure="tls", array $replyto=null, array $ccto=null, array $bccto=null, array $attachfiles=null, string $content_type="text/html", string $charset=ENCODE) : bool {
        // Requerindo manualmente o PHPMailer
        $version = \KeyClass\Registry::getDependencyRequiredVersion('insider-framework','2.1.1','phpmailer');
        $phpmailerDir = INSTALL_DIR.DIRECTORY_SEPARATOR."frame_src".DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."php".DIRECTORY_SEPARATOR."phpmailer".DIRECTORY_SEPARATOR.$version;

        // Se não encontrar o diretório do phpmailer
        if (!is_dir($phpmailerDir)) {
            \KeyClass\Error::i10nErrorRegister("Error resizing image with invalid dimensions", 'pack/sys');
        }

        \KeyClass\FileTree::requireOnceFile($phpmailerDir.DIRECTORY_SEPARATOR."PHPMailerAutoload.php");

        // Criando novo objeto mailer
        $mail = new \PHPMailer;

        // Modificando o charset
        $mail->CharSet = $charset;

        // Dizendo que será utilizado SMTP
        $mail->isSMTP();

        // Especifica o servidor de envio
        $mail->Host = $smtpserver;

        // Habilita ou não a autenticação SMTP
        $mail->SMTPAuth = $smtpauth;

        // Usuário SMTP
        $mail->Username = $from;

        // Senha SMTP
        $mail->Password = $frompass;

        // Ativa a segurança do SMTP se necessário
        $mail->SMTPSecure = $smtpsecure;

        // Porta do SMTP
        $mail->Port = $smtpport;

        // Remetente
        $mail->setFrom($from, $from);

        // Destinatário
        $mail->addAddress($to);

        // Responder para
        $mail->addReplyTo($replyto, $replyto);

        // Cópia para
        if ($ccto !== null) {
            foreach ($ccto as $cc) {
                $mail->addCC($cc);
            }
        }

        // Cópia oculta
        if ($bccto !== null) {
            foreach ($bccto as $bcc) {
                $mail->addBCC($bcc);
            }
        }

        // Envia email com anexo
        if ($attachfiles !== null) {
            foreach ($attachfiles as $attfile) {
                $mail->addAttachment($attfile);
            }
        }

        // Se for HTML
        if ($content_type === "text/html") {
            $mail->isHTML(true);
        }
        else {
            $mail->isHTML(false);
        }

        // Definido assunto, mensagem e mensagem alternativa
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = $messagealt;

        // Definindo a linguagem do email
        $mail->setLanguage('br', $phpmailerDir.DIRECTORY_SEPARATOR."language".DIRECTORY_SEPARATOR);

        // Enviando email
        if (!$mail->send()) {
            return $mail->ErrorInfo;
        } else {
            return true;
        }
    }
}
