<?php

namespace Runalyze\Bundle\CoreBundle\Queue\Receiver;

use Bernard\Message\PlainMessage;
use Psr\Log\LoggerInterface;
use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\PosterGeneratedMessage;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter\AbstractSvgToPngConverter;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter\InkscapeConverter;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\Converter\RsvgConverter;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\FileHandler;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\GeneratePoster;
use Runalyze\Bundle\CoreBundle\Repository\AccountRepository;
use Runalyze\Bundle\CoreBundle\Entity\Notification;
use Runalyze\Bundle\CoreBundle\Repository\NotificationRepository;
use Runalyze\Bundle\CoreBundle\Repository\SportRepository;
use Runalyze\Bundle\CoreBundle\Component\Tool\Poster\GenerateJsonData;
use Runalyze\Bundle\CoreBundle\Services\AccountMailer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class PosterReceiver
{
    /** @var LoggerInterface */
    protected $Logger;

    /** @var AccountRepository */
    protected $AccountRepository;

    /** @var SportRepository */
    protected $SportRepository;

    /** @var NotificationRepository */
    protected $NotificationRepository;

    /** @var GenerateJsonData */
    protected $GenerateJsonData;

    /** @var GeneratePoster */
    protected $GeneratePoster;

    /** @var FileHandler */
    protected $FileHandler;

    /** @var AccountMailer */
    protected $AccountMailer;

    /** @var string */
    protected $dataDirectory;

    /** @var string */
    protected $RsvgPath;

    /** @var string */
    protected $InkscapePath;

    public function __construct(
        LoggerInterface $logger,
        AccountRepository $accountRepository,
        SportRepository $sportRepository,
        NotificationRepository $notificationRepository,
        GenerateJsonData $generateJsonData,
        GeneratePoster $generatePoster,
        FileHandler $posterFileHandler,
        AccountMailer $accountMailer,
        string $dataDirectory,
        string $rsvgPath,
        string $inkscapePath)
    {
        $this->Logger = $logger;
        $this->AccountRepository = $accountRepository;
        $this->SportRepository = $sportRepository;
        $this->NotificationRepository = $notificationRepository;
        $this->GenerateJsonData = $generateJsonData;
        $this->GeneratePoster = $generatePoster;
        $this->FileHandler = $posterFileHandler;
        $this->AccountMailer = $accountMailer;
        $this->dataDirectory = $dataDirectory;
        $this->RsvgPath = $rsvgPath;
        $this->InkscapePath = $inkscapePath;
    }

    public function posterGenerator(PlainMessage $message)
    {
        $account = $this->AccountRepository->find((int)$message->get('accountid'));
        $sport = $this->SportRepository->find((int)$message->get('sportid'));

        if (null === $account || null === $sport || $sport->getAccount()->getId() != $account->getId()) {
            return;
        }

        $generatedFiles = 0;
        $this->GenerateJsonData->createJsonFilesFor($account, $sport, $message->get('year'));
        $jsonFiles = (new Finder())->files()->in($this->GenerateJsonData->getPathToJsonFiles());

        if ($jsonFiles->count() > 0) {
            foreach ($message->get('types') as $type) {
                try {
                    $this->GeneratePoster->buildCommand(
                        $type,
                        $this->GenerateJsonData->getPathToJsonFiles(),
                        $message->get('year'),
                        $account,
                        $sport,
                        $message->get('title'),
                        $message->get('backgroundColor'),
                        $message->get('trackColor'),
                        $message->get('textColor'),
                        $message->get('raceColor')
                    );

                    $finalName = $this->FileHandler->buildFinalFileName($account, $sport, $message->get('year'), $type, $message->get('size'));
                    $finalFile = $this->exportDirectory().$finalName;

                    $gen = $this->GeneratePoster->generate();
                    if (!(new Filesystem())->exists($gen)) {
                        $this->Logger->error('Poster generator subprocess failed', ['type' => $type, 'stderr' => $this->GeneratePoster->getErrorOutput()]);
                    } else {
                        $converter = $this->getConverter($type);
                        $converter->setHeight($message->get('size'));
                        $exitCode = $converter->callConverter($gen, $this->exportDirectory().md5($finalName));
                        if ($exitCode > 0) {
                            $this->Logger->error('Poster converter subprocess failed', ['type' => $type, 'exitCode' => $exitCode, 'stderr' => $converter->getErrorOutput()]);
                        } else {
                            $filesystem = new Filesystem();
                            $filesystem->rename($this->exportDirectory().md5($finalName), $finalFile);

                            if ((new Filesystem())->exists($finalFile)) {
                                $generatedFiles++;
                            }
                        }
                        $this->GeneratePoster->deleteSvg();
                    }
                } catch (\Exception $e) {
                    $this->Logger->error('Poster creation failed', ['type' => $type, 'exception' => $e]);
                }
            }
        }

        $state = $this->getNotificationState($generatedFiles, count($message->get('types')));
        $this->NotificationRepository->save(
            Notification::createFromMessage(new PosterGeneratedMessage($state), $account)
        );

        $this->GenerateJsonData->deleteGeneratedFiles();
        gc_collect_cycles();
    }

    /**
     * @param int $generatedFiles
     * @param int $requestedFiles
     * @return int
     */
    protected function getNotificationState($generatedFiles, $requestedFiles)
    {
        if (0 == $generatedFiles) {
            return PosterGeneratedMessage::STATE_FAILED;
        }

        if ($requestedFiles != $generatedFiles) {
            return PosterGeneratedMessage::STATE_PARTIAL;
        }

        return PosterGeneratedMessage::STATE_SUCCESS;
    }

    /**
     * @param string $posterType
     * @return AbstractSvgToPngConverter
     */
    protected function getConverter($posterType)
    {
        if ('circular' == $posterType) {
            return new InkscapeConverter($this->InkscapePath);
        }

        return new RsvgConverter($this->RsvgPath);
    }

    /**
     * @return string
     */
    protected function exportDirectory()
    {
        return $this->dataDirectory.'/poster/';
    }
}
