if (!window.Joomla) {
    throw new Error('Joomla API was not properly initialised');
}

const showAll = (event) => {
    event.target.innerHTML = "jsddjd";
};

document.querySelectorAll('.mod_blogroll_showall').forEach(element => {
    element.addEventListener('click', showAll);
});
document.querySelectorAll('.mod_blogroll_showall').forEach(element => {
    element.innerText += "yoloo";
});