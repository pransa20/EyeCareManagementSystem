<?php
class ProductVariation {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function getVariations($product_id) {
        $stmt = $this->conn->prepare("SELECT * FROM product_variations WHERE product_id = ?");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAvailableFilters() {
        $filters = [
            'sizes' => $this->getUniqueValues('size'),
            'colors' => $this->getUniqueValues('color'),
            'frame_materials' => $this->getUniqueValues('frame_material'),
            'lens_materials' => $this->getUniqueValues('lens_material')
        ];
        return $filters;
    }

    private function getUniqueValues($column) {
        $sql = "SELECT DISTINCT $column FROM product_variations WHERE $column IS NOT NULL AND $column != ''";
        $result = $this->conn->query($sql);
        $values = [];
        while ($row = $result->fetch_assoc()) {
            $values[] = $row[$column];
        }
        return $values;
    }

    public function addVariation($product_id, $data) {
        $stmt = $this->conn->prepare("INSERT INTO product_variations (product_id, size, color, frame_material, lens_material) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $product_id, $data['size'], $data['color'], $data['frame_material'], $data['lens_material']);
        return $stmt->execute();
    }

    public function updateVariation($variation_id, $data) {
        $stmt = $this->conn->prepare("UPDATE product_variations SET size = ?, color = ?, frame_material = ?, lens_material = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $data['size'], $data['color'], $data['frame_material'], $data['lens_material'], $variation_id);
        return $stmt->execute();
    }

    public function deleteVariation($variation_id) {
        $stmt = $this->conn->prepare("DELETE FROM product_variations WHERE id = ?");
        $stmt->bind_param('i', $variation_id);
        return $stmt->execute();
    }
}