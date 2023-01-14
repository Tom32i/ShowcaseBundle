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
use Tom32i\ShowcaseBundle\Model\File;
use Tom32i\ShowcaseBundle\Model\Group;
use Tom32i\ShowcaseBundle\Service\Browser;
use Tom32i\ShowcaseBundle\Service\Processor;

#[AsCommand(
    name: 'showcase:normalize-names',
    description: 'Normalize file names.',
    hidden: false,
    aliases: ['showcase:cg']
)]
class NormalizeNamesCommand extends Command
{
    use Behaviour\CommandHelper;

    public function __construct(
        private Browser $browser,
        private Processor $processor,
        private string $path)
    {
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
        $sortBy = $this->parseString($input->getOption('sort')) ?? 'slug';
        $shuffle = (bool) $input->getOption('shuffle');
        $groups = $this->browser->list(null, [sprintf('[%s]', $sortBy) => true], $this->filterBySlug($slug));

        // Ask for confirmation before shuffling all images:
        if ($shuffle && \is_null($slug) && $io->confirm('Are you sure you want to shuffle images in all directories?') === false) {
            $io->comment('Aborting.');

            return Command::SUCCESS;
        }

        if (\count($groups) === 0) {
            if ($slug !== null) {
                $io->info(sprintf('No directory found for slug "%s".', $slug));
            } else {
                $io->info('No directory found.');
            }
        }

        foreach ($groups as $group) {
            $io->comment(sprintf('Normalize file names in "%s"...', $group->getSlug()));
            $io->progressStart(\count($group->getImages()));
            $tmpDir = sys_get_temp_dir();
            $images = $group->getImages();

            if ($shuffle) {
                shuffle($images);
            }

            foreach ($images as $index => $file) {
                $this->move($file, $tmpDir);
            }

            foreach ($images as $index => $file) {
                $newName = $this->generateName($group, $index, $pattern);

                $this->rename($file, $tmpDir, $newName);

                $io->progressAdvance();
            }

            $io->progressFinish();
        }

        return Command::SUCCESS;
    }

    private function move(File $file, string $tmpDir): bool
    {
        return rename(
            sprintf('%s/%s', $this->path, $file->getPath()),
            sprintf('%s/%s', $tmpDir, $file->getSlug())
        );
    }

    private function rename(File $file, string $tmpDir, string $newName): void
    {
        $name = pathinfo($file->getPath(), PATHINFO_FILENAME);
        $group = pathinfo($file->getPath(), PATHINFO_DIRNAME);
        $extension = pathinfo($file->getPath(), PATHINFO_EXTENSION);
        $oldPath = sprintf('%s/%s', $tmpDir, $file->getSlug());
        $newPath = sprintf('%s/%s/%s.%s', $this->path, $group, $newName, strtolower($extension));

        if (file_exists($newPath)) {
            throw new \Exception(sprintf('Could not rename "%s" to "%s": file already exists.', $oldPath, $newPath));
        }

        rename($oldPath, $newPath);

        if ($name !== $newName) {
            $this->processor->clear($file->getPath());
        }
    }

    private function generateName(Group $group, int $index, string $pattern): string
    {
        $newName = $pattern;
        $newName = str_replace('%group%', $group->getSlug(), $newName);
        $newName = str_replace('%index%', str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT), $newName);

        return $newName;
    }
}
