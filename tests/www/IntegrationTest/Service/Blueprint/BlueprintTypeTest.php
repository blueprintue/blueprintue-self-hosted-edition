<?php

/* @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace tests\www\IntegrationTest\Service\Blueprint;

use app\services\www\BlueprintService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @internal */
class BlueprintTypeTest extends TestCase
{
    public static function provideDataCases(): iterable
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

    #[DataProvider('provideDataCases')]
    public function testFindBlueprintType(string $content, string $type): void
    {
        static::assertSame($type, BlueprintService::findBlueprintType($content));
    }
}
