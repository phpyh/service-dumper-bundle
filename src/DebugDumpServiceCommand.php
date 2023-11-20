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
    /**
     * @readonly
     * @var \PHPyh\ServiceDumperBundle\DependencyInjection\AllServicesContainer
     */
    private $container;
    /**
     * @readonly
     * @var \PHPyh\ServiceDumperBundle\ServiceDumper
     */
    private $serviceDumper;
    /**
     * @readonly
     * @var \PHPyh\ServiceDumperBundle\ServiceFinder
     */
    private $serviceFinder;
    public function __construct(AllServicesContainer $container = null, ServiceDumper $serviceDumper = null, ServiceFinder $serviceFinder = null)
    {
        $container = $container ?? new AllServicesContainer();
        $serviceDumper = $serviceDumper ?? new VarDumpServiceDumper();
        $serviceFinder = $serviceFinder ?? new BasicServiceFinder();
        $this->container = $container;
        $this->serviceDumper = $serviceDumper;
        $this->serviceFinder = $serviceFinder;
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
    public static function interactivelyResolveServiceIds(array $serviceIds, ServiceFinder $serviceFinder, callable $selectServiceIds, array $inputIds): array
    {
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
                $resolvedIds = array_merge($resolvedIds, is_array($selectServiceIds('Select service(s)', $serviceIds)) ? $selectServiceIds('Select service(s)', $serviceIds) : iterator_to_array($selectServiceIds('Select service(s)', $serviceIds)));

                continue;
            }

            $foundServiceIds = $serviceFinder->find($serviceIds, $inputId);

            $resolvedIds = array_merge($resolvedIds, (function () use ($foundServiceIds, $inputId, $selectServiceIds) {
                switch (\count($foundServiceIds)) {
                    case 0:
                        throw new \RuntimeException(sprintf('No services matching "%s" found.', $inputId));
                    case 1:
                        return $foundServiceIds;
                    default:
                        return $selectServiceIds(sprintf('Select service(s), matching "%s"', $inputId), $foundServiceIds);
                }
            })());
        }
        /** @var non-empty-list<string> */
        return $resolvedIds;
    }

    protected function configure(): void
    {
        $this->addArgument('ids', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Full service id or a keyword.');
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $input->setArgument('ids', self::interactivelyResolveServiceIds($this->container->ids(), $this->serviceFinder, static function (string $title, array $options) use ($io): array {
            $question = new ChoiceQuestion($title, $options);
            $question->setMultiselect(true);

            /** @var non-empty-list<string> */
            return $io->askQuestion($question);
        }, $input->getArgument('ids')));
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
