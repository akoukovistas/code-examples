jQuery(document).ready(function ($) {

	let predictionCollection = document.getElementsByClassName("autocomplete-search");

	// Apply to all autocomplete forms on page.
	for (let i = 0; i < predictionCollection.length; i++) {
		autocomplete(predictionCollection[i], predictions);
	}
});
