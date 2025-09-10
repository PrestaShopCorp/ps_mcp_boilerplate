<?php

namespace PrestaShop\Module\PsMcpTools;

/**
 * @phpstan-ignore attribute.notFound
 */
class HelloWorld
{
    #[\PhpMcp\Server\Attributes\McpTool(
        name: 'say_hello',
        description: 'Say hello to a user'
    )]
    #[\PhpMcp\Server\Attributes\Schema(
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
