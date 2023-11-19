<?php

declare(strict_types=1);

namespace PHPyh\ServiceDumperBundle;

use PHPyh\ServiceDumperBundle\DependencyInjection\AllServicesContainer;
use PHPyh\ServiceDumperBundle\ServiceDumper\VarDumpServiceDumper;
use PHPyh\ServiceDumperBundle\ServiceFinder\BasicServiceFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DebugDumpServiceCommand extends Command
{
    public function __construct(
        private readonly AllServicesContainer $container = new AllServicesContainer(),
        private readonly ServiceDumper $serviceDumper = new VarDumpServiceDumper(),
        private readonly ServiceFinder $serviceFinder = new BasicServiceFinder(),
    ) {
        parent::__construct();
    }

    public static function getDefaultName(): ?string
    {
        return 'debug:dump-service|service';
    }

    public static function getDefaultDescription(): ?string
    {
        return 'Dump dependency injection service(s)';
    }

    /**
     * @internal
     * @psalm-internal PHPyh\ServiceDumperBundle
     * @param callable(string, non-empty-list<string>): non-empty-list<string> $selectServiceIds
     * @param non-empty-list<string> $serviceIds
     * @param array<string> $inputIds
     * @return non-empty-list<string>
     */
    public static function interactivelyResolveServiceIds(
        array $serviceIds,
        ServiceFinder $serviceFinder,
        callable $selectServiceIds,
        array $inputIds,
    ): array {
        if ($inputIds === []) {
            return $selectServiceIds('Select service(s)', $serviceIds);
        }

        $resolvedIds = [];

        foreach ($inputIds as $inputId) {
            if (\in_array($inputId, $serviceIds, true)) {
                $resolvedIds[] = $inputId;

                continue;
            }

            if ($inputId === '') {
                $resolvedIds = [...$resolvedIds, ...$selectServiceIds('Select service(s)', $serviceIds)];

                continue;
            }

            $foundServiceIds = $serviceFinder->find($serviceIds, $inputId);

            $resolvedIds = [...$resolvedIds, ...match (\count($foundServiceIds)) {
                0 => throw new \RuntimeException(sprintf('No services matching "%s" found.', $inputId)),
                1 => $foundServiceIds,
                default => $selectServiceIds(sprintf('Select service(s), matching "%s"', $inputId), $foundServiceIds),
            }];
        }

        /** @var non-empty-list<string> */
        return $resolvedIds;
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
        $input->setArgument('ids', self::interactivelyResolveServiceIds(
            serviceIds: $this->container->ids(),
            serviceFinder: $this->serviceFinder,
            selectServiceIds: static function (string $title, array $options) use ($io): array {
                $question = new ChoiceQuestion($title, $options);
                $question->setMultiselect(true);

                /** @var non-empty-list<string> */
                return $io->askQuestion($question);
            },
            inputIds: $input->getArgument('ids'),
        ));
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
}
