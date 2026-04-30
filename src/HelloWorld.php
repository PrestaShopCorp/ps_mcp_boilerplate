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

namespace PrestaShop\Module\PsMcpBoilerplate;

use Configuration;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpTool;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpSchema;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpIcon;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpToolAnnotations;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpPrompt;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpPromptArgument;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpResource;
use PrestaShop\Module\PsMcpServer\Server\Attributes\PsMcpResourceTemplate;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PromptGetException;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PsMcpResourceReadException;
use PrestaShop\Module\PsMcpServer\Server\Exceptions\PsMcpToolCallException;
use Product;

class HelloWorld
{
    #[PsMcpTool(
        name: 'say_hello',
        title: 'Hello User',
        description: 'Say hello to a user',
        annotations: new PsMcpToolAnnotations(
            title: 'Hello User',
            readOnlyHint: true,
            destructiveHint: false,
            idempotentHint: true,
            openWorldHint: false,
        ),
        icons: [
            new PsMcpIcon(
                src: 'https://picsum.photos/100/100',
                mimeType: 'image/png',
                sizes: ['100x100']
            )
        ],
        meta: ['category' => 'greeting']
    )]
    #[PsMcpSchema(
        properties: [
            'username' => ['type' => 'string', 'description' => 'Username'],
        ],
        required: ['username']
    )]
    public function sayHello(string $username): string
    {
        try {
            return "Hello, $username! Welcome to your PrestaShop store.";
        } catch (\Exception $e) {
            throw new PsMcpToolCallException($e->getMessage(), $e->getCode());
        }
    }

    #[PsMcpPrompt(
        name: 'image_generation',
        title: 'Image Generation',
        description: 'Generate images for products',
        icons: [
            new PsMcpIcon(
                src: 'https://picsum.photos/100/100',
                mimeType: 'image/png',
                sizes: ['100x100']
            )
        ],
        meta: ['category' => 'image_generation']
    )]
    public function generateImage(string $color, string $size): array {
        try {
            return [
                ['role' => 'assistant', 'content' => 'You are an image generation tool for a PrestaShop store. You generate product images based on the provided color and size.'],
                ['role' => 'user', 'content' => "Generate an image with the following parameters: color: $color, size: $size."],
                ['role' => 'assistant', 'content' => "Here is your generated image: https://your-awesome-image-generation-url?color=" . urlencode($color) . "&size=" . urlencode($size)]
            ];
        } catch (\Exception $e) {
            throw new PromptGetException($e->getMessage(), $e->getCode());
        }
    }

    #[PsMcpResource(
        uri: 'store://configuration',
        name: 'store_configuration',
        description: 'Returns the current store configuration (currency, language, timezone...)',
        mimeType: 'application/json',
        size: 18,
        icons: [
            new PsMcpIcon(
                src: 'https://picsum.photos/100/100',
                mimeType: 'image/png',
                sizes: ['100x100']
            )
        ],
        meta: ['category' => 'store_management']
    )]
    public function getStoreConfiguration(): array {
        try {
            return [
                'currency' => Configuration::get('PS_CURRENCY_DEFAULT'),
                'language' => Configuration::get('PS_LANG_DEFAULT'),
            ];
        } catch (\Exception $e) {
            throw new PsMcpResourceReadException($e->getMessage(), $e->getCode());
        }
    }

    #[PsMcpResourceTemplate(
        uriTemplate: 'products://{id}',
        name: 'product_by_id',
        description: 'Returns the full details of a product identified by its numeric ID',
        mimeType: 'application/json',
        meta: ['category' => 'product_management']
    )]
    public function getProductById(int $id): array {
        try {
             $product = new Product($id, true);
            return $product->getFields();
        } catch (\Exception $e) {
            throw new PsMcpResourceReadException($e->getMessage(), $e->getCode());
        }
    }
}
