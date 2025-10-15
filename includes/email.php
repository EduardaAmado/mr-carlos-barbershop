<?php
/**
 * Sistema de Email - Mr. Carlos Barbershop
 * Autor: Sistema Mr. Carlos Barbershop
 * Data: 14 de Outubro de 2025
 * Finalidade: Sistema completo de envio de emails com PHPMailer
 */

// Importar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carregar autoloader do Composer (assumindo instalação via Composer)
// Se não tiver Composer, descomente as linhas abaixo e baixe o PHPMailer manualmente
require_once __DIR__ . '/../vendor/autoload.php';

// Configurações de email (configure estas constantes no config.php)
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com'); // ou seu servidor SMTP
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', 'seu-email@gmail.com');
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', 'sua-senha-de-app');
if (!defined('SMTP_FROM_EMAIL')) define('SMTP_FROM_EMAIL', SMTP_USERNAME);
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'Mr. Carlos Barbershop');

/**
 * Classe principal para envio de emails
 */
class EmailService {
    private $mailer;
    private $debug_mode;
    
    public function __construct($debug_mode = false) {
        $this->debug_mode = $debug_mode;
        $this->configurarMailer();
    }
    
    /**
     * Configurar PHPMailer com as configurações SMTP
     */
    private function configurarMailer() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Configurações do servidor SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = SMTP_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = SMTP_USERNAME;
            $this->mailer->Password = SMTP_PASSWORD;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = SMTP_PORT;
            
            // Configurações do remetente
            $this->mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            
            // Configurações gerais
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
            
            // Debug (apenas se habilitado)
            if ($this->debug_mode) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            
        } catch (Exception $e) {
            error_log("Erro ao configurar PHPMailer: " . $e->getMessage());
            throw new Exception("Erro na configuração do email");
        }
    }
    
    /**
     * Enviar email de confirmação de agendamento
     */
    public function enviarConfirmacaoAgendamento($dados) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($dados['cliente_email'], $dados['cliente_nome']);
            
            $this->mailer->Subject = 'Confirmação de Agendamento - Mr. Carlos Barbershop';
            
            $html = $this->templateConfirmacaoAgendamento($dados);
            $this->mailer->Body = $html;
            
            // Versão texto alternativa
            $this->mailer->AltBody = $this->extrairTextoSimples($html);
            
            $resultado = $this->mailer->send();
            
            if ($resultado) {
                error_log("Email de confirmação enviado para: " . $dados['cliente_email']);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar confirmação: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar lembrete de agendamento (24h antes)
     */
    public function enviarLembreteAgendamento($dados) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($dados['cliente_email'], $dados['cliente_nome']);
            
            $this->mailer->Subject = 'Lembrete: Seu agendamento é amanhã - Mr. Carlos Barbershop';
            
            $html = $this->templateLembreteAgendamento($dados);
            $this->mailer->Body = $html;
            $this->mailer->AltBody = $this->extrairTextoSimples($html);
            
            $resultado = $this->mailer->send();
            
            if ($resultado) {
                error_log("Lembrete enviado para: " . $dados['cliente_email']);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar lembrete: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificação de cancelamento
     */
    public function enviarCancelamentoAgendamento($dados) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($dados['cliente_email'], $dados['cliente_nome']);
            
            $this->mailer->Subject = 'Agendamento Cancelado - Mr. Carlos Barbershop';
            
            $html = $this->templateCancelamentoAgendamento($dados);
            $this->mailer->Body = $html;
            $this->mailer->AltBody = $this->extrairTextoSimples($html);
            
            $resultado = $this->mailer->send();
            
            if ($resultado) {
                error_log("Cancelamento enviado para: " . $dados['cliente_email']);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar cancelamento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar email de contato do site
     */
    public function enviarEmailContato($dados) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress(SMTP_FROM_EMAIL, 'Administração');
            $this->mailer->addReplyTo($dados['email'], $dados['nome']);
            
            $this->mailer->Subject = 'Novo Contato do Site - ' . $dados['assunto'];
            
            $html = $this->templateEmailContato($dados);
            $this->mailer->Body = $html;
            $this->mailer->AltBody = $this->extrairTextoSimples($html);
            
            $resultado = $this->mailer->send();
            
            if ($resultado) {
                error_log("Email de contato enviado de: " . $dados['email']);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar contato: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Template para confirmação de agendamento
     */
    private function templateConfirmacaoAgendamento($dados) {
        $data_formatada = date('d/m/Y \à\s H:i', strtotime($dados['data_hora']));
        
        return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Confirmação de Agendamento</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 0; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #C9A227 0%, #B8941F 100%); color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>Mr. Carlos Barbershop</h1>
                    <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Agendamento Confirmado</p>
                </div>
                
                <!-- Conteúdo -->
                <div style='padding: 30px;'>
                    <h2 style='color: #C9A227; margin-top: 0;'>Olá, {$dados['cliente_nome']}!</h2>
                    
                    <p style='font-size: 16px; margin-bottom: 25px;'>
                        Seu agendamento foi confirmado com sucesso! Aqui estão os detalhes:
                    </p>
                    
                    <!-- Detalhes do Agendamento -->
                    <div style='background-color: #f8f9fa; border-left: 4px solid #C9A227; padding: 20px; margin: 25px 0;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; width: 30%;'>Serviço:</td>
                                <td style='padding: 8px 0;'>{$dados['servico_nome']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Barbeiro:</td>
                                <td style='padding: 8px 0;'>{$dados['barbeiro_nome']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Data e Hora:</td>
                                <td style='padding: 8px 0; color: #C9A227; font-weight: bold;'>{$data_formatada}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Preço:</td>
                                <td style='padding: 8px 0;'>R$ " . number_format($dados['preco'], 2, ',', '.') . "</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Instruções -->
                    <div style='background-color: #e8f4f8; border: 1px solid #b8e6ff; border-radius: 5px; padding: 20px; margin: 25px 0;'>
                        <h3 style='margin-top: 0; color: #2c5282;'>Instruções Importantes:</h3>
                        <ul style='margin: 0; padding-left: 20px;'>
                            <li style='margin-bottom: 8px;'>Chegue com 10 minutos de antecedência</li>
                            <li style='margin-bottom: 8px;'>Traga um documento de identificação</li>
                            <li style='margin-bottom: 8px;'>Em caso de cancelamento, avise com 24h de antecedência</li>
                            <li>Pagamento pode ser feito em dinheiro, cartão ou PIX</li>
                        </ul>
                    </div>
                    
                    <p style='font-size: 16px; text-align: center; margin: 30px 0;'>
                        <strong>Endereço:</strong><br>
                        Rua das Palmeiras, 123 - Centro<br>
                        São Paulo, SP - CEP: 01234-567<br>
                        <strong>Telefone:</strong> (11) 99999-9999
                    </p>
                    
                    <!-- Botão -->
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . get_base_url('pages/perfil.php') . "' 
                           style='background: linear-gradient(135deg, #C9A227 0%, #B8941F 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                            Ver Meus Agendamentos
                        </a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #2d3748; color: white; padding: 20px; text-align: center; font-size: 14px;'>
                    <p style='margin: 0;'>Mr. Carlos Barbershop - Tradição e qualidade desde 1985</p>
                    <p style='margin: 5px 0 0 0; opacity: 0.8;'>
                        Este é um email automático. Em caso de dúvidas, entre em contato conosco.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Template para lembrete de agendamento
     */
    private function templateLembreteAgendamento($dados) {
        $data_formatada = date('d/m/Y \à\s H:i', strtotime($dados['data_hora']));
        
        return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Lembrete de Agendamento</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 0; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>Mr. Carlos Barbershop</h1>
                    <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Lembrete de Agendamento</p>
                </div>
                
                <!-- Conteúdo -->
                <div style='padding: 30px;'>
                    <h2 style='color: #2563eb; margin-top: 0;'>Olá, {$dados['cliente_nome']}!</h2>
                    
                    <p style='font-size: 18px; margin-bottom: 25px; color: #1f2937; font-weight: 600;'>
                        Seu agendamento é <strong>amanhã</strong>! Não se esqueça:
                    </p>
                    
                    <!-- Detalhes do Agendamento -->
                    <div style='background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; border-radius: 10px; padding: 25px; margin: 25px 0; text-align: center;'>
                        <h3 style='margin: 0 0 15px 0; color: #92400e; font-size: 20px;'>{$dados['servico_nome']}</h3>
                        <p style='margin: 0; font-size: 24px; font-weight: bold; color: #C9A227;'>{$data_formatada}</p>
                        <p style='margin: 10px 0 0 0; color: #92400e;'>com {$dados['barbeiro_nome']}</p>
                    </div>
                    
                    <!-- Instruções de Chegada -->
                    <div style='background-color: #ecfdf5; border: 1px solid #10b981; border-radius: 5px; padding: 20px; margin: 25px 0;'>
                        <h3 style='margin-top: 0; color: #047857;'>⏰ Lembre-se:</h3>
                        <ul style='margin: 0; padding-left: 20px; color: #065f46;'>
                            <li style='margin-bottom: 8px;'>Chegue 10 minutos antes do horário</li>
                            <li style='margin-bottom: 8px;'>Traga documento de identificação</li>
                            <li>Se não puder comparecer, cancele com antecedência</li>
                        </ul>
                    </div>
                    
                    <p style='font-size: 16px; text-align: center; margin: 30px 0; background-color: #f3f4f6; padding: 15px; border-radius: 5px;'>
                        <strong>📍 Endereço:</strong><br>
                        Rua das Palmeiras, 123 - Centro<br>
                        São Paulo, SP<br>
                        <strong>📞 Telefone:</strong> (11) 99999-9999
                    </p>
                    
                    <!-- Botões -->
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . get_base_url('pages/perfil.php') . "' 
                           style='background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; margin-right: 15px;'>
                            Ver Detalhes
                        </a>
                        <a href='" . get_base_url('pages/perfil.php') . "' 
                           style='background: #ef4444; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                            Cancelar
                        </a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #2d3748; color: white; padding: 20px; text-align: center; font-size: 14px;'>
                    <p style='margin: 0;'>Mr. Carlos Barbershop - Esperamos por você!</p>
                    <p style='margin: 5px 0 0 0; opacity: 0.8;'>
                        Este é um lembrete automático. Obrigado por escolher nossos serviços.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Template para cancelamento de agendamento
     */
    private function templateCancelamentoAgendamento($dados) {
        $data_formatada = date('d/m/Y \à\s H:i', strtotime($dados['data_hora']));
        
        return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Agendamento Cancelado</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 0; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>Mr. Carlos Barbershop</h1>
                    <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Agendamento Cancelado</p>
                </div>
                
                <!-- Conteúdo -->
                <div style='padding: 30px;'>
                    <h2 style='color: #dc2626; margin-top: 0;'>Olá, {$dados['cliente_nome']}</h2>
                    
                    <p style='font-size: 16px; margin-bottom: 25px;'>
                        Seu agendamento foi cancelado conforme solicitado:
                    </p>
                    
                    <!-- Detalhes Cancelados -->
                    <div style='background-color: #fef2f2; border-left: 4px solid #dc2626; padding: 20px; margin: 25px 0;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; width: 30%;'>Serviço:</td>
                                <td style='padding: 8px 0; text-decoration: line-through; opacity: 0.7;'>{$dados['servico_nome']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Barbeiro:</td>
                                <td style='padding: 8px 0; text-decoration: line-through; opacity: 0.7;'>{$dados['barbeiro_nome']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Data e Hora:</td>
                                <td style='padding: 8px 0; text-decoration: line-through; opacity: 0.7;'>{$data_formatada}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <p style='font-size: 16px; color: #4b5563;'>
                        Sentimos muito pelo cancelamento. Ficamos à disposição para um novo agendamento 
                        quando você desejar.
                    </p>
                    
                    <!-- Novo Agendamento -->
                    <div style='text-align: center; margin: 30px 0; background-color: #f0fdf4; padding: 20px; border-radius: 8px; border: 1px solid #16a34a;'>
                        <h3 style='margin: 0 0 15px 0; color: #16a34a;'>Que tal agendar novamente?</h3>
                        <p style='margin: 0 0 20px 0; color: #15803d;'>
                            Estamos sempre prontos para cuidar do seu visual com excelência.
                        </p>
                        <a href='" . get_base_url('pages/agendar.php') . "' 
                           style='background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>
                            Fazer Novo Agendamento
                        </a>
                    </div>
                    
                    <p style='font-size: 14px; text-align: center; color: #6b7280; margin: 30px 0;'>
                        <strong>Contato:</strong> (11) 99999-9999 | Rua das Palmeiras, 123 - Centro, São Paulo
                    </p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #2d3748; color: white; padding: 20px; text-align: center; font-size: 14px;'>
                    <p style='margin: 0;'>Mr. Carlos Barbershop - Sempre à sua disposição</p>
                    <p style='margin: 5px 0 0 0; opacity: 0.8;'>
                        Agradecemos sua compreensão e esperamos vê-lo em breve.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Template para email de contato
     */
    private function templateEmailContato($dados) {
        return "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Novo Contato do Site</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 0; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>Novo Contato do Site</h1>
                    <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>Mr. Carlos Barbershop</p>
                </div>
                
                <!-- Conteúdo -->
                <div style='padding: 30px;'>
                    <h2 style='color: #6366f1; margin-top: 0;'>Dados do Contato:</h2>
                    
                    <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; padding: 20px; margin: 20px 0;'>
                        <table style='width: 100%; border-collapse: collapse;'>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; width: 25%;'>Nome:</td>
                                <td style='padding: 8px 0;'>{$dados['nome']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Email:</td>
                                <td style='padding: 8px 0;'>{$dados['email']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Telefone:</td>
                                <td style='padding: 8px 0;'>" . ($dados['telefone'] ?? 'Não informado') . "</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold;'>Assunto:</td>
                                <td style='padding: 8px 0;'>{$dados['assunto']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; font-weight: bold; vertical-align: top;'>Data:</td>
                                <td style='padding: 8px 0;'>" . date('d/m/Y H:i:s') . "</td>
                            </tr>
                        </table>
                    </div>
                    
                    <h3 style='color: #374151; margin: 25px 0 10px 0;'>Mensagem:</h3>
                    <div style='background-color: #f9fafb; border-left: 4px solid #6366f1; padding: 20px; margin: 15px 0; font-style: italic; white-space: pre-line;'>" . 
                        htmlspecialchars($dados['mensagem']) . 
                    "</div>
                    
                    <p style='font-size: 14px; color: #6b7280; text-align: center; margin: 30px 0;'>
                        <strong>Atenção:</strong> Responda este email para entrar em contato diretamente com o cliente.
                    </p>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #2d3748; color: white; padding: 20px; text-align: center; font-size: 14px;'>
                    <p style='margin: 0;'>Sistema Automático - Mr. Carlos Barbershop</p>
                    <p style='margin: 5px 0 0 0; opacity: 0.8;'>
                        Email enviado automaticamente pelo formulário de contato do site.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Extrair texto simples do HTML para versão alternativa
     */
    private function extrairTextoSimples($html) {
        $texto = strip_tags($html);
        $texto = html_entity_decode($texto, ENT_QUOTES, 'UTF-8');
        $texto = preg_replace('/\s+/', ' ', $texto);
        return trim($texto);
    }
}

/**
 * Função para enviar emails de agendamento (para usar nas APIs)
 */
function enviar_email_agendamento($tipo, $dados) {
    try {
        $emailService = new EmailService(false); // debug_mode = false
        
        switch ($tipo) {
            case 'confirmacao':
                return $emailService->enviarConfirmacaoAgendamento($dados);
            case 'lembrete':
                return $emailService->enviarLembreteAgendamento($dados);
            case 'cancelamento':
                return $emailService->enviarCancelamentoAgendamento($dados);
            default:
                return false;
        }
    } catch (Exception $e) {
        error_log("Erro no envio de email: " . $e->getMessage());
        return false;
    }
}

/**
 * Função para enviar email de contato
 */
function enviar_email_contato($dados) {
    try {
        $emailService = new EmailService(false);
        return $emailService->enviarEmailContato($dados);
    } catch (Exception $e) {
        error_log("Erro no envio de email de contato: " . $e->getMessage());
        return false;
    }
}

/**
 * Função para processar fila de lembretes automáticos
 * Esta função deve ser executada via CRON diariamente
 */
function processar_lembretes_automaticos() {
    try {
        require_once __DIR__ . '/../config/config.php';
        
        // Buscar agendamentos para amanhã que ainda não receberam lembrete
        $amanha = date('Y-m-d', strtotime('+1 day'));
        $agendamentos_result = execute_prepared_query(
            "SELECT a.*, c.nome as cliente_nome, c.email as cliente_email,
                    b.nome as barbeiro_nome, s.nome as servico_nome, s.preco
             FROM agendamentos a
             LEFT JOIN clientes c ON a.cliente_id = c.id
             LEFT JOIN barbeiros b ON a.barbeiro_id = b.id
             LEFT JOIN servicos s ON a.servico_id = s.id
             WHERE DATE(a.data_hora) = ? 
             AND a.status IN ('agendado', 'confirmado')
             AND (a.lembrete_enviado IS NULL OR a.lembrete_enviado = 0)
             AND c.email IS NOT NULL AND c.email != ''",
            [$amanha],
            's'
        );
        
        $emails_enviados = 0;
        
        if ($agendamentos_result) {
            while ($agendamento = $agendamentos_result->fetch_assoc()) {
                $dados_email = [
                    'cliente_nome' => $agendamento['cliente_nome'],
                    'cliente_email' => $agendamento['cliente_email'],
                    'barbeiro_nome' => $agendamento['barbeiro_nome'],
                    'servico_nome' => $agendamento['servico_nome'],
                    'data_hora' => $agendamento['data_hora'],
                    'preco' => $agendamento['preco']
                ];
                
                if (enviar_email_agendamento('lembrete', $dados_email)) {
                    // Marcar como lembrete enviado
                    execute_prepared_query(
                        "UPDATE agendamentos SET lembrete_enviado = 1 WHERE id = ?",
                        [$agendamento['id']],
                        'i'
                    );
                    $emails_enviados++;
                }
                
                // Pausa entre envios para não sobrecarregar o servidor
                sleep(1);
            }
        }
        
        error_log("Lembretes processados: {$emails_enviados} emails enviados");
        return $emails_enviados;
        
    } catch (Exception $e) {
        error_log("Erro no processamento de lembretes: " . $e->getMessage());
        return false;
    }
}
?>