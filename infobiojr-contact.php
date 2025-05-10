<?php
/*
Plugin Name: Contato InfoBioJr
Description: Plugin para envio de emails via PHPMailer.
Version: 1.1
Author: InfoBioJr
*/

// Esta versão foi feita pelo José Augusto R. de Andrade em 2025.

// Impede acesso direto
if (!defined('ABSPATH')) {
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

require 'PHPMailer/vendor/autoload.php';
require_once 'envloader.php';
loadEnv(__DIR__ . '/.env');

function infobiojr_handle_form() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
        
        $name = strip_tags(htmlspecialchars($_POST['name']));
        $email = strip_tags(htmlspecialchars($_POST['email']));
        $assunto = strip_tags(htmlspecialchars($_POST['assunto']));
        $message = strip_tags(htmlspecialchars($_POST['message']));

        $mail = new PHPMailer(TRUE);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPAuth = true;
        $mail->AuthType = 'XOAUTH2';
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        $mail = $_ENV['AUTH_MAIL'];
	$clientId = $_ENV['CLIENT_ID'];
        $clientSecret = $_ENV['CLIENT_SECRET'];
        $refreshToken = $_ENV['REFRESH_TOKEN'];

        $provider = new Google([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]);

        $mail->setOAuth(
            new OAuth([
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName' => 'contato@infobiojr.com.br',
            ])
        );

        $email_da_infobio = 'contato@infobiojr.com.br';

        $mail->setFrom($email_da_infobio, $name);
        $mail->addAddress($email_da_infobio, 'infobioJr');
        $mail->addCC('contato@infobiojr.com.br', 'InfoBioJr.');
        $mail->Subject = $assunto;
        $mail->Body = "Email do cliente: $email\nNome do cliente: $name\n\nConteúdo:\n$message";

        if (!$mail->send()) {
            	error_log('Erro PHPMailer: ' . $mail->ErrorInfo);  // ← Isso ajuda no log do servidor
    		wp_send_json_error(['message' => 'Erro ao enviar: ' . $mail->ErrorInfo]);
	

        } else {
            wp_send_json_success(['message' => 'Email enviado com sucesso!']);
        }
    }
}

add_action('wp_ajax_infobiojr_contact', 'infobiojr_handle_form');
add_action('wp_ajax_nopriv_infobiojr_contact', 'infobiojr_handle_form');

function infobiojr_contact_form() {
    ob_start(); ?>
<form id="infobiojr-contact-form" method="POST">
    <section class="text-gray-600 body-font relative">
      <div class="container px-5 py-0 mx-auto">
        <div class="lg:w-1/2 md:w-2/3 mx-auto">
          <div class="flex flex-wrap -m-2">
            <div class="p-2 w-1/2">
              <div class="relative">
                <label for="nome" class="leading-7 text-sm text-gray-600">Nome*</label>
                <input type="text" id="nome" name="name" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
              </div>
            </div>
            <div class="p-2 w-1/2">
              <div class="relative">
                <label for="email" class="leading-7 text-sm text-gray-600">Email*</label>
                <input type="email" id="email" name="email" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
              </div>
            </div>
            <div class="p-2 w-full">
              <div class="relative">
                <label for="assunto" class="leading-7 text-sm text-gray-600">Assunto*</label>
                <input type="text" id="assunto" name="assunto" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out">
              </div>
            </div>
            <div class="p-2 w-full">
              <div class="relative">
                <label for="mensagem" class="leading-7 text-sm text-gray-600">Mensagem*</label>
                <textarea id="mensagem" name="message" class="w-full bg-gray-100 bg-opacity-50 rounded border border-gray-300 focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-200 h-32 text-base outline-none text-gray-700 py-1 px-3 resize-none leading-6 transition-colors duration-200 ease-in-out"></textarea>
              </div>
            </div>
            <div class="p-2 w-full">
              <button class="flex mx-auto text-white bg-[#5F7A5F] border-0 py-2 px-8 focus:outline-none hover:bg-[#425442] rounded text-lg">Enviar</button>
            </div>
            <div class="p-2 w-full pt-8 mt-8 border-t border-gray-200 text-center">
            	<a href="mailto:contato@infobiojr.com.br" class="text-green-700 font-semibold hover:underline">
			contato@infobiojr.com.br
		</a>
              <p class="leading-normal my-5">Universidade de São Paulo - Ribeirão Preto</p>
            </div>
          </div>
        </div>
      </div>
    </section>
<div id="infobiojr-response" class="text-center mt-4 text-green-600 font-semibold"></div>

</form>
    <script>
	document.addEventListener("DOMContentLoaded", function () {
    	const form = document.getElementById('infobiojr-contact-form');
    	if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=infobiojr_contact', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('infobiojr-response').textContent = data.success ? data.data.message : 'Erro ao enviar!';
            })
            .catch(error => console.error('Erro:', error));
        });
    } else {
        console.error("Erro: Formulário 'infobiojr-contact-form' não encontrado.");
    }
});
	</script>

    <?php return ob_get_clean();
}

add_shortcode('infobiojr_form', 'infobiojr_contact_form');

