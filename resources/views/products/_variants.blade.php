@php
  $nextVariantIndex = empty($variantRows) ? 0 : max(array_keys($variantRows)) + 1;
@endphp

<div class="col-12">
  <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
    <div>
      <label class="form-label fw-semibold mb-0">المقاسات والألوان والمخزون</label>
      <div class="form-text">أضف سطرًا لكل تركيبة متاحة من المقاس واللون.</div>
    </div>
    <button class="btn btn-outline-primary" id="add-variant" type="button">إضافة اختيار</button>
  </div>

  <div class="d-grid gap-2" id="variants-list" data-next-index="{{ $nextVariantIndex }}">
    @foreach ($variantRows as $index => $variant)
      <div class="row g-2 align-items-start product-variant-row">
        <div class="col-md-4">
          <input class="form-control @error("variants.$index.size") is-invalid @enderror"
            name="variants[{{ $index }}][size]" type="text" value="{{ $variant['size'] ?? '' }}"
            placeholder="المقاس، مثل M" required>
        </div>
        <div class="col-md-4">
          <input class="form-control @error("variants.$index.color") is-invalid @enderror"
            name="variants[{{ $index }}][color]" type="text" value="{{ $variant['color'] ?? '' }}"
            placeholder="اللون، مثل أسود" required>
        </div>
        <div class="col-md-2">
          <input class="form-control @error("variants.$index.stock") is-invalid @enderror"
            name="variants[{{ $index }}][stock]" type="number" min="0"
            value="{{ $variant['stock'] ?? 0 }}" aria-label="المخزون" required>
        </div>
        <div class="col-md-2 d-grid">
          <button class="btn btn-outline-danger remove-variant" type="button">حذف</button>
        </div>
      </div>
    @endforeach
  </div>
</div>
