<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tom32i\ShowcaseBundle\Service\Browser;
use Tom32i\ShowcaseBundle\Service\Processor;

#[AsCommand(
    name: 'showcase:cache-clear',
    description: 'Delete the cached thumbnails for images.',
    hidden: false,
    aliases: ['showcase:cc']
)]
class ClearCacheCommand extends Command
{
    use Behaviour\CommandHelper;

    public function __construct(
        private Browser $browser,
        private Processor $processor
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('slug', InputArgument::OPTIONAL, 'Specific path to clear cache for', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Clear cached images');
        $slug = $this->parseString($input->getArgument('slug'));
        $filter = $slug !== null ? (static fn (array $group): bool => $group['slug'] === $slug) : null;
        $groups = $this->browser->list(null, null, $filter);

        foreach ($groups as $group) {
            $io->comment(sprintf('Clearing all cached images in "%s"...', $group['slug']));
            $io->progressStart(\count($group['images']));

            foreach ($group['images'] as $image) {
                $this->processor->clear($image['path']);
                $io->progressAdvance();
            }

            $io->progressFinish();
        }

        return Command::SUCCESS;
    }
}
