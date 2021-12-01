<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tom32i\ShowcaseBundle\Service\Browser;
use Tom32i\ShowcaseBundle\Service\Processor;

class NormalizeNamesCommand extends Command
{
    protected static $defaultName = 'showcase:normalize-names';

    private Browser $browser;
    private Processor $processor;
    private string $path;

    public function __construct(Browser $browser, Processor $processor, string $path)
    {
        $this->browser = $browser;
        $this->processor = $processor;
        $this->path = $path;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Normalize file names')
            ->addArgument('slug', InputArgument::OPTIONAL, 'Specific path', null)
            ->addOption('pattern', 'p', InputOption::VALUE_REQUIRED, 'Pattern', '%group%-%index%')
            ->addOption('shuffle', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Normalize file names');

        $slug = $input->getArgument('slug');
        $pattern = $input->getOption('pattern');
        $shuffle = $input->getOption('shuffle');
        $filter = $slug ? (fn ($group) => $group['slug'] === $slug) : null;
        $groups = $this->browser->list(null, ['[slug]' => true], $filter);

        // Ask for confirmation before shuffling all images:
        if ($shuffle && \is_null($slug) && !$io->confirm('Are you sure you want to shuffle images in all groups?')) {
            $io->comment('Aborting.');

            return 0;
        }

        foreach ($groups as $group) {
            $io->comment(sprintf('Normalize file names in "%s"...', $group['slug']));
            $io->progressStart(\count($group['images']));
            $tmpDir = sys_get_temp_dir();

            if ($shuffle) {
                shuffle($group['images']);
            }

            foreach ($group['images'] as $index => $file) {
                $this->move($file, $tmpDir);
            }

            foreach ($group['images'] as $index => $file) {
                $newName = $this->generateName($group, $file, $index, $pattern);

                $this->rename($file, $tmpDir, $newName);

                $io->progressAdvance();
            }

            $io->progressFinish();
        }

        return 0;
    }

    private function move(array $file, string $tmpDir): bool
    {
        return rename(
            sprintf('%s/%s', $this->path, $file['path']),
            sprintf('%s/%s', $tmpDir, $file['slug'])
        );
    }

    private function rename(array $file, string $tmpDir, string $newName): void
    {
        $name = pathinfo($file['path'], PATHINFO_FILENAME);
        $directory = pathinfo($file['path'], PATHINFO_DIRNAME);
        $extension = pathinfo($file['path'], PATHINFO_EXTENSION);
        $oldPath = sprintf('%s/%s', $tmpDir, $file['slug']);
        $newPath = sprintf('%s/%s/%s.%s', $this->path, $directory, $newName, strtolower($extension));

        if (file_exists($newPath)) {
            throw new \Exception(sprintf('Could not rename "%s" to "%s": file already exists.', $oldPath, $newPath));
        }

        rename($oldPath, $newPath);

        if ($name !== $newName) {
            $this->processor->clear($file['path']);
        }
    }

    private function generateName(array $group, array $file, int $index, string $pattern): string
    {
        $newName = $pattern;
        $newName = str_replace('%group%', $group['slug'], $newName);
        $newName = str_replace('%index%', str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT), $newName);

        return $newName;
    }
}
