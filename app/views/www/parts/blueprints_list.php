<?php

/* @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Rancoud\Security\Security;

/* @var $data array */
?>
<div class="block__element">
    <?php if ($data['blueprints']) { ?>
    <ul class="list">
        <li class="list__row list__row--header">
            <div class="list__col list__col--header list__col--first">Image</div>
            <div class="list__col list__col--header">Type</div>
            <div class="list__col list__col--header">UE Version</div>
            <div class="list__col list__col--header">Title</div>
            <div class="list__col list__col--header">Author</div>
            <div class="list__col list__col--header">Date</div>
        </li>
        <?php foreach ($data['blueprints'] as $blueprint) { ?>
        <li class="list__row list__row--data">
            <div class="list__col list__col--first" data-name="Image">
                <a class="list__link-on-placeholder" href="<?php echo Security::escAttr($blueprint['url']); ?>">
                    <?php if ($blueprint['thumbnail_url'] !== null) { ?>
                    <img alt="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder" src="<?php echo Security::escAttr($blueprint['thumbnail_url']); ?>" />
                    <?php } else { ?>
                        <svg aria-label="Blueprint thumbnail" class="list__thumbnail list__thumbnail--placeholder">
                            <use href="/sprite/sprite.svg#blueprint-placeholder"></use>
                        </svg>
                    <?php } ?>
                </a>
            </div>
            <div class="list__col" data-name="Type"><?php echo ($blueprint['type'] === 'behavior_tree') ? 'behavior<br/>tree' : Security::escHTML($blueprint['type']); ?></div>
            <div class="list__col" data-name="UE Version"><?php echo Security::escHTML($blueprint['ue_version']); ?></div>
            <div class="list__col" data-name="Title"><a class="list__link" href="<?php echo Security::escAttr($blueprint['url']); ?>"><?php echo Security::escHTML($blueprint['title']); ?></a></div>
            <div class="list__col" data-name="Author"><a class="list__link" href="<?php echo Security::escAttr($blueprint['author']['profile_url']); ?>"><?php echo Security::escHTML($blueprint['author']['username']); ?></a></div>
            <div class="list__col" data-name="Date"><?php echo Security::escHTML($blueprint['since']); ?></div>
        </li>
        <?php } ?>
    </ul>
    <?php } else { ?>
    <p>No blueprints for the moment</p>
    <?php } ?>
</div>
