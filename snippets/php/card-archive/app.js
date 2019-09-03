import $ from "jquery";
import whatInput from "what-input";
// import Zooming from "zooming";
import matchHeight from "jquery-match-height-browserify";
import slick from "slick-carousel";
import "./lib/responsive-tables";
import "./lib/fresco";
import "./lib/isotope.pkgd.min.js";
import AOS from 'aos';

require("a11y-toggle");

window.$ = $;

// import Foundation from 'foundation-sites';
// If you want to pick and choose which modules to include, comment out the above and uncomment
// the line below
import "./lib/foundation-explicit-pieces";

$(document).foundation();
AOS.init();
$(document).ready(function() {
	// READY
	// startZooming();
	startMatchHeight();
	categorySelect();
	foundationResponsiveTables();
	tripsSlider();
	excursionsSlider();
	testimonialSlider();
	blogCatFilter();
	cardFilters();
	headhesiveInit();
});


// Match Height of elements
function startMatchHeight() {
	const defaultOptions = {
		byRow: true,
		property: "height",
		target: null,
		remove: false
	};

	$(".signpost__title").matchHeight();
	$(".signpost__excerpt").matchHeight();

	// Card
	// $(".card__content").matchHeight(defaultOptions);
	// $(".card__footer").matchHeight(defaultOptions);
	// $(".card__role").matchHeight(defaultOptions);
	// $(".card__title").matchHeight(defaultOptions);

	// Card Offer
	$(".card-offer__content").matchHeight(defaultOptions);

	// Content Block
	const contentBlockOptions = {
		byRow: false
	};
	$(".content-block__title").matchHeight(contentBlockOptions);
	$(".content-block__content").matchHeight(contentBlockOptions);

	$(".cta-standard__content, .cta-standard__image").matchHeight();
}


// gForms validation
(function($) {
	if ($(".gform_wrapper").length) {
		var $body = $("body");
		$body.on("click", "input,textarea", function() {
			hideErrors($(this));
		});
		$body.on("click", ".validation_message", function() {
			$(this).hide();
			hideErrors($(this));
		});
	}
	function hideErrors($el) {
		$el.closest("li.gfield_error").removeClass("gfield_error");
		$el.closest("div.ginput_container")
			.next("div.validation_message")
			.hide();
	}
})(jQuery);

function cardFilters() {
	var $buttonContainer = $(".card-filter__button-group");
	var $grid = $(".page-template-page-tour .card-grid");
	var activeClass = "is-active";
	var activeFilters = []; // Formatted list of filters.
	var joinFilters = ""; // Joined list of filters.
	var selectedFilters = ["*"]; // Clean list of filter properties.
	var behaviour = "MULTIPLE"; // MULTIPLE Multiple active filters / SINGLE one active filter.
	var logic = "AND"; // AND selected filters / OR selected filters.

	// Override settings for Team pages.
	if ($(".post-type-archive-team_members .card-grid").length) {
		$grid = $(".post-type-archive-team_members .card-grid");
		behaviour = "SINGLE";
	}

	$buttonContainer.find(".button").on("click", function() {
		var $this = $(this);
		var filterValue = $(this).data("filter");

		if (behaviour === "SINGLE") {
			$this
				.parent()
				.find(".button")
				.removeClass(activeClass);

			$this.addClass(activeClass);

			selectedFilters = activeFilters = joinFilters = "*";
			selectedFilters = [filterValue];
		} else if ("*" === filterValue) {
			// Show All.
			selectedFilters = activeFilters = joinFilters = "*";

			$this
				.parent()
				.find(".button")
				.removeClass(activeClass);

			$this.addClass(activeClass);
		} else if ($this.hasClass(activeClass)) {
			// Currently Active.
			$this.removeClass(activeClass);

			if (selectedFilters === "") {
				selectedFilters = ["*"];
			}

			for (var i = 0; i < selectedFilters.length; i++) {
				if (selectedFilters[i] === filterValue) {
					selectedFilters.splice(i, 1);
				}
			}
		} else {
			if (selectedFilters.includes("*")) {
				selectedFilters = [filterValue];
			} else {
				selectedFilters.push(filterValue);
			}

			$this
				.parent()
				.find('.button[data-filter="*"]')
				.removeClass(activeClass);

			$this.addClass(activeClass);
		}

		// Format classes for filters.
		if ("*" !== filterValue) {
			activeFilters = []; // Reset.
			selectedFilters.forEach(function(element, index) {
				activeFilters[index] = "." + element;
			});

			// Check for OR / AND logic.
			if ("OR" === logic) {
				joinFilters = activeFilters.join();
			} else {
				joinFilters = activeFilters.join("");
			}
		}

		if (joinFilters === "") {
			selectedFilters = activeFilters = joinFilters = "*";
			$this
				.parent()
				.find('.button[data-filter="*"]')
				.addClass(activeClass);
		}

		$grid.isotope({
			filter: joinFilters
		});
	});
}
