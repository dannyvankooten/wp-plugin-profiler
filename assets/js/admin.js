(function() {
	'use strict';

	// Config model
	var Config = function( data ) {
		this.url = m.prop( data.url );
		this.plugins = m.prop( data.plugins );
		this.slug = m.prop( data.slug || Object.keys(data.plugins)[0] );
		this.n = m.prop( data.n || 10 );
	};



	// Profiler
	var Profiler = {};

	Profiler.vm = (function() {

		var vm = {};

		vm.init = function() {
			vm.running = m.prop( false );
			vm.config = new Config( wpp.config );
			vm.results = m.prop( [] );
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

	Profiler.start = function() {
		Profiler.vm.running( true );
		Profiler.getResult();
	};

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
		Profiler.vm.percentageDifference( percentageDifference );
		m.redraw();
	};

	Profiler.getResult = function() {

		// Quit loopback if we're at the desired number of results
		if( ! Profiler.vm.running() || Profiler.vm.results().length === Profiler.vm.config.n() ) {
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

	Profiler.toggle = function() {
		( Profiler.vm.running() ) ? Profiler.stop() : Profiler.start();
	};

	Profiler.stop = function() {
		Profiler.vm.running( false );
	};

	Profiler.reset = function() {
		Profiler.vm.init();
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

			// render fields
			return [
				m( "table.form-table", [
					m("tr", [
						m("th", "Select the plugin to profile."),
						m("td", [
							m("select", { onchange: m.withAttr( "value", Profiler.vm.config.slug ) }, Object.keys(Profiler.vm.config.plugins()).map( function( key ) {
								return m("option", { value: key }, Profiler.vm.config.plugins()[key] );
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

		return m("div.results", [
			m("p", "On average, the profiled plugin added " + vm.percentageDifference().toPrecision(2) +"% to each request."),
			m("table", [
				m("tr", [
					m("th.row-title", "#"),
					vm.results().map( function( r, i ) {
						return m( "td", i + 1 );
					}),
					m("th", "Total")
				]),
				m("tr", [
					m("th", "No plugins"),
					vm.results().map( function( r, i ) {
						return m("td", r.no_plugins );
					}),
					m("td", vm.sums().no_plugins.toPrecision(3))
				]),
				m("tr", [
					m("th", "Only profiled plugin"),
					vm.results().map( function( r, i ) {
						return m("td", r.only_slug );
					}),
					m("td", vm.sums().only_slug.toPrecision(3))
				]),
				m("tr", [
					m("th", "All but profiled"),
					vm.results().map( function( r, i ) {
						return m("td", r.all_but_slug );
					}),
					m("td", vm.sums().all_but_slug.toPrecision(3))
				]),
				m("tr", [
					m("th", "All plugins"),
					vm.results().map( function( r, i ) {
						return m("td", r.all_plugins );
					}),
					m("td", vm.sums().all_plugins.toPrecision(3))
				])
			]),
			m("p", [
				m( "input", { type: "button", class: "button", onclick: Profiler.toggle, value: ( vm.running() ? "Stop" : "Continue" ) }),
				( vm.running() ) ? '' : m("input", { type: "button", class: "button button-danger", onclick: Profiler.reset, value: "Reset" } )
			])
		]);
	};

	m.module( document.getElementById('profiler-mount'), Profiler );

})();