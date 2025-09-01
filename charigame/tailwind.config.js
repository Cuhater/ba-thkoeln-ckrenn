/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		"./assets/js/**/*.{js,jsx}",
		"./blocks/**/*.{js,jsx,php}",
		"./templates/**/*.php",
		"./frontend/**/*.php",
		"./admin/**/*.php",
		"./includes/**/*.php",
		"./src/**/*.{js,jsx,php}",
		"./**/*.php"
	],
	theme: {
		extend: {
			colors: {
				primary: {
					DEFAULT: 'var(--color-primary)',
						50: 'color-mix(in srgb, var(--color-primary) 10%, white)',
						100: 'color-mix(in srgb, var(--color-primary) 20%, white)',
						200: 'color-mix(in srgb, var(--color-primary) 30%, white)',
						300: 'color-mix(in srgb, var(--color-primary) 40%, white)',
						400: 'color-mix(in srgb, var(--color-primary) 60%, white)',
						500: 'var(--color-primary)',
						600: 'color-mix(in srgb, var(--color-primary) 80%, black)',
						700: 'color-mix(in srgb, var(--color-primary) 70%, black)',
						800: 'color-mix(in srgb, var(--color-primary) 60%, black)',
						900: 'color-mix(in srgb, var(--color-primary) 50%, black)',
						},
						secondary: {
							DEFAULT: 'var(--color-secondary)',
								50: 'color-mix(in srgb, var(--color-secondary) 10%, white)',
								500: 'var(--color-secondary)',
								900: 'color-mix(in srgb, var(--color-secondary) 50%, black)',
								},
								tertiary: {
									DEFAULT: 'var(--color-tertiary)',
										50: 'color-mix(in srgb, var(--color-tertiary) 10%, white)',
										500: 'var(--color-tertiary)',
										900: 'color-mix(in srgb, var(--color-tertiary) 50%, black)',
										},
										success: 'var(--color-success)',
										warning: 'var(--color-warning)',
										error: 'var(--color-error)',
										}
										},
										},
										plugins: [],
										}
