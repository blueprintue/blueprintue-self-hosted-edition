<?php

declare(strict_types=1);

namespace app\services\www;

use app\models\TagModel;
use Rancoud\Application\Application;

class TagService
{
    protected static int $maxTags = 25;

    public static function slugify(string $string): string
    {
        $string = \mb_trim($string);
        $string = \mb_strtolower($string);
        $string = \str_replace(['.', ' ', '@'], ['-', '-', ''], $string);

        return \preg_replace(['/([--]{2,})/', '/^-/', '/-$/'], ['-', '', ''], $string);
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function getTagsWithListIDs(?string $ids): ?array
    {
        if (empty($ids)) {
            return null;
        }

        $tags = (new TagModel(Application::getDatabase()))->getTagsWithListIDs($ids);
        if ($tags === null) {
            return null;
        }

        foreach ($tags as $key => $tag) {
            $tags[$key]['url'] = Application::getRouter()->generateUrl('tag-blueprints', ['tag_slug' => $tag['slug'], 'page' => '1']);
        }

        return $tags;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function findTagWithSlug(string $slug): ?array
    {
        $tagModel = new TagModel(Application::getDatabase());
        $tag = $tagModel->findTagWithSlug($slug);

        if (empty($tag)) {
            return null;
        }

        return $tag;
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     */
    public static function createAndFindTagsWithTextareaTags(string $textareaTagsRaw): ?string
    {
        $tagsRaw = \explode("\n", $textareaTagsRaw);

        // extract non empty tags
        $tagsToCreate = [];
        $tagsSlugToSeek = [];
        $itemsCount = 0;
        foreach ($tagsRaw as $tagRaw) {
            $tagRaw = \mb_trim($tagRaw);
            if ($tagRaw === '') {
                continue;
            }

            if (\preg_match('/^[a-zA-Z0-9._ -]*$/', $tagRaw) !== 1) {
                continue;
            }

            $tagRaw = \preg_replace('/\s+/', ' ', $tagRaw);

            $tagsToCreate[] = \mb_strtolower($tagRaw);
            $tagsSlugToSeek[] = static::slugify($tagRaw);
            ++$itemsCount;

            if ($itemsCount > static::$maxTags) {
                break;
            }
        }

        if (empty($tagsToCreate)) {
            return null;
        }

        // seek tag already created
        $tagModel = new TagModel(Application::getDatabase());
        $tagsFound = $tagModel->findTagsWithSlugs($tagsSlugToSeek) ?? [];
        $tagsAlreadyPresent = [];

        foreach ($tagsFound as $tagFound) {
            $idx = \array_search($tagFound['slug'], $tagsSlugToSeek, true);
            if ($idx !== false) {
                $tagsAlreadyPresent[] = $tagFound['id'];
                unset($tagsToCreate[$idx], $tagsSlugToSeek[$idx]);
            }
        }

        if (empty($tagsToCreate)) {
            return \implode(',', $tagsAlreadyPresent);
        }

        // create new tags
        $newTagsIDs = [];
        foreach ($tagsToCreate as $tagToCreate) {
            $slug = static::slugify($tagToCreate);
            if ($tagModel->findTagWithSlug($slug) === null) {
                $newTagsIDs[] = $tagModel->create(['name' => $tagToCreate, 'slug' => $slug]);
            }
        }

        return \implode(',', \array_merge($tagsAlreadyPresent, $newTagsIDs));
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     */
    public static function getAllTags(): array
    {
        return (new TagModel(Application::getDatabase()))
            ->all(['no_limit' => 1, 'order' => 'name|asc'], ['name']);
    }
}
