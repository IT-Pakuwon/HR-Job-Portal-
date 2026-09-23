export function sidebarMenuSearch(index = []) {
    return {
        index,
        query: '',
        open: false,

        get results() {
            const term = this.query.trim().toLowerCase();
            if (!term) return [];

            return this.index.filter(item =>
                item.menu_name.toLowerCase().includes(term) ||
                (item.parent_name || '').toLowerCase().includes(term)
            ).slice(0, 8);
        }
    };
}

export function sidebarFavourites(indexUrl, toggleUrl) {
    return {
        items: [],
        open: true,

        init() {
            this.load();
            window.addEventListener('favourites-changed', () => this.load());
        },

        async load() {
            try {
                const res = await fetch(indexUrl, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const { data = [] } = await res.json();
                this.items = data;
            } catch (e) {
                console.error('sidebar favourites load failed', e);
            }
        },

        async removeItem(item) {
            try {
                await window.axios.post(toggleUrl, {
                    screen_id: item.screen_id,
                    application_id: item.application_id
                });
                this.items = this.items.filter(i => i.id !== item.id);
                window.dispatchEvent(new CustomEvent('favourites-changed'));
            } catch (e) {
                console.error('sidebar remove favourite failed', e);
            }
        }
    };
}
