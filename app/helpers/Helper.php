<?php

declare(strict_types=1);

namespace app\helpers;

use Rancoud\Application\Application;

class Helper
{
    /** @throws \Rancoud\Application\ApplicationException */
    public static function getBlueprintLink(string $slug, ?int $version = null): string
    {
        if ($version !== null) {
            return Application::getRouter()->generateUrl('blueprint', ['blueprint_slug' => $slug, 'version' => $version]);
        }

        $link = Application::getRouter()->generateUrl('blueprint', ['blueprint_slug' => $slug, 'version' => '__REMOVE__ME__']);

        return \str_replace('__REMOVE__ME__/', '', $link);
    }

    /** @throws \Rancoud\Application\ApplicationException */
    public static function getBlueprintRenderLink(string $slug, ?int $version = null): string
    {
        if ($version !== null) {
            return Application::getRouter()->generateUrl('render', ['blueprint_slug' => $slug, 'version' => $version]);
        }

        $link = Application::getRouter()->generateUrl('render', ['blueprint_slug' => $slug, 'version' => '__REMOVE__ME__']);

        return \str_replace('__REMOVE__ME__/', '', $link);
    }

    /** @throws \Rancoud\Application\ApplicationException */
    public static function getBlueprintDiffLink(string $slug, int $previousVersion, int $currentVersion): string
    {
        return Application::getRouter()->generateUrl('blueprint-diff', ['blueprint_slug' => $slug, 'previous_version' => $previousVersion, 'current_version' => $currentVersion]);
    }

    /** @throws \Rancoud\Application\ApplicationException */
    public static function formatUser(array $user): array
    {
        $user['avatar_url'] = static::getAvatarUrl($user['avatar']);
        $user['profile_url'] = Application::getRouter()->generateUrl('profile', ['profile_slug' => $user['slug']]);

        return $user;
    }

    public static function getAvatarUrl(?string $avatar): ?string
    {
        if ($avatar === null) {
            return null;
        }

        return '/medias/avatars/' . $avatar;
    }

    public static function getThumbnailUrl(?string $thumbnail): ?string
    {
        if ($thumbnail === null) {
            return null;
        }

        return '/medias/blueprints/' . $thumbnail;
    }

    /** @throws \Exception */
    public static function getSince(string $publishedAt): string
    {
        try {
            $publishedAtObject = new \DateTimeImmutable($publishedAt, new \DateTimeZone('UTC'));
        } catch (\Exception) {
            // date is invalid so we assume it was a few seconds ago
            return 'few seconds ago';
        }

        $nowObject = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        if ($publishedAtObject >= $nowObject) {
            return 'few seconds ago';
        }

        $strings = [' years ago', ' months ago', ' days ago', ' hours ago', ' mins ago'];
        $diffDateObject = $publishedAtObject->diff($nowObject);
        $dateTrick = \explode('|', $diffDateObject->format('%y|%m|%a|%h|%i'));

        for ($i = 0; $i < 5; ++$i) {
            $value = (int) $dateTrick[$i];
            if ($value >= 1) {
                return $value . $strings[$i];
            }
        }

        return 'few seconds ago';
    }

    public static function getAllUEVersion(): array
    {
        return [
            '5.6',
            '5.5',
            '5.4',
            '5.3',
            '5.2',
            '5.1',
            '5.0',
            '4.27',
            '4.26',
            '4.25',
            '4.24',
            '4.23',
            '4.22',
            '4.21',
            '4.20',
            '4.19',
            '4.18',
            '4.17',
            '4.16',
            '4.15',
            '4.14',
            '4.13',
            '4.12',
            '4.11',
            '4.10',
            '4.9',
            '4.8',
            '4.7',
            '4.6',
            '4.5',
            '4.4',
            '4.3',
            '4.2',
            '4.1',
            '4.0'
        ];
    }

    public static function getCurrentUEVersion(): string
    {
        return '5.5';
    }

    /**
     * @throws \Rancoud\Application\ApplicationException
     * @throws \Rancoud\Environment\EnvironmentException
     */
    public static function getHostname(): string
    {
        static $hostname;
        if ($hostname !== null) {
            return $hostname;
        }

        $config = Application::getConfig();
        $host = (string) $config->get('HOST');
        $scheme = ((bool) $config->get('HTTPS') === true) ? 'https://' : 'http://';

        $hostname = $scheme . $host;

        return $scheme . $host;
    }

    /** @throws \Exception */
    public static function getTimeleft(?string $expiration): ?string
    {
        if ($expiration === null) {
            return null;
        }

        try {
            $expirationAtObject = new \DateTimeImmutable($expiration, new \DateTimeZone('UTC'));
        } catch (\Exception) {
            // date is invalid so we assume it was a few seconds ago
            return 'few seconds left';
        }

        $nowObject = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        if ($expirationAtObject <= $nowObject) {
            return 'few seconds left';
        }

        $diffDateObject = $expirationAtObject->diff($nowObject);

        $days = (int) $diffDateObject->days;
        $hours = (int) $diffDateObject->format('%h');
        $minutes = (int) $diffDateObject->format('%i');

        if ($days === 0 && $hours === 0) {
            return $minutes . ' min left';
        }

        if ($days === 0 && $hours > 0) {
            return $hours . ' h and ' . $minutes . ' min left';
        }

        return $days . ' days left';
    }

    /** @throws \Exception */
    public static function formatDate(string $datetime, string $format = 'F j, Y'): string
    {
        return (new \DateTimeImmutable($datetime, new \DateTimeZone('UTC')))->format($format);
    }

    /** @throws \Exception */
    public static function getRandomString(int $length): string
    {
        $string = '';
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $countCharacters = \mb_strlen($characters) - 1;

        for ($i = 0; $i < $length; ++$i) {
            $string .= $characters[\random_int(0, $countCharacters)];
        }

        return $string;
    }

    public static function getVideoPrivacyURL(?string $videoProvider): string
    {
        if ($videoProvider === null) {
            return '#';
        }

        $videoProvider = \mb_strtolower($videoProvider);

        if ($videoProvider === 'youtube') {
            return 'https://policies.google.com/privacy';
        }

        if ($videoProvider === 'dailymotion') {
            return 'https://www.dailymotion.com/legal/privacy';
        }

        if ($videoProvider === 'vimeo') {
            return 'https://vimeo.com/privacy';
        }

        if ($videoProvider === 'niconico') {
            return 'https://account.nicovideo.jp/rules/account';
        }

        if ($videoProvider === 'bilibili') {
            return 'https://www.bilibili.com/blackboard/privacy-pc.html';
        }

        return '#';
    }

    /** @throws \Rancoud\Application\ApplicationException */
    public static function organizeVersionHistoryForDisplay(string $fileID, array $versions): array
    {
        $out = ['count' => \count($versions), 'versions' => []];
        for ($idxVersion = 0; $idxVersion < $out['count']; ++$idxVersion) {
            $day = \explode(' ', $versions[$idxVersion]['created_at'])[0];

            $versions[$idxVersion]['last'] = false;
            if (!isset($out['versions'][$day])) {
                $out['versions'][$day] = [];
            }

            if ($idxVersion + 1 === $out['count']) {
                $versions[$idxVersion]['last'] = true;
            } elseif (\explode(' ', $versions[$idxVersion + 1]['created_at'])[0] !== $day) {
                $versions[$idxVersion]['last'] = true;
            }

            if (!isset($versions[$idxVersion]['current'])) {
                $versions[$idxVersion]['current'] = false;
            }
            $versions[$idxVersion]['url'] = static::getBlueprintLink($fileID, $versions[$idxVersion]['version']);
            $versions[$idxVersion]['diff_url'] = '';
            if (($idxVersion + 1) < $out['count']) {
                $versions[$idxVersion]['diff_url'] = static::getBlueprintDiffLink($fileID, $versions[$idxVersion + 1]['version'], $versions[$idxVersion]['version']);
            }

            $out['versions'][$day][] = $versions[$idxVersion];
        }

        return $out;
    }

    public static function getFitSentence(string $string, int $maxLetters): string
    {
        if (\mb_strlen($string) < $maxLetters) {
            return $string;
        }

        if (\mb_substr($string, $maxLetters, 1) === ' ') {
            return \mb_substr($string, 0, $maxLetters);
        }

        $sentence = \mb_substr($string, 0, $maxLetters);
        $posLastSpace = \mb_strrpos($sentence, ' ');
        if ($posLastSpace === false) {
            return '';
        }

        return \mb_substr($sentence, 0, $posLastSpace);
    }

    /** @throws \Exception */
    public static function getNowUTCFormatted(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
    }

    /** @throws \Exception */
    public static function getDateFormattedWithUserTimezone(string $date, string $format = 'Y-m-d H:i:s', string $timezone = 'UTC'): string
    {
        return (new \DateTimeImmutable($date, new \DateTimeZone($timezone)))->format($format);
    }
}
