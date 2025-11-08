<?php

class ProductFilter {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getRecommendedProducts($product_id, $limit = 4) {
        // Get the current product details
        $product = $this->getProductById($product_id);
        if (!$product) {
            return [];
        }
        
        // Get all products in the same category
        $sql = "SELECT * FROM products WHERE id != " . (int)$product_id . " AND category = '" . 
               $this->conn->real_escape_string($product['category']) . "' LIMIT " . (int)$limit;
        $result = $this->conn->query($sql);
        
        $recommendations = [];
        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }
        
        return $recommendations;
    }
    
    public function getProductById($product_id) {
        $sql = "SELECT * FROM products WHERE id = " . (int)$product_id;
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }

    public function getFilteredProducts($filters, $items_per_page, $offset) {
        // Get all categories with count
        $categories = [];
        $sql = "SELECT category, COUNT(*) as count FROM products GROUP BY category ORDER BY category";
        $result = $this->conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        // Extract filters
        $min_price = $filters['min_price'] ?? null;
        $max_price = $filters['max_price'] ?? null;
        $category_filter = $filters['category'] ?? null;
        $search_query = $filters['search'] ?? null;
        $size_filter = $filters['size'] ?? null;
        $color_filter = $filters['color'] ?? null;
        $frame_material_filter = $filters['frame_material'] ?? null;
        $lens_material_filter = $filters['lens_material'] ?? null;
        $sort_by = $filters['sort_by'] ?? 'newest';

        // Base query
        $sql = "SELECT p.*, COALESCE(pi.image_path, p.image_path) as primary_image_path, p.stock as stock_quantity FROM products p LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = TRUE LEFT JOIN product_variations pv ON p.id = pv.product_id WHERE 1=1 AND p.stock > 0";

        // Apply filters
        if ($category_filter) {
            $sql .= " AND category = '" . $this->conn->real_escape_string($category_filter) . "'";
        }
        if ($search_query) {
            $sql .= " AND (name LIKE '%" . $this->conn->real_escape_string($search_query) . "%' OR description LIKE '%" . $this->conn->real_escape_string($search_query) . "%')";
        }
        if ($size_filter) {
            $sql .= " AND pv.size = '" . $this->conn->real_escape_string($size_filter) . "'";
        }
        if ($color_filter) {
            $sql .= " AND pv.color = '" . $this->conn->real_escape_string($color_filter) . "'";
        }
        $frame_material_filter = $filters['frame_material'] ?? null;
if ($frame_material_filter) {
            $sql .= " AND pv.frame_material = '" . $this->conn->real_escape_string($frame_material_filter) . "'";
        }
        $lens_material_filter = $filters['lens_material'] ?? null;
if ($lens_material_filter) {
            $sql .= " AND pv.lens_material = '" . $this->conn->real_escape_string($lens_material_filter) . "'";
        }
        $min_price = $filters['min_price'] ?? null;
if ($min_price !== null && $min_price !== '') {
            $sql .= " AND p.price >= " . (float)$min_price;
        }
        $max_price = $filters['max_price'] ?? null;
if ($max_price !== null && $max_price !== '') {
            $sql .= " AND p.price <= " . (float)$max_price;
        }

        // Apply sorting
        switch ($sort_by) {
            case 'price_low':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_high':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'oldest':
                $sql .= " ORDER BY created_at ASC";
                break;
            default:
                $sql .= " ORDER BY created_at DESC";
        }

        // Get total count for pagination
        $count_sql = "SELECT COUNT(*) as total FROM (" . $sql . ") as count_table";
        $count_result = $this->conn->query($count_sql);
        $total_products = $count_result->fetch_assoc()['total'];
        $total_pages = ceil($total_products / $items_per_page);

        // Add pagination
        $sql .= " LIMIT $items_per_page OFFSET $offset";

        // Execute query
        $result = $this->conn->query($sql);
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        // Get all categories with count
        $categories = [];
        $sql = "SELECT category, COUNT(*) as count FROM products GROUP BY category ORDER BY category";
        $result = $this->conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }

        return [
            'products' => $products,
            'total_products' => $total_products,
        ];
    }

    public function getTotalProducts($filters) {
        // Extract filters
        $category_filter = $filters['category'] ?? null;
        $search_query = $filters['search'] ?? null;

        // Base query
        $sql = "SELECT COUNT(*) as total FROM products p
               LEFT JOIN product_variations pv ON p.id = pv.product_id
               WHERE 1=1 AND p.stock > 0";

        // Apply filters
        if ($category_filter) {
            $sql .= " AND category = '" . $this->conn->real_escape_string($category_filter) . "'";
        }
        if ($search_query) {
            $sql .= " AND (name LIKE '%" . $this->conn->real_escape_string($search_query) . "%' OR description LIKE '%" . $this->conn->real_escape_string($search_query) . "%')";
        }
        $frame_material_filter = $filters['frame_material'] ?? null;
if ($frame_material_filter) {
            $sql .= " AND pv.frame_material = '" . $this->conn->real_escape_string($frame_material_filter) . "'";
        }
        $lens_material_filter = $filters['lens_material'] ?? null;
if ($lens_material_filter) {
            $sql .= " AND pv.lens_material = '" . $this->conn->real_escape_string($lens_material_filter) . "'";
        }
        $min_price = $filters['min_price'] ?? null;
if ($min_price !== null && $min_price !== '') {
            $sql .= " AND p.price >= " . (float)$min_price;
        }
        $max_price = $filters['max_price'] ?? null;
if ($max_price !== null && $max_price !== '') {
            $sql .= " AND p.price <= " . (float)$max_price;
        }

        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['total'];
    }


}