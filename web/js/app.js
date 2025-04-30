// app.js
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.toggleButton');

    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const textContainer = document.getElementById('textContainer');
            const divID = button.getAttribute('div-id');

            if (divID.style.display === "none") {
                divID.style.display = "block";
              } else {
                divID.style.display = "none";
            }
        });
    });
});

/**
 * Toggles the visibility of the div with the given id
 * @param {string} divID ID of the div block to toggle
 */
function toggleVisibility(divID) {
    console.log(divID);
    if (divID.style.display === "block") {
        divID.style.display = "none";
    } else {
        divID.style.display = "block";
    }
}