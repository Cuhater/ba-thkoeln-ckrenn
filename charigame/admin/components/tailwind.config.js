/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: ["class"],
  content: [
    '../components/ui/**/*.{js,jsx,php}',
    '../partials/**/*.php',
    '../class-charigame-admin-menu.php',
    '../class-charigame-sortable-table.php'
  ],
  safelist: [
    // Button variants
    'shadcn-bg-primary',
    'shadcn-text-primary-foreground',
    'shadcn-bg-destructive',
    'shadcn-text-destructive-foreground',
    'shadcn-border',
    'shadcn-border-input',
    'shadcn-bg-background',
    'shadcn-bg-accent',
    'shadcn-text-accent-foreground',
    'shadcn-bg-secondary',
    'shadcn-text-secondary-foreground',
    'shadcn-text-primary',
    'shadcn-underline-offset-4',
    
    // Button sizes
    'shadcn-h-9', 'shadcn-h-10', 'shadcn-h-11',
    'shadcn-px-3', 'shadcn-px-4', 'shadcn-px-8',
    'shadcn-py-2',
    'shadcn-w-10',
    
    // Common classes
    'shadcn-inline-flex',
    'shadcn-items-center',
    'shadcn-justify-center',
    'shadcn-rounded-md',
    'shadcn-text-sm',
    'shadcn-font-medium',
    'shadcn-ring-offset-background',
    'shadcn-transition-colors',
    
    // Cards
    'shadcn-rounded-lg',
    'shadcn-border',
    'shadcn-bg-card',
    'shadcn-text-card-foreground',
    'shadcn-shadow-sm',
    'shadcn-flex',
    'shadcn-flex-col',
    'shadcn-space-y-1.5',
    'shadcn-p-6',
    'shadcn-pt-0',
    'shadcn-text-2xl',
    'shadcn-font-semibold',
    'shadcn-leading-none',
    'shadcn-tracking-tight',
    'shadcn-text-muted-foreground',
    'shadcn-flex-wrap',
    'shadcn-gap-4',
    'shadcn-mt-6',
    'shadcn-mb-6',
    'shadcn-ml-auto',
    
    // Layout
    'shadcn-grid',
    'shadcn-grid-cols-1',
    'shadcn-grid-cols-2',
    'shadcn-grid-cols-3',
    'shadcn-gap-2',
    'shadcn-p-2',
    'shadcn-p-4',
    'shadcn-mb-2',
    'shadcn-mt-4',
    'shadcn-justify-between',
    'shadcn-text-right',
    'shadcn-h-16',
    'shadcn-w-auto',
    'shadcn-text-base',
    
    // Tables
    'shadcn-w-full',
    'shadcn-caption-bottom',
    'shadcn-border-collapse',
    'shadcn-border-b',
    'shadcn-relative',
    'shadcn-overflow-auto',
    'shadcn-h-12',
    'shadcn-text-left',
    'shadcn-align-middle',
    'shadcn-cursor-pointer',
    'shadcn-p-4',
    'shadcn-transition-colors',
    'hover:shadcn-bg-muted/50',
    'hover:shadcn-bg-primary/90',
    'hover:shadcn-bg-destructive/90',
    'hover:shadcn-bg-secondary/80',
    'hover:shadcn-bg-accent',
    'hover:shadcn-text-accent-foreground',
    'hover:shadcn-underline',
    'data-[state=selected]:shadcn-bg-muted',
    'focus-visible:shadcn-outline-none',
    'focus-visible:shadcn-ring-2',
    'focus-visible:shadcn-ring-ring',
    'focus-visible:shadcn-ring-offset-2',
    'disabled:shadcn-pointer-events-none',
    'disabled:shadcn-opacity-50',
    
    // Progress bar
    'shadcn-h-4',
    'shadcn-bg-primary',
    'shadcn-bg-secondary',
    'shadcn-mt-1',
    
    // Responsive helpers
    'md:shadcn-grid-cols-1',
    'md:shadcn-grid-cols-2',
    'md:shadcn-grid-cols-3'
  ],
  prefix: "shadcn-",
  theme: {
    container: {
      center: true,
      padding: "2rem",
      screens: {
        "2xl": "1400px",
      },
    },
    extend: {
      colors: {
        border: "hsl(var(--border))",
        input: "hsl(var(--input))",
        ring: "hsl(var(--ring))",
        background: "hsl(var(--background))",
        foreground: "hsl(var(--foreground))",
        primary: {
          DEFAULT: "hsl(var(--primary))",
          foreground: "hsl(var(--primary-foreground))",
        },
        secondary: {
          DEFAULT: "hsl(var(--secondary))",
          foreground: "hsl(var(--secondary-foreground))",
        },
        destructive: {
          DEFAULT: "hsl(var(--destructive))",
          foreground: "hsl(var(--destructive-foreground))",
        },
        muted: {
          DEFAULT: "hsl(var(--muted))",
          foreground: "hsl(var(--muted-foreground))",
        },
        accent: {
          DEFAULT: "hsl(var(--accent))",
          foreground: "hsl(var(--accent-foreground))",
        },
        popover: {
          DEFAULT: "hsl(var(--popover))",
          foreground: "hsl(var(--popover-foreground))",
        },
        card: {
          DEFAULT: "hsl(var(--card))",
          foreground: "hsl(var(--card-foreground))",
        },
      },
      borderRadius: {
        lg: "var(--radius)",
        md: "calc(var(--radius) - 2px)",
        sm: "calc(var(--radius) - 4px)",
      },
      keyframes: {
        "accordion-down": {
          from: { height: "0" },
          to: { height: "var(--radix-accordion-content-height)" },
        },
        "accordion-up": {
          from: { height: "var(--radix-accordion-content-height)" },
          to: { height: "0" },
        },
      },
      animation: {
        "accordion-down": "accordion-down 0.2s ease-out",
        "accordion-up": "accordion-up 0.2s ease-out",
      },
    },
  },
  plugins: [require("tailwindcss-animate")],
}