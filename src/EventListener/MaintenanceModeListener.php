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
        //if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/', $request->getPathInfo())) {
        //    return;
        //}

        // Vérifier si l'IP du client est autorisée
        $clientIp = $request->getClientIp();
        if (in_array($clientIp, $this->allowedIps)) {
            return;
        }

        // Récupérer les informations de maintenance
        $maintenanceData = json_decode(file_get_contents($this->maintenanceFilePath), true);
        $endTime = $maintenanceData['end_time'] ?? null;
        $message = $maintenanceData['message'] ?? 'Site en maintenance. Merci de revenir plus tard.';

        try {
            // Essayer d'utiliser le template Twig
            $content = $this->twig->render('maintenance/maintenance.html.twig', [
                'message' => $message,
                'end_time' => $endTime ? new \DateTime('@' . $endTime) : null
            ]);
        } catch (\Exception $e) {
            // En cas d'erreur (ex: fichiers SASS manquants), utiliser un HTML de secours
            $content = $this->getFallbackMaintenanceHtml($message, $endTime);
        }

        $response = new Response($content, Response::HTTP_SERVICE_UNAVAILABLE);
        $response->headers->set('Retry-After', '300');

        $event->setResponse($response);
    }

    private function getFallbackMaintenanceHtml(string $message, ?int $endTime): string
    {
        // HTML minimal de secours avec CSS intégré
        $html = '<!DOCTYPE html>
        <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Site en maintenance</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                    .container { max-width: 600px; margin: 0 auto; }
                    h1 { color: #e74c3c; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Site en maintenance POPO</h1>
                    <p>' . htmlspecialchars($message) . '</p>';

        if ($endTime) {
            $dateTime = new \DateTime('@' . $endTime);
            $html .= '<p>Fin estimée: ' . $dateTime->format('d/m/Y H:i') . '</p>';
        }

        $html .= '</div></body></html>';

        return $html;
    }


    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10], // Priorité élevée
        ];
    }
}
