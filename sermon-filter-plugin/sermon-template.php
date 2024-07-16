<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    </header>
    <div class="entry-content">
        <?php the_content(); ?>
    </div>
    <footer class="entry-footer">
        <?php
        // Get the stored shortcode attributes
        $shortcode_atts = get_option('sfb_shortcode_atts', array());
        // Ensure the taxonomy and display_names are trimmed
        $taxonomies = !empty($shortcode_atts['taxonomy']) ? array_map('trim', explode(',', $shortcode_atts['taxonomy'])) : array();
        $display_names = !empty($shortcode_atts['display_names']) ? array_map('trim', explode(',', $shortcode_atts['display_names'])) : array();

        error_log('$taxonomies: ' . print_r($taxonomies, true));
        foreach ($taxonomies as $index => $taxonomy) {
            $terms = get_the_terms(get_the_ID(), $taxonomy);
            error_log('$taxonomy: ' . print_r($taxonomy, true));
            error_log('$terms: ' . print_r($terms, true));
            if ($terms && !is_wp_error($terms)) {
                $term_names = array();
                foreach ($terms as $term) {
                    $term_names[] = $term->name;
                }
                $display_name = isset($display_names[$index]) ? $display_names[$index] : ucwords(str_replace('_', ' ', $taxonomy));
                echo '<div><strong>' . esc_html($display_name) . ':</strong> ' . esc_html(implode(', ', $term_names)) . '</div>';
            }
        }
        ?>
    </footer>
    <?php /* <footer class="entry-footer">
        <div class="entry-meta">
            <?php
            // Output speaker taxonomy terms
            $speaker_terms = get_the_terms(get_the_ID(), 'speaker');
            if ($speaker_terms && !is_wp_error($speaker_terms)) {
                $speaker_names = array_map(function ($term) {
                    return $term->name;
                }, $speaker_terms);
                echo '<span class="speaker">' . esc_html__('Speaker: ', 'sermon-filter-plugin') . implode(', ', $speaker_names) . '&nbsp;</span>';
            }

            // Output scripture taxonomy terms
            $scripture_terms = get_the_terms(get_the_ID(), 'scripture');
            if ($scripture_terms && !is_wp_error($scripture_terms)) {
                $scripture_names = array_map(function ($term) {
                    return $term->name;
                }, $scripture_terms);
                echo '<span class="scripture">' . esc_html__('Scripture: ', 'sermon-filter-plugin') . implode(', ', $scripture_names) . '</span>';
            }
            ?>
        </div>
    </footer>
    */ ?>
</article>
