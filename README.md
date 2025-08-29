# WooCommerce: Swap Short Description with Main Description — WPCode-Compatible

🤖 **AI-Enhanced (GPT-5)** snippet that **replaces the WooCommerce short description with the full main product description** in the single product summary.  

- On **product pages**: swaps the short description area with the main content.  
- Optionally **removes the duplicate “Description” tab** below the product.  
- Can also **use trimmed main content** for **search result excerpts** instead of the short description.  

Clean, lightweight, and compatible with **WPCode**, **Code Snippets**, or a child theme’s `functions.php`.  

---

## Working Idea  
Highlight the **full story and details** of your product up top, instead of relying on the limited short description.  

---

## Features  

- 🔄 **Swaps short description** for the long (main) description in the summary area  
- 🚫 **Optional tab removal**: hides the default “Description” tab to avoid duplication  
- 🔍 **Search result tweak**: make search excerpts use main content instead of short desc  
- ⚙️ **Configurable constants** for headings, classes, and behavior at the top of the file  
- 🧩 **Theme-agnostic**: works with Astra, Spectra, and any WooCommerce-friendly theme  
- 🧼 **Minimal markup** for styling flexibility  
- 🧠 **Functional-style helpers** and early returns for clarity  

---

## Requirements  

- WordPress  
- WooCommerce (active)  
- One of the following for installation:  
  - [WPCode plugin](https://wordpress.org/plugins/wpcode/) (recommended)  
  - Code Snippets plugin  
  - A child theme’s `functions.php`  

---

## Installation  

### Option 1: WPCode (Recommended)  
1. Install and activate **WPCode**.  
2. Go to **Code Snippets → Add New**.  
3. Choose **“Add Your Custom Code (New Snippet)”** → **PHP Snippet**.  
4. Name it: `Woo Main Description Swap`.  
5. Paste the code from the **Code** section below.  
6. Set **Location** to `Run Everywhere`.  
7. Save and **Activate**.  

### Option 2: Add to `functions.php`  
1. Open your child theme’s `functions.php`.  
2. Paste the PHP code at the end.  
3. Save the file.  

---

## Customization  

- **Toggle features**: Edit constants like `SANSE_REMOVE_DESC_TAB` or `SANSE_SEARCH_USE_CONTENT_EXCERPT`.  
- **Adjust excerpt length**: Change `SANSE_SEARCH_EXCERPT_WORDS`.  
- **Style wrapper**: Target `.sanse-main-desc-substitute` in CSS.  

> Tip: Keep search excerpts concise for best UX and SEO.  

---

## Code  

```php
<?php
/**
 * WooCommerce: Use Long Description in the Short Description Spot (+ optional search tweak)
 * Theme-agnostic. Tested with Astra + Spectra.
 *
 * - Replaces the single product summary "short description" with the main content.
 * - Optional: remove duplicate “Description” tab.
 * - Optional: swap search excerpts to use trimmed main content.
 *
 * Author: jsohn data / ChatGPT (GPT-5)
 */
declare(strict_types=1);

if (!defined('ABSPATH')) { exit; }

// =====================
// CONFIG (edit freely)
// =====================
const SANSE_SWAP_ENABLE                 = true;
const SANSE_REPLACE_PRIORITY            = 20;
const SANSE_REMOVE_DESC_TAB             = true;
const SANSE_WRAP_CLASS                  = 'sanse-main-desc-substitute';

// Search page behavior (optional)
const SANSE_SEARCH_USE_CONTENT_EXCERPT  = false;
const SANSE_SEARCH_EXCERPT_WORDS        = 28;

// =====================
// HOOKS
// =====================
add_action('wp', 'sanse_setup_product_description_swap');
function sanse_setup_product_description_swap(): void {
    if (!SANSE_SWAP_ENABLE || !function_exists('is_product')) {
        return;
    }

    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
    add_action('woocommerce_single_product_summary', 'sanse_output_main_description_in_summary', SANSE_REPLACE_PRIORITY);

    if (SANSE_REMOVE_DESC_TAB) {
        add_filter('woocommerce_product_tabs', 'sanse_remove_description_tab', 98);
    }
}

function sanse_output_main_description_in_summary(): void {
    if (!function_exists('is_product') || !is_product()) {
        return;
    }

    global $post;
    if (!$post instanceof \WP_Post) {
        return;
    }

    $long = (string) apply_filters('the_content', (string) $post->post_content);
    if ('' === trim(wp_strip_all_tags($long))) {
        if (function_exists('woocommerce_template_single_excerpt')) {
            woocommerce_template_single_excerpt();
        }
        return;
    }

    printf('<div class="%s">%s</div>', esc_attr(SANSE_WRAP_CLASS), $long);
}

function sanse_remove_description_tab(array $tabs): array {
    unset($tabs['description']);
    return $tabs;
}

add_filter('get_the_excerpt', 'sanse_product_search_excerpt_from_content', 10, 2);
function sanse_product_search_excerpt_from_content(string $excerpt, \WP_Post $post): string {
    if (
        !SANSE_SEARCH_USE_CONTENT_EXCERPT ||
        is_admin() ||
        !is_search() ||
        'product' !== $post->post_type
    ) {
        return $excerpt;
    }

    $content = wp_strip_all_tags( (string) apply_filters('the_content', (string) $post->post_content) );
    $content = trim(preg_replace('/\s+/', ' ', $content));
    if ($content === '') {
        return $excerpt;
    }

    return wp_trim_words($content, SANSE_SEARCH_EXCERPT_WORDS, '…');
}
```
---

## FAQs  

**Will this remove my existing short descriptions?**  
No — it just swaps display. Short descriptions remain stored in the database.  

**Can I restore the Description tab?**  
Yes — set `SANSE_REMOVE_DESC_TAB` to `false`.  

**Does this impact SEO?**  
It shouldn’t negatively. Search engines will index the full description more prominently.  

---

## Changelog  

- **1.0.0** — Initial release: swaps short description with main description, optional tab removal, optional search tweak.  
