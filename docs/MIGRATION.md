# Миграция контента ksenonspb.ru → новая тема

## Общая стратегия

Старые «статьи» блога на ksenonspb.ru — это **кейсы работ**, привязанные к марке автомобиля. Их нужно перенести в CPT `portfolio`, а рубрики марок (BMW, Mercedes и т.д.) — в CPT `brand`.

Отдельный CPT «Блог» **не используется**.

## Шаг 1. Подготовка WordPress

1. Установить тему `ksenonspb` и активировать.
2. В админке ACF → **Sync** для всех групп из `acf-json/` (9 файлов `group_ksenon_*`).
3. Настройки → Постоянные ссылки → сохранить (flush rewrite rules).
4. Создать страницы:
   - Главная (назначить как Front Page)
   - О компании → шаблон «О компании» (`page-o-kompanii.php`)
   - Стоимость услуг → шаблон «Стоимость услуг» (`page-stoimost.php`)
   - Наши услуги → шаблон «Наши услуги» (`page-uslugi.php`) — опционально, есть архив `/uslugi/`

## Шаг 2. Импорт марок (brand)

Для каждой рубрики «марка» на старом сайте (BMW, Mercedes, Volvo…):

```sql
-- Пример: создать CPT brand через WP-CLI
wp post create --post_type=brand --post_title='BMW' --post_name='bmw' --post_status=publish
```

Или скрипт PHP / плагин импорта:

```php
$old_brand_terms = get_terms(['taxonomy' => 'category', 'hide_empty' => false]);
foreach ($old_brand_terms as $term) {
    wp_insert_post([
        'post_type'   => 'brand',
        'post_title'  => $term->name,
        'post_name'   => $term->slug,
        'post_status' => 'publish',
    ]);
}
```

Сохранить маппинг `old_term_id → new_brand_post_id` в JSON для второго этапа.

## Шаг 3. Импорт кейсов (portfolio)

Для каждого старого поста блога:

1. Создать запись `portfolio` с тем же заголовком и контентом.
2. Установить excerpt, featured image.
3. Заполнить ACF:
   - `case_description` — контент или excerpt
   - `related_brands` — связь с CPT brand по маппингу
   - `related_services` — по ключевым словам или вручную
4. Slug сохранить или настроить 301 redirect.

### WP-CLI (черновик)

```bash
wp post list --post_type=post --format=ids | while read id; do
  wp post create \
    --post_type=portfolio \
    --post_title="$(wp post get $id --field=post_title)" \
    --post_content="$(wp post get $id --field=post_content)" \
    --post_status=publish
done
```

## Шаг 4. 301-редиректы

Добавить в `.htaccess` или плагин Redirection:

| Старый URL | Новый URL |
|------------|-----------|
| `/category/bmw/` | `/marki/bmw/` |
| `/2024/01/post-slug/` | `/portfolio/post-slug/` |
| `/blog/` | `/portfolio/` |

## Шаг 5. Услуги и акции

- Старые страницы услуг → CPT `service` (ручной перенос или импорт).
- Акции → CPT `promotion`.

## Шаг 6. Отзывы

Заполнить repeater **Отзывы клиентов** в «Настройки сайта» — один блок используется на главной и в акциях.

## Шаг 7. Проверка

- [ ] `/uslugi/`, `/portfolio/`, `/marki/`, `/akcii/` открываются
- [ ] Главная: все секции flexible content
- [ ] Формы CF7 отправляются
- [ ] 301 с ключевых старых URL работают
- [ ] ACF Sync без конфликтов

## Файлы темы

| CPT | Шаблон | ACF группа |
|-----|--------|------------|
| service | `single-service.php` | `group_ksenon_service.json` |
| portfolio | `single-portfolio.php` | `group_ksenon_portfolio.json` |
| brand | `single-brand.php` | `group_ksenon_brand.json` |
| promotion | `single-promotion.php` | `group_ksenon_promotion.json` |
