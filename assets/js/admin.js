(function() {
	'use strict';

	// Config model
	var Config = function( data ) {
		this.url = m.prop( data.url );
		this.plugins = m.prop( data.plugins );
		this.slug = m.prop( data.slug );
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

	Profiler.start = function() {
		Profiler.vm.running( true );
		Profiler.getResult();
	};

	Profiler.getResult = function() {

		if( Profiler.vm.results().length === Profiler.vm.config.n() ) {
			return;
		}

		var results = Profiler.vm.results();
		results.push( results.length + 1 );

		// recalculate average
		var sum = 0;
		var avg = 0;
		results.forEach( function( r ) {
			sum += r;
		});

		avg = sum / results.length;

		Profiler.vm.average(avg);

		setTimeout( Profiler.getResult, 500 );
		m.redraw();
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
							m("select", { onchange:m.withAttr( "value", Profiler.vm.config.slug ) }, Object.keys(Profiler.vm.config.plugins()).map( function( key ) {
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

		return [
			m("p", "Average time: " + vm.average() ),
			m("p", vm.results().map( function( result ) {
					return m("p", "Result " + result );
			}) )
		];
	};

	m.module( document.getElementById('profiler-mount'), Profiler );

})();