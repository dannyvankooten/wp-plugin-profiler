(function() {
	'use strict';

	// Config model
	var Config = function( data ) {
		var self = this;
		this.url = m.prop( data.url );
		this.plugins = m.prop( data.plugins );
		this.slug = m.prop( data.slug || Object.keys(data.plugins)[0] );
		this.n = m.prop( data.n || 10 );

		// Get name of selected plugin
		this.plugin_name = function() {
			var slugs = Object.keys( self.plugins() );

			for( var i=0; i < slugs.length; i++ ) {
				if( slugs[i] === self.slug() ) {
					return self.plugins()[slugs[i]];
				}
			}

			return 'Unknown';
		}
	};

	// Profiler
	var Profiler = {};

	/**
	 * View Model
	 *
	 * Holds the state of the profiler.
	 */
	Profiler.vm = (function() {

		var vm = {};
		vm.config = new Config( wpp.config );

		// Initialize initial state
		vm.init = function() {
			vm.running = m.prop( false );
			vm.results = m.prop( [] );
			vm.finished = m.prop( false );
			vm.percentageDifference = m.prop( 0 );
			vm.sums = m.prop( {
				no_plugins: 0,
				only_slug: 0,
				all_but_slug: 0,
				all_plugins: 0
			} );
		};

		return vm;

	})();

	/**
	 * Add a result
	 *
	 * - Adds to view-model results array
	 * - Updates the total sum
	 * - Recalculates the average percentage difference
	 *
	 * @param result
	 */
	Profiler.addResult = function( result ) {

		// Add to results
		Profiler.vm.results().push( result );

		// Add to sums array
		var sums = Profiler.vm.sums();
		Object.keys(result).forEach( function( key ) {
			sums[key] += result[key];
		});

		// Recalculate average difference
		var percentageDifference = ( ( sums.only_slug / sums.no_plugins ) +
			( sums.all_plugins / sums.all_but_slug ) ) / 2 * 100 - 100;
		Profiler.vm.percentageDifference( ( percentageDifference > 0 ) ? percentageDifference : 0 );
		m.redraw();
	};

	/**
	 * Get the benchmark times from server
	 * - Will call itself until desired number of results is reached
	 */
	Profiler.getResult = function() {

		// Quit loopback if we're at the desired number of results
		Profiler.vm.finished( Profiler.vm.results().length >= Profiler.vm.config.n() );
		if( ! Profiler.vm.running() || Profiler.vm.finished() ) {
			return;
		}

		// Benchmark each step
		var args = {
			method: "GET",
			url: wpp.ajaxurl,
			data: {
				action: 'plugin_profiler',
				url: Profiler.vm.config.url(),
				slug: Profiler.vm.config.slug()
			}
		};

		// Get benchmark times & call self again
		m.request( args).then( function(result) {
			Profiler.addResult(result);
			Profiler.getResult();
		});
	};

	/**
	 * Toggle profiler (start / stop)
	 * - Redraws view
	 */
	Profiler.toggle = function() {
		( Profiler.vm.running() ) ? Profiler.stop() : Profiler.start();
		m.redraw();
	};


	/**
	 * Start the profiler
	 */
	Profiler.start = function() {
		Profiler.vm.running( true );
		Profiler.getResult();
	};

	/**
	 * Stop (pause) the profiler
	 */
	Profiler.stop = function() {
		Profiler.vm.running( false );
	};

	/**
	 * Reset the profiler to its initial state
	 */
	Profiler.reset = function() {
		Profiler.vm.init();
	};

	/**
	 * Updates the canvas element with the new chart
	 *
	 * @param element
	 * @param initialized
	 * @param context
	 */
	Profiler.updateChart = function( element, initialized, context ) {

		var vm = Profiler.vm;

		// Recalculate averages
		var sums = vm.sums();
		var numberOfResults = vm.results().length;
		var resultData = Object.keys(sums).map( function( key ) {
			return ( sums[key] / numberOfResults ).toPrecision(4);
		});

		// Initialize chart if this is the first time it's drawn.
		if( ! initialized ) {

			// Initialize chart
			var data = {
				labels: [ "No plugins", "Only " + vm.config.plugin_name(), "All but " + vm.config.plugin_name(), "All plugins"],
				datasets: [
					{
						label: "Load times",
						strokeColor: "rgba(6, 211, 92,1)",
						fillColor: "rgba(6, 211, 92,1)",
						data: resultData
					}
				]
			};
			var options = {};

			context.chart = new Chart( element.getContext("2d") ).Bar( data, options);
		} else {

			// Change data in chart with newly calculated data
			for( var i=0; i < context.chart.datasets[0].bars.length; i++ ) {
				context.chart.datasets[0].bars[i].value = resultData[i];
			}
			context.chart.update();
		}

		// add data to chart
	};

	/**
	 * Controller
	 */
	Profiler.controller = function() {
		Profiler.vm.init();
	};

	/**
	 * View
	 *
	 * @param ctrl
	 */
	Profiler.view = function( ctrl ) {

		var vm = Profiler.vm;

		if( ! Profiler.vm.running() && vm.results().length === 0 ) {

			// Profiler is not running, render configuration form.
			return [
				m( "table.form-table", [
					m("tr", [
						m("th", "Select the plugin to profile."),
						m("td", [
							m("select", { onchange: m.withAttr( "value", Profiler.vm.config.slug ) }, Object.keys(Profiler.vm.config.plugins()).map( function( key ) {
								return m("option", { value: key, selected: ( vm.config.slug() == key ) }, Profiler.vm.config.plugins()[key] );
							}) )
						] )
					]),
					m("tr", [
						m("th", "Enter the URL to profile"),
						m("td", [
							m("input", { type: "text", class: "regular-text", value: Profiler.vm.config.url(), onchange: m.withAttr( "value", Profiler.vm.config.url ) } )
						] )
					]),
					m("tr", [
						m("th", "Number of requests"),
						m("td", [
							m("input", { type: "number", min: 0, step: 1, value: Profiler.vm.config.n(), onchange: m.withAttr( "value", Profiler.vm.config.n ) } )
						] )
					])
				]),
				m( "p", [
					m( "input", { type: "submit", class: "button button-primary", value: "Run Profile", onclick: Profiler.start } )
				])
			];
		}

		// We have results to show. Render results.
		return m("div.results", [
			m("p", [
				( vm.finished() ) ? '' : m( "input", { type: "button", class: "button", onclick: Profiler.toggle, value: ( vm.running() ? "Stop" : "Continue" ) }),
				" ",
				( vm.running() && ! vm.finished() ) ? '' : m("input", { type: "button", class: "button button-danger", onclick: Profiler.reset, value: "Reset" } )
			]),
			m("p", [
				m("strong", ( vm.finished() ) ? "Done!" : "Running... " + vm.results().length + '/' + vm.config.n() )
			]),
			m("p", m.trust( "On average, "+ vm.config.plugin_name() +" added <strong>" + vm.percentageDifference().toPrecision(3) +"%</strong> to each request." )),
			m("canvas", { width: 600, height: 400, config: Profiler.updateChart } )
		]);
	};

	m.module( document.getElementById('profiler-mount'), Profiler );

})();