# Optimacros Test Task

Сделал только один тест: сраниваю массив до конвертации в json с массивом, который получаю из вашего примера (output.json).
Тест проходит успешно. Потратил примерно 5 часов. Часть времени потерял из-за работы без привычного Framework-а и из-за устаревшей версии php.

Есть что рефакторить. Например, Repo можно было сделать более универсальным и отвязать его от работы с файлами.
RepoItem можно было сделать более защищенным от изменений снаружи. Сейчас у него просто публичные поля.

## Requirements

- PHP >= 7.1.33
- Composer >= 2.2.18

## Install

```
composer install
```

## Run

```
php run.php <input.csv> <output.json>
```

## Test

```
./vendor/bin/phpunit ./src
```