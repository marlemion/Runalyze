<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\Poster;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Repository\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class GeneratePoster
{
    /** @var array */
    protected $Parameter = [];

    /** @var string */
    protected $KernelRootDir;

    /** @var string */
    protected $Python3path;

    /** @var TrainingRepository */
    protected $TrainingRepository;

    /** @var string */
    protected $Filename;

    /** @var string */
    protected $StdErr = '';

    /**
     * @param string $kernelRootDir
     * @param string $python3Path
     * @param TrainingRepository $trainingRepository
     */
    public function __construct($kernelRootDir, $python3Path, TrainingRepository $trainingRepository)
    {
        $this->KernelRootDir = $kernelRootDir;
        $this->Python3Path = $python3Path;
        $this->TrainingRepository = $trainingRepository;
    }

    /**
     * @return string
     */
    protected function pathToRepository()
    {
        return $this->KernelRootDir.'/../vendor/runalyze/gpxtrackposter/';
    }

    /**
     * @return string
     */
    protected function pathToSvgDirectory()
    {
        return $this->KernelRootDir.'/../var/poster/';
    }

    /**
     * @param string $athlete
     * @param string $year
     */
    protected function generateRandomFileName($athlete, $year)
    {
        $this->Filename = md5($athlete.$year.strtotime("now")).'.svg';
    }

    /**
     * @return string path to generated file
     */
    public function generate()
    {
        $cmd = $this->Python3Path.' create_poster.py '.implode(' ', $this->Parameter);
        $builder = new Process($cmd);
        $builder->setWorkingDirectory(realpath($this->pathToRepository()));
        $builder->run();
        $this->StdErr = $builder->getErrorOutput();

        return $this->pathToSvgDirectory().$this->Filename;
    }

    /**
     * @param string $type
     * @param string $jsonDir
     * @param int $year
     * @param Account $account
     * @param Sport $sport
     * @param null|string $title
     */
    public function buildCommand($type, $jsonDir, $year, Account $account, Sport $sport, $title, $backgroundColor, $trackColor, $textColor, $raceColor)
    {
        $this->Parameter = [];
        
        $this->generateRandomFileName($account->getUsername(), (string)$year);

        $this->Parameter[] = '--json-dir '.escapeshellarg($jsonDir);
        $this->Parameter[] = '--athlete '.escapeshellarg($account->getUsername());
        $this->Parameter[] = '--year '.(string)(int)$year;
        $this->Parameter[] = '--output '.escapeshellarg($this->pathToSvgDirectory().$this->Filename);
        $this->Parameter[] = '--type '.$type;
        $this->Parameter[] = '--title '.escapeshellarg($title);
        $this->Parameter[] = '--background-color '.escapeshellarg($backgroundColor);
        $this->Parameter[] = '--track-color '.escapeshellarg($trackColor);
        $this->Parameter[] = '--text-color '.escapeshellarg($textColor);
        $this->Parameter[] = '--special-color '.escapeshellarg($raceColor);

        $this->addStatsParameter($account, $sport, $year);

        if ((new Filesystem())->exists($jsonDir.'/special.params')) {
            foreach(json_decode(file_get_contents($jsonDir.'/special.params')) as $special) {
                $this->Parameter[] = '--special '.$special;
            }
        }
    }

    /**
     * @param Account $account
     * @param Sport $sport
     * @param int $year
     */
    private function addStatsParameter(Account $account, Sport $sport, $year)
    {
        $stats = $this->TrainingRepository->getStatsForPoster($account, $sport, $year)->getArrayResult();
        $data = $stats[0];

        $this->Parameter[] = '--stat-num '.(int)$data['num'];
        $this->Parameter[] = '--stat-total '.(float)$data['total_distance'];
        $this->Parameter[] = '--stat-min '.(float)$data['min_distance'];
        $this->Parameter[] = '--stat-max '.(float)$data['max_distance'];
    }

    /**
     * @return array
     */
    public function availablePosterTypes()
    {
        return ['grid', 'calendar', 'circular', 'heatmap'];
    }

    public function deleteSvg()
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->pathToSvgDirectory().$this->Filename);
    }
    
    /**
     * @return string
     */
    public function getErrorOutput() {
        return $this->StdErr;
    }
}
