module.exports = {
	globDirectory: '../public_html/u/',
	globPatterns: [
		'**/*.{html,js,css,scss,eot,ttf,woff,woff2,svg,txt,mjs,png,json,md,jpg,gif,php,ts,rb,less,ico,rar,mp4}'
	],
	swDest: '../public_html/u/sw.js',
	ignoreURLParametersMatching: [
		/^utm_/,
		/^fbclid$/
	]
};