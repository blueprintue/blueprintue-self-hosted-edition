<?php

declare(strict_types=1);

namespace tests\www\IntegrationTest\Service\Blueprint;

use app\services\www\BlueprintService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @internal */
class VideoTest extends TestCase
{
    public static function provideVideoDataCases(): iterable
    {
        yield 'url empty' => [
            'videoURL'      => '',
            'videoOut'      => null,
            'videoProvider' => null,
        ];

        yield 'url incorrect' => [
            'videoURL'      => '869748674874',
            'videoOut'      => null,
            'videoProvider' => null,
        ];
    }

    public static function provideYoutubeDataCases(): iterable
    {
        yield 'https://www.youtube.com/watch?v=Z-VfaG9ZN_U' => [
            'videoURL'      => 'https://www.youtube.com/watch?v=Z-VfaG9ZN_U',
            'videoOut'      => '//www.youtube.com/embed/Z-VfaG9ZN_U',
            'videoProvider' => 'youtube',
        ];

        yield 'https://www.youtube.com/watch?v=rdwz7QiG0lk' => [
            'videoURL'      => 'https://www.youtube.com/watch?v=rdwz7QiG0lk',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtu.be/rdwz7QiG0lk' => [
            'videoURL'      => 'youtu.be/rdwz7QiG0lk',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtube.com/watch?v=rdwz7QiG0lk' => [
            'videoURL'      => 'youtube.com/watch?v=rdwz7QiG0lk',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtube.com/watch?feature=related&v=rdwz7QiG0lk' => [
            'videoURL'      => 'youtube.com/watch?feature=related&v=rdwz7QiG0lk',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtube.com/watch?v=rdwz7QiG0lk&feature=related' => [
            'videoURL'      => 'youtube.com/watch?v=rdwz7QiG0lk&feature=related',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtube.com/watch/?v=rdwz7QiG0lk' => [
            'videoURL'      => 'youtube.com/watch/?v=rdwz7QiG0lk',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtube.com/watch/?feature=related&v=rdwz7QiG0lk' => [
            'videoURL'      => 'youtube.com/watch/?feature=related&v=rdwz7QiG0lk',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtube.com/watch/?v=rdwz7QiG0lk&feature=related' => [
            'videoURL'      => 'youtube.com/watch/?v=rdwz7QiG0lk&feature=related',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtube.com/watch?v=rdwz7QiG0lk&feature=youtube_gdata_player' => [
            'videoURL'      => 'youtube.com/watch?v=rdwz7QiG0lk&feature=youtube_gdata_player',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtube.com/watch?v=rdwz7QiG0lk&feature=channel' => [
            'videoURL'      => 'youtube.com/watch?v=rdwz7QiG0lk&feature=channel',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtu.be/rdwz7QiG0lk?feature=youtube_gdata_player' => [
            'videoURL'      => 'youtu.be/rdwz7QiG0lk?feature=youtube_gdata_player',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'youtu.be/rdwz7QiG0lk?list=PLToa5JuFMsXTNkrLJbRlB--76IAOjRM9b' => [
            'videoURL'      => 'youtu.be/rdwz7QiG0lk?list=PLToa5JuFMsXTNkrLJbRlB--76IAOjRM9b',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube-nocookie.com/embed/rdwz7QiG0lk?rel=0' => [
            'videoURL'      => 'www.youtube-nocookie.com/embed/rdwz7QiG0lk?rel=0',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/embed/rdwz7QiG0lk?rel=0' => [
            'videoURL'      => 'www.youtube.com/embed/rdwz7QiG0lk?rel=0',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/embed/rdwz7QiG0lk' => [
            'videoURL'      => 'www.youtube.com/embed/rdwz7QiG0lk',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/watch?v=rdwz7QiG0lk' => [
            'videoURL'      => 'www.youtube.com/watch?v=rdwz7QiG0lk',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=feedrec_grec_index' => [
            'videoURL'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=feedrec_grec_index',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/watch?v=rdwz7QiG0lk#t=0m10s' => [
            'videoURL'      => 'www.youtube.com/watch?v=rdwz7QiG0lk#t=0m10s',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=youtube_gdata_player' => [
            'videoURL'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=youtube_gdata_player',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=em-uploademail' => [
            'videoURL'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=em-uploademail',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=youtu.be' => [
            'videoURL'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=youtu.be',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=channel' => [
            'videoURL'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=channel',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/watch?v=rdwz7QiG0lk&list=PLGup6kBfcU7Le5laEaCLgTKtlDcxMqGxZ&index=106&shuffle=2655' => [
            'videoURL'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&list=PLGup6kBfcU7Le5laEaCLgTKtlDcxMqGxZ&index=106&shuffle=2655',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/watch?v=rdwz7QiG0lk&playnext_from=TL&videos=osPknwzXEas&feature=sub' => [
            'videoURL'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&playnext_from=TL&videos=osPknwzXEas&feature=sub',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'www.youtube.com/watch?feature=player_embedded&v=rdwz7QiG0lk' => [
            'videoURL'      => 'www.youtube.com/watch?feature=player_embedded&v=rdwz7QiG0lk',
            'videoOut'      => '//www.youtube.com/embed/rdwz7QiG0lk',
            'videoProvider' => 'youtube',
        ];

        yield 'invalid -> www.y0utube.com/watch?feature=player_embedded&v=rdwz7QiG0lk' => [
            'videoURL'      => 'www.y0utube.com/watch?feature=player_embedded&v=rdwz7QiG0lk',
            'videoOut'      => null,
            'videoProvider' => null,
        ];
    }

    public static function provideVimeoDataCases(): iterable
    {
        yield 'https://vimeo.com/288789407' => [
            'videoURL'      => 'https://vimeo.com/288789407',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/288789407' => [
            'videoURL'      => 'vimeo.com/288789407',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/288789407?a=1' => [
            'videoURL'      => 'vimeo.com/288789407?a=1',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/288789407#test' => [
            'videoURL'      => 'vimeo.com/288789407#test',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/288789407&a' => [
            'videoURL'      => 'vimeo.com/288789407&a',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/288789407/' => [
            'videoURL'      => 'vimeo.com/288789407/',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'www.vimeo.com/288789407' => [
            'videoURL'      => 'www.vimeo.com/288789407',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/channels/dioid/288789407' => [
            'videoURL'      => 'vimeo.com/channels/dioid/288789407',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/channels/dioid/288789407?a=1' => [
            'videoURL'      => 'vimeo.com/channels/dioid/288789407?a=1',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/channels/dioid/288789407#test' => [
            'videoURL'      => 'vimeo.com/channels/dioid/288789407#test',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/channels/dioid/288789407&a' => [
            'videoURL'      => 'vimeo.com/channels/dioid/288789407&a',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/channels/dioid/288789407/' => [
            'videoURL'      => 'vimeo.com/channels/dioid/288789407/',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/groups/motion/videos/288789407' => [
            'videoURL'      => 'vimeo.com/groups/motion/videos/288789407',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/groups/motion/videos/288789407?a=1' => [
            'videoURL'      => 'vimeo.com/groups/motion/videos/288789407?a=1',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/groups/motion/videos/288789407#test' => [
            'videoURL'      => 'vimeo.com/groups/motion/videos/288789407#test',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/groups/motion/videos/288789407&a' => [
            'videoURL'      => 'vimeo.com/groups/motion/videos/288789407&a',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/groups/motion/videos/288789407/' => [
            'videoURL'      => 'vimeo.com/groups/motion/videos/288789407/',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/showcase/456468465484684/video/288789407' => [
            'videoURL'      => 'vimeo.com/showcase/456468465484684/video/288789407',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/showcase/456468465484684/video/288789407?a=1' => [
            'videoURL'      => 'vimeo.com/showcase/456468465484684/video/288789407?a=1',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/showcase/456468465484684/video/288789407#test' => [
            'videoURL'      => 'vimeo.com/showcase/456468465484684/video/288789407#test',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/showcase/456468465484684/video/288789407&a' => [
            'videoURL'      => 'vimeo.com/showcase/456468465484684/video/288789407&a',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'vimeo.com/showcase/456468465484684/video/288789407/' => [
            'videoURL'      => 'vimeo.com/showcase/456468465484684/video/288789407/',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'player.vimeo.com/video/288789407' => [
            'videoURL'      => 'player.vimeo.com/video/288789407',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'player.vimeo.com/video/288789407?a=1' => [
            'videoURL'      => 'player.vimeo.com/video/288789407?a=1',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'player.vimeo.com/video/288789407#test' => [
            'videoURL'      => 'player.vimeo.com/video/288789407#test',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'player.vimeo.com/video/288789407&a' => [
            'videoURL'      => 'player.vimeo.com/video/288789407&a',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'player.vimeo.com/video/288789407/' => [
            'videoURL'      => 'player.vimeo.com/video/288789407/',
            'videoOut'      => '//player.vimeo.com/video/288789407',
            'videoProvider' => 'vimeo',
        ];

        yield 'invalid -> https://vime0.com/288789407' => [
            'videoURL'      => 'https://vime0.com/288789407',
            'videoOut'      => null,
            'videoProvider' => null,
        ];
    }

    public static function provideDailymotionDataCases(): iterable
    {
        yield 'https://www.dailymotion.com/video/x3mfzb3?playlist=x5nmbq' => [
            'videoURL'      => 'https://www.dailymotion.com/video/x3mfzb3?playlist=x5nmbq',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'https://www.dailymotion.com/video/x3mfzb3' => [
            'videoURL'      => 'https://www.dailymotion.com/video/x3mfzb3',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'www.dailymotion.com/video/x3mfzb3' => [
            'videoURL'      => 'www.dailymotion.com/video/x3mfzb3',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'www.dailymotion.com/video/x3mfzb3?a=1' => [
            'videoURL'      => 'www.dailymotion.com/video/x3mfzb3?a=1',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'www.dailymotion.com/video/x3mfzb3#test' => [
            'videoURL'      => 'www.dailymotion.com/video/x3mfzb3#test',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'www.dailymotion.com/video/x3mfzb3&a' => [
            'videoURL'      => 'www.dailymotion.com/video/x3mfzb3&a',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'www.dailymotion.com/video/x3mfzb3/' => [
            'videoURL'      => 'www.dailymotion.com/video/x3mfzb3/',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'https://api.dailymotion.com/video/x3mfzb3' => [
            'videoURL'      => 'https://api.dailymotion.com/video/x3mfzb3',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'api.dailymotion.com/video/x3mfzb3' => [
            'videoURL'      => 'api.dailymotion.com/video/x3mfzb3',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'api.dailymotion.com/video/x3mfzb3?a=1' => [
            'videoURL'      => 'api.dailymotion.com/video/x3mfzb3?a=1',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'api.dailymotion.com/video/x3mfzb3#test' => [
            'videoURL'      => 'api.dailymotion.com/video/x3mfzb3#test',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'api.dailymotion.com/video/x3mfzb3&a' => [
            'videoURL'      => 'api.dailymotion.com/video/x3mfzb3&a',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'api.dailymotion.com/video/x3mfzb3/' => [
            'videoURL'      => 'api.dailymotion.com/video/x3mfzb3/',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'https://dai.ly/x3mfzb3' => [
            'videoURL'      => 'https://dai.ly/x3mfzb3',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'dai.ly/x3mfzb3' => [
            'videoURL'      => 'dai.ly/x3mfzb3',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'dai.ly/x3mfzb3?a=1' => [
            'videoURL'      => 'dai.ly/x3mfzb3?a=1',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'dai.ly/x3mfzb3#test' => [
            'videoURL'      => 'dai.ly/x3mfzb3#test',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'dai.ly/x3mfzb3&a' => [
            'videoURL'      => 'dai.ly/x3mfzb3&a',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'dai.ly/x3mfzb3/' => [
            'videoURL'      => 'dai.ly/x3mfzb3/',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'https://www.dailymotion.com/embed/video/x3mfzb3' => [
            'videoURL'      => 'https://www.dailymotion.com/embed/video/x3mfzb3',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'www.dailymotion.com/embed/video/x3mfzb3' => [
            'videoURL'      => 'www.dailymotion.com/embed/video/x3mfzb3',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'www.dailymotion.com/embed/video/x3mfzb3?a=1' => [
            'videoURL'      => 'www.dailymotion.com/embed/video/x3mfzb3?a=1',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'www.dailymotion.com/embed/video/x3mfzb3#test' => [
            'videoURL'      => 'www.dailymotion.com/embed/video/x3mfzb3#test',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'www.dailymotion.com/embed/video/x3mfzb3&a' => [
            'videoURL'      => 'www.dailymotion.com/embed/video/x3mfzb3&a',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'www.dailymotion.com/embed/video/x3mfzb3/' => [
            'videoURL'      => 'www.dailymotion.com/embed/video/x3mfzb3/',
            'videoOut'      => '//www.dailymotion.com/embed/video/x3mfzb3',
            'videoProvider' => 'dailymotion',
        ];

        yield 'invalid -> https://www.dailym0tion.com/video/x3mfzb3?playlist=x5nmbq' => [
            'videoURL'      => 'https://www.dailym0tion.com/video/x3mfzb3?playlist=x5nmbq',
            'videoOut'      => null,
            'videoProvider' => null,
        ];
    }

    public static function providePeertubeDataCases(): iterable
    {
        yield 'https://vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954' => [
            'videoURL'      => 'https://vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954' => [
            'videoURL'      => 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954?a=1' => [
            'videoURL'      => 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954?a=1',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954#test' => [
            'videoURL'      => 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954#test',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954&a' => [
            'videoURL'      => 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954&a',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954/' => [
            'videoURL'      => 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954/',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954' => [
            'videoURL'      => 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954?a=1' => [
            'videoURL'      => 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954?a=1',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954#test' => [
            'videoURL'      => 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954#test',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954&a' => [
            'videoURL'      => 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954&a',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954/' => [
            'videoURL'      => 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954/',
            'videoOut'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoProvider' => 'peertube',
        ];

        yield 'invalid -> https://vloggers.social/videos/watch/5636c3ff-709-47da-af53-5f0857a26954' => [
            'videoURL'      => 'https://vloggers.social/videos/watch/5636c3ff-709-47da-af53-5f0857a26954',
            'videoOut'      => null,
            'videoProvider' => null,
        ];

        yield 'invalid -> https://vloggers.social/vide0s/watch/5636c3ff-7009-47da-af53-5f0857a26954' => [
            'videoURL'      => 'https://vloggers.social/vide0s/watch/5636c3ff-7009-47da-af53-5f0857a26954',
            'videoOut'      => null,
            'videoProvider' => null,
        ];
    }

    public static function provideBilibiliDataCases(): iterable
    {
        yield 'https://www.bilibili.com/video/av58844374' => [
            'videoURL'      => 'https://www.bilibili.com/video/av58844374',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'www.bilibili.com/video/av58844374' => [
            'videoURL'      => 'www.bilibili.com/video/av58844374',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'bilibili.com/video/av58844374' => [
            'videoURL'      => 'bilibili.com/video/av58844374',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'bilibili.com/video/av58844374?a=1' => [
            'videoURL'      => 'bilibili.com/video/av58844374?a=1',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'bilibili.com/video/av58844374#test' => [
            'videoURL'      => 'bilibili.com/video/av58844374#test',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'bilibili.com/video/av58844374&a' => [
            'videoURL'      => 'bilibili.com/video/av58844374&a',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'bilibili.com/video/av58844374/' => [
            'videoURL'      => 'bilibili.com/video/av58844374/',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'player.bilibili.com/player.html?aid=58844374' => [
            'videoURL'      => 'player.bilibili.com/player.html?aid=58844374',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'player.bilibili.com/player.html?aid=58844374?a=1' => [
            'videoURL'      => 'player.bilibili.com/player.html?aid=58844374?a=1',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'player.bilibili.com/player.html?aid=58844374#test' => [
            'videoURL'      => 'player.bilibili.com/player.html?aid=58844374#test',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'player.bilibili.com/player.html?aid=58844374&a' => [
            'videoURL'      => 'player.bilibili.com/player.html?aid=58844374&a',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'player.bilibili.com/player.html?aid=58844374/' => [
            'videoURL'      => 'player.bilibili.com/player.html?aid=58844374/',
            'videoOut'      => '//player.bilibili.com/player.html?aid=58844374',
            'videoProvider' => 'bilibili',
        ];

        yield 'invalid -> https://www.b1libili.com/video/av58844374' => [
            'videoURL'      => 'https://www.b1libili.com/video/av58844374',
            'videoOut'      => null,
            'videoProvider' => null,
        ];
    }

    public static function provideNiconicoDataCases(): iterable
    {
        yield 'https://www.nicovideo.jp/watch/sm34330764' => [
            'videoURL'      => 'https://www.nicovideo.jp/watch/sm34330764',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'www.nicovideo.jp/watch/sm34330764' => [
            'videoURL'      => 'www.nicovideo.jp/watch/sm34330764',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'nicovideo.jp/watch/sm34330764' => [
            'videoURL'      => 'nicovideo.jp/watch/sm34330764',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'nicovideo.jp/watch/sm34330764?a=1' => [
            'videoURL'      => 'nicovideo.jp/watch/sm34330764?a=1',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'nicovideo.jp/watch/sm34330764#test' => [
            'videoURL'      => 'nicovideo.jp/watch/sm34330764#test',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'nicovideo.jp/watch/sm34330764&a' => [
            'videoURL'      => 'nicovideo.jp/watch/sm34330764&a',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'nicovideo.jp/watch/sm34330764/' => [
            'videoURL'      => 'nicovideo.jp/watch/sm34330764/',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'embed.nicovideo.jp/watch/sm34330764' => [
            'videoURL'      => 'embed.nicovideo.jp/watch/sm34330764',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'embed.nicovideo.jp/watch/sm34330764?a=1' => [
            'videoURL'      => 'embed.nicovideo.jp/watch/sm34330764?a=1',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'embed.nicovideo.jp/watch/sm34330764#test' => [
            'videoURL'      => 'embed.nicovideo.jp/watch/sm34330764#test',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'embed.nicovideo.jp/watch/sm34330764&a' => [
            'videoURL'      => 'embed.nicovideo.jp/watch/sm34330764&a',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'embed.nicovideo.jp/watch/sm34330764/' => [
            'videoURL'      => 'embed.nicovideo.jp/watch/sm34330764/',
            'videoOut'      => '//embed.nicovideo.jp/watch/sm34330764',
            'videoProvider' => 'niconico',
        ];

        yield 'invalid -> https://www.nicovide0.jp/watch/sm34330764' => [
            'videoURL'      => 'https://www.nicovide0.jp/watch/sm34330764',
            'videoOut'      => null,
            'videoProvider' => null,
        ];
    }

    #[DataProvider('provideVideoDataCases')]
    #[DataProvider('provideYoutubeDataCases')]
    #[DataProvider('provideVimeoDataCases')]
    #[DataProvider('provideDailymotionDataCases')]
    #[DataProvider('providePeertubeDataCases')]
    #[DataProvider('provideBilibiliDataCases')]
    #[DataProvider('provideNiconicoDataCases')]
    public function testVideo(?string $videoURL, ?string $videoOut, ?string $videoProvider): void
    {
        [$videoFound, $providerFound] = BlueprintService::findVideoProvider($videoURL);
        static::assertSame($videoOut, $videoFound);
        static::assertSame($videoProvider, $providerFound);
    }
}
