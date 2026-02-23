import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
const plugin = require('tailwindcss/plugin')
/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            fontSize: {
                tag: ['0.5625rem', '0.875rem'],
                regularSmall: ['0.8125rem', '1rem'],
                regularLarge: ['1.125rem', '1.625rem'],
                regularMedium: ['0.875rem', '1.25rem'],
            },
            maxWidth: {
                "7xl": "1128px",
            },
            colors: {
                primary: "var(--va-primary)",
                secondary: "var(--va-secondary)",
                success: "var(--va-success)",
                info: "var(--va-info)",
                danger: "var(--va-danger)",
                warning: "var(--va-warning)",
                backgroundPrimary: "var(--va-background-primary)",
                backgroundSecondary: "var(--va-background-secondary)",
                backgroundElement: "var(--va-background-element)",
                backgroundCardPrimary: "var(--va-background-card-primary)",
                backgroundCardSecondary: "var(--va-background-card-secondary)",
                backgroundBorder: "var(--va-background-border)",
                textPrimary: "var(--va-text-primary)",
                textInverted: "var(--va-text-inverted)",
                shadow: "var(--va-shadow)",
                focus: "var(--va-focus)",
            },
        },
        screens: {
            xs: "0px",
            sm: "576px",
            md: "768px",
            lg: "992px",
            xl: "1200px",
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
