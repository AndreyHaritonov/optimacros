<?php

namespace Andrey\Optimacros;

class RepoItem implements Arrayable {
    public $name;
    public $type;
    public $parent;
    public $children = [];
    public $relation = null;

    public function __construct(
        string $name,
        string $type,
        ?RepoItem $parent
    )
    {
        $this->name = $name;
        $this->type = $type;
        $this->parent = $parent;
    }

    public function addChild(RepoItem $child): void {
        $this->children[] = $child;
    }

    /**
     * Представление в виде массива с учетом relation
     */
    public function toArray(): array {
        //Маасив наших детей
        $childrenArr = array_map(function($child) {
            return $child->toArray();
        }, $this->children);

        //К массиву наших детей добавим детей из relation.
        //Не забудем поменять родителя на себя для них.
        if ($this->relation) {
            $childreRelationArr = array_map(function($child) {
                return array_merge($child->toArray(), ['parent' => $this->name]);
            }, $this->relation->children);
            $childrenArr = array_merge($childrenArr, $childreRelationArr);
        }

        return [
            'itemName' => $this->name,
            'parent' => $this->parent ? $this->parent->name : null,
            'children' => $childrenArr,
        ];
    }

    /**
     * Печать дерева без учета relation
     */
    public function printTree(int $level = 0): void {
        echo str_repeat("\t", $level) . "{$this->name}\n";
        foreach($this->children as $child) {
            $child->printTree($level + 1);
        }
    }
}