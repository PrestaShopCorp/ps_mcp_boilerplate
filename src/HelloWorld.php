<?php

namespace PrestaShop\Module\PsMcpTools;

use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

class HelloWorld
{
    /** @phpstan-ignore attribute.notFound */
    #[McpTool(
        name: 'say_hello',
        description: 'Say hello to a user'
    )]
    /** @phpstan-ignore attribute.notFound */
    #[Schema(
        properties: [
            'username' => ['type' => 'string', 'description' => 'Username'],
        ],
        required: ['username']
    )]
    public function sayHello(string $username): string
    {
        return 'Hello, ' . $username . '!';
    }
}
