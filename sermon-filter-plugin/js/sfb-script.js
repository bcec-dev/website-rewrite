jQuery(document).ready(function ($) {
  // Variable to store the current page and current filter type
  var currentPage = 1;
  var currentFilter = '';
  
  // Check if the sermon filter buttons are present on the page
  if ($('.sermon-filter-buttons').length > 0) {

    // click handler for the filter buttons
    $(document).on('click', '.sermon-filter-button', function () {
      var filter = $(this).data('filter');
      var paged = 1;
      if (filter === currentFilter) {
        paged = currentPage;
      }
      $('.sermon-filter-button').removeClass('active');
      $(this).addClass('active');
      // loadFilteredResults(filter, paged);
    });
  }
});