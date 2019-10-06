<?php
/**
  Arquivo KeyClass\System
*/

// Namespace das KeyClass
namespace KeyClass;

/**
   KeyClass de funções do sistema

   @package KeyClass\System

   @author Marcello Costa
*/
class System{
    /**
        Função que verifica se o monitor de carregamento da CPU está ativo
     
        @author Marcello Costa
      
        @package KeyClass\System
     
        @return  void  Without return
    */
    public static function checkCpuAvg() : void {
        global $kernelspace;
        $urlRequested = $kernelspace->getVariable('urlRequested', 'insiderFrameworkSystem');
        $loadAVG = $kernelspace->getVariable('loadAVG', 'insiderFrameworkSystem');

        // Se a checagem está ativa
        if ($loadAVG["max_use"] > 0) {
            // Verifica o uso
            $load = sys_getloadavg();

            // Verificando a configuração informada
            switch ($loadAVG["time"]) {
                // Média de uso no último minuto
                case 1:
                    $loadAVG["timefunc"]=0;
                break;

                // Média de uso nos últimos 5 minutos
                case 5:
                    $loadAVG["timefunc"]=1;
                break;

                // Média de uso nos últimos 15 minutos
                case 15:
                    $loadAVG["timefunc"]=2;
                break;

                // Inválido
                default:
                    \KeyClass\Error::i10nErrorRegister('Invalid load_avg check time: %'.$loadAVG["time"].'%', 'pack/sys');
                break;
            }
            $kernelspace->setVariable(array('loadAVG' => $loadAVG), 'insiderFrameworkSystem');

            // Se a porcentagem de uso da CPU está acima do estipulado
            if ($load[$loadAVG["timefunc"]] > $loadAVG["max_use"]) {
                // Se é para enviar um email quando ocorrer
                if ($loadAVG['send_email'] == true) {
                    // Se não conseguir enviar um email para o mailbox default
                    if (!(\KeyClass\Mail::sendMail(MAILBOX, MAILBOX, MAILBOX_PASS, "Load AVG - InsiderFramework", "CPU usage alarm - ".REQUESTED_URL, "CPU usage alarm - ".REQUESTED_URL." - ".implode(",",$load), MAILBOX_SMTP, MAILBOX_SMTP_PORT, MAILBOX_SMTP_AUTH, MAILBOX_SMTP_SECURE))) {
                        // Envia uma mensagem para o log do apache
                        error_log("It was not possible to send an error message via email to the default mailbox!", 0);
                    }
                }

                // Throttle
                if (strpos($loadAVG['action'],'throttle') !== false) {
                  $throttle = explode('-', $loadAVG['action']);
                  if (count($throttle) <= 1 || intval($throttle[1]) === 0) {
                    \KeyClass\Error::i10nErrorRegister("Invalid time interval in LOAD_AVG_ACTION setting for throttle", 'pack/sys');
                  }
                  $loadAVG['action'] = 'throttle';
                  $kernelspace->setVariable(array('loadAVG' => $loadAVG), 'insiderFrameworkSystem');
                }

                switch (strtolower(trim($loadAVG['action']))) {
                    case 'throttle':
                        $throttleTime = intval($throttle[1]);
                        while ($load[$loadAVG["timefunc"]] > $loadAVG["max_use"]) {
                            // Aguarda
                            usleep($throttleTime);

                            // Verifica o uso
                            $load = sys_getloadavg();
                        }
                    break;

                    // Se é para exibir o erro amigável
                    case 'block-screen':
                        $urlRequested = "/error/loadAvg";
                        $KcRoute = new KeyClass\Route();
                        $KcRoute->RequestRoute("/error/loadAvg");
                        die();
                    break;

                    case 'deny':
                        die();
                    break;

                    default:
                        \KeyClass\Error::i10nErrorRegister("Invalid action '%" . $loadAVG['action'] . "%' in the LOAD_AVG_ACTION setting", 'pack/sys');
                    break;
                }
            }
        }
    }
}
