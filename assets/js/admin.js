(function() {
	'use strict';

	// Config model
	var Config = function( data ) {
		this.url = m.prop( data.url );
		this.plugins = m.prop( data.plugins );
		this.slug = m.prop( data.slug || Object.keys(data.plugins)[0] );
		this.n = m.prop( data.n );
	};



	// Profiler
	var Profiler = {};

	Profiler.vm = (function() {

		var vm = {};

		vm.init = function() {
			vm.running = m.prop( false );
			vm.config = new Config( wpp.config );
			vm.results = m.prop( [] );
			vm.average = m.prop( 0 );
		};

		return vm;

	})();

	Profiler.recalculate = function() {
		var results = Profiler.vm.results();

		//console.log( sums );
	};

	Profiler.start = function() {
		Profiler.vm.running( true );
		Profiler.getResult();
	};

	Profiler.getResult = function() {

		if( Profiler.vm.results().length === Profiler.vm.config.n() ) {
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

		m.request( args).then( function(result) {
			Profiler.vm.results().push( result );
			//Profiler.recalculate();
			m.redraw();
			Profiler.getResult();
		});
	};

	Profiler.stop = function() {
		Profiler.vm.running( false );
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

		if( ! Profiler.vm.running() ) {

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
			m("p", "On average, the profiled plugin added xx% to each request."),
			m("table", [
				m("tr", [
					m("th.row-title", "#"),
					vm.results().map( function( r, i ) {
						return m( "td", i + 1 );
					})
				]),
				m("tr", [
					m("th", "No plugins"),
					vm.results().map( function( r, i ) {
						return m("td", r.no_plugins );
					})
				]),
				m("tr", [
					m("th", "Only profiled plugin"),
					vm.results().map( function( r, i ) {
						return m("td", r.only_slug );
					})
				]),
				m("tr", [
					m("th", "All but profiled"),
					vm.results().map( function( r, i ) {
						return m("td", r.all_but_slug );
					})
				]),
				m("tr", [
					m("th", "All plugins"),
					vm.results().map( function( r, i ) {
						return m("td", r.all_plugins );
					})
				])
			])
		]);
	};

	m.module( document.getElementById('profiler-mount'), Profiler );

})();