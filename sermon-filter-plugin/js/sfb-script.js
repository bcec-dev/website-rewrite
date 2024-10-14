jQuery(document).ready(function ($) {
  // Variable to store the current page of the filtered list
  var currentNonTaxonomyPage = 1;
  var currentFilter = '';

  // Check if the sermon filter buttons are present on the page
  if ($('.sfb-filter-button').length > 0) {
    function saveFilterState(filter, paged, taxonomy, searchQuery, nonTaxonomyPage) {
      const filterState = {
        filter: filter,
        paged: paged,
        taxonomy: taxonomy,
        searchQuery: searchQuery,
        nonTaxonomyPage: nonTaxonomyPage,
      };
      sessionStorage.setItem('sermonFilterState', JSON.stringify(filterState));
    }
    
    function loadFilteredResults(filter, paged = 1, taxonomy = '', searchQuery = '') {
      // Show spinner
      $('.sfb-container').addClass('sfb-loading');

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
            currentNonTaxonomyPage = paged;  // Store the current page of the speaker list
            currentFilter = filter;
          }
          // Hide spinner
          $('.sfb-container').removeClass('sfb-loading');
          saveFilterState(filter, paged, taxonomy, searchQuery, currentNonTaxonomyPage);
        },
        error: function () {
          // Hide spinner on error
          $('.sfb-container').removeClass('sfb-loading');
        }
      });
    }

    // Function to set the 'active' class on the corresponding filter button
    function setActiveFilterButton(filter) {
      // Remove the 'active' class from all buttons first
      $('.sfb-filter-button').removeClass('active');

      // Find the button that matches the filter value and add the 'active' class
      const $activeButton = $(`.sfb-filter-button[data-filter="${filter}"]`);
      if ($activeButton.length) {
          $activeButton.addClass('active');
      }
    }

    function initLoad() {
      // Check if 'from_sermon_post' parameter is present in the URL
      const urlParams = new URLSearchParams(window.location.search);
      const fromSermonPost = urlParams.get('from_sermon_post');

      if (fromSermonPost === 'true') {
        const filterState = JSON.parse(sessionStorage.getItem('sermonFilterState'));
        if (filterState) {
          const { filter, paged, taxonomy, searchQuery, nonTaxonomyPage } = filterState;

          loadFilteredResults(filter, paged, taxonomy, searchQuery);
          // Set the active class on the corresponding filter button
          setActiveFilterButton(filter);
          // Set the current values too
          currentNonTaxonomyPage = nonTaxonomyPage;
          currentFilter = filter;
          return;
        }
      }
      // Load recent sermons by default
      loadFilteredResults('recent');
    }

    initLoad();

    $(document).on('click', '.sfb-filter-button', function () {
      var filter = $(this).data('filter');
      var paged = 1;
      if (filter === currentFilter) {
        paged = currentNonTaxonomyPage;
      }
      $('.sfb-filter-button').removeClass('active');
      $(this).addClass('active');
      // empty the search field
      $('.sfb-search-input').val('');
      loadFilteredResults(filter, paged);
    });

    $(document).on('click', '.sfb-child-taxonomy-link', function () {
      var taxonomy = $(this).data('taxonomy');
      var filter = $('.sfb-filter-button.active').data('filter');
      loadFilteredResults(filter, 1, taxonomy);
    });

    $(document).on('click', '.sfb-page-link a', function (e) {
      e.preventDefault(); // Prevent default action of <a> tag
      // Get the 'page' data from the parent element of the <a> tag
      var page = $(this).parent().data('page');
      var sermonResultContainer = $('.sfb-sermons-grid-container');
      var filter = '';
      var taxonomy = '';
      var searchQuery = '';
      if (sermonResultContainer.length > 0) {
        // if sermon posts are displayed.
        // get the filter, taxonomy and search query info from the container
        filter = $('.sfb-sermons-grid-container').data('filter');
        taxonomy = $('.sfb-sermons-grid-container').data('taxonomy');
        searchQuery = $('.sfb-sermons-grid-container').data('searchquery');
      } else {
        filter = $('.sfb-filter-button.active').data('filter');
      }
      loadFilteredResults(filter, page, taxonomy, searchQuery);
    });

    // Function to handle the search action
    function handleSearch() {
      $('.sfb-filter-button').removeClass('active');
      var searchQuery = $('.sfb-search-input').val();
      loadFilteredResults('search', 1, '', searchQuery);
    }

    // Handler for search button click
    $('.sfb-search-icon').on('click', function () {
      handleSearch();
    });

    // Handler for search keydown
    $('.sfb-search-input').on('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault(); // Prevent the default form submission
        handleSearch();
      }
    });
  }
});
