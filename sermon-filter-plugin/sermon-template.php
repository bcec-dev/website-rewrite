<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    // Retrieve the custom fields
    $sermon_video_url = get_field('sermon_video_url');
    $theme = get_field('sermon_theme');
    $verses = get_field('sermon_verses');
    $sermon_date = get_field('sermon_date');
    // Get the speaker taxonomy value
    $speakers = get_the_terms(get_the_ID(), 'speaker');
    $speaker_names = $speakers && !is_wp_error($speakers) ? wp_list_pluck($speakers, 'name') : [];
    ?>

    <div class="entry-content">
        <?php if ($sermon_video_url) : ?>
            <!-- Display the embedded video if sermon_video_url exists -->
            <div class="sfb-sermon-embed-video">
                <?php echo $sermon_video_url; ?>
            </div>
        <?php endif; ?>
         <!-- Display the title link and the remaining blocks -->
         <div class="entry-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </div>
        <?php if ($theme) : ?>
            <div class="sermon-theme"><?php echo esc_html($theme); ?></div>
        <?php endif; ?>
        <?php if ($verses) : ?>
            <div class="sermon-verses"><?php echo esc_html($verses); ?></div>
        <?php endif; ?>
        <?php if ($sermon_date) : ?>
            <div class="sermon_date"><?php echo esc_html($sermon_date); ?></div>
        <?php endif; ?>
        <?php if (!empty($speaker_names)) : ?>
            <div class="sermon-speaker">
                <?php echo esc_html(implode(', ', $speaker_names)); ?>
            </div>
        <?php endif; ?>
    </div>
</article>