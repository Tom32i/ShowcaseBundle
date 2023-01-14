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
use Tom32i\ShowcaseBundle\Service\PresetManager;
use Tom32i\ShowcaseBundle\Service\Processor;

#[AsCommand(
    name: 'showcase:cache-generate',
    description: 'Delete and regenerate the cached thumbnails for images.',
    hidden: false,
    aliases: ['showcase:cg']
)]
class GenerateCacheCommand extends Command
{
    use Behaviour\CommandHelper;

    public function __construct(
        private Browser $browser,
        private Processor $processor,
        private PresetManager $presetManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('preset', InputArgument::OPTIONAL, 'Specific preset to generate cache for', null)
            ->addArgument('slug', InputArgument::OPTIONAL, 'Specific path to generate cache for', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generate images cache');

        $slug = $this->parseString($input->getArgument('slug'));
        $preset = $this->parseString($input->getArgument('preset'));
        $presets = $preset !== null ? [$preset] : array_keys($this->presetManager->getAll());
        $groups = $this->browser->list(null, null, $this->filterBySlug($slug));

        if (\count($groups) === 0) {
            if ($slug !== null) {
                $io->info(sprintf('No directory found for slug "%s".', $slug));
            } else {
                $io->info('No directory found.');
            }
        }

        foreach ($groups as $group) {
            $io->comment(sprintf(
                'Generating missing cache images in "%s" for presets: %s.',
                $group->getSlug(),
                implode(', ', $presets)
            ));
            $io->progressStart(\count($group->getImages()) * \count($presets));

            foreach ($group->getImages() as $image) {
                foreach ($presets as $key) {
                    $this->processor->warmup($image->getPath(), $key);
                    $io->progressAdvance();
                }
            }

            $io->progressFinish();
        }

        return Command::SUCCESS;
    }
}
