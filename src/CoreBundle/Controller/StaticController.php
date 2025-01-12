<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StaticController extends Controller
{
    /** @var string */
    protected $runalyzeVersion;

    public function __construct(
        string $runalyzeVersion)
    {
        $this->runalyzeVersion = $runalyzeVersion;
    }

    /**
     * @Route("/dashboard/help", name="help")
     * @Security("has_role('ROLE_USER')")
     */
    public function dashboardHelpAction()
    {
        return $this->render('static/help.html.twig', [
            'version' => $this->runalyzeVersion
        ]);
    }

    /**
     * @Route("/dashboard/help-calculations", name="help-calculations")
     * @Security("has_role('ROLE_USER')")
     */
    public function dashboardHelpCalculationsAction()
    {
        return $this->render('static/help_calculations.html.twig');
    }
}
