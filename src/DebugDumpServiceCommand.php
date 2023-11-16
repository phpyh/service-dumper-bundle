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
        private readonly AllServicesContainer $container,
        private readonly ServiceDumper $serviceDumper = new VarDumpServiceDumper(),
        private readonly ServiceFinder $serviceFinder = new BasicServiceFinder(),
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('ids', InputArgument::REQUIRED | InputArgument::IS_ARRAY);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $input->setArgument('ids', array_map(
            fn (string $id): string => $this->interactivelyResolveId($input, $output, $id),
            $input->getArgument('ids'),
        ));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($input->getArgument('ids') as $id) {
            $this->serviceDumper->dump($this->container->get($id));
        }

        return self::SUCCESS;
    }

    private function interactivelyResolveId(InputInterface $input, OutputInterface $output, string $id): string
    {
        if ($this->container->has($id)) {
            return $id;
        }

        $ids = $this->container->ids();

        if ($ids === []) {
            throw new \LogicException('Container is empty.');
        }

        $matchedServiceIds = $this->serviceFinder->find($ids, $id);

        if ($matchedServiceIds === []) {
            throw new \LogicException(sprintf('No services matching "%s" found.', $id));
        }

        if (\count($matchedServiceIds) === 1) {
            return $matchedServiceIds[0];
        }

        /** @var string */
        return (new SymfonyStyle($input, $output))
            ->askQuestion(new ChoiceQuestion('Select service', $matchedServiceIds));
    }
}
