<?php
/**
 * @package BlackBrix Content Sliders
 * @license GPL-2.0-or-later
 */
defined('_JEXEC') or die;

use BlackBrix\Plugin\Content\Bbcontentsliders\Extension\Bbcontentsliders;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(PluginInterface::class, static function (Container $container): PluginInterface {
            $plugin = new Bbcontentsliders(
                $container->get(DispatcherInterface::class),
                (array) PluginHelper::getPlugin('content', 'bbcontentsliders')
            );
            $plugin->setApplication(Factory::getApplication());

            return $plugin;
        });
    }
};
