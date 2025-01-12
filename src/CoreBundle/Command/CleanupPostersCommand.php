<?php

namespace Runalyze\Bundle\CoreBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CleanupPostersCommand extends ContainerAwareCommand
{
    /** @var Filesystem */
    protected $filesystem;

    /** @var string */
    protected $dataDirectory;

    /** @var string */
    protected $posterStoragePeriod;

    public function __construct(
        Filesystem $filesystem,
        string $dataDirectory,
        string $posterStoragePeriod)
    {
        $this->filesystem = $filesystem;
        $this->dataDirectory = $dataDirectory;
        $this->posterStoragePeriod = $posterStoragePeriod;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('runalyze:cleanup:posters')
            ->setDescription('Cleanup posters older than parameter: poster_storage_period')
            ->addArgument('days', InputArgument::OPTIONAL, 'min. age posters')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days = $input->getArgument('days') ?: $this->posterStoragePeriod;
        $output->writeln(sprintf('<info>Delete all posters older than %s days</info>', $days));

        $finder = new Finder();
        $finder
            ->files()
            ->name('*.png')
            ->in($this->dataDirectory.'/poster/')
            ->date(sprintf('until %s days ago', $days));

        $deleted= $finder->count();

        foreach ($finder as $file) {
            $this->filesystem->remove($file);
        }

        $output->writeln(sprintf('<info>%s deleted posters</info>', $deleted));
        $output->writeln('');

        return 0;
    }
}
