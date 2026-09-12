<script>
  document.addEventListener('DOMContentLoaded', () => {
    const variantsList = document.getElementById('variants-list');
    const addVariantButton = document.getElementById('add-variant');

    if (!variantsList || !addVariantButton) {
      return;
    }

    addVariantButton.addEventListener('click', () => {
      const index = Number(variantsList.dataset.nextIndex);
      variantsList.dataset.nextIndex = index + 1;
      variantsList.insertAdjacentHTML('beforeend', `
        <div class="row g-2 align-items-start product-variant-row">
          <div class="col-md-4">
            <input class="form-control" name="variants[${index}][size]" type="text"
              placeholder="المقاس، مثل M" required>
          </div>
          <div class="col-md-4">
            <input class="form-control" name="variants[${index}][color]" type="text"
              placeholder="اللون، مثل أسود" required>
          </div>
          <div class="col-md-2">
            <input class="form-control" name="variants[${index}][stock]" type="number"
              min="0" value="0" aria-label="المخزون" required>
          </div>
          <div class="col-md-2 d-grid">
            <button class="btn btn-outline-danger remove-variant" type="button">حذف</button>
          </div>
        </div>
      `);
    });

    variantsList.addEventListener('click', (event) => {
      const removeButton = event.target.closest('.remove-variant');

      if (!removeButton || variantsList.querySelectorAll('.product-variant-row').length === 1) {
        return;
      }

      removeButton.closest('.product-variant-row')?.remove();
    });
  });
</script>
