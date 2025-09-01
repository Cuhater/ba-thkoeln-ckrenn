import classnames from 'classnames'

export const visibleFromValues = [
	{ label: 'Always', value: 'always', classes: 'block' },
	{ label: 'Small', value: 'small', classes: 'hidden sm:block' },
	{ label: 'Medium', value: 'medium', classes: 'hidden md:block' },
	{ label: 'Large', value: 'large', classes: 'hidden lg:block' },
	{ label: 'Extra Large', value: 'xl', classes: 'hidden xl:block' },
]

export const fontSizes = [
	{ label: 'S', value: 'sm', classes: 'text-sm md:text-base' },
	{ label: 'M', value: 'md', classes: 'text-base md:text-lg' },
	{ label: 'L', value: 'lg', classes: 'text-lg md:text-xl' },
]

export const paddings = [
	{ label: 'Kein', value: '0', classes: 'p-0' },
	{ label: 'S', value: 's', classes: 'p-4 lg:p-8' },
	{ label: 'M', value: 'm', classes: 'p-6 lg:p-12' },
	{ label: 'L', value: 'l', classes: 'p-10 lg:p-16' },
	{ label: 'XL', value: 'xl', classes: 'p-16 lg:p-20' }
]

export const spacesY = [
	{ label: 'Kein', value: '0', classes: 'space-y-0' },
	{ label: 'S', value: 's', classes: 'space-y-4 lg:space-y-8' },
	{ label: 'M', value: 'm', classes: 'space-y-6 lg:space-y-12' },
	{ label: 'L', value: 'l', classes: 'space-y-10 lg:space-y-16' },
	{ label: 'XL', value: 'xl', classes: 'space-y-16 lg:space-y-20' },
	{ label: 'Auto', value: 'auto', classes: 'space-y-auto' },
]

export const spacings = [
	{ label: 'Kein', value: '0', classes: { top: 'mt-0' } },
	{ label: 'S', value: 's', classes: { top: 'mt-4 lg:mt-8' } },
	{ label: 'M', value: 'm', classes: { top: 'mt-6 lg:mt-12' } },
	{ label: 'L', value: 'l', classes: { top: 'mt-10 lg:mt-16' } },
	{ label: 'XL', value: 'xl', classes: { top: 'mt-16 lg:mt-20' } },
	{ label: 'Auto', value: 'auto', classes: { top: 'mt-auto' } },
]

export const gaps = [
	{ label: 'Kein', value: 'gap-0' },
	{ label: 'S', value: 'gap-4' },
	{ label: 'M', value: 'gap-10' },
	{ label: 'L', value: 'gap-16' },
	{ label: 'XL', value: 'gap-24' },
	{ label: 'XXL', value: 'gap-32' },
]

export const paddingsY = [
	{ label: 'Kein', value: '0', classes: 'py-0' },
	{ label: 'S', value: 's', classes: 'py-4 lg:py-8' },
	{ label: 'M', value: 'm', classes: 'py-6 lg:py-12' },
	{ label: 'L', value: 'l', classes: 'py-10 lg:py-16' },
	{ label: 'XL', value: 'xl', classes: 'py-16 lg:py-20' },
]

export const maxWidths = [
	{ label: 'S', value: 'small', classes: 'md:max-w-6/12 md:ml-3/12' },
	{ label: 'M', value: 'medium', classes: 'md:max-w-8/12 md:ml-2/12' },
	{ label: 'L', value: 'large', classes:  'md:max-w-10/12 md:ml-1/12' },
	{ label: 'Full', value: 'full', classes: 'max-w-none' },
]
