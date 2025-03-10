<?php
// src/EventListener/MaintenanceModeListener.php
namespace App\EventListener;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class MaintenanceModeListener implements EventSubscriberInterface
{
    private $twig;
    private $params;
    private $maintenanceFilePath;
    private $allowedIps;

    public function __construct(
        Environment $twig,
        ParameterBagInterface $params,
        string $projectDir
    ) {
        $this->twig = $twig;
        $this->params = $params;
        $this->maintenanceFilePath = $projectDir . '/var/maintenance.lock';
        $this->allowedIps = $this->params->get('maintenance.allowed_ips');
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        // Vérifier si le mode maintenance est activé
        if (!file_exists($this->maintenanceFilePath)) {
            return;
        }

        $request = $event->getRequest();

        // Ignorer les requêtes pour les assets statiques
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/', $request->getPathInfo())) {
            return;
        }

        // Vérifier si l'IP du client est autorisée
        $clientIp = $request->getClientIp();
        if (in_array($clientIp, $this->allowedIps)) {
            return;
        }

        // Récupérer les informations de maintenance
        $maintenanceData = json_decode(file_get_contents($this->maintenanceFilePath), true);
        $endTime = $maintenanceData['end_time'] ?? null;
        $message = $maintenanceData['message'] ?? 'Site en maintenance. Merci de revenir plus tard.';

        /// Afficher la page de maintenance sans dépendre des assets compilés
        $content = $this->renderStandaloneMaintenancePage($message, $endTime);

        $response = new Response($content, Response::HTTP_SERVICE_UNAVAILABLE);
        $response->headers->set('Retry-After', '300');

        $event->setResponse($response);
    }

    private function renderStandaloneMaintenancePage(string $message, ?int $endTime): string
    {
        // Page de maintenance autonome avec CSS intégré
        return '<!DOCTYPE html>
        <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Site en maintenance</title>
                <style>
                    body {
                        font-family: \'Helvetica Neue\', Arial, sans-serif;
                        background-color: #f5f5f5;
                        color: #333;
                        text-align: center;
                        margin: 0;
                        padding: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                    }
                    .maintenance-container {
                        background-color: #fff;
                        border-radius: 8px;
                        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                        padding: 40px;
                        max-width: 600px;
                        width: 90%;
                    }
                    h1 {
                        color: #e74c3c;
                        margin-bottom: 20px;
                    }
                    .icon {
                        font-size: 60px;
                        margin-bottom: 20px;
                        color: #e74c3c;
                    }
                    .message {
                        margin-bottom: 30px;
                        line-height: 1.6;
                    }
                    .countdown {
                        background-color: #f8f9fa;
                        border-radius: 4px;
                        padding: 15px;
                        margin-top: 20px;
                        font-weight: bold;
                    }
                </style>
            </head>
            <body>
                <div class="maintenance-container">
                    <div class="icon">🛠️</div>
                    <h1>Site en maintenance</h1>
                    <div class="message">
                        ' . $message . '
                    </div>
                    ' . ($endTime ? $this->generateCountdownHtml($endTime) : '') . '
                </div>
            </body>
        </html>';
    }

    private function generateCountdownHtml(int $endTime): string
    {
        $dateTime = new \DateTime('@' . $endTime);
        $formattedDate = $dateTime->format('d/m/Y H:i');
        $isoDate = $dateTime->format('Y-m-d H:i:s');

        return '<div class="countdown">
            <p>Fin estimée: ' . $formattedDate . '</p>
            <div id="countdown-timer"></div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var endTime = new Date("' . $isoDate . '").getTime();

                var timer = setInterval(function() {
                    var now = new Date().getTime();
                    var distance = endTime - now;

                    if (distance < 0) {
                        clearInterval(timer);
                        document.getElementById("countdown-timer").innerHTML = "La maintenance devrait être terminée. Essayez de rafraîchir la page.";
                        return;
                    }

                    var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    document.getElementById("countdown-timer").innerHTML =
                        "Temps restant: " + hours + "h " + minutes + "m " + seconds + "s";
                }, 1000);
            });
        </script>';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10], // Priorité élevée
        ];
    }
}
