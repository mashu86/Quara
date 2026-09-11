# Product Master size suggestions

Create and Edit Product include a magic button beside each size label, including dynamically added rows. It fills only that row's label. The result is editable and has an Undo action; stock and measurements are not changed. Saving uses the existing product form.

Type detection uses the current name first, then unambiguous selected categories. An explicit type selector overrides detection. Children's clothing, menswear, sarees, unstitched fabric, kaftans, oversized and free-size styles require a supplier label rather than an automatic guess.

Measurements default to full circumference in inches. Flat-width mode doubles chest and waist only for the suggestion, never length or stored values. Decimal values and inch suffixes are accepted. Empty required measurements, invalid values, ranges, conflicting proportions and unsupported sizes leave the original label intact.

The editable algorithm lives in `public/js/product-size-suggestion.js`:

- Regular-fit tops, kurtis and dresses start at XS: chest 34, waist 30, with two-inch steps through XXL. The larger chest/waist result is suggested, rounding intermediate measurements upward. 3XL–6XL extend that progression as an approximation.
- Bottoms use a numeric waist label, rounded upward to the next even inch, within 24–52 inches.
- Abayas use the nearest even length label within 48–62 inches; chest and waist still need supplier fit verification.
- Length does not determine an alpha size for regular tops/dresses or a waist label for bottoms.

These are approximate defaults, not a universal or brand-specific guarantee. The regular-fit starting chart is based on [Siaraa's garment measurements](https://www.siaraa.in/size-guide); different cuts on that same page have different charts. The abaya length convention is illustrated by [Awrah's sizing guide](https://www.awrahabayas.ae/pages/abaya-sizing-guide). Replace defaults with the shop's own supplier chart when available.

Run algorithm tests with `node --test tests/JavaScript/product-size-suggestion.test.js`.
