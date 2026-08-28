<?php
define('DATA_DIR', __DIR__ . '/../data/');

function dataRead($file) {
    $path = DATA_DIR . $file . '.json';
    if (!file_exists($path)) return [];
    $content = file_get_contents($path);
    return json_decode($content, true) ?? [];
}

function dataWrite($file, $data) {
    $path = DATA_DIR . $file . '.json';
    $tmp = $path . '.tmp';
    $result = file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    if ($result === false) return false;
    return rename($tmp, $path);
}

function dataReadMap($file) {
    $path = DATA_DIR . $file . '.json';
    if (!file_exists($path)) return [];
    $content = file_get_contents($path);
    return json_decode($content, true) ?? [];
}

function dataWriteMap($file, $data) {
    return dataWrite($file, $data);
}

function findById($collection, $id) {
    foreach ($collection as $item) {
        if ($item['id'] === $id) return $item;
    }
    return null;
}

function filterBy($collection, $field, $value) {
    return array_values(array_filter($collection, fn($item) => $item[$field] === $value));
}

function removeById($collection, $id) {
    return array_values(array_filter($collection, fn($item) => $item['id'] !== $id));
}

function updateById($collection, $id, $updates) {
    return array_map(function ($item) use ($id, $updates) {
        if ($item['id'] === $id) {
            return array_merge($item, $updates);
        }
        return $item;
    }, $collection);
}
