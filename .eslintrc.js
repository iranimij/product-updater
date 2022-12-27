module.exports = {
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended-with-formatting' ],
	globals: {
		jQuery: true,
		$: true,
		_: true,
		ravenEditor: true,
		ravenFrontend: true,
		elementor: true,
		elementorFrontend: true,
		elementorModules: true,
		ElementorConfig: true,
		wp: true,
		FormData: true,
		location: true,
		_wpUtilSettings: true,
		savvior: true,
		panel: true,
		model: true,
		view: true,
		objectFitPolyfill: true,
		window: true,
		document: true,
		require: true,
		setTimeout: true,
		console: true,
		module: true,
	},
	rules: {
		'no-new': 0,
		'one-va': 0,
		'react/jsx-no-target-blank': 'off',
		'@wordpress/no-unused-vars-before-return': 'off',
		'@wordpress/i18n-ellipsis': 'off',
	},
};
