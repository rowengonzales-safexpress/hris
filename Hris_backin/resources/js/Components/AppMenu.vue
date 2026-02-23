<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import BaseIcon from '@/Components/BaseIcon.vue'

const props = defineProps({
  menus: {
    type: Array,
    default: () => []
  },
  collapsed: {
    type: Boolean,
    default: false
  }
});

const page = usePage();
const expandedMenus = ref(new Set());
const hoveredMenu = ref<any>(null);
const contextMenuPosition = ref({ x: 0, y: 0 });
const contextMenuTimeout = ref<any>(null);
const sidebarRef = ref(null);

// Use menus from props or fallback to page props
const userMenus = computed(() => {
  return props.menus.length > 0 ? props.menus : (page.props.userMenus || []);
});



const isActive = (routeName: string) => routeName ? route().current(routeName) : false;

const toggleSubmenu = (menuId: number) => {
  if (expandedMenus.value.has(menuId)) {
    expandedMenus.value.delete(menuId);
  } else {
    expandedMenus.value.add(menuId);
  }
};

const isCurrentRoute = (route: string) => {
  if (!route) return false;

  // Normalize route and current page url for comparison
  const cleanRoute = route
    .replace(/^https?:\/\/[^/]+/, '')
    .replace(/^\/|\/$/g, '');
  const currentUrl = page.url.replace(/^\/|\/$/g, '');

  return currentUrl === cleanRoute || currentUrl.startsWith(cleanRoute + '/');
};

const isMenuActive = (menu: any) => {
  if (menu.route && isCurrentRoute(menu.route)) {
    return true;
  }
  if (menu.submenus && menu.submenus.length > 0) {
    return menu.submenus.some((submenu: any) => isCurrentRoute(submenu.route));
  }
  return false;
};

const getProperLink = (route: string) => {
  if (!route) return '#';
  if (route.startsWith('http')) return route;
  const cleanRoute = route.startsWith('/') ? route.substring(1) : route;
  return '/' + cleanRoute;
};

const showContextMenu = (menu: any, event: MouseEvent) => {
  if (!props.collapsed) return;

  // Clear any existing timeout
  if (contextMenuTimeout.value) {
    clearTimeout(contextMenuTimeout.value);
    contextMenuTimeout.value = null;
  }

  const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();
  contextMenuPosition.value = {
    x: rect.right + 8,
    y: rect.top
  };
  hoveredMenu.value = menu;
};

const hideContextMenu = () => {
  // Add a delay to allow smooth transition to contextmenu
  contextMenuTimeout.value = setTimeout(() => {
    hoveredMenu.value = null;
  }, 150);
};

const keepContextMenuOpen = () => {
  // Clear the timeout when hovering over contextmenu
  if (contextMenuTimeout.value) {
    clearTimeout(contextMenuTimeout.value);
    contextMenuTimeout.value = null;
  }
};

// Auto-expand menus that contain the current route
const setActiveExpand = () => {
  const next = new Set<number>();
  userMenus.value.forEach((menu: any) => {
    if (isMenuActive(menu)) {
      next.add(menu.id);
    }
  });
  expandedMenus.value = next;
};

watch(() => page.url, setActiveExpand, { immediate: true });
watch(userMenus, setActiveExpand, { immediate: true });
onMounted(() => setActiveExpand());
</script>

<template>
  <div
    ref="sidebarRef"
    class="admin-menu"
  >
    <!-- Debug info (remove in production) -->
    <div v-if="userMenus.length === 0" class="p-4 text-center text-gray-500">
      No menus available
    </div>

    <ul v-else class="menu-list">
      <li v-for="menu in userMenus" :key="menu.id" class="menu-item">
        <!-- Collapsed sidebar - show only icons with hover contextmenu -->
        <div v-if="collapsed" class="collapsed-menu-item">
          <div
            class="menu-icon-container"
            @mouseenter="showContextMenu(menu, $event)"
            @mouseleave="hideContextMenu"
          >
            <Link
              v-if="!menu.submenus || menu.submenus.length === 0"
              :href="getProperLink(menu.route)"
              class="flex items-center justify-center w-12 h-12 rounded-md transition-colors duration-200"
              :class="isCurrentRoute(menu.route)
                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
            >

              <BaseIcon
                v-if="menu.icon"
                :path="menu.icon"
                class="flex-none"
                :class="isCurrentRoute(menu.route) ? 'text-white' : 'text-slate-400'"
                w="w-5"
                :size="18"
              />
              <span v-else class="text-sm font-medium">{{ menu.name?.charAt(0) || '?' }}</span>
            </Link>
            <button
              v-else
              class="flex items-center justify-center w-12 h-12 rounded-md transition-colors duration-200"
              :class="isMenuActive(menu)
                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
            >
              <BaseIcon
                v-if="menu.icon"
                :path="menu.icon"
                class="flex-none"
                :class="isMenuActive(menu) ? 'text-white' : 'text-slate-400'"
                w="w-5"
                :size="18"
              />
              <span v-else class="text-sm font-medium">{{ menu.name?.charAt(0) || '?' }}</span>
            </button>
          </div>
        </div>

        <!-- Expanded sidebar - show full menu -->
        <div v-else>
          <!-- Menu with submenus -->
          <div v-if="menu.submenus && menu.submenus.length > 0" class="menu-with-submenu">
            <button
              @click="toggleSubmenu(menu.id)"
              class="menu-button flex items-center w-full px-4 py-3 text-left rounded-xl transition-colors duration-200"
              :class="isMenuActive(menu)
                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
              :aria-expanded="expandedMenus.has(menu.id) ? 'true' : 'false'"
              :aria-controls="`submenu-${menu.id}`"
              type="button"
            >
              <BaseIcon
                v-if="menu.icon"
                :path="menu.icon"
                class="flex-none mr-3"
                :class="isMenuActive(menu) ? 'text-white' : 'text-slate-400'"
                w="w-5"
                :size="18"
              />
              <span class="flex-1 font-medium">{{ menu.name }}</span>
              <span class="ml-auto inline-flex items-center justify-center w-6 h-6 rounded">
                <svg v-if="!expandedMenus.has(menu.id)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 opacity-70">
                  <path fill-rule="evenodd" d="M12 15a1 1 0 0 1-.707-.293l-5-5a1 1 0 0 1 1.414-1.414L12 12.586l4.293-4.293a1 1 0 1 1 1.414 1.414l-5 5A1 1 0 0 1 12 15z" clip-rule="evenodd" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 opacity-70">
                  <path fill-rule="evenodd" d="M12 9a1 1 0 0 1 .707.293l5 5a1 1 0 0 1-1.414 1.414L12 11.414l-4.293 4.293a1 1 0 1 1-1.414-1.414l5-5A1 1 0 0 1 12 9z" clip-rule="evenodd" />
                </svg>
              </span>
            </button>

            <ul v-if="expandedMenus.has(menu.id)" class="submenu pl-2 mt-2 space-y-1" :id="`submenu-${menu.id}`">
              <li v-for="submenu in menu.submenus" :key="submenu.id" class="submenu-item">
                <Link
                  :href="getProperLink(submenu.route)"
                  class="flex items-center px-4 py-2 text-sm rounded-lg transition-colors duration-200"
                  :class="isCurrentRoute(submenu.route)
                    ? 'text-white font-medium bg-slate-800'
                    : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                >
                  {{ submenu.name }}
                </Link>
              </li>
            </ul>
          </div>

          <!-- Menu without submenus -->
          <div v-else class="menu-without-submenu">
            <Link
              :href="getProperLink(menu.route)"
              class="flex items-center px-4 py-3 rounded-xl transition-colors duration-200"
              :class="isCurrentRoute(menu.route)
                ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20'
                : 'text-slate-400 hover:bg-slate-800 hover:text-white'"
            >
              <BaseIcon
                v-if="menu.icon"
                :path="menu.icon"
                class="flex-none mr-3"
                :class="isCurrentRoute(menu.route) ? 'text-white' : 'text-slate-400'"
                w="w-5"
                :size="18"
              />
              <span class="font-medium">{{ menu.name }}</span>
            </Link>
          </div>
        </div>
      </li>
    </ul>

    <!-- Context Menu for Collapsed Sidebar -->
    <Teleport to="body">
      <div
        v-if="hoveredMenu && collapsed"
        class="context-menu"
        :style="{
          position: 'fixed',
          left: contextMenuPosition.x + 'px',
          top: contextMenuPosition.y + 'px',
          zIndex: 9999
        }"
        @mouseenter="keepContextMenuOpen"
        @mouseleave="hideContextMenu"
      >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 min-w-48">
          <!-- Menu Title -->
          <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center">
              <BaseIcon
                v-if="hoveredMenu.icon"
                :path="hoveredMenu.icon"
                class="flex-none mr-2"
                :size="16"
              />
              <span class="font-medium text-gray-900 dark:text-white">{{ hoveredMenu.name }}</span>
            </div>
          </div>

          <!-- Menu without submenus -->
          <div v-if="!hoveredMenu.submenus || hoveredMenu.submenus.length === 0">
            <Link
              :href="getProperLink(hoveredMenu.route)"
              class="flex items-center px-4 py-2 text-sm rounded-lg mx-2 transition-colors duration-150"
              :class="isCurrentRoute(hoveredMenu.route)
                ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
            >
              <span>Go to {{ hoveredMenu.name }}</span>
            </Link>
          </div>

          <!-- Submenus -->
          <div v-else class="py-1">
            <Link
              v-for="submenu in hoveredMenu.submenus"
              :key="submenu.id"
              :href="getProperLink(submenu.route)"
              class="flex items-center px-4 py-2 text-sm rounded-lg mx-2 transition-colors duration-150"
              :class="isCurrentRoute(submenu.route)
                ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'
                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
            >
              <BaseIcon
                v-if="submenu.icon"
                :path="submenu.icon"
                class="flex-none mr-2"
                :size="14"
              />
              <span>{{ submenu.name }}</span>
            </Link>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.admin-menu {
  @apply w-full;
}

.menu-list {
  @apply space-y-1;
}

.menu-item {
  @apply mb-1;
}

.submenu {
  @apply space-y-1 transition-all duration-200 ease-in-out;
}

.collapsed-menu-item {
  @apply flex justify-center;
}

.menu-icon-container {
  @apply relative;
}

.context-menu {
  animation: fadeIn 0.15s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.menu-button,
.menu-without-submenu a,
.submenu-item a {
  @apply truncate;
  max-width: 100%;
}
</style>
