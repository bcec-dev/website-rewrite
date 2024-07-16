jQuery(document).ready(function ($) {
  // Variable to store the current page of the speaker list
  var currentPage = 1;
  var currentFilter = '';

  // Check if the sermon filter buttons are present on the page
  if ($('.sermon-filter-buttons').length > 0) {
    function loadFilteredResults(filter, paged = 1, taxonomy = '') {
      $.ajax({
        url: sfb_ajax.ajax_url,
        type: 'POST',
        data: {
          action: 'sfb_filter',
          filter: filter,
          paged: paged,
          taxonomy: taxonomy
        },
        success: function (response) {
          $('.sermon-filter-results').html(response);
          if (!taxonomy) {
            currentPage = paged;  // Store the current page of the speaker list
            currentFilter = filter;
          }
          // $('.sermon-filter-results').attr('data-filter', filter).attr('data-taxonomy', taxonomy);
        }
      });
    }

    // Load recent sermons by default
    loadFilteredResults('recent');

    $(document).on('click', '.sermon-filter-button', function () {
      var filter = $(this).data('filter');
      var paged = 1;
      if (filter === currentFilter) {
        paged = currentPage;
      }
      $('.sermon-filter-button').removeClass('active');
      $(this).addClass('active');
      loadFilteredResults(filter, paged);
    });

    $(document).on('click', '.child-taxonomy-link', function () {
      var taxonomy = $(this).data('taxonomy');
      var filter = $('.sermon-filter-button.active').data('filter');
      loadFilteredResults(filter, 1, taxonomy);
    });

    $(document).on('click', '.page-link', function (e) {
      e.preventDefault(); // Prevent default action of <a> tag
      var page = $(this).data('page');
      var sermonResultContainer = $('.sermon-results-container');
      var filter = '';
      var taxonomy = '';
      if (sermonResultContainer.length > 0) {
        // if sermon posts are displayed.
        // get the filter and taxonomy info from the container
        filter = $('.sermon-results-container').data('filter');
        taxonomy = $('.sermon-results-container').data('taxonomy');
      } else {
        filter = $('.sermon-filter-button.active').data('filter');
      }
      loadFilteredResults(filter, page, taxonomy);
    });
  }
});
