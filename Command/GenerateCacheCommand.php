<?php

namespace Tom32i\ShowcaseBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tom32i\ShowcaseBundle\Service\Browser;
use Tom32i\ShowcaseBundle\Service\Processor;

class GenerateCacheCommand extends Command
{
    protected static $defaultName = 'showcase:cache-generate';

    private Browser $browser;
    private Processor $processor;
    private array $presets;

    public function __construct(Browser $browser, Processor $processor, array $presets)
    {
        $this->browser = $browser;
        $this->processor = $processor;
        $this->presets = $presets;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Clear cache')
            ->addArgument('preset', InputArgument::OPTIONAL, 'Specific preset  to generate cache for', null)
            ->addArgument('slug', InputArgument::OPTIONAL, 'Specific path to generate cache for', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generate images cache');

        $slug = $input->getArgument('slug');
        $presets = $input->getArgument('preset') ? [$input->getArgument('preset')] : array_keys($this->presets);
        $filter = $slug ? (fn($group) => $group['slug'] === $slug) : null;
        $groups = $this->browser->list(null, null, $filter);

        foreach ($groups as $group) {
            $io->comment(sprintf('Generating missing cache images in "%s" for presets: %s.', $group['slug'], implode(', ', $presets)));
            $io->progressStart(count($group['images']) * count($presets));

            foreach ($group['images'] as $image) {
                foreach ($presets as $key => $config) {
                    $this->processor->warmup($image['path'], $key);
                    $io->progressAdvance();
                }
            }

            $io->progressFinish();
        }

        return 0;
    }
}
