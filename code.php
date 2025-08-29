/**
 * WooCommerce: Use Long Description in the Short Description Spot (+ optional search tweak)
 * Theme-agnostic. Tested with Astra + Spectra.
 *
 * - Replaces the single product summary "short description" with the main content.
 * - Optional: make product search results use trimmed main content instead of short description.
 *
 * Author: jsohn data / ChatGPT (GPT-5)
 */
declare(strict_types=1);

if (!defined('ABSPATH')) { exit; }

// =====================
// CONFIG (edit freely)
// =====================
const SANSE_SWAP_ENABLE                 = true;     // Master switch
const SANSE_REPLACE_PRIORITY            = 20;       // Where to inject long description (matches Woo short desc default)
const SANSE_REMOVE_DESC_TAB             = true;     // Remove the "Description" tab to avoid duplicate content
const SANSE_WRAP_CLASS                  = 'sanse-main-desc-substitute'; // Wrapper class for styling

// Search page behavior (optional)
const SANSE_SEARCH_USE_CONTENT_EXCERPT  = false;    // true = search results use long description snippet
const SANSE_SEARCH_EXCERPT_WORDS        = 28;       // words to show in search excerpt

// =====================
// HOOKS
// =====================
add_action('wp', 'sanse_setup_product_description_swap');
function sanse_setup_product_description_swap(): void {
	if (!SANSE_SWAP_ENABLE || !function_exists('is_product')) {
		return;
	}

	// Remove short description from summary area
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);

	// Add long description in its place
	add_action('woocommerce_single_product_summary', 'sanse_output_main_description_in_summary', SANSE_REPLACE_PRIORITY);

	// Optionally remove the Description tab to prevent repetition lower on the page
	if (SANSE_REMOVE_DESC_TAB) {
		add_filter('woocommerce_product_tabs', 'sanse_remove_description_tab', 98);
	}
}

/**
 * Output the product's main content (long description) in the summary area.
 * Falls back to short description if long description is empty.
 */
function sanse_output_main_description_in_summary(): void {
	if (!function_exists('is_product') || !is_product()) {
		return;
	}

	global $post;
	if (!$post instanceof \WP_Post) {
		return;
	}

	$long = (string) apply_filters('the_content', (string) $post->post_content);

	// If no long description, gracefully fallback to short description to avoid an empty block.
	if ('' === trim(wp_strip_all_tags($long))) {
		if (function_exists('woocommerce_template_single_excerpt')) {
			woocommerce_template_single_excerpt();
		}
		return;
	}

	printf('<div class="%s">%s</div>', esc_attr(SANSE_WRAP_CLASS), $long);
}

/** Remove the "Description" tab (prevents duplicated content below). */
function sanse_remove_description_tab(array $tabs): array {
	unset($tabs['description']);
	return $tabs;
}

// =====================
// OPTIONAL: Search page uses main content excerpt for products
// =====================
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
		return $excerpt; // Respect existing excerpt if no content
	}

	return wp_trim_words($content, SANSE_SEARCH_EXCERPT_WORDS, '…');
}
