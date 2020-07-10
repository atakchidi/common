<?php

declare(strict_types=1);

namespace Damax\Common\Tests\Bridge\Symfony\Bundle\DependencyInjection;

use Damax\Common\Bridge\Symfony\Bundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * @test
     */
    public function it_processes_empty_config(): void
    {
        $config = [];

        $this->assertProcessedConfigurationEquals([$config], [
            'listeners' => [
                'serialize' => false,
                'deserialize' => false,
                'pagination' => false,
                'domain_events' => false,
            ],
            'serialize_context' => [],
            'deserialize_context' => [],
        ]);
    }

    /**
     * @test
     */
    public function it_configures_listeners(): void
    {
        $config = [
            'listeners' => [
                'serialize' => true,
                'deserialize' => true,
                'pagination' => true,
                'domain_events' => true,
            ],
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'listeners' => [
                'serialize' => true,
                'deserialize' => true,
                'pagination' => true,
                'domain_events' => true,
            ],
            'serialize_context' => [],
            'deserialize_context' => [],
        ]);
    }

    /**
     * @test
     */
    public function it_configures_serializer_contexts(): void
    {
        $config = [
            'serialize_context' => [
                'foo' => true,
                'bar' => false,
            ],
            'deserialize_context' => [
                'baz' => true,
            ],
        ];

        $this->assertProcessedConfigurationEquals([$config], [
            'serialize_context' => [
                'foo' => true,
                'bar' => false,
            ],
            'deserialize_context' => [
                'baz' => true,
            ],
        ]);
    }

    protected function getConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }
}
