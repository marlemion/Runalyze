<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CallController extends Controller
{
    /** @var string */
    protected $garminApiKey;

    public function __construct(
        string $garminApiKey)
    {
        $this->garminApiKey = $garminApiKey;
    }

    /**
     * @Route("/call/call.DataBrowser.display.php")
     * @Route("databrowser", name="databrowser")
     * @Security("has_role('ROLE_USER')")
     */
    public function dataBrowserAction(TokenStorageInterface $tokenStorage)
    {
        $Frontend = new \Frontend(true, $tokenStorage);
        $DataBrowser = new \DataBrowser();
        $DataBrowser->display();

        return new Response();
    }

    /**
     * @Route("/call/call.garminCommunicator.php")
     * @Route("/upload/garminCommunicator")
     * @Security("has_role('ROLE_USER')")
     */
    public function garminCommunicatorAction()
    {
        return $this->render('import/garmin_communicator.html.twig', array(
            'garminAPIKey' => $this->garminApiKey,
        ));
    }

    /**
     * @Route("/call/savePng.php")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     */
    public function savePngAction()
    {
        header("Content-type: image/png");
        header("Content-Disposition: attachment; filename=".strtolower(str_replace(' ', '_', $_POST['filename'])));

        $encodeData = substr($_POST['image'], strpos($_POST['image'], ',') + 1);

        return new Response(base64_decode($encodeData));
    }

    /**
     * @Route("/settings", name="settings")
     * @Security("has_role('ROLE_USER')")
     */
    public function windowConfigAction(
        Request $request,
        TokenStorageInterface $tokenStorage) {
        $Frontend = new \Frontend(true, $tokenStorage);
        $ConfigTabs = new \ConfigTabs();
        $ConfigTabs->addDefaultTab(new  \ConfigTabGeneral());
        $ConfigTabs->addTab(new \ConfigTabPlugins());
        $ConfigTabs->display();

        echo \Ajax::wrapJSforDocumentReady('Runalyze.Overlay.removeClasses();');

        return new Response();
    }

    /**
     * @Route("/call/ajax.change.Config.php")
     * @Security("has_role('ROLE_USER')")
     * @Method({"GET"})
     */
    public function ajaxChanceConfigAction(TokenStorageInterface $tokenStorage)
    {
        $Frontend = new \Frontend(true, $tokenStorage);

        switch ($_GET['key']) {
        	case 'garmin-ignore':
        		\Runalyze\Configuration::ActivityForm()->ignoreActivityID($_GET['value']);
        		break;

        	case 'leaflet-layer':
        		\Runalyze\Configuration::ActivityView()->updateLayer($_GET['value']);
        		break;

        	default:
        		if (substr($_GET['key'], 0, 5) == 'show-') {
        			$key = substr($_GET['key'], 5);
        			\Runalyze\Configuration::ActivityForm()->update($key, (bool)$_GET['value']);
        		}
        }

        return new Response();
    }

    /**
     * @Route("/my/search", name="my-search")
     * @Security("has_role('ROLE_USER')")
     */
    public function windowSearchAction(TokenStorageInterface $tokenStorage)
    {
        $Frontend = new \Frontend(false, $tokenStorage);
        $showResults = !empty($_POST);

        if (isset($_GET['get']) && $_GET['get'] == 'true') {
        	$_POST = array_merge($_POST, $_GET);
        	$showResults = true;

        	\SearchFormular::transformOldParamsToNewParams();
        }

        if (empty($_POST) || Request::createFromGlobals()->query->get('get') == 'true') {
        	echo '<div class="panel-heading">';
        	echo '<h1>'.__('Search for activities').'</h1>';
        	echo '</div>';

        	$Formular = new \SearchFormular($this->generateUrl('my-search'));
        	$Formular->display();
        }

        $Results = new \SearchResults($showResults);

        if ($showResults && $Results->multiEditorRequested()) {
            return $this->redirectToRoute('multi-editor', ['ids' => implode(',', $Results->getIdsForMultiEditor())]);
        }

        $Results->display();

        return new Response();
    }

    /**
     * @return \PlotSumData
     */
    protected function getPlotSumData() {
        $Request = Request::createFromGlobals();

        if (is_null($Request->query->get('y'))) {
        	$_GET['y'] = \PlotSumData::LAST_12_MONTHS;
        }

        return 'week' == $Request->query->get('type', 'month') ? new \PlotWeekSumData() : new \PlotMonthSumData();
    }

    /**
     * @Route("/call/window.plotSumData.php")
     */
    public function windowsPlotSumDataAction(TokenStorageInterface $tokenStorage)
    {
        $Frontend = new \Frontend(false, $tokenStorage);
        $this->getPlotSumData()->display();

        return new Response();
    }

    /**
     * @Route("/call/login.php")
     */
    public function loginAction()
    {
        return $this->render('login/ajax_not_logged_in.html.twig');
    }
}
