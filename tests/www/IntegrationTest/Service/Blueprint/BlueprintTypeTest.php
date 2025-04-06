<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\IntegrationTest\Service\Blueprint;

use app\services\www\BlueprintService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BlueprintTypeTest extends TestCase
{
    public static function dataCases(): array
    {
        return [
            'BehaviorTreeGraphNode_ = behavior_tree' => [
                'content' => 'BehaviorTreeGraphNode_',
                'type'    => 'behavior_tree',
            ],
            'BehaviorTreeDecoratorGraphNode_ = behavior_tree' => [
                'content' => 'BehaviorTreeDecoratorGraphNode_',
                'type'    => 'behavior_tree',
            ],
            'MaterialGraphNode = material' => [
                'content' => 'MaterialGraphNode',
                'type'    => 'material',
            ],
            'AnimGraphNode_ = animation' => [
                'content' => 'AnimGraphNode_',
                'type'    => 'animation',
            ],
            '/Script/MetasoundEditor = metasound' => [
                'content' => '/Script/MetasoundEditor',
                'type'    => 'metasound',
            ],
            '/Script/NiagaraEditor = niagara' => [
                'content' => '/Script/NiagaraEditor',
                'type'    => 'niagara',
            ],
            'PCGEditorGraphNode = pcg' => [
                'content' => 'PCGEditorGraphNode',
                'type'    => 'pcg',
            ],
            'empty = blueprint' => [
                'content' => '',
                'type'    => 'blueprint',
            ],
        ];
    }

    /**
     * @dataProvider dataCases
     */
    #[DataProvider('dataCases')]
    public function testFindBlueprintType(string $content, string $type): void
    {
        static::assertSame($type, BlueprintService::findBlueprintType($content));
    }
}
