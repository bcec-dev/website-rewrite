<?php

class Sermon_Filter_Helper {

    // Function to handle the display of sermons
    public static function display_sermons($query, $filter, $taxonomy, $search_query) {
      echo '<div class="sfb-sermons-grid-container" data-filter="' . esc_attr($filter) . '" data-taxonomy="' . esc_attr($taxonomy) . '" data-searchquery="' . esc_attr($search_query) .'">';
      if ($query->have_posts()) {
          echo '<div class="sfb-sermons-grid">'; // Add a container for the grid
          while ($query->have_posts()) {
              $query->the_post();

              // Make the $filter and $taxonomy variable available in the template
              $current_filter = $filter; 
              $current_taxonomy = $taxonomy;

              include plugin_dir_path(__FILE__) . '../sermon-template.php';
          }
          echo '</div>'; // Close the container
          self::display_pagination($query->max_num_pages);
      } else {
          esc_html_e('No results found.', 'sermon-filter-plugin');
      }
      wp_reset_postdata();
    }

    public static function display_taxonomies($taxonomies, $taxonomy_name) {
      if (!empty($taxonomies)) {
          echo '<ul class="sfb-taxonomy-list">';
          foreach ($taxonomies as $taxonomy) {
              $term_count = $taxonomy->count;
              echo '<li class="sfb-child-taxonomy-link" data-taxonomy="' . esc_attr($taxonomy->slug) . '">';
              echo '<div class="sfb-term-name">' . $taxonomy->name . '</div>';
              echo '<div class="sfb-term-count-arrow">';
              echo '<div class="sfb-term-count">' . $term_count . '</div>';
              echo '<div class="sfb-term-arrow">&gt;</div>';
              echo '</div>';
              echo '</li>';
          }
          self::display_taxonomy_pagination($taxonomy_name);
      } else {
        esc_html_e('No results found.', 'sermon-filter-plugin');
      }
  }

  public static function generatePaginationButtons($pagination, $currentPage) {
    echo '<div class="sfb-pagination">';
    $length = count($pagination); 
    for ($i = 0; $i < $length; $i++) {
      $page_link = $pagination[$i];
      // get page number from the $page_link
      $regex = '/#page=(\d+)/';  // Regular expression to match '#page=' followed by digits
  
      $page_num = 1;
      if (preg_match($regex, $page_link, $matches)) {
        $page_num = $matches[1];
      } elseif (strpos($page_link, 'current') !== false) {
        $page_num = $currentPage;
      }
      $page_num = $page_num ? $page_num : 1; // Fallback to page 1 if no number found
      
      // for first item, if it is not a 'prev' indicator,
      // add a disable '<' text 
      if ($i == 0 && strpos($page_link, 'prev') == false) {
        echo '<span class="sfb-page-link disabled"><span>&lt;</span></span>';
      }

      if (strpos($page_link, 'dots') !== false ) {
        echo '<span>' . $page_link . '</span>';
      } else {
        echo '<span class="sfb-page-link" data-page="' . abs($page_num) . '">' . $page_link . '</span>';
      }

      // for last item, if it is not a 'next' indicator,
      // add a disable '>' text 
      if ($i == $length - 1 && strpos($page_link, 'next') == false) {
        echo '<span class="sfb-page-link disabled"><span>&gt;</span></span>';
      }
    }
    echo '</div>';
  }

  public static function display_pagination($max_num_pages) {
    $currentPage = intval($_POST['paged']);
    $pagination = paginate_links(array(
        'base' => '%_%',
        'format' => '#page=%#%',
        'current' => max(1, intval($_POST['paged'])),
        'total' => $max_num_pages,
        'type' => 'array',
        'prev_text' => '&lt;', // < symbol
        'next_text' => '&gt;', // > symbol
    ));
  
    if ($pagination) {
      self::generatePaginationButtons($pagination, $currentPage);
    }
  }

  public static function display_taxonomy_pagination($taxonomy_name) {
    $total_taxonomies = wp_count_terms($taxonomy_name, array('parent' => 0, 'hide_empty' => true));
    $shortcode_atts = get_option('sfb_shortcode_atts');
    $taxonomy_terms_per_page = $shortcode_atts['taxonomy_terms_per_page'];
    $total_pages = ceil($total_taxonomies / $taxonomy_terms_per_page);
    $currentPage = intval($_POST['paged']);
    if ($total_pages > 1) {
        $pagination = paginate_links(array(
            'base' => '%_%',
            'format' => '#page=%#%',
            'current' => max(1, intval($_POST['paged'])),
            'total' => $total_pages,
            'type' => 'array',
            'prev_text' => '&lt;', // < symbol
            'next_text' => '&gt;', // > symbol
        ));
  
        if ($pagination) {
          self::generatePaginationButtons($pagination, $currentPage);
        }
    }
  }
}
