export default function createCompareApp() {
    return {
        active: "top",
        modalOpen: false,
        selectedProduct: null,
        items: [],
        redirecting: false,

        activeClass: "bg-sky-100 text-sky-700",
        defaultClass: "border",

        init() {
            if (window.location.pathname === "/compare") {
                localStorage.removeItem("compare_items");
                this.items = [];
                return;
            }

            try {
                const saved = localStorage.getItem("compare_items");
                const parsed = saved ? JSON.parse(saved) : [];
                this.items = this.normalizeItems(parsed);
            } catch {
                this.items = [];
            }

            window.addEventListener("storage", (e) => {
                if (e.key === "compare_items") {
                    try { this.items = this.normalizeItems(JSON.parse(e.newValue || "[]")); }
                    catch { this.items = []; }
                }
            });

            // A completed pair is never a pending selection, including on browser Back.
            window.addEventListener("pageshow", () => {
                this.redirecting = false;
                try { this.items = this.normalizeItems(JSON.parse(localStorage.getItem("compare_items") || "[]")); }
                catch { this.items = []; }
                this.save();
            });
            this.save();
            this.initScrollSpy();
        },
        normalizeItems(value) {
            if (!Array.isArray(value)) return [];
            const ids = [...new Set(value.filter(id => /^\d+$/.test(String(id)) && Number(id) > 0).map(String))];
            return ids.length < 2 ? ids : [];
        },

        clearSelection() {
            this.items = [];
            this.redirecting = false;
            this.save();
        },

        toggle(id) {
            if (!id) return;
            id = String(id);

            if (this.items.includes(id)) {
                this.items = this.items.filter((i) => i !== id);
                this.save();
                return;
            }

            if (this.items.length >= 2) {
                this.clearSelection();
            }

            this.items.push(id);
            this.save();

            if (this.items.length === 2 && !this.redirecting) {
                this.redirecting = true;
                this.redirect();
            }
        },

        save() {
            localStorage.setItem("compare_items", JSON.stringify(this.items));
        },

        redirect() {
            const ids = this.items.join(",");

            localStorage.removeItem("compare_items");
            this.items = [];

            window.location.href = `/compare?ids=${ids}`;
        },

        isSelected(id) {
            if (!id || !Array.isArray(this.items)) return false;
            return this.items.includes(String(id));
        },

        setProduct(product) {
            this.selectedProduct = product;
            this.modalOpen = true;
        },

        formatPrice(price) {
            if (price === null || price === undefined || price === "") return "Harga belum tersedia";

            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
            }).format(Number(price));
        },

        scrollTo(id, state) {
            const el = document.getElementById(id);
            if (!el) return;

            this.active = state;

            el.scrollIntoView({
                behavior: "smooth",
                block: "start",
            });
        },

        initScrollSpy() {
            const sections = [
                { id: "top-penjualan", key: "top" },
                { id: "rekomendasi", key: "rekomendasi" },
                { id: "discount", key: "discount" },
            ];

            const handler = () => {
                let current = "top";

                sections.forEach((section) => {
                    const el = document.getElementById(section.id);
                    if (!el) return;

                    const rect = el.getBoundingClientRect();

                    if (rect.top <= 120) {
                        current = section.key;
                    }
                });

                this.active = current;
            };

            window.addEventListener("scroll", handler, { passive: true });
        },
    };
}
