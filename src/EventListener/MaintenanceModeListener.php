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

        // Afficher la page de maintenance
        $content = $this->twig->render('maintenance/maintenance.html.twig', [
            'message' => $message,
            'end_time' => $endTime ? new \DateTime('@' . $endTime) : null
        ]);

        $response = new Response($content, Response::HTTP_SERVICE_UNAVAILABLE);
        $response->headers->set('Retry-After', '300');

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10], // Priorité élevée
        ];
    }
}
