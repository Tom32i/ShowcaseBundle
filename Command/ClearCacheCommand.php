<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tom32i\ShowcaseBundle\Service\Browser;
use Tom32i\ShowcaseBundle\Service\Processor;

class ClearCacheCommand extends Command
{
    protected static $defaultName = 'showcase:cache-clear';

    private Browser $browser;
    private Processor $processor;

    public function __construct(Browser $browser, Processor $processor)
    {
        $this->browser = $browser;
        $this->processor = $processor;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Delete the cached thumbnails for images')
            ->addArgument('slug', InputArgument::OPTIONAL, 'Specific path to clear cache for', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Clear cached images');
        $slug = $input->getArgument('slug');
        $filter = $slug ? (fn ($group) => $group['slug'] === $slug) : null;
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

        return 0;
    }
}
