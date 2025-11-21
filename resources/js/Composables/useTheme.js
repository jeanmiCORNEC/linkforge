import { ref, onMounted, watch } from 'vue';

const theme = ref(localStorage.getItem('theme') || 'system');

export function useTheme() {
    const applyTheme = () => {
        const isDark =
            theme.value === 'dark' ||
            (theme.value === 'system' &&
                window.matchMedia('(prefers-color-scheme: dark)').matches);

        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    const setTheme = (newTheme) => {
        theme.value = newTheme;
        localStorage.setItem('theme', newTheme);
        applyTheme();
    };

    onMounted(() => {
        applyTheme();

        // Listen for system changes if in system mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (theme.value === 'system') {
                applyTheme();
            }
        });
    });

    return {
        theme,
        setTheme,
    };
}
