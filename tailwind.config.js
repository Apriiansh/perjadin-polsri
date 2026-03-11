/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'./app/Views/**/*.php',
		'./app/Views/**/*.html',
		'./public/assets/js/**/*.js',
	],
	theme: {
		extend: {
			colors: {
				primary: {
					50: '#fffef0',
					100: '#fffbc7',
					200: '#ffee8c',
					300: '#ffdf4d',
					400: '#facc15',
					500: '#eab308',
					600: '#ca8a04',
					700: '#a16207',
					800: '#854d0e',
					900: '#713f12',
				},
				secondary: {
					50: '#f0f9ff',
					100: '#e0f2fe',
					200: '#bae6fd',
					300: '#8cd6ff',
					400: '#38bdf8',
					500: '#0ea5e9',
					600: '#0284c7',
					700: '#0369a1',
					800: '#075985',
					900: '#0c4a6e',
				},
				accent: {
					50: '#f8f5ff',
					100: '#f0eaff',
					200: '#e1d4ff',
					300: '#cbb3ff',
					400: '#b58cff',
					500: '#935eff',
					600: '#7e3af2',
					700: '#6c2bd9',
					800: '#5b21b6',
					900: '#4c1d95',
				},
				surface: {
					50: '#f8fafc',
					100: '#f1f5f9',
					200: '#e2e8f0',
					300: '#cbd5e1',
				},
			},
			boxShadow: {
				soft: '0 20px 25px -5px rgba(181, 140, 255, 0.1), 0 10px 10px -5px rgba(140, 214, 255, 0.1)',
			},
		},
	},
	plugins: [],
};
