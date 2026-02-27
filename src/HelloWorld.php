<?php
/**
 * Copyright (c) 2025 PrestaShop SA
 *
 * All Rights Reserved.
 *
 * This module is proprietary software owned by PrestaShop SA. All intellectual property rights, including copyrights, trademarks, and trade secrets, are reserved by PrestaShop SA.
 *
 * The PS MCP Server module was developed by PrestaShop, which holds all associated intellectual property rights. The license granted to the user does not entail any transfer of rights. The user shall refrain from any act that may infringe upon PrestaShop's rights and undertakes to strictly comply with the limitations of the license set out below. PrestaShop grants the user a personal, non-exclusive, non-transferable, and non-sublicensable license to use the MCP Server module, worldwide and for the entire duration of use of the module. This license is strictly limited to installing the module and using it solely for the operation of the user's PrestaShop store.
 */

namespace PrestaShop\Module\PsMcpTools;

use Configuration;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpTool;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpSchema;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpToolAnnotations;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpPrompt;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpResource;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpResourceTemplate;
use Product;

class HelloWorld
{
    #[PsMcpTool(
        name: 'say_hello',
        description: 'Say hello to a user'
    )]
    #[PsMcpSchema(
        properties: [
            'username' => ['type' => 'string', 'description' => 'Username'],
        ],
        required: ['username']
    )]
    public function sayHello(string $username): string
    {
        return 'Hello, ' . $username . '!';
    }

    #[PsMcpPrompt(
        name: 'low_stock_analysis',
        description: 'Analyse products with low stock and suggest restocking actions',
    )]
    public function lowStockAnalysis(): string {
        return 'List all products in this PrestaShop store that have a stock quantity below 5. '
            . 'For each product, show its name, ID, current stock, and suggest a restocking quantity based on its sales history.';
    }

    #[PsMcpResource(
        uri: 'store://configuration',
        name: 'Store Configuration',
        description: 'Returns the current store configuration (currency, language, timezone...)',
        mimeType: 'application/json',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false),
    )]
    public function getStoreConfiguration(): array {
        return [
            'currency' => Configuration::get('PS_CURRENCY_DEFAULT'),
            'language' => Configuration::get('PS_LANG_DEFAULT'),
        ];
    }

    #[PsMcpResourceTemplate(
        uriTemplate: 'products://{id}',
        name: 'Product by ID',
        description: 'Returns the full details of a product identified by its numeric ID',
        mimeType: 'application/json',
        annotations: new PsMcpToolAnnotations(readOnlyHint: true, destructiveHint: false),
    )]
    public function getProductById(int $id): array {
        $product = new Product($id, true);
        return $product->getFields();
    }
}
