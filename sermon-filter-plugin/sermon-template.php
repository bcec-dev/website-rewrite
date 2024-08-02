

<?php
/*
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <h3 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    </header>
    <div class="entry-content">
        <?php the_content(); ?>
    </div>
</article>
*/
?>

<?php
/*
<?php
// Get the post content
$content = get_the_content();
// $content = wp_kses_post($content);

// Parse blocks to handle content more reliably
$blocks = parse_blocks($content);

// Split the content blocks
// $parts = split_content_blocks($blocks);
$video_block = split_content_blocks($blocks);

// Get the speaker taxonomy value
$speakers = get_the_terms(get_the_ID(), 'speaker');
$speaker_names = $speakers && !is_wp_error($speakers) ? wp_list_pluck($speakers, 'name') : [];

?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content">
        <?php if (!empty($video_block)) : ?>
            <!-- Display the video block -->
            <?php echo $video_block; ?>
        <?php endif; ?>

        <!-- Display the title link and the remaining blocks -->
        <div class="entry-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </div>
        <?php if (!empty($speaker_names)) : ?>
            <div class="sermon-speaker">
                <?php 
                echo esc_html__('Speaker:', 'sermon-filter-plugin') . ' ' . esc_html(implode(', ', $speaker_names)); 
                ?>
            </div>
        <?php endif; ?>
    </div>
</article>
*/
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php
    // Retrieve the custom fields
    $video_url = get_field('video_url');
    $theme = get_field('theme');
    $verses = get_field('verses');
    $sermon_date = get_field('sermon-date');
    // Get the speaker taxonomy value
    $speakers = get_the_terms(get_the_ID(), 'speaker');
    $speaker_names = $speakers && !is_wp_error($speakers) ? wp_list_pluck($speakers, 'name') : [];
error_log('$video_url:' . print_r($video_url, true));
    // // Display the embedded video if video_url exists
    // if ($video_url) {
    //     echo '<div class="sermon-video">';
    //     echo wp_oembed_get($video_url);
    //     echo '</div>';
    // }
    ?>

    <div class="entry-content">
        <?php if (video_url) : ?>
            <!-- Display the embedded video if video_url exists 2-->
            <!-- <figure class="wp-block-embed-youtube wp-block-embed is-type-video is-provider-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio"> -->
            <!-- <div class="wp-block-embed__wrapper"> -->
            <div class="sfb-sermon-embed-video">
                <?php echo $video_url; ?>
            </div>
            <!-- </figure> -->
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
            <div class="sermon-date"><?php echo esc_html($sermon_date); ?></div>
        <?php endif; ?>
        <?php if (!empty($speaker_names)) : ?>
            <div class="sermon-speaker">
                <?php echo esc_html(implode(', ', $speaker_names)); ?>
            </div>
        <?php endif; ?>
    </div>
   
</article>
