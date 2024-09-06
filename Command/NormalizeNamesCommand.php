<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tom32i\ShowcaseBundle\Model\Group;
use Tom32i\ShowcaseBundle\Model\Image;
use Tom32i\ShowcaseBundle\Service\Browser;
use Tom32i\ShowcaseBundle\Service\Normalizer;

#[AsCommand(
    name: 'showcase:normalize-names',
    description: 'Normalize file names.',
    hidden: false,
    aliases: ['showcase:cg']
)]
class NormalizeNamesCommand extends Command
{
    use Behaviour\CommandHelper;

    /**
     * @param Browser<Group, Image> $browser
     */
    public function __construct(
        private Browser $browser,
        private Normalizer $normalizer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('slug', InputArgument::OPTIONAL, 'Specific path', null)
            ->addOption('sort', 's', InputOption::VALUE_REQUIRED, 'Sort by', null)
            ->addOption('pattern', 'p', InputOption::VALUE_REQUIRED, 'Pattern', null)
            ->addOption('shuffle', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Normalize file names');

        $slug = $this->parseString($input->getArgument('slug'));
        $pattern = $this->parseString($input->getOption('pattern')) ?? '%group%-%index%';
        $sorter = [\sprintf('[%s]', $this->parseString($input->getOption('sort')) ?? 'slug') => true];
        $shuffle = (bool) $input->getOption('shuffle');

        if ($slug !== null) {
            $groups = array_filter([$this->browser->read($slug, sortBy: $sorter)]);
        } else {
            $groups = $this->browser->list(sortContentBy: $sorter);
        }

        if (\count($groups) === 0) {
            if ($slug !== null) {
                $io->info(\sprintf('No directory found for slug "%s".', $slug));
            } else {
                $io->info('No directory found.');
            }

            return Command::INVALID;
        }

        // Ask for confirmation before shuffling all images:
        if ($shuffle && \is_null($slug) && $io->confirm('Are you sure you want to shuffle images in all directories?') === false) {
            $io->comment('Aborting.');

            return Command::SUCCESS;
        }

        foreach ($groups as $group) {
            $io->comment(\sprintf('Normalize file names in "%s"...', $group->getSlug()));
            $io->progressStart(\count($group->getImages()));
            $this->normalizer->normalize($group, $pattern, $shuffle, fn () => $io->progressAdvance());
            $io->progressFinish();
        }

        return Command::SUCCESS;
    }
}
