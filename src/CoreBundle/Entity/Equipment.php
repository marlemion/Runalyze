<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Runalyze\Bundle\CoreBundle\Entity\Common\AccountRelatedEntityInterface;
use Runalyze\Bundle\CoreBundle\Entity\Common\IdentifiableEntityInterface;
use Runalyze\Bundle\CoreBundle\Entity\Common\NamedEntityInterface;

/**
 * Equipment
 *
 * @ORM\Table(name="equipment")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Repository\EquipmentRepository")
 */
class Equipment implements IdentifiableEntityInterface, NamedEntityInterface, AccountRelatedEntityInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", length=255, nullable=false)
     */
    private $notes = '';

    /**
     * @var float [km]
     *
     * @ORM\Column(name="distance", type="casted_decimal_2", precision=8, scale=2, options={"unsigned":true})
     */
    private $distance = 0.00;

    /**
     * @var int [s]
     *
     * @ORM\Column(name="time", type="integer", nullable=false, options={"unsigned":true})
     */
    private $time = 0;

    /**
     * @var int [km]
     *
     * @ORM\Column(name="additional_km", type="smallint", nullable=false, options={"unsigned":true})
     */
    private $additionalKm = 0;

    /**
     * @var null|\DateTime
     *
     * @ORM\Column(name="date_start", type="date", nullable=true)
     */
    private $dateStart;

    /**
     * @var null|\DateTime
     *
     * @ORM\Column(name="date_end", type="date", nullable=true)
     */
    private $dateEnd;

    /**
     * @var EquipmentType
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\EquipmentType", inversedBy="equipment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="typeid", referencedColumnName="id", nullable=false)
     * })
     */
    private $type;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account", inversedBy="equipment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Training", mappedBy="equipment")
     */
    private $activity;

    public function __construct()
    {
        $this->activity = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $notes
     *
     * @return $this
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param float $distance [km]
     *
     * @return $this
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * @param float $distance [km]
     *
     * @return $this
     */
    public function addDistance($distance)
    {
        $this->distance += $distance;

        return $this;
    }

    /**
     * @return float [km]
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param int $time [s]
     *
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * @param int $time [s]
     *
     * @return $this
     */
    public function addTime($time)
    {
        $this->time += $time;

        return $this;
    }

    /**
     * @return int [s]
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return float|null [s/km]
     */
    public function getPace()
    {
        return $this->distance > 0 ? $this->time / $this->distance : null;
    }

    /**
     * @param int $additionalKm [km]
     *
     * @return $this
     */
    public function setAdditionalKm($additionalKm)
    {
        $this->additionalKm = $additionalKm;

        return $this;
    }

    /**
     * @return int [km]
     */
    public function getAdditionalKm()
    {
        return $this->additionalKm;
    }

    /**
     * @return float [km]
     */
    public function getTotalDistance()
    {
        return $this->distance + $this->additionalKm;
    }

    /**
     * @param null|\DateTime $dateStart
     *
     * @return $this
     */
    public function setDateStart(\DateTime $dateStart = null)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @return bool
     */
    public function hasStartDate()
    {
        return null !== $this->dateStart;
    }

    /**
     * @param null|\DateTime $dateEnd
     *
     * @return $this
     */
    public function setDateEnd(\DateTime $dateEnd = null)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return null === $this->dateEnd;
    }

    /**
     * @param EquipmentType $type
     *
     * @return $this
     */
    public function setType(EquipmentType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return \Runalyze\Bundle\CoreBundle\Entity\EquipmentType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Account $account
     *
     * @return $this
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param Training $activity
     *
     * @return $this
     */
    public function addActivity(Training $activity)
    {
        $this->activity[] = $activity;

        return $this;
    }

    /**
     * @param Training $activity
     */
    public function removeActivity(Training $activity)
    {
        $this->activity->removeElement($activity);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivity()
    {
        return $this->activity;
    }
}
