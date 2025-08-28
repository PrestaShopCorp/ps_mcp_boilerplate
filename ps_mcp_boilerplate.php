<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

// DONT'T USE "use" STATEMENT HERE, IT'S NOT COMPAT WITH 1.6
// PREFER "use" IN CLASS DIRECTLY

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_mcp_boilerplate extends Module
{
    /**
     * @var string
     */
    public $version;

    /**
     * @var int Defines the multistore compatibility level of the module
     */
    public $multistoreCompatibility;

    /**
     * @var string contact email of the maintainers (please consider using github issues)
     */
    public $emailSupport;

    /**
     * @var string available terms of services
     */
    public $termsOfServiceUrl;

    /**
     * __construct.
     */
    public function __construct()
    {
        // @see https://devdocs.prestashop-project.org/8/modules/concepts/module-class/
        $this->name = 'ps_mcp_boilerplate';
        $this->tab = 'administration';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->version = '0.0.0';
        $this->module_key = '';

        parent::__construct();

        $this->emailSupport = 'cloudsync-support@prestashop.com';
        $this->termsOfServiceUrl = 'https://www.prestashop.com/en/prestashop-account-privacy';
        $this->displayName = $this->l('PrestaShop MCP Boilerplate');
        $this->description = $this->l('This is simple boilerplate for ps_mcp module and demonstrate how to add some tools on another modules for ps_mcp.');
        $this->confirmUninstall = $this->l('Do you really want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '8.0', 'max' => _PS_VERSION_];
    }

    public function getMultistoreCompatibility(): int
    {
        return (int) true;
    }

    public function isMcpCompliant(): bool
    {
        return true;
    }
}
