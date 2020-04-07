<?php

namespace Tom32i\ShowcaseBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tom32i\ShowcaseBundle\Service\Browser;
use Tom32i\ShowcaseBundle\Service\Processor;

class NormalizeNamesCommand extends Command
{
    protected static $defaultName = 'showcase:normalize-names';

    private Browser $browser;
    private Processor $processor;

    public function __construct(Browser $browser, Processor $processor)
    {
        $this->browser = $browser;
        $this->processor = $processor;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Normalize file names')
            ->addArgument('pattern', InputArgument::OPTIONAL, 'Pattern', '%group%-%index%')
            ->addArgument('slug', InputArgument::OPTIONAL, 'Specific path', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Normalize file names');

        $pattern = $input->getArgument('pattern');
        $slug = $input->getArgument('slug');
        $filter = $slug ? (fn($group) => $group['slug'] === $slug) : null;
        $groups = $this->browser->list(null, null, $filter);

        foreach ($groups as $group) {
            $io->comment(sprintf('Normalize file names in "%s"...', $group['slug']));
            $io->progressStart(count($group['images']));

            foreach ($group['images'] as $index => $file) {
                $this->processor->rename(
                    $file['path'],
                    $this->generateName($group, $file, $index, $pattern)
                );

                $io->progressAdvance();
            }

            $io->progressFinish();
        }

        return 0;
    }

    private function generateName(array $group, array $file, int $index, string $pattern): string
    {
        $newName = $pattern;
        $newName = str_replace('%group%', $group['slug'], $newName);
        $newName = str_replace('%index%', str_pad($index + 1, 2, '0'), $newName);

        return $newName;
    }
}
