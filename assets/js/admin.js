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
		this.pluginName = function() {
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
			vm.running = m.prop(false);
			vm.results = m.prop([]);
			vm.finished = m.prop( false );
			vm.percentageDifference = m.prop( 0 );
			vm.sums = m.prop([ 0, 0, 0, 0 ]);
			vm.averages = m.prop([ 0, 0, 0, 0 ]);
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
		var averages = Profiler.vm.averages();

		// Loop through results
		for( var i=0; i<result.length; i++) {

			// add to sums array
			sums[i] += result[i];

			// recalculate average
			averages[i] = ( sums[i] / Profiler.vm.results().length ).toPrecision(3);
		}

		// Store new data in View-Model
		Profiler.vm.sums(sums);
		Profiler.vm.averages(averages);

		// Recalculate percentage
		var percentageDifference = ( ( sums[1] / sums[0] ) +
			( sums[3] / sums[2] ) ) / 2 * 100 - 100;
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

		var chartData = vm.averages();

		// Initialize chart if this is the first time it's drawn.
		if( ! initialized ) {

			// Initialize chart
			var data = {
				labels: [ "No plugins", "Only " + vm.config.pluginName(), "All but " + vm.config.pluginName(), "All plugins"],
				datasets: [
					{
						label: "Load times",
						strokeColor: "rgba(6, 211, 92,1)",
						fillColor: "rgba(6, 211, 92,1)",
						data: chartData
					}
				]
			};
			context.chart = new Chart( element.getContext("2d") ).Bar( data, {});
		} else {

			// Change data in chart with newly calculated data
			for( var i=0; i < context.chart.datasets[0].bars.length; i++ ) {
				context.chart.datasets[0].bars[i].value = chartData[i];
			}
			context.chart.update();
		}

	};

	/**
	 * Check if result deviates from average by more than 15%.
	 *
	 * @param result
	 * @param step
	 * @returns {boolean}
	 */
	Profiler.doesResultDeviate = function( result, step ) {
		var averages = Profiler.vm.averages();
		return result[step] > ( averages[step] * 1.15 ) || result[step] < ( averages[step] * 0.85 );
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
		var config = vm.config;

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
		var results = vm.results();
		return m("div.results", [
			m("p", [
				( vm.finished() ) ? '' : m( "input", { type: "button", class: "button", onclick: Profiler.toggle, value: ( vm.running() ? "Stop" : "Continue" ) }),
				" ",
				( vm.running() && ! vm.finished() ) ? '' : m("input", { type: "button", class: "button button-danger", onclick: Profiler.reset, value: "Reset" } )
			]),
			m("p", [
				m("strong", ( vm.finished() ) ? "Done!" : "Running... " + vm.results().length + '/' + config.n() )
			]),
			m("p", m.trust( "On average, "+ config.pluginName() +" added <strong>" + vm.percentageDifference().toPrecision(3) +"%</strong> to each request." )),
			m("canvas", { width: 600, height: 400, config: Profiler.updateChart } ),
			m("h3", "Profile Details"),
			m("table.data", [
				m("thead", [
					m("tr", [
						m("th", "#" ),
						m("th", "No plugins"),
						m("th", "Only " + config.pluginName()),
						m("th", "All but " + config.pluginName()),
						m("th", "All plugins")
					])
				]),
				m('tbody', [
					vm.results().map( function( row, resultIndex ) {
						return m("tr", [
							m("td", resultIndex + 1 ),
							row.map( function( time, timeIndex ) {

								var classes = [];
								if( time == 0 ) {
									classes.push('error');
								} else if( Profiler.doesResultDeviate( row, timeIndex ) ) {
									classes.push('deviates')
								}

								return m(
									"td", { class: classes.join(',') }, time + "s" );
							})
						])
					})
				])
			])
		]);
	};

	// Mount the Profiler module
	m.module( document.getElementById('profiler-mount'), Profiler );

})();