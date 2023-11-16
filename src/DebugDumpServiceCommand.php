<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

use PHPyh\ServiceDumperBundle\DependencyInjection\AllServicesContainer;
use PHPyh\ServiceDumperBundle\ServiceDumper\VarDumpServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceFinder\BasicServiceFinder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 * @psalm-internal PHPyh\ServiceDumperBundle
 */
#[AsCommand(name: 'debug:dump-service', description: 'Dump dependency injection service(s)', aliases: ['service'])]
final class DebugDumpServiceCommand extends Command
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(
        private readonly AllServicesContainer $container = new AllServicesContainer(),
        private readonly ServiceDumper $serviceDumper = new VarDumpServiceDumper(),
        private readonly ServiceFinder $serviceFinder = new BasicServiceFinder(),
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            name: 'ids',
            mode: InputArgument::REQUIRED | InputArgument::IS_ARRAY,
            description: 'Full service id or a keyword.',
        );
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $serviceIds = $this->container->ids();
        $ids = [];

        foreach ($input->getArgument('ids') as $id) {
            if (\in_array($id, $serviceIds, true)) {
                $ids[] = $id;

                continue;
            }

            $foundServiceIds = $this->serviceFinder->find($serviceIds, $id);

            if ($foundServiceIds === []) {
                throw new \LogicException(sprintf('No services matching "%s" found.', $id));
            }

            if (\count($foundServiceIds) === 1) {
                $ids[] = $foundServiceIds[0];

                continue;
            }

            /** @var non-empty-list<string> */
            $answer = $io->askQuestion($this->createServiceChoiceQuestion($id, $foundServiceIds));
            $ids = [...$ids, ...$answer];
        }

        $input->setArgument('ids', $ids);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var non-empty-list<string> $ids */
        $ids = $input->getArgument('ids');
        $servicesById = [];

        foreach ($ids as $id) {
            $servicesById[$id] = $this->container->get($id);
        }

        $this->serviceDumper->dump($servicesById);

        return self::SUCCESS;
    }

    /**
     * @param non-empty-list<string> $ids
     */
    private function createServiceChoiceQuestion(string $id, array $ids): ChoiceQuestion
    {
        $question = new ChoiceQuestion(sprintf('Here are the services, found for "%s", choose one or more', $id), $ids);
        $question->setMultiselect(true);

        return $question;
    }
}
