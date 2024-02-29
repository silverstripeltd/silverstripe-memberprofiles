const confirmedPasswordShowOnClick = document.querySelector('.confirmedpassword .showOnClick a');

if (confirmedPasswordShowOnClick) {
  const showOnClickContainer = confirmedPasswordShowOnClick.parentNode.querySelector('.showOnClickContainer');

  showOnClickContainer.style.display = 'none';

  confirmedPasswordShowOnClick.addEventListener('click', function(e) {
    e.preventDefault();

    if (showOnClickContainer.style.display === 'none') {
      showOnClickContainer.style.display = 'block';
    } else {
      showOnClickContainer.style.display = 'none';
    }

    return false;
  });
}