<?php
// src/Command/MaintenanceModeCommand.php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:maintenance',
    description: 'Active ou désactive le mode maintenance',
)]
class MaintenanceModeCommand extends Command
{
    private $maintenanceFilePath;
    private $dirVar;

    public function __construct(string $projectDir)
    {
        parent::__construct();
        $this->maintenanceFilePath = $projectDir . '/var/maintenance.lock';
        $this->dirVar = $projectDir . '/var';
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'Action à effectuer (enable, disable, status)')
            ->addOption('duration', 'd', InputOption::VALUE_OPTIONAL, 'Durée de la maintenance en minutes')
            ->addOption('message', 'm', InputOption::VALUE_OPTIONAL, 'Message à afficher pendant la maintenance');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');

        switch ($action) {
            case 'enable':
                $duration = $input->getOption('duration');
                $message = $input->getOption('message') ?? 'Site en maintenance. Nous serons de retour bientôt!';

                $endTime = null;
                if ($duration) {
                    $endTime = time() + (intval($duration) * 60);
                }

                $maintenanceData = [
                    'enabled' => true,
                    'start_time' => time(),
                    'end_time' => $endTime,
                    'message' => $message,
                ];

                if (!is_dir(dirname($this->dirVar))) {
                    mkdir(dirname($this->dirVar), 0777, true);
                }

                file_put_contents($this->maintenanceFilePath, json_encode($maintenanceData));

                $io->success('Mode maintenance activé' . ($duration ? ' pour ' . $duration . ' minutes' : ''));
                break;

            case 'disable':
                if (file_exists($this->maintenanceFilePath)) {
                    unlink($this->maintenanceFilePath);
                    $io->success('Mode maintenance désactivé');
                } else {
                    $io->note('Le mode maintenance n\'était pas activé');
                }
                break;

            case 'status':
                if (file_exists($this->maintenanceFilePath)) {
                    $data = json_decode(file_get_contents($this->maintenanceFilePath), true);
                    $io->note('Le mode maintenance est activé');

                    if (isset($data['end_time'])) {
                        $remainingTime = $data['end_time'] - time();
                        if ($remainingTime > 0) {
                            $io->note(sprintf(
                                'Temps restant: %d minutes et %d secondes',
                                floor($remainingTime / 60),
                                $remainingTime % 60
                            ));
                        } else {
                            $io->note('La durée prévue est dépassée, mais le mode maintenance est toujours actif');
                        }
                    }

                    if (isset($data['message'])) {
                        $io->note('Message: ' . $data['message']);
                    }
                } else {
                    $io->note('Le mode maintenance n\'est pas activé');
                }
                break;

            default:
                $io->error('Action non reconnue. Utilisez "enable", "disable" ou "status"');
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
