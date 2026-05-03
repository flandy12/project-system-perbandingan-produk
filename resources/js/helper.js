export default function createCompareApp() {
    return {
        active: 'top',
        modalOpen: false,
        selectedProduct: null,
        items: [],
        redirecting: false,

        activeClass: 'bg-sky-100 text-sky-700',
        defaultClass: 'border',

        init() {
            try {
                const saved = localStorage.getItem("compare_items");
                const parsed = saved ? JSON.parse(saved) : [];
                this.items = Array.isArray(parsed) ? parsed : [];
            } catch {
                this.items = [];
            }

            // sync antar tab
            window.addEventListener("storage", (e) => {
                if (e.key === "compare_items") {
                    this.items = JSON.parse(e.newValue || "[]");
                }
            });

            this.initScrollSpy();
        },

        toggle(id) {
            if (!id) return;
            id = String(id);

            if (this.items.includes(id)) {
                this.items = this.items.filter(i => i !== id);
                this.save();
                return;
            }

            if (this.items.length >= 2) {
                alert("Maksimal 2 produk");
                return;
            }

            this.items.push(id);
            this.save();

            if (this.items.length === 2 && !this.redirecting) {
                this.redirecting = true;
                setTimeout(() => this.redirect(), 300);
            }
        },

        save() {
            localStorage.setItem("compare_items", JSON.stringify(this.items));
        },

        redirect() {
            window.location.href = `/compare?ids=${this.items.join(",")}`;
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
            if (!price) return 'Rp 0';

            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(Number(price));
        },

        scrollTo(id, state) {
            const el = document.getElementById(id);
            if (!el) return;

            this.active = state;

            el.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        },

        initScrollSpy() {
            const sections = [
                { id: 'top-penjualan', key: 'top' },
                { id: 'rekomendasi', key: 'rekomendasi' },
                { id: 'discount', key: 'discount' },
            ];

            const handler = () => {
                let current = 'top';

                sections.forEach(section => {
                    const el = document.getElementById(section.id);
                    if (!el) return;

                    const rect = el.getBoundingClientRect();

                    if (rect.top <= 120) {
                        current = section.key;
                    }
                });

                this.active = current;
            };

            window.addEventListener('scroll', handler, { passive: true });
        }
    }
}