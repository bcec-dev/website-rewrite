jQuery(document).ready(function ($) {
  // Variable to store the current page and current filter type
  var currentPage = 1;
  var currentFilter = '';
  
  // Check if the sermon filter buttons are present on the page
  if ($('.sfb-buttons-group').length > 0) {
    function loadFilteredResults(filter, paged = 1, taxonomy = '', searchQuery = '') {
      $.ajax({
        url: sfb_ajax.ajax_url,
        type: 'POST',
        data: {
          action: 'sfb_filter',
          filter: filter,
          paged: paged,
          taxonomy: taxonomy,
          search_query: searchQuery,
        },
        success: function (response) {
          $('.sfb-results-container').html(response);
          if (!taxonomy) {
            currentPage = paged;  // Store the current page of the speaker list
            currentFilter = filter;
          }
        }
      });
    }

    // Load recent sermons by default
    loadFilteredResults('recent');
    
    // click handler for the filter buttons
    $(document).on('click', '.sfb-filter-button', function () {
      var filter = $(this).data('filter');
      var paged = 1;
      if (filter === currentFilter) {
        paged = currentPage;
      }
      $('.sfb-filter-button').removeClass('active');
      $(this).addClass('active');
      loadFilteredResults(filter, paged);
    });

    $(document).on('click', '.sfb-page-link', function (e) {
      e.preventDefault(); // Prevent default action of <a> tag
      var page = $(this).data('page');
      var sermonResultContainer = $('.sfb-sermons-grid-container');
      var filter = '';
      var taxonomy = '';
      if (sermonResultContainer.length > 0) {
        // if sermon posts are displayed.
        // get the filter and taxonomy info from the container
        filter = $('.sfb-sermons-grid-container').data('filter');
        taxonomy = $('.sfb-sermons-grid-container').data('taxonomy');
      } else {
        filter = $('.sfb-filter-button.active').data('filter');
      }
      loadFilteredResults(filter, page, taxonomy);
    });
  }
});