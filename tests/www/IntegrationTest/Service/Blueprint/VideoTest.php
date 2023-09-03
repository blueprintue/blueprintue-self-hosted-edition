<?php

/* @noinspection PhpMethodNamingConventionInspection */
/* @noinspection PhpTooManyParametersInspection */
/* phpcs:disable Generic.Files.LineLength */
/* phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps */

declare(strict_types=1);

namespace tests\www\IntegrationTest\Service\Blueprint;

use app\services\www\BlueprintService;
use PHPUnit\Framework\TestCase;

class VideoTest extends TestCase
{
    public function dataCasesNoVideo(): array
    {
        return [
            'url empty' => [
                'video_url'      => '',
                'video_out'      => null,
                'video_provider' => null,
            ],
            'url incorrect' => [
                'video_url'      => '869748674874',
                'video_out'      => null,
                'video_provider' => null,
            ],
        ];
    }

    public function dataCasesYoutube(): array
    {
        return [
            'https://www.youtube.com/watch?v=Z-VfaG9ZN_U' => [
                'video_url'      => 'https://www.youtube.com/watch?v=Z-VfaG9ZN_U',
                'video_out'      => '//www.youtube.com/embed/Z-VfaG9ZN_U',
                'video_provider' => 'youtube',
            ],
            'https://www.youtube.com/watch?v=rdwz7QiG0lk' => [
                'video_url'      => 'https://www.youtube.com/watch?v=rdwz7QiG0lk',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtu.be/rdwz7QiG0lk' => [
                'video_url'      => 'youtu.be/rdwz7QiG0lk',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtube.com/watch?v=rdwz7QiG0lk' => [
                'video_url'      => 'youtube.com/watch?v=rdwz7QiG0lk',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtube.com/watch?feature=related&v=rdwz7QiG0lk' => [
                'video_url'      => 'youtube.com/watch?feature=related&v=rdwz7QiG0lk',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtube.com/watch?v=rdwz7QiG0lk&feature=related' => [
                'video_url'      => 'youtube.com/watch?v=rdwz7QiG0lk&feature=related',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtube.com/watch/?v=rdwz7QiG0lk' => [
                'video_url'      => 'youtube.com/watch/?v=rdwz7QiG0lk',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtube.com/watch/?feature=related&v=rdwz7QiG0lk' => [
                'video_url'      => 'youtube.com/watch/?feature=related&v=rdwz7QiG0lk',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtube.com/watch/?v=rdwz7QiG0lk&feature=related' => [
                'video_url'      => 'youtube.com/watch/?v=rdwz7QiG0lk&feature=related',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtube.com/watch?v=rdwz7QiG0lk&feature=youtube_gdata_player' => [
                'video_url'      => 'youtube.com/watch?v=rdwz7QiG0lk&feature=youtube_gdata_player',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtube.com/watch?v=rdwz7QiG0lk&feature=channel' => [
                'video_url'      => 'youtube.com/watch?v=rdwz7QiG0lk&feature=channel',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtu.be/rdwz7QiG0lk?feature=youtube_gdata_player' => [
                'video_url'      => 'youtu.be/rdwz7QiG0lk?feature=youtube_gdata_player',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'youtu.be/rdwz7QiG0lk?list=PLToa5JuFMsXTNkrLJbRlB--76IAOjRM9b' => [
                'video_url'      => 'youtu.be/rdwz7QiG0lk?list=PLToa5JuFMsXTNkrLJbRlB--76IAOjRM9b',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube-nocookie.com/embed/rdwz7QiG0lk?rel=0' => [
                'video_url'      => 'www.youtube-nocookie.com/embed/rdwz7QiG0lk?rel=0',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/embed/rdwz7QiG0lk?rel=0' => [
                'video_url'      => 'www.youtube.com/embed/rdwz7QiG0lk?rel=0',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/embed/rdwz7QiG0lk' => [
                'video_url'      => 'www.youtube.com/embed/rdwz7QiG0lk',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/watch?v=rdwz7QiG0lk' => [
                'video_url'      => 'www.youtube.com/watch?v=rdwz7QiG0lk',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/watch?v=rdwz7QiG0lk&feature=feedrec_grec_index' => [
                'video_url'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=feedrec_grec_index',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/watch?v=rdwz7QiG0lk#t=0m10s' => [
                'video_url'      => 'www.youtube.com/watch?v=rdwz7QiG0lk#t=0m10s',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/watch?v=rdwz7QiG0lk&feature=youtube_gdata_player' => [
                'video_url'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=youtube_gdata_player',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/watch?v=rdwz7QiG0lk&feature=em-uploademail' => [
                'video_url'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=em-uploademail',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/watch?v=rdwz7QiG0lk&feature=youtu.be' => [
                'video_url'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=youtu.be',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/watch?v=rdwz7QiG0lk&feature=channel' => [
                'video_url'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&feature=channel',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/watch?v=rdwz7QiG0lk&list=PLGup6kBfcU7Le5laEaCLgTKtlDcxMqGxZ&index=106&shuffle=2655' => [
                'video_url'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&list=PLGup6kBfcU7Le5laEaCLgTKtlDcxMqGxZ&index=106&shuffle=2655',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/watch?v=rdwz7QiG0lk&playnext_from=TL&videos=osPknwzXEas&feature=sub' => [
                'video_url'      => 'www.youtube.com/watch?v=rdwz7QiG0lk&playnext_from=TL&videos=osPknwzXEas&feature=sub',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'www.youtube.com/watch?feature=player_embedded&v=rdwz7QiG0lk' => [
                'video_url'      => 'www.youtube.com/watch?feature=player_embedded&v=rdwz7QiG0lk',
                'video_out'      => '//www.youtube.com/embed/rdwz7QiG0lk',
                'video_provider' => 'youtube',
            ],
            'invalid -> www.y0utube.com/watch?feature=player_embedded&v=rdwz7QiG0lk' => [
                'video_url'      => 'www.y0utube.com/watch?feature=player_embedded&v=rdwz7QiG0lk',
                'video_out'      => null,
                'video_provider' => null,
            ],
        ];
    }

    public function dataCasesVimeo(): array
    {
        return [
            'https://vimeo.com/288789407' => [
                'video_url'      => 'https://vimeo.com/288789407',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/288789407' => [
                'video_url'      => 'vimeo.com/288789407',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/288789407?a=1' => [
                'video_url'      => 'vimeo.com/288789407?a=1',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/288789407#test' => [
                'video_url'      => 'vimeo.com/288789407#test',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/288789407&a' => [
                'video_url'      => 'vimeo.com/288789407&a',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/288789407/' => [
                'video_url'      => 'vimeo.com/288789407/',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'www.vimeo.com/288789407' => [
                'video_url'      => 'www.vimeo.com/288789407',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/channels/dioid/288789407' => [
                'video_url'      => 'vimeo.com/channels/dioid/288789407',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/channels/dioid/288789407?a=1' => [
                'video_url'      => 'vimeo.com/channels/dioid/288789407?a=1',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/channels/dioid/288789407#test' => [
                'video_url'      => 'vimeo.com/channels/dioid/288789407#test',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/channels/dioid/288789407&a' => [
                'video_url'      => 'vimeo.com/channels/dioid/288789407&a',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/channels/dioid/288789407/' => [
                'video_url'      => 'vimeo.com/channels/dioid/288789407/',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/groups/motion/videos/288789407' => [
                'video_url'      => 'vimeo.com/groups/motion/videos/288789407',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/groups/motion/videos/288789407?a=1' => [
                'video_url'      => 'vimeo.com/groups/motion/videos/288789407?a=1',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/groups/motion/videos/288789407#test' => [
                'video_url'      => 'vimeo.com/groups/motion/videos/288789407#test',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/groups/motion/videos/288789407&a' => [
                'video_url'      => 'vimeo.com/groups/motion/videos/288789407&a',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/groups/motion/videos/288789407/' => [
                'video_url'      => 'vimeo.com/groups/motion/videos/288789407/',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/showcase/456468465484684/video/288789407' => [
                'video_url'      => 'vimeo.com/showcase/456468465484684/video/288789407',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/showcase/456468465484684/video/288789407?a=1' => [
                'video_url'      => 'vimeo.com/showcase/456468465484684/video/288789407?a=1',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/showcase/456468465484684/video/288789407#test' => [
                'video_url'      => 'vimeo.com/showcase/456468465484684/video/288789407#test',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/showcase/456468465484684/video/288789407&a' => [
                'video_url'      => 'vimeo.com/showcase/456468465484684/video/288789407&a',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'vimeo.com/showcase/456468465484684/video/288789407/' => [
                'video_url'      => 'vimeo.com/showcase/456468465484684/video/288789407/',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'player.vimeo.com/video/288789407' => [
                'video_url'      => 'player.vimeo.com/video/288789407',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'player.vimeo.com/video/288789407?a=1' => [
                'video_url'      => 'player.vimeo.com/video/288789407?a=1',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'player.vimeo.com/video/288789407#test' => [
                'video_url'      => 'player.vimeo.com/video/288789407#test',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'player.vimeo.com/video/288789407&a' => [
                'video_url'      => 'player.vimeo.com/video/288789407&a',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'player.vimeo.com/video/288789407/' => [
                'video_url'      => 'player.vimeo.com/video/288789407/',
                'video_out'      => '//player.vimeo.com/video/288789407',
                'video_provider' => 'vimeo',
            ],
            'invalid -> https://vime0.com/288789407' => [
                'video_url'      => 'https://vime0.com/288789407',
                'video_out'      => null,
                'video_provider' => null,
            ],
        ];
    }

    public function dataCasesDailymotion(): array
    {
        return [
            'https://www.dailymotion.com/video/x3mfzb3?playlist=x5nmbq' => [
                'video_url'      => 'https://www.dailymotion.com/video/x3mfzb3?playlist=x5nmbq',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'https://www.dailymotion.com/video/x3mfzb3' => [
                'video_url'      => 'https://www.dailymotion.com/video/x3mfzb3',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'www.dailymotion.com/video/x3mfzb3' => [
                'video_url'      => 'www.dailymotion.com/video/x3mfzb3',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'www.dailymotion.com/video/x3mfzb3?a=1' => [
                'video_url'      => 'www.dailymotion.com/video/x3mfzb3?a=1',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'www.dailymotion.com/video/x3mfzb3#test' => [
                'video_url'      => 'www.dailymotion.com/video/x3mfzb3#test',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'www.dailymotion.com/video/x3mfzb3&a' => [
                'video_url'      => 'www.dailymotion.com/video/x3mfzb3&a',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'www.dailymotion.com/video/x3mfzb3/' => [
                'video_url'      => 'www.dailymotion.com/video/x3mfzb3/',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'https://api.dailymotion.com/video/x3mfzb3' => [
                'video_url'      => 'https://api.dailymotion.com/video/x3mfzb3',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'api.dailymotion.com/video/x3mfzb3' => [
                'video_url'      => 'api.dailymotion.com/video/x3mfzb3',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'api.dailymotion.com/video/x3mfzb3?a=1' => [
                'video_url'      => 'api.dailymotion.com/video/x3mfzb3?a=1',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'api.dailymotion.com/video/x3mfzb3#test' => [
                'video_url'      => 'api.dailymotion.com/video/x3mfzb3#test',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'api.dailymotion.com/video/x3mfzb3&a' => [
                'video_url'      => 'api.dailymotion.com/video/x3mfzb3&a',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'api.dailymotion.com/video/x3mfzb3/' => [
                'video_url'      => 'api.dailymotion.com/video/x3mfzb3/',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'https://dai.ly/x3mfzb3' => [
                'video_url'      => 'https://dai.ly/x3mfzb3',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'dai.ly/x3mfzb3' => [
                'video_url'      => 'dai.ly/x3mfzb3',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'dai.ly/x3mfzb3?a=1' => [
                'video_url'      => 'dai.ly/x3mfzb3?a=1',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'dai.ly/x3mfzb3#test' => [
                'video_url'      => 'dai.ly/x3mfzb3#test',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'dai.ly/x3mfzb3&a' => [
                'video_url'      => 'dai.ly/x3mfzb3&a',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'dai.ly/x3mfzb3/' => [
                'video_url'      => 'dai.ly/x3mfzb3/',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'https://www.dailymotion.com/embed/video/x3mfzb3' => [
                'video_url'      => 'https://www.dailymotion.com/embed/video/x3mfzb3',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'www.dailymotion.com/embed/video/x3mfzb3' => [
                'video_url'      => 'www.dailymotion.com/embed/video/x3mfzb3',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'www.dailymotion.com/embed/video/x3mfzb3?a=1' => [
                'video_url'      => 'www.dailymotion.com/embed/video/x3mfzb3?a=1',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'www.dailymotion.com/embed/video/x3mfzb3#test' => [
                'video_url'      => 'www.dailymotion.com/embed/video/x3mfzb3#test',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'www.dailymotion.com/embed/video/x3mfzb3&a' => [
                'video_url'      => 'www.dailymotion.com/embed/video/x3mfzb3&a',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'www.dailymotion.com/embed/video/x3mfzb3/' => [
                'video_url'      => 'www.dailymotion.com/embed/video/x3mfzb3/',
                'video_out'      => '//www.dailymotion.com/embed/video/x3mfzb3',
                'video_provider' => 'dailymotion',
            ],
            'invalid -> https://www.dailym0tion.com/video/x3mfzb3?playlist=x5nmbq' => [
                'video_url'      => 'https://www.dailym0tion.com/video/x3mfzb3?playlist=x5nmbq',
                'video_out'      => null,
                'video_provider' => null,
            ],
        ];
    }

    public function dataCasesPeertube(): array
    {
        return [
            'https://vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954' => [
                'video_url'      => 'https://vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954' => [
                'video_url'      => 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954?a=1' => [
                'video_url'      => 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954?a=1',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954#test' => [
                'video_url'      => 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954#test',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954&a' => [
                'video_url'      => 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954&a',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954/' => [
                'video_url'      => 'vloggers.social/videos/watch/5636c3ff-7009-47da-af53-5f0857a26954/',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954' => [
                'video_url'      => 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954?a=1' => [
                'video_url'      => 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954?a=1',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954#test' => [
                'video_url'      => 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954#test',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954&a' => [
                'video_url'      => 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954&a',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954/' => [
                'video_url'      => 'vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954/',
                'video_out'      => '//vloggers.social/videos/embed/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_provider' => 'peertube',
            ],
            'invalid -> https://vloggers.social/videos/watch/5636c3ff-709-47da-af53-5f0857a26954' => [
                'video_url'      => 'https://vloggers.social/videos/watch/5636c3ff-709-47da-af53-5f0857a26954',
                'video_out'      => null,
                'video_provider' => null,
            ],
            'invalid -> https://vloggers.social/vide0s/watch/5636c3ff-7009-47da-af53-5f0857a26954' => [
                'video_url'      => 'https://vloggers.social/vide0s/watch/5636c3ff-7009-47da-af53-5f0857a26954',
                'video_out'      => null,
                'video_provider' => null,
            ],
        ];
    }

    public function dataCasesBilibili(): array
    {
        return [
            'https://www.bilibili.com/video/av58844374' => [
                'video_url'      => 'https://www.bilibili.com/video/av58844374',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'www.bilibili.com/video/av58844374' => [
                'video_url'      => 'www.bilibili.com/video/av58844374',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'bilibili.com/video/av58844374' => [
                'video_url'      => 'bilibili.com/video/av58844374',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'bilibili.com/video/av58844374?a=1' => [
                'video_url'      => 'bilibili.com/video/av58844374?a=1',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'bilibili.com/video/av58844374#test' => [
                'video_url'      => 'bilibili.com/video/av58844374#test',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'bilibili.com/video/av58844374&a' => [
                'video_url'      => 'bilibili.com/video/av58844374&a',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'bilibili.com/video/av58844374/' => [
                'video_url'      => 'bilibili.com/video/av58844374/',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'player.bilibili.com/player.html?aid=58844374' => [
                'video_url'      => 'player.bilibili.com/player.html?aid=58844374',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'player.bilibili.com/player.html?aid=58844374?a=1' => [
                'video_url'      => 'player.bilibili.com/player.html?aid=58844374?a=1',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'player.bilibili.com/player.html?aid=58844374#test' => [
                'video_url'      => 'player.bilibili.com/player.html?aid=58844374#test',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'player.bilibili.com/player.html?aid=58844374&a' => [
                'video_url'      => 'player.bilibili.com/player.html?aid=58844374&a',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'player.bilibili.com/player.html?aid=58844374/' => [
                'video_url'      => 'player.bilibili.com/player.html?aid=58844374/',
                'video_out'      => '//player.bilibili.com/player.html?aid=58844374',
                'video_provider' => 'bilibili',
            ],
            'invalid -> https://www.b1libili.com/video/av58844374' => [
                'video_url'      => 'https://www.b1libili.com/video/av58844374',
                'video_out'      => null,
                'video_provider' => null,
            ],
        ];
    }

    public function dataCasesNiconico(): array
    {
        return [
            'https://www.nicovideo.jp/watch/sm34330764' => [
                'video_url'      => 'https://www.nicovideo.jp/watch/sm34330764',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'www.nicovideo.jp/watch/sm34330764' => [
                'video_url'      => 'www.nicovideo.jp/watch/sm34330764',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'nicovideo.jp/watch/sm34330764' => [
                'video_url'      => 'nicovideo.jp/watch/sm34330764',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'nicovideo.jp/watch/sm34330764?a=1' => [
                'video_url'      => 'nicovideo.jp/watch/sm34330764?a=1',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'nicovideo.jp/watch/sm34330764#test' => [
                'video_url'      => 'nicovideo.jp/watch/sm34330764#test',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'nicovideo.jp/watch/sm34330764&a' => [
                'video_url'      => 'nicovideo.jp/watch/sm34330764&a',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'nicovideo.jp/watch/sm34330764/' => [
                'video_url'      => 'nicovideo.jp/watch/sm34330764/',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'embed.nicovideo.jp/watch/sm34330764' => [
                'video_url'      => 'embed.nicovideo.jp/watch/sm34330764',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'embed.nicovideo.jp/watch/sm34330764?a=1' => [
                'video_url'      => 'embed.nicovideo.jp/watch/sm34330764?a=1',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'embed.nicovideo.jp/watch/sm34330764#test' => [
                'video_url'      => 'embed.nicovideo.jp/watch/sm34330764#test',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'embed.nicovideo.jp/watch/sm34330764&a' => [
                'video_url'      => 'embed.nicovideo.jp/watch/sm34330764&a',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'embed.nicovideo.jp/watch/sm34330764/' => [
                'video_url'      => 'embed.nicovideo.jp/watch/sm34330764/',
                'video_out'      => '//embed.nicovideo.jp/watch/sm34330764',
                'video_provider' => 'niconico',
            ],
            'invalid -> https://www.nicovide0.jp/watch/sm34330764' => [
                'video_url'      => 'https://www.nicovide0.jp/watch/sm34330764',
                'video_out'      => null,
                'video_provider' => null,
            ],
        ];
    }

    /**
     * @dataProvider dataCasesNoVideo
     * @dataProvider dataCasesYoutube
     * @dataProvider dataCasesVimeo
     * @dataProvider dataCasesDailymotion
     * @dataProvider dataCasesPeertube
     * @dataProvider dataCasesBilibili
     * @dataProvider dataCasesNiconico
     *
     * @param string|null $videoURL
     * @param string|null $videoOUT
     * @param string|null $provider
     */
    public function testVideo(?string $videoURL, ?string $videoOUT, ?string $provider): void
    {
        [$videoFound, $providerFound] = BlueprintService::findVideoProvider($videoURL);
        static::assertSame($videoOUT, $videoFound);
        static::assertSame($provider, $providerFound);
    }
}
