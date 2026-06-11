<?php
/**
 * Shop Type Model
 * Handles database operations for shop types
 */

class ShopTypeModel {
    private $db;
    
    /**
     * Constructor
     * @param object $db Database connection object
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all active shop types
     * @return array Array of shop type records
     */
    public function getActiveShopTypes() {
        try {
            $query = 'SELECT * FROM `tb_shopType` WHERE status = ? ORDER BY name ASC';
            $result = $this->db->query($query, 1)->fetchAll();
            return $result;
        } catch (Exception $e) {
            error_log('ShopTypeModel Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get shop type by ID
     * @param int $id Shop type ID
     * @return array|null Shop type record or null if not found
     */
    public function getById($id) {
        try {
            $query = 'SELECT * FROM `tb_shopType` WHERE id = ? AND status = ?';
            $result = $this->db->query($query, [$id, 1])->fetch();
            return $result;
        } catch (Exception $e) {
            error_log('ShopTypeModel Error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get shop types as options for HTML select element
     * @return string HTML options string
     */
    public function getOptionsHtml() {
        $shopTypes = $this->getActiveShopTypes();
        $html = '<option value="">-- Select Shop Type --</option>';
        
        foreach ($shopTypes as $row) {
            $html .= sprintf(
                '<option value="%d">%s</option>',
                htmlspecialchars($row['id']),
                htmlspecialchars($row['name'])
            );
        }
        
        return $html;
    }
}
