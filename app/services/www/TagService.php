<?php

declare(strict_types=1);

namespace app\services\www;

use app\models\TagModel;
use Rancoud\Application\Application;

class TagService
{
    protected static int $maxTags = 25;

    /**
     * @param string $string
     *
     * @return string
     */
    public static function slugify(string $string): string
    {
        $string = \trim($string);
        $string = \mb_strtolower($string);
        $string = \str_replace(['.', ' ', '@'], ['-', '-', ''], $string);

        return \preg_replace(['/([--]{2,})/', '/^-/', '/-$/'], ['-', '', ''], $string);
    }

    /**
     * @param string|null $ids
     *
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     *
     * @return array|null
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
            $tags[$key]['url'] = Application::getRouter()->generateUrl('tag-blueprints', ['tag_slug' => $tag['slug'], 'page' => '1']); // phpcs:ignore
        }

        return $tags;
    }

    /**
     * @param string $slug
     *
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     *
     * @return array|null
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
     * @param string $textareaTagsRaw
     *
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Database\DatabaseException
     * @throws \Rancoud\Model\ModelException
     *
     * @return string|null
     */
    public static function createAndFindTagsWithTextareaTags(string $textareaTagsRaw): ?string
    {
        $tagsRaw = \explode("\n", $textareaTagsRaw);

        // extract non empty tags
        $tagsToSeek = [];
        $itemsCount = 0;
        foreach ($tagsRaw as $tagRaw) {
            $tagRaw = \trim($tagRaw);
            if ($tagRaw === '') {
                continue;
            }

            if (\preg_match('/^[a-zA-Z0-9._ -]*$/', $tagRaw) !== 1) {
                continue;
            }

            $tagRaw = \preg_replace('/\s+/', ' ', $tagRaw);

            $tagsToSeek[] = \mb_strtolower($tagRaw);
            ++$itemsCount;

            if ($itemsCount > static::$maxTags) {
                break;
            }
        }

        if (empty($tagsToSeek)) {
            return null;
        }

        // seek tag already created
        $tagModel = new TagModel(Application::getDatabase());
        $tagsFound = $tagModel->findTagsWithNames($tagsToSeek) ?? [];
        $tagsToCreate = $tagsToSeek;
        $tagsCleaned = [];

        foreach ($tagsFound as $tagFound) {
            if (\in_array($tagFound['name'], $tagsToSeek, true)) {
                $tagsCleaned[] = $tagFound['id'];
                unset($tagsToCreate[\array_search($tagFound['name'], $tagsToCreate, true)]);
            }
        }

        if (empty($tagsToCreate)) {
            return \implode(',', $tagsCleaned);
        }

        // create new tags
        $newTagsIDs = [];
        foreach ($tagsToCreate as $tagToCreate) {
            $slug = static::slugify($tagToCreate);
            if ($tagModel->findTagWithSlug($slug) === null) {
                $newTagsIDs[] = $tagModel->create(['name' => $tagToCreate, 'slug' => $slug]);
            }
        }

        return \implode(',', \array_merge($tagsCleaned, $newTagsIDs));
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Model\ModelException
     *
     * @return array
     */
    public static function getAllTags(): array
    {
        return (new TagModel(Application::getDatabase()))->all(['no_limit' => 1, 'order' => 'name|asc'], ['name']);
    }
}
