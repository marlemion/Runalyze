<?php

namespace Runalyze\Bundle\CoreBundle\Component\Account;

use Doctrine\ORM\EntityManagerInterface;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Conf;
use Runalyze\Bundle\CoreBundle\Entity\Equipment;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentType;
use Runalyze\Bundle\CoreBundle\Entity\Plugin;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Repository\SportRepository;
use Runalyze\Bundle\CoreBundle\Entity\Type;
use Runalyze\Metrics\Velocity\Unit\PaceEnum;
use Runalyze\Parameter\Application\Timezone;
use Runalyze\Profile\Sport\SportProfile;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class Registration
{
    /** @var Account */
    protected $Account;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var object[] */
    protected $specialVars;

    /**
     * @param EntityManagerInterface $em
     * @param Account $account
     */
    public function __construct(EntityManagerInterface $em, Account $account)
    {
        $this->em = $em;
        $this->Account = $account;
    }

    /**
     * Add hash to activation_hash
     */
    public function requireAccountActivation()
    {
        $this->Account->setActivationHash(self::getNewSalt());
    }

    /**
     * @param string $timezoneName
     */
    public function setTimezoneByName($timezoneName)
    {
        try {
            $this->Account->setTimezone(Timezone::getEnumByOriginalName($timezoneName));
        } catch (\InvalidArgumentException $e) {
            $this->Account->setTimezone(Timezone::getEnumByOriginalName(date_default_timezone_get()));
        }
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->Account->setLanguage($locale);
    }

    /**
     * @param string $password
     * @param EncoderFactoryInterface $encoderFactory
     */
    public function setPassword($password, EncoderFactoryInterface $encoderFactory)
    {
        $encoder = $encoderFactory->getEncoder($this->Account);
        $this->Account->setPassword($encoder->encodePassword($password, $this->Account->getSalt()));
    }

    private function setEmptyData()
    {
        $this->setEquipmentData();
        $this->setPluginData();
        $this->setSportData();
        $this->collectSpecialVars();
        $this->setTypeData();
        $this->setSpecialVars();
    }

    private function setEquipmentData()
    {
        $equipmentType = array(
            array(__('Shoes'), 0),
            array(__('Clothes'), 1),
            array(__('Bikes'), 0)
        );

        foreach ($equipmentType as $eqType) {
            $Type = new EquipmentType();
            $Type->setAccount($this->Account);
            $Type->setName($eqType[0]);
            $Type->setInput($eqType[1]);
            $this->em->persist($Type);
        }
    }

    private function setPluginData()
    {
        $pluginData = array(
            array('RunalyzePluginPanel_Sports', 'panel', 1, 1),
            array('RunalyzePluginPanel_Rechenspiele', 'panel', 1, 2),
            array('RunalyzePluginPanel_Prognose', 'panel', 2, 3),
            array('RunalyzePluginPanel_Equipment', 'panel', 2, 4),
            array('RunalyzePluginPanel_Sportler', 'panel', 1, 5),
            array('RunalyzePluginStat_Analyse', 'stat', 1, 2),
            array('RunalyzePluginStat_Statistiken', 'stat',1, 1),
            array('RunalyzePluginStat_Wettkampf', 'stat', 1, 3),
            array('RunalyzePluginStat_Wetter', 'stat', 1, 5),
            array('RunalyzePluginStat_Rekorde', 'stat', 2, 6),
            array('RunalyzePluginStat_Strecken', 'stat', 2, 7),
            array('RunalyzePluginStat_Trainingszeiten', 'stat', 2, 8),
            array('RunalyzePluginStat_Trainingspartner', 'stat', 2, 9),
            array('RunalyzePluginStat_Hoehenmeter', 'stat', 2, 10),
            array('RunalyzePluginStat_Tag', 'stat', 1, 11),
            array('RunalyzePluginPanel_Ziele', 'panel', 0, 6)
        );

        foreach ($pluginData as $pData) {
            $Plugin = new Plugin();
            $Plugin->setKey($pData[0]);
            $Plugin->setType($pData[1]);
            $Plugin->setActive($pData[2]);
            $Plugin->setOrder($pData[3]);
            $Plugin->setAccount($this->Account);
            $this->em->persist($Plugin);
        }

        $this->em->flush();
    }

    private function setSportData()
    {
        $sportData = array(
            array(__('Running'),       'icons8-Running', false, 880, 140, true,  PaceEnum::SECONDS_PER_KILOMETER, false, true,  true,  SportProfile::RUNNING),
            array(__('Swimming'),     'icons8-Swimming', false, 743, 130, true,  PaceEnum::SECONDS_PER_100M,      false, false, true,  SportProfile::SWIMMING),
            array(__('Biking'), 'icons8-Regular-Biking', false, 770, 120, true,  PaceEnum::KILOMETER_PER_HOUR,    true,  true,  true,  SportProfile::CYCLING),
            array(__('Gymnastics'),       'icons8-Yoga', true,  280, 100, false, PaceEnum::KILOMETER_PER_HOUR,    false, false, false, null),
            array(__('Other'),     'icons8-Sports-Mode', false, 500, 120, false, PaceEnum::KILOMETER_PER_HOUR,    false, false, false, null)
        );

        foreach ($sportData as $sData) {
            $Sport = new Sport();
            $Sport->setAccount($this->Account);
            $Sport->setName($sData[0]);
            $Sport->setImg($sData[1]);
            $Sport->setShort($sData[2]);
            $Sport->setKcal($sData[3]);
            $Sport->setHfavg($sData[4]);
            $Sport->setDistances($sData[5]);
            $Sport->setSpeed($sData[6]);
            $Sport->setPower($sData[7]);
            $Sport->setOutside($sData[8]);
            $Sport->setIsMain($sData[9]);
            $Sport->setInternalSportId($sData[10]);

            $this->em->persist($Sport);
        }

        $this->em->flush();
    }

    private function setTypeData()
    {
        $TypeData = array(
            array(__('Jogging'),            __('JOG'), 143, false),
            array(__('Fartlek'),            __('FL'),  150, true),
            array(__('Interval training'),  __('IT'),  165, true),
            array(__('Tempo Run'),          __('TR'),  165, true),
            array(__('Race'),               __('RC'),  190, true),
            array(__('Regeneration Run'),   __('RG'),  128, false),
            array(__('Long Slow Distance'), __('LSD'), 150, true),
            array(__('Warm-up'),            __('WU'),  128, false)
        );

        foreach ($TypeData as $tData) {
            $Type = new Type();
            $Type->setAccount($this->Account);
            $Type->setName($tData[0]);
            $Type->setAbbr($tData[1]);
            $Type->setHrAvg($tData[2]);
            $Type->setQualitySession($tData[3]);
            $Type->setSport($this->specialVars['RUNNINGSPORT']);
            $this->em->persist($Type);
        }

        $this->em->flush();
    }

    private function collectSpecialVars()
    {
        /** @var SportRepository */
        $sportRepository = $this->em->getRepository('CoreBundle:Sport');
        /** @var Sport[] $sport */
        $sport = $sportRepository->findByAccount($this->Account);

        foreach ($sport as $item) {
            switch ($item->getImg()) {
                case 'icons8-Running':
                    $this->specialVars['RUNNINGSPORT'] = $item;
                    break;
                case 'icons8-Regular-Biking':
                    $this->specialVars['BIKESPORT'] = $item;
                    break;
            }
        }

        $equipmentType = $this->em->getRepository('CoreBundle:EquipmentType');
        $equipmentClothes = $equipmentType->findOneBy(array('name' => __('Clothes'), 'account' => $this->Account->getId()));
        $equipmentShoes = $equipmentType->findOneBy(array('name' => __('Shoes'), 'account' => $this->Account->getId()));
        $equipmentBikes = $equipmentType->findOneBy(array('name' => __('Bikes'), 'account' => $this->Account->getId()));

        $this->specialVars['EQUIPMENT_CLOTHES'] = $equipmentClothes;
        $this->specialVars['EQUIPMENT_SHOES'] = $equipmentShoes;
        $this->specialVars['EQUIPMENT_BIKES'] = $equipmentBikes;

    }

    private function setSpecialVars()
    {
        $Clothes = array(__('long sleeve'), __('T-shirt'), __('singlet'), __('jacket'), __('long pants'), __('shorts'), __('gloves'), __('hat'));
        foreach ($Clothes as $cloth) {
            $Equipment = new Equipment();
            $Equipment->setAccount($this->Account);
            $Equipment->setName($cloth);
            $Equipment->setType($this->specialVars['EQUIPMENT_CLOTHES']);
            $this->em->persist($Equipment);
        }

        foreach (array('MAINSPORT', 'RUNNINGSPORT') as $cKey) {
            $Conf = new Conf();
            $Conf->setAccount($this->Account);
            $Conf->setCategory('general');
            $Conf->setKey($cKey);
            $Conf->setValue($this->specialVars['RUNNINGSPORT']->getId());
            $this->em->persist($Conf);
        }

        $Running = $this->specialVars['RUNNINGSPORT'];
        $Running->setMainEquipmenttype($this->specialVars['EQUIPMENT_SHOES']);
        $Running->addEquipmentType($this->specialVars['EQUIPMENT_CLOTHES']);
        $Running->addEquipmentType($this->specialVars['EQUIPMENT_SHOES']);
        $this->em->persist($Running);

        $Biking = $this->specialVars['BIKESPORT'];
        $Biking->addEquipmentType($this->specialVars['EQUIPMENT_BIKES']);
        $this->em->persist($Biking);
        $this->em->flush();
        // $this->em->clear();
    }

    /**
     * @return Account
     */
    public function registerAccount()
    {
        $this->em->persist($this->Account);
        $this->em->flush();
        $this->setEmptyData();

        return $this->Account;
    }

    /**
     * @return Sport
     */
    public function getRegisteredSportForRunning()
    {
        if (!isset($this->specialVars['RUNNINGSPORT'])) {
            throw new \LogicException('Account has to be registered first.');
        }

        return $this->specialVars['RUNNINGSPORT'];
    }

    /**
     * @return Sport
     */
    public function getRegisteredSportForCycling()
    {
        if (!isset($this->specialVars['BIKESPORT'])) {
            throw new \LogicException('Account has to be registered first.');
        }

        return $this->specialVars['BIKESPORT'];
    }

    /**
     * @return EquipmentType
     */
    public function getRegisteredEquipmentTypeClothes()
    {
        if (!isset($this->specialVars['EQUIPMENT_CLOTHES'])) {
            throw new \LogicException('Account has to be registered first.');
        }

        return $this->specialVars['EQUIPMENT_CLOTHES'];
    }

    /**
     * @return string
     */
    public static function getNewSalt()
    {
        return bin2hex(random_bytes(16));
    }
}
