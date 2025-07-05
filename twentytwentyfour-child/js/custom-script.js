document.addEventListener('DOMContentLoaded', function () {
  // add this to workaround issue that hamburger menu button not able to open menu
  // in old iphone
  const hamburgerBtn = document.querySelector('.wp-block-navigation__responsive-container-open');
  const closeBtn = document.querySelector('.wp-block-navigation__responsive-container-close');
  const navContainer = document.querySelector('.wp-block-navigation__responsive-container');

  if(hamburgerBtn && closeBtn && navContainer) {
    hamburgerBtn.addEventListener('click', function () {
      navContainer.classList.add('is-menu-open');
    });
    
    closeBtn.addEventListener('click', function() {
      navContainer.classList.remove('is-menu-open');
    });
  }
	
  // script to workaround the cache issue of the random image not being displayed
  // in the homepage
  const imgElement = document.querySelector('.ud-random-img-block__images img');
  if (imgElement && window.udRandomImages) {
      // Hide the image initially
      imgElement.style.opacity = '0';
      
      // Array of your image URLs
      const imageUrls = window.udRandomImages;
      
      // Get random image
      const randomImage = imageUrls[Math.floor(Math.random() * imageUrls.length)];
      
      // Preload the new image
      const newImg = new Image();
      newImg.onload = function() {
          // Once loaded, update src and show
          imgElement.src = randomImage.url;
          imgElement.style.opacity = '1';
          imgElement.style.transition = 'opacity 0.3s ease-in';
      };
      newImg.src = randomImage.url;
  }
});
