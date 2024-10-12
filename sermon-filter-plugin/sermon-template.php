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
    
    $show_theme_row = true;
    $show_date_scripture_in_one_row = true;
    $show_location = true;
    // Set the $title variable based on the filter
    $title = get_the_title(); // Default to the sermon title if no filter is set
    if ($theme) {
        $title = $theme;
        $show_theme_row = false;
    }

    if (isset($current_taxonomy) && !empty($current_taxonomy)) {
        $term = get_term_by('slug', $current_taxonomy, $current_filter);
        if ($term && !is_wp_error($term)) {
            $title = $term->name . ' - ' . $theme;
            $show_theme_row = false;
            // $show_date_scripture_in_one_row = true;
            $show_location = true;
        }
    }
    // Get the categories for the current post
    $categories = get_the_category();

    $contains_newton = false; // Initialize a flag

    // Loop through each category
    foreach ($categories as $category) {
        // Check if the slug contains 'newton'
        if (strpos($category->slug, 'newton') !== false) {
            $contains_newton = true; // Set flag to true if found
            break; // Exit the loop early
        }
    }
    $location = __('Chinatown', 'sermon-filter-plugin');
    if ($contains_newton) {
        $location = __('Newton', 'sermon-filter-plugin');
    }
    ?>

    <div class="sermon-content">
        <?php if ($sermon_video_url) : ?>
            <!-- Display the embedded video if sermon_video_url exists -->
            <div class="sfb-sermon-embed-video">
                <?php echo $sermon_video_url; ?>
            </div>
        <?php endif; ?>
         <!-- Display the title link and the remaining blocks -->
         <div class="sermon-title">
            <a href="<?php the_permalink(); ?>"><?php echo esc_html($title); ?></a>
        </div>
        <?php if ($theme && $show_theme_row) : ?>
            <div class="sermon-theme"><?php echo esc_html($theme); ?></div>
        <?php endif; ?>
        <?php if ($verses && !$show_date_scripture_in_one_row) : ?>
            <div class="sermon-verses"><?php echo esc_html($verses); ?></div>
        <?php endif; ?>
        <?php if ($sermon_date && !$show_date_scripture_in_one_row) : ?>
            <div class="sermon_date"><?php echo esc_html($sermon_date); ?></div>
        <?php endif; ?>
        <?php if ($show_date_scripture_in_one_row && ($sermon_date || $verses)) : ?>
            <div class="sermon_date_verses"><?php echo esc_html($sermon_date . ' / ' . $verses); ?></div>
        <?php endif; ?>
        <?php if (!empty($speaker_names)) : ?>
            <div class="sermon-speaker">
                <?php echo esc_html(implode(', ', $speaker_names)); ?>
            </div>
        <?php endif; ?>
        <?php if ($show_location) : ?>
            <div class="sermon_location"><?php echo esc_html($location); ?></div>
        <?php endif; ?>
    </div>
</article>
