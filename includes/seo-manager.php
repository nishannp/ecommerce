<?php
// File: includes/seo-manager.php

/**
 * SEO Manager Class for Umesh Cycle Store
 * Handles dynamic SEO content for different pages
 */
class SEOManager {
    // Default SEO values
    private $defaults = [
        'title' => 'Umesh Cycle Store | Premium Bicycles & Accessories in Birendrangar, Surkhet',
        'description' => 'Established in 2004, Umesh Cycle Store offers high-quality bicycles, parts, and accessories in Birendrangar, Surkhet. Visit our store for expert service and the best cycling products.',
        'keywords' => 'cycles, bicycles, mountain bikes, cycling accessories, bike parts, cycle repair, birendrangar, surkhet, umesh cycle store, bike shop',
        'og_image' => 'https://umeshcycle.rf.gd/imgs/store-front.png',
        'canonical' => '',
        'page_specific_schema' => ''
    ];
    
    // SEO data for the current page
    private $seo_data = [];
    
    /**
     * Constructor - initializes with default values
     */
    public function __construct() {
        $this->seo_data = $this->defaults;
        $this->seo_data['canonical'] = $this->getCurrentURL();
    }
    
    /**
     * Get the current page URL for canonical tag
     */
    private function getCurrentURL() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        return $protocol . $host . $uri;
    }
    
    /**
     * Set a single SEO value
     */
    public function set($key, $value) {
        if (array_key_exists($key, $this->seo_data)) {
            $this->seo_data[$key] = $value;
        }
        return $this;
    }
    
    /**
     * Set multiple SEO values at once
     */
    public function setMultiple($data) {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }
    
    /**
     * Get a specific SEO value
     */
    public function get($key) {
        return isset($this->seo_data[$key]) ? $this->seo_data[$key] : null;
    }
    
    /**
     * Generate all SEO meta tags
     */
    public function generateMetaTags() {
        $output = '';
        
        // Basic meta tags
        $output .= '<title>' . htmlspecialchars($this->seo_data['title']) . '</title>' . "\n";
        $output .= '    <meta name="title" content="' . htmlspecialchars($this->seo_data['title']) . '">' . "\n";
        $output .= '    <meta name="description" content="' . htmlspecialchars($this->seo_data['description']) . '">' . "\n";
        $output .= '    <meta name="keywords" content="' . htmlspecialchars($this->seo_data['keywords']) . '">' . "\n";
        
        // Canonical URL
        $output .= '    <link rel="canonical" href="' . htmlspecialchars($this->seo_data['canonical']) . '">' . "\n";
        
        // Open Graph meta tags
        $output .= '    <meta property="og:type" content="website">' . "\n";
        $output .= '    <meta property="og:url" content="' . htmlspecialchars($this->seo_data['canonical']) . '">' . "\n";
        $output .= '    <meta property="og:title" content="' . htmlspecialchars($this->seo_data['title']) . '">' . "\n";
        $output .= '    <meta property="og:description" content="' . htmlspecialchars($this->seo_data['description']) . '">' . "\n";
        $output .= '    <meta property="og:image" content="' . htmlspecialchars($this->seo_data['og_image']) . '">' . "\n";
        
        // Twitter meta tags
        $output .= '    <meta property="twitter:card" content="summary_large_image">' . "\n";
        $output .= '    <meta property="twitter:url" content="' . htmlspecialchars($this->seo_data['canonical']) . '">' . "\n";
        $output .= '    <meta property="twitter:title" content="' . htmlspecialchars($this->seo_data['title']) . '">' . "\n";
        $output .= '    <meta property="twitter:description" content="' . htmlspecialchars($this->seo_data['description']) . '">' . "\n";
        $output .= '    <meta property="twitter:image" content="' . htmlspecialchars($this->seo_data['og_image']) . '">' . "\n";
        
        // Add page-specific Schema.org JSON-LD if available
        if (!empty($this->seo_data['page_specific_schema'])) {
            $output .= $this->seo_data['page_specific_schema'] . "\n";
        }
        
        return $output;
    }
    
    /**
     * Generate Schema.org JSON-LD for product pages
     */
    public function generateProductSchema($product) {
        $schema = '    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "' . htmlspecialchars($product['name']) . '",
      "image": "' . htmlspecialchars($product['image_url']) . '",
      "description": "' . htmlspecialchars($product['description']) . '",
      "brand": {
        "@type": "Brand",
        "name": "' . htmlspecialchars($product['brand']) . '"
      },';
      
        if (isset($product['price']) && $product['price'] > 0) {
            $schema .= '
      "offers": {
        "@type": "Offer",
        "url": "' . htmlspecialchars($this->getCurrentURL()) . '",
        "priceCurrency": "NPR",
        "price": "' . htmlspecialchars($product['price']) . '",
        "availability": "https://schema.org/' . (($product['in_stock']) ? 'InStock' : 'OutOfStock') . '",
        "seller": {
          "@type": "Organization",
          "name": "Umesh Cycle Store"
        }
      }';
        }
      
        $schema .= '
    }
    </script>';
        
        return $schema;
    }
    
    /**
     * Generate Schema.org JSON-LD for category pages
     */
    public function generateCategorySchema($category) {
        $schema = '    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "CollectionPage",
      "name": "' . htmlspecialchars($category['name']) . ' - Umesh Cycle Store",
      "description": "' . htmlspecialchars($category['description']) . '",
      "url": "' . htmlspecialchars($this->getCurrentURL()) . '"
    }
    </script>';
        
        return $schema;
    }
}


// Create global SEO manager instance
$seoManager = new SEOManager();

?>