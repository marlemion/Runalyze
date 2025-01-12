<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Component\Statistics\MonthlyStats\AnalysisData;
use Runalyze\Bundle\CoreBundle\Component\Statistics\MonthlyStats\AnalysisSelection;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Repository\SportRepository;
use Runalyze\Bundle\CoreBundle\Repository\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Runalyze\Bundle\CoreBundle\Services\Selection\SportSelectionFactory;
use Runalyze\Bundle\CoreBundle\Twig\ValueExtension;
use Runalyze\Util\LocalTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPluginsAwareController extends Controller
{
    protected $IsShowingAllPanels = false;
    
    /** @var SportRepository */
    protected $sportRepository;

    /** @var ConfigurationManager */
    protected $configurationManager;

    /** @var SportSelectionFactory */
    protected $sportSelectionFactory;

    /** @var TrainingRepository */
    protected $trainingRepository;

    public function __construct(
        SportRepository $sportRepository,
        ConfigurationManager $configurationManager,
        SportSelectionFactory $sportSelectionFactory,
        TrainingRepository $trainingRepository)
    {
        $this->sportRepository = $sportRepository;
        $this->configurationManager = $configurationManager;
        $this->sportSelectionFactory = $sportSelectionFactory;
        $this->trainingRepository = $trainingRepository;
    }

    /**
     * @param Request $request
     * @param Account $account
     * @return Response
     */
    protected function getResponseForAllEnabledPanels(Request $request, Account $account)
    {
        $this->IsShowingAllPanels = true;
        $factory = new \PluginFactory();
        $content = '';

        foreach ($factory->enabledPanels() as $key) {
            $panel = $factory->newInstance($key);
            $panelContent = $this->getResponseFor($panel->id(), $request, $account)->getContent();

            if ($panel instanceof \RunalyzePluginPanel_Sports) {
                $panelContent = '<div class="panel" id="panel-'.$panel->id().'">'.$panelContent.'</div>';
            }

            $content .= $panelContent;
        }

        return new Response($content);
    }

    /**
     * @param int $pluginId
     * @param Request $request
     * @param Account $account
     * @return Response
     */
    protected function getResponseFor($pluginId, Request $request, Account $account)
    {
        $factory = new \PluginFactory();
        $content = '';

        try {
        	$plugin = $factory->newInstanceFor($pluginId);
        } catch (\Exception $E) {
            $plugin = null;

        	echo \HTML::error(__('The plugin could not be found.'));
        }

        if (null !== $plugin) {
        	if ($plugin instanceof \RunalyzePluginPanel_Sports) {
        	    return $this->getResponseForSportsPanel($account, $plugin);
        	} elseif ($plugin instanceof \RunalyzePluginStat_MonthlyStats) {
        	    return $this->getResponseForMonthlyStats($request, $account, $pluginId);
            } elseif ($plugin instanceof \PluginPanel) {
                $plugin->setSurroundingDivVisible($this->IsShowingAllPanels);
            }

            ob_start();
            $plugin->display();
        	$content = ob_get_clean();
        }

        return (new Response())->setContent($content);
    }

    /**
     * @param Account $account
     * @param int $pluginId
     * @return Response
     */
    protected function getResponseForSportsPanel(Account $account, \Plugin $plugin)
    {
        $today = (new LocalTime())->setTime(0, 0, 0);

        return $this->render('my/panels/sports/base.html.twig', [
            'isHidden' => $plugin->isHidden(),
            'pluginId' => $plugin->id(),
            'weekStatistics' => $this->sportRepository->getSportStatisticsSince($today->weekstart(), $account),
            'monthStatistics' => $this->sportRepository->getSportStatisticsSince($today->setDate((int)$today->format('Y'), (int)$today->format('m'), 1)->getTimestamp(), $account),
            'yearStatistics' => $this->sportRepository->getSportStatisticsSince($today->setDate((int)$today->format('Y'), 1, 1)->getTimestamp(), $account),
            'totalStatistics' => $this->sportRepository->getSportStatisticsSince(null, $account)
        ]);
    }

    /**
     * @param Request $request
     * @param Account $account
     * @param int $pluginId
     * @return Response
     */
    protected function getResponseForMonthlyStats(Request $request, Account $account, $pluginId)
    {
        $valueExtension = new ValueExtension($this->configurationManager);
        $sportSelection = $this->sportSelectionFactory->getSelection($request->get('sport'));
        $analysisList = new AnalysisSelection($request->get('dat'));

        if (!$analysisList->hasCurrentKey()) {
            $analysisList->setCurrentKey(AnalysisSelection::DISTANCE);
        }

        $analysisData = new AnalysisData(
            $sportSelection,
            $analysisList,
            $this->trainingRepository,
            $account
        );
        $analysisData->setValueExtension($valueExtension);

        return $this->render('my/statistics/monthly-stats/base.html.twig', [
            'pluginId' => $pluginId,
            'analysisData' => $analysisData
        ]);
    }
}
