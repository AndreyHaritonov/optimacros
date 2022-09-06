<?php

namespace Andrey\Optimacros;

/**
 * Хранилище для всей структуры.
 * Поддерживает чтение из csv и сохранение в json.
 */
class Repo implements Arrayable {
    private $data = [];

    /**
     * Загрузка из файла
     */
    public function loadFromFile($filename): void {
        $this->data = [];

        $handle = fopen($filename, "r");
        if ($handle === false) {
            throw new RepoException("Unable to open file {$filename}");
        }

        try {
            $row = 1;
            $columnName = $columnType = $columnParent = $columnRelation = false; //Колонки могут быть динамичными
            $relations = []; //Сохраним зависимости для второго прохода

            //Чтение файла, заполнение структуры дерева, сохранение зависимостей
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                if (count($data) !== 4) {
                    throw new RepoException("Invalid data in {$filename}:{$row}");
                }

                if ($row === 1) {
                    $columnName = array_search('Item Name', $data, true);
                    $columnType = array_search('Type', $data, true);
                    $columnParent = array_search('Parent', $data, true);
                    $columnRelation = array_search('Relation', $data, true);
                    if ($columnName === false || $columnType === false || $columnParent === false || $columnRelation === false) {
                        throw new RepoException("Invalid header in {$filename}");
                    }
                } else {
                    $parentName = $data[$columnParent];
                    $relationName = $data[$columnRelation];
                    $type = $data[$columnType];

                    $parent = $this->data[$parentName] ?? null;
                    if ($parentName !== '' && $parent === null) {
                        throw new RepoException("Parent {$parentName} not found in {$filename}:{$row}");
                    }

                    $item = new RepoItem($data[$columnName], $type, $parent);
                    $this->data[$item->name] = $item;
                    if ($parent) {
                        $parent->addChild($item);
                    }

                    if ($relationName !== '') {
                        if ($type !== ItemType::DIRECT_COMPONENTS) {
                            throw new RepoException("Found relation for invalid type in {$filename}:{$row}");
                        }
                        $relations[$item->name] = $relationName;
                    }
                }

                $row++;
            }

            //Установка зависимостей
            foreach($relations as $itemName => $relationName) {
                $item = $this->data[$itemName];
                $relation = $this->data[$relationName] ?? null;
                if (!$relation) {
                    throw new RepoException("Relation {$relationName} not found in {$filename}");
                }
                if ($relation->type !== ItemType::PRODUCTS_COMPONENTS) {
                    throw new RepoException("Invalid relation type for {$relationName} in {$filename}");
                }
                $item->relation = $relation;
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Сохранить в формате json.
     * Вернет размер файла.
     */
    public function saveToJson($filename): int {
        $res = @file_put_contents($filename, json_encode($this->toArray(), JSON_UNESCAPED_UNICODE));
        if ($res === false) {
            throw new RepoException("Unable to save file {$filename}");
        }
        return $res;
    }

    /**
     * Количество загруженных элементов
     */
    public function getCount(): int {
        return count($this->data);
    }

    /**
     * Представление в виде массива с учетом relation
     */
    public function toArray(): array {
        $roots = array_filter($this->data, function($item) {
            return !$item->parent;
        });
        return array_values(array_map(function($root) {
            return $root->toArray();
        }, $roots));
    }

    /**
     * Печать дерева без учета relation (для отладки)
     */
    public function printTree(): void {
        $roots = array_filter($this->data, function($item) {
            return !$item->parent;
        });
        foreach($roots as $root) {
            $root->printTree();
        }
    }
}