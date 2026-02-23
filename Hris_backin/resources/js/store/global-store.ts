import { defineStore } from 'pinia'

export type ThemeMode = 'light' | 'dark' | 'system'

export const useGlobalStore = defineStore('global', {
  state: () => {
    return {
      isSidebarMinimized: false,
      themeMode: (localStorage.getItem('themeMode') as ThemeMode) || 'light',
      isDarkMode: false,
    }
  },

  getters: {
    currentTheme: (state) => {
      if (state.themeMode === 'system') {
        return state.isDarkMode ? 'dark' : 'light'
      }
      return state.themeMode === 'dark' ? 'dark' : 'light'
    },
  },

  actions: {
    toggleSidebar() {
      this.isSidebarMinimized = !this.isSidebarMinimized
    },

    setThemeMode(mode: ThemeMode) {
      this.themeMode = mode
      localStorage.setItem('themeMode', mode)
      this.updateTheme()
    },

    toggleDarkMode() {
      const newMode = this.themeMode === 'dark' ? 'light' : 'dark'
      this.setThemeMode(newMode)
    },

    async updateTheme() {
      if (this.themeMode === 'system') {
        // Detect system preference
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
        this.isDarkMode = prefersDark
      } else {
        this.isDarkMode = this.themeMode === 'dark'
      }

      // Update document class for additional styling
      document.documentElement.classList.toggle('dark', this.currentTheme === 'dark')
      document.documentElement.setAttribute('data-theme', this.currentTheme)
    },

    initializeTheme() {
      // Listen for system theme changes
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
      mediaQuery.addEventListener('change', (e) => {
        if (this.themeMode === 'system') {
          this.isDarkMode = e.matches
          this.updateTheme()
        }
      })

      // Set initial theme
      this.updateTheme()
    },
  },
})
