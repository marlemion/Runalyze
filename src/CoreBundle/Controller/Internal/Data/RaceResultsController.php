<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Internal\Data;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Repository\PluginConfRepository;
use Runalyze\Bundle\CoreBundle\Repository\RaceresultRepository;
use Runalyze\Bundle\CoreBundle\Services\Activity\AgeGradeLookup;
use Runalyze\Util\LocalTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/_internal/data/race-results")
 */
class RaceResultsController extends Controller
{
    /**
     * @Route("/all", name="internal-data-race-results-all")
     * @Security("has_role('ROLE_USER')")
     */
    public function allRaceResultsAction(
        Account $account,
        RaceresultRepository $raceresultRepository,
        PluginConfRepository $pluginConfRepository,
        AgeGradeLookup $ageGradeLookup)
    {
        $result = [];
        $races = $raceresultRepository->findAllWithActivityStats($account);
        $ageGradeLookup = $ageGradeLookup->getLookup() ?: $ageGradeLookup->getDefaultLookup();
        $funIds = $pluginConfRepository->getAllActivityIdsOfFunRaces($account);

        foreach ($races as $race) {
            $ageGrade = $ageGradeLookup->getAgeGrade(
                $race->getOfficialDistance(),
                $race->getOfficialTime(),
                (int) $race->getActivity()->getDateTime()->diff(new LocalTime())->format('%y')
            );

            $result[] = [
                'name' => $race->getName(),
                'date' => $race->getActivity()->getDateTime()->format('c'),
                'sport_id' => $race->getActivity()->getSport()->getId(),
                'distance' => $race->getOfficialDistance(),
                'duration' => $race->getOfficialTime(),
                'officially_measured' => $race->getOfficiallyMeasured(),
                'place_total' => $race->getPlaceTotal(),
                'place_gender' => $race->getPlaceGender(),
                'place_ageclass' => $race->getPlaceAgeclass(),
                'participants_total' => $race->getParticipantsTotal(),
                'participants_gender' => $race->getParticipantsGender(),
                'participants_ageclass' => $race->getParticipantsAgeclass(),
                'vo2max' => $race->getActivity()->getVO2max(),
                'vo2max_by_time' => $race->getActivity()->getVO2maxByTime(),
                'vo2max_with_elevation' => $race->getActivity()->getVO2maxWithElevation(),
                'age_grade' => $ageGrade->getPerformance(),
                'is_fun' => in_array($race->getActivity()->getId(), $funIds)
            ];
        }

        return new JsonResponse($result);
    }
}
