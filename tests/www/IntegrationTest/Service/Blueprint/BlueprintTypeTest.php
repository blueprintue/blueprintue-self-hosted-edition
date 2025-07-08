<?php

declare(strict_types=1);

namespace tests\www\IntegrationTest\Service\Blueprint;

use app\services\www\BlueprintService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @internal */
class BlueprintTypeTest extends TestCase
{
    public static function provideFindBlueprintTypeDataCases(): iterable
    {
        yield 'BehaviorTreeGraphNode_ = behavior_tree' => [
            'content' => 'BehaviorTreeGraphNode_',
            'type'    => 'behavior_tree',
        ];

        yield 'BehaviorTreeDecoratorGraphNode_ = behavior_tree' => [
            'content' => 'BehaviorTreeDecoratorGraphNode_',
            'type'    => 'behavior_tree',
        ];

        yield 'MaterialGraphNode = material' => [
            'content' => 'MaterialGraphNode',
            'type'    => 'material',
        ];

        yield 'AnimGraphNode_ = animation' => [
            'content' => 'AnimGraphNode_',
            'type'    => 'animation',
        ];

        yield '/Script/MetasoundEditor = metasound' => [
            'content' => '/Script/MetasoundEditor',
            'type'    => 'metasound',
        ];

        yield '/Script/NiagaraEditor = niagara' => [
            'content' => '/Script/NiagaraEditor',
            'type'    => 'niagara',
        ];

        yield 'PCGEditorGraphNode = pcg' => [
            'content' => 'PCGEditorGraphNode',
            'type'    => 'pcg',
        ];

        yield 'empty = blueprint' => [
            'content' => '',
            'type'    => 'blueprint',
        ];
    }

    #[DataProvider('provideFindBlueprintTypeDataCases')]
    public function testFindBlueprintType(string $content, string $type): void
    {
        static::assertSame($type, BlueprintService::findBlueprintType($content));
    }
}
