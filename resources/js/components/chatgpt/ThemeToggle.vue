<template>
  <button
    @click="toggleTheme"
    class="theme-toggle"
    :title="isDark ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro'"
  >
    <!-- Ícono SOL -->
    <svg v-if="isDark" width="20" height="20" viewBox="0 0 24 24" fill="none">
      <circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/>
      <path stroke="currentColor" stroke-width="2" d="M12 1v3M12 20v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M1 12h3M20 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>
    </svg>

    <!-- Ícono LUNA -->
    <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none">
      <path
        stroke="currentColor"
        stroke-width="2"
        d="M21 12.79A9 9 0 0 1 11.21 3 7 7 0 1 0 21 12.79z"
      />
    </svg>
  </button>
</template>

<script>
export default {
  name: "ThemeToggle",

  props: {
    isDark: {
      type: Boolean,
      required: true
    }
  },

  methods: {
    toggleTheme() {
      this.$emit("toggle-theme");
    }
  }
};
</script>

<style scoped>
.theme-toggle {
  background: transparent;
  border: 1px solid var(--border-color);
  color: var(--color-text-primary);
  padding: var(--spacing-md);
  border-radius: var(--border-radius-md);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all var(--transition-base);
  width: 44px;
  height: 44px;
}

.theme-toggle:hover {
  background: var(--color-bg-quaternary);
  border-color: var(--color-accent-primary);
  color: var(--color-accent-primary);
  transform: rotate(20deg);
}

.theme-toggle:active {
  transform: rotate(0deg) scale(0.95);
}
</style>
