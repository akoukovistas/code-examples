jQuery(document).ready(function ($) {

	// -----------------
	// Match height.
	// -----------------
	// Match the heights on the property card titles.
	$('.card__title').matchHeight();
	// Match the heights on the property card caption area.
	$('.card__caption').matchHeight();

	// -----------------
	// Accordion.
	// -----------------
	var accordion = document.getElementsByClassName("accordion");
	var i;

	for (i = 0; i < accordion.length; i++) {
		accordion[i].addEventListener("click", function () {
			// Toggle between adding and removing the "active" class, to highlight the button that controls the panel.
			this.classList.toggle("active");

			// Toggle between hiding and showing the active panel.
			var panel = this.nextElementSibling;
			if (panel.style.maxHeight) {
				panel.style.maxHeight = null;
			} else {
				panel.style.maxHeight = panel.scrollHeight + "px";
			}
		});
	}

});