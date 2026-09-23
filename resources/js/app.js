import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

const initProductMasonry = () => {
    const galleries = document.querySelectorAll('.product-gallery');

    galleries.forEach(gallery => {
        gallery.classList.add('is-masonry');

        const layoutItems = () => {
            const styles = window.getComputedStyle(gallery);
            const rowHeight = Number.parseFloat(styles.gridAutoRows);
            const rowGap = Number.parseFloat(styles.rowGap);

            gallery.querySelectorAll('.product-gallery-item').forEach(item => {
                const content = item.firstElementChild;

                if (!content) {
                    return;
                }

                item.style.gridRowEnd = 'auto';
                const itemHeight = content.getBoundingClientRect().height;
                const rowSpan = Math.ceil((itemHeight + rowGap) / (rowHeight + rowGap));
                item.style.gridRowEnd = 'span ' + rowSpan;
            });
        };

        const scheduleLayout = () => window.requestAnimationFrame(layoutItems);

        gallery.querySelectorAll('img').forEach(image => {
            if (!image.complete) {
                image.addEventListener('load', scheduleLayout, { once: true });
            }
        });

        scheduleLayout();
        window.addEventListener('resize', scheduleLayout, { passive: true });
    });
};

document.addEventListener('DOMContentLoaded', initProductMasonry);
