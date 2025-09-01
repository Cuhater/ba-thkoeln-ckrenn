<?php

const SPACINGS = [
	'0'    => [ 'top' => 'mt-0', 'space-y' => "space-y-0", 'padding' => "p-0" ],
	's'    => [ 'top' => 'mt-4', 'space-y' => "space-y-4 lg:space-y-8", 'padding' => "p-4 lg:p-8" ],
	'm'    => [ 'top' => 'mt-8', 'space-y' => "space-y-6 lg:space-y-12", 'padding' => "p-6 lg:p-12" ],
	'l'    => [ 'top' => 'mt-10', 'space-y' => "space-y-10 lg:space-y-16", 'padding' => "p-10 lg:p-16" ],
	'xl'   => [ 'top' => 'mt-12', 'space-y' => "space-y-16 lg:space-y-20", 'spadding' => "p-16 lg:p-20" ],
	'auto' => [ 'top' => 'mt-auto', 'space-y' => "space-y-auto", 'padding' => "p-0" ],
];

const PADDINGSY = [
	'0'  => 'py-0',
	's'  => 'py-4 lg:py-8',
	'm'  => 'py-6 lg:py-12',
	'l'  => 'py-10 lg:py-16',
	'xl' => 'py-16 lg:py-20',
];

const TEXT_ALIGN_CLASSES = [
	'left'   => 'text-left',
	'center' => 'text-center',
	'right'  => 'text-right',
];

const FLEX_ALIGN_CLASSES = [
	'left'   => 'justify-start',
	'center' => 'justify-center',
	'right'  => 'justify-end',
];

const FONT_SIZES = [
	"sm" => "text-sm md:text-base",
	"md" => "text-base md:text-lg",
	"lg" => "text-lg md:text-xl",
];

const MAX_WIDTHS = [
	"small"  => "md:max-w-6/12 md:ml-3/12",
	"medium" => "md:max-w-8/12 md:ml-2/12",
	"large"  => "md:max-w-10/12 md:ml-1/12",
	"full"   => "max-w-none",
];
