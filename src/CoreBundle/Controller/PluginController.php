<?php

namespace Runalyze\Bundle\CoreBundle\Controller;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PluginController extends AbstractPluginsAwareController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/call/call.Plugin.install.php")
     * @Security("has_role('ROLE_USER')")
     */
    public function pluginInstallAction()
    {
        $Frontend = new \Frontend(false, $this->tokenStorage);
        $Pluginkey = filter_input(INPUT_GET, 'key');

        $Installer = new \PluginInstaller($Pluginkey);

        echo '<h1>'.__('Install').' '.$Pluginkey.'</h1>';

        if ($Installer->install()) {
        	$Factory = new \PluginFactory();
        	$Plugin = $Factory->newInstance($Pluginkey);

        	echo \HTML::okay(__('The plugin has been successfully installed.'));

        	echo '<ul class="blocklist"><li>';
        	echo $Plugin->getConfigLink(\Icon::$CONF.' '.__('Configuration'));
        	echo '</li></ul>';

        	\Ajax::setReloadFlag(\Ajax::$RELOAD_ALL);
        	echo \Ajax::getReloadCommand();
        } else {
        	echo \HTML::error(__('There was a problem, the plugin could not be installed.'));
        }

        echo '<ul class="blocklist"><li>';
        echo \Ajax::window('<a href="'.\ConfigTabPlugins::getExternalUrl().'">'.\Icon::$TABLE.' '.__('back to list').'</a>');
        echo '</li></ul>';

        return new Response();
    }

    /**
     * @Route("/my/plugin/{id}", requirements={"id" = "\d+"}, name="plugin-display")
     * @Security("has_role('ROLE_USER')")
    */
    public function pluginDisplayAction($id, Request $request, Account $account)
    {
        $Frontend = new \Frontend(false, $this->tokenStorage);

        return $this->getResponseFor($id, $request, $account);
    }

    /**
     * @Route("/call/call.Plugin.config.php", name="plugin-config")
     * @Security("has_role('ROLE_USER')")
    */
    public function pluginConfigAction()
    {
        $Frontend = new \Frontend(false, $this->tokenStorage);
        $Factory = new \PluginFactory();

        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        	$Plugin = $Factory->newInstanceFor((int)$_GET['id']);
        	$Plugin->displayConfigWindow();
        } else {
        	echo '<em>'.__('Something went wrong ...').'</em>';
        }

        return new Response();
    }
}
