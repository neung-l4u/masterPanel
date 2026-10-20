/**
 * invoiceItemsEditor.js
 * 
 * จัดการแก้ไข items ใน invoice table
 * และอัพเดท thBathIn ผ่าน API
 */

class InvoiceItemsEditor {
    constructor(tableSelector, invoiceId) {
        this.tableSelector = tableSelector;
        this.invoiceId = invoiceId;
        this.$table = $(tableSelector);
        this.items = [];
        this.init();
    }
    
    init() {
        this.loadItems();
        this.attachEventHandlers();
    }
    
    loadItems() {
        // อ่าน items จากตาราง
        this.items = [];
        const rows = this.$table.find('tbody tr');
        
        rows.each((idx, row) => {
            const $row = $(row);
            const productInput = $row.find('input[data-field="product"]');
            const qytInput = $row.find('input[data-field="qyt"]');
            const amountInput = $row.find('input[data-field="amount"]');
            
            if (productInput.length) {
                this.items.push({
                    product: productInput.val() || '',
                    qyt: qytInput.val() || '1',
                    amount: parseFloat(amountInput.val() || 0)
                });
            }
        });
    }
    
    attachEventHandlers() {
        // Edit button
        this.$table.on('click', '.btn-edit-item', (e) => {
            const $btn = $(e.currentTarget);
            const $row = $btn.closest('tr');
            this.enableRowEdit($row);
        });
        
        // Save button
        this.$table.on('click', '.btn-save-item', (e) => {
            const $btn = $(e.currentTarget);
            const $row = $btn.closest('tr');
            this.saveRowEdit($row);
        });
        
        // Cancel button
        this.$table.on('click', '.btn-cancel-item', (e) => {
            const $btn = $(e.currentTarget);
            const $row = $btn.closest('tr');
            this.cancelRowEdit($row);
        });
        
        // Delete button
        this.$table.on('click', '.btn-delete-item', (e) => {
            const $btn = $(e.currentTarget);
            const $row = $btn.closest('tr');
            if (confirm('ต้องการลบรายการนี้หรือไม่?')) {
                $row.remove();
                this.updateInvoice();
            }
        });
    }
    
    enableRowEdit($row) {
        $row.find('.item-display').hide();
        $row.find('.item-edit').show();
        $row.find('.btn-edit-item').hide();
        $row.find('.btn-save-item, .btn-cancel-item').show();
    }
    
    cancelRowEdit($row) {
        $row.find('.item-edit').hide();
        $row.find('.item-display').show();
        $row.find('.btn-save-item, .btn-cancel-item').hide();
        $row.find('.btn-edit-item').show();
    }
    
    saveRowEdit($row) {
        const product = $row.find('input[data-field="product"]').val();
        const qyt = $row.find('input[data-field="qyt"]').val();
        const amount = parseFloat($row.find('input[data-field="amount"]').val() || 0);
        
        if (!product || !qyt || amount <= 0) {
            alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            return;
        }
        
        // Update display
        $row.find('.product-display').text(product);
        $row.find('.qyt-display').text(qyt);
        $row.find('.amount-display').text('฿' + amount.toLocaleString('th-TH', {minimumFractionDigits: 2}));
        
        this.cancelRowEdit($row);
        this.updateInvoice();
    }
    
    updateInvoice() {
        this.loadItems();
        
        if (this.items.length === 0) {
            alert('ต้องมีอย่างน้อย 1 รายการ');
            return;
        }
        
        // Call API to update invoice
        $.ajax({
            url: 'api/invoice/updateInvoiceItems.php',
            type: 'POST',
            dataType: 'json',
            data: {
                invoice_id: this.invoiceId,
                items: JSON.stringify(this.items)
            },
            success: (res) => {
                if (res.success) {
                    console.log('Invoice updated:', res.data);
                    // Update total display if exists
                    const totalElement = $('[data-invoice-total]');
                    if (totalElement.length) {
                        totalElement.text('฿' + res.data.total_amount.toLocaleString('th-TH', {minimumFractionDigits: 2}));
                    }
                    
                    // Update Thai text display if exists
                    const thaiTextElement = $('[data-invoice-thai-text]');
                    if (thaiTextElement.length) {
                        thaiTextElement.text(res.data.thai_text);
                    }
                    
                    alert('บันทึกข้อมูลสำเร็จ');
                } else {
                    alert('เกิดข้อผิดพลาด: ' + res.message);
                }
            },
            error: (xhr) => {
                console.error('Error:', xhr);
                alert('เกิดข้อผิดพลาดในการบันทึก');
            }
        });
    }
    
    addNewItem() {
        const newRow = `
            <tr>
                <td>
                    <div class="item-display">-</div>
                    <input type="text" class="form-control form-control-sm item-edit" data-field="product" style="display:none;" placeholder="ชื่อสินค้า">
                </td>
                <td class="text-center">
                    <div class="item-display">1</div>
                    <input type="number" class="form-control form-control-sm item-edit" data-field="qyt" style="display:none;" value="1" min="1">
                </td>
                <td class="text-right">
                    <div class="item-display">฿0.00</div>
                    <input type="number" class="form-control form-control-sm item-edit" data-field="amount" style="display:none;" value="0" step="0.01" min="0">
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-warning btn-edit-item" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-success btn-save-item" style="display:none;" title="บันทึก"><i class="bi bi-check"></i></button>
                    <button class="btn btn-sm btn-secondary btn-cancel-item" style="display:none;" title="ยกเลิก"><i class="bi bi-x"></i></button>
                    <button class="btn btn-sm btn-danger btn-delete-item" title="ลบ"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;
        
        this.$table.find('tbody').append(newRow);
    }
}

// Export for use
window.InvoiceItemsEditor = InvoiceItemsEditor;
