if (!window.Joomla) {
    throw new Error('Joomla API was not properly initialised');
}

const showMoreLabel = Joomla.Text._('MOD_BLOGROLL_SHOW_MORE');
const showLessLabel = Joomla.Text._('MOD_BLOGROLL_SHOW_LESS');

var content = document.querySelector('.mod_blogroll_showall_container');
var images = document.querySelectorAll('.mod_blogroll_img');
var firstClick = true;

const showAll = (event) => {
    if (firstClick) {
        images.forEach((img) => {
            if (!img.hasAttribute('src')) img.src = img.dataset.src
        });
        firstClick = false;
    }

    if (content.style.display === "block") {
        content.style.display = "none";
        event.target.innerHTML = showMoreLabel;
    } else {
        content.style.display = "block";
        event.target.innerHTML = showLessLabel;
    }
};

document.querySelector('.mod_blogroll_showall_button').addEventListener('click', showAll);