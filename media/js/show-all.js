if (!window.Joomla) {
    throw new Error('Joomla API was not properly initialised');
}

const showMoreLabel = Joomla.Text._('MOD_BLOGROLL_SHOW_MORE');
const showLessLabel = Joomla.Text._('MOD_BLOGROLL_SHOW_LESS');

var content = document.querySelector('.mod_blogroll_showall_container');

const showAll = (event) => {
    if (content.style.display === "block") {
        content.style.display = "none";
        event.target.innerHTML = showMoreLabel;
    } else {
        content.style.display = "block";
        event.target.innerHTML = showLessLabel;
    }
};

document.querySelector('.mod_blogroll_showall_button').addEventListener('click', showAll);