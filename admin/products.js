document.addEventListener('DOMContentLoaded', function () {
    const addItemBtn = document.querySelector('.add-item-btn');
    const addItemModal = document.getElementById('addItemModal');
    const closeModal = document.querySelector('.close-modal');
    const cancelBtn = document.getElementById('cancelAddItem');

    const categorySelect = document.getElementById('item-category');
    const subcategoryInput = document.getElementById('item-subcategory');
    const skuInput = document.getElementById('item-sku');

    const viewItemModal = document.getElementById('viewItemModal');
    const editItemModal = document.getElementById('editItemModal');
    const deleteItemModal = document.getElementById('deleteItemModal');
    
    // Get close buttons
    const closeViewModal = document.querySelector('.close-view-modal');
    const closeEditModal = document.querySelector('.close-edit-modal');
    const closeDeleteModal = document.querySelector('.close-delete-modal');

        // Close view modal
        closeViewModal.addEventListener('click', () => {
            viewItemModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        document.getElementById('closeViewItem').addEventListener('click', () => {
            viewItemModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        // Close edit modal
        closeEditModal.addEventListener('click', () => {
            editItemModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        document.getElementById('cancelEditItem').addEventListener('click', () => {
            editItemModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        // Close delete modal
        closeDeleteModal.addEventListener('click', () => {
            deleteItemModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        document.getElementById('cancelDeleteItem').addEventListener('click', () => {
            deleteItemModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === viewItemModal) {
                viewItemModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            } else if (event.target === editItemModal) {
                editItemModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            } else if (event.target === deleteItemModal) {
                deleteItemModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
        
        // Open view modal from inventory list
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('view-btn')) {
                const row = event.target.closest('tr');
                const itemId = row.querySelector('td:nth-child(2)').textContent;
                viewItem(itemId);
            }
        });
        
        // Open edit modal from inventory list
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('edit-btn')) {
                const row = event.target.closest('tr');
                const itemId = row.querySelector('td:nth-child(2)').textContent;
                editItem(itemId);
            }
        });
        
        // Open delete modal from inventory list
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('delete-btn')) {
                const row = event.target.closest('tr');
                const itemId = row.querySelector('td:nth-child(2)').textContent;
                const itemName = row.querySelector('td:nth-child(3)').textContent;
                const category = row.querySelector('td:nth-child(4)').textContent;
                const itemSku = row.querySelector('td:nth-child(5)').textContent;
                
                document.getElementById('delete-item-id').textContent = itemId;
                 document.getElementById('delete-item-category').textContent = category;
                document.getElementById('delete-item-name').textContent = itemName;
                document.getElementById('delete-item-sku').textContent = itemSku;
                
                deleteItemModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            }
        });
        
        // Edit from view modal
        document.getElementById('editFromViewBtn').addEventListener('click', function() {
            const itemId = document.getElementById('view-item-id').textContent;
            viewItemModal.style.display = 'none';
            editItem(itemId);
        });
        
        // Preview image in edit form
        document.getElementById('edit-item-image').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit-image-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Save edited item
        document.getElementById('saveEditItem').addEventListener('click', function() {
            const formData = new FormData();
            
            // Add form fields to FormData
            formData.append('item_id', document.getElementById('edit-item-id').value);
            formData.append('name', document.getElementById('edit-item-name').value);
            formData.append('category', document.getElementById('edit-item-category').value);
            formData.append('subCategory', document.getElementById('edit-item-subcategory').value);
            formData.append('sku', document.getElementById('edit-item-sku').value);
            formData.append('quantity', document.getElementById('edit-item-quantity').value);
            formData.append('price', document.getElementById('edit-item-price').value);
            formData.append('status', document.getElementById('edit-item-status').value);
            formData.append('description', document.getElementById('edit-item-description').value);
            
            // Add image if selected
            const imageInput = document.getElementById('edit-item-image');
            if (imageInput.files.length > 0) {
                formData.append('image', imageInput.files[0]);
            }
            
            // Send update request
            $.ajax({
                url: "http://localhost/fatima/admin/update_inventory.php",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.status === 'success') {
                            alert('Item updated successfully');
                            editItemModal.style.display = 'none';
                            document.body.style.overflow = 'auto';
                            
                            // Refresh inventory data
                            fetchInventoryItems()
                                .then(data => {
                                    inventoryItems = data;
                                    filterInventory();
                                });
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (e) {
                        console.error('Invalid JSON response:', response);
                        alert('Error updating item. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('Error updating item. Please try again.');
                }
            });
        });
        
        // Confirm delete item
        document.getElementById('confirmDeleteItem').addEventListener('click', function() {
            const itemId = document.getElementById('delete-item-id').textContent;
            const category = document.getElementById('delete-item-category').textContent;
        
            
            $.ajax({
                url: "http://localhost/fatima/admin/delete_inventory.php",
                method: 'POST',
                data: { item_id: itemId,
                    category:category
                 },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Item deleted successfully');
                        deleteItemModal.style.display = 'none';
                        document.body.style.overflow = 'auto';
                        
                        // Refresh inventory data
                        fetchInventoryItems()
                            .then(data => {
                                inventoryItems = data;
                                filterInventory();
                            });
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.log("Raw response on error:", xhr.responseText);
                    alert('Error deleting item. Please try again.');
                }
            });
        });
        
        // Function to view item details
        function viewItem(itemId) {
            const item = inventoryItems.find(item => item.id.toString() === itemId.toString());
            
            if (item) {
                document.getElementById('view-item-image').src = item.image;
                document.getElementById('view-item-id').textContent = item.id;
                document.getElementById('view-item-name').textContent = item.name;
                document.getElementById('view-item-category').textContent = item.category;
                document.getElementById('view-item-subcategory').textContent = item.subCategory;
                document.getElementById('view-item-sku').textContent = item.sku;
                document.getElementById('view-item-quantity').textContent = item.quantity;
                
                const formattedPrice = new Intl.NumberFormat('en-PH', {
                    style: 'currency', currency: 'PHP'
                }).format(item.price);
                document.getElementById('view-item-price').textContent = formattedPrice;
                
                document.getElementById('view-item-status').textContent = formatStatus(item.status);
                document.getElementById('view-item-status').className = 'detail-value ' + item.status;
                
                document.getElementById('view-item-lastUpdated').textContent = formatDate(item.lastUpdated);
                document.getElementById('view-item-description').textContent = item.description || 'No description available';
                
                viewItemModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            } else {
                alert('Item not found');
            }
        }
        
        // Function to edit item
        function editItem(itemId) {
            const item = inventoryItems.find(item => item.id.toString() === itemId.toString());
            
            if (item) {
                document.getElementById('edit-item-id').value = item.id;
                document.getElementById('edit-item-name').value = item.name;
                document.getElementById('edit-item-category').value = item.category;
                document.getElementById('edit-item-subcategory').value = item.subCategory;
                document.getElementById('edit-item-sku').value = item.sku;
                document.getElementById('edit-item-quantity').value = item.quantity;
                document.getElementById('edit-item-price').value = item.price;
                document.getElementById('edit-item-status').value = item.status;
                document.getElementById('edit-item-description').value = item.description || '';
                document.getElementById('edit-image-preview').src = item.image;
                
                editItemModal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            } else {
                alert('Item not found');
            }
        }
    

    // Open Modal
    addItemBtn.addEventListener('click', () => {
        addItemModal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    });

    // Close Modal
    function closeAddItemModal() {
        addItemModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('addItemForm').reset();
    }

    closeModal.addEventListener('click', closeAddItemModal);
    cancelBtn.addEventListener('click', closeAddItemModal);
    window.addEventListener('click', event => {
        if (event.target === addItemModal) {
            closeAddItemModal();
        }
    });

    // SKU Generator
    function updateSKU() {
        const category = categorySelect.value.trim();
        const subcategory = subcategoryInput.value.trim();

        if (category && subcategory) {
            let prefix = category.toLowerCase();
            const categoryPrefixMap = {
                'bedroom': 'BED',
                'living-room': 'LIV',
                'dining-room': 'DIN',
                'office': 'OFF',
                'kitchen': 'KIT',
                'outdoor': 'OUT'
            };

            const categoryPrefix = categoryPrefixMap[prefix] || prefix.substring(0, 3).toUpperCase();
            const subPrefix = subcategory.replace(/\s+/g, '-').substring(0, 2).toUpperCase();
            const randomNum = Math.floor(Math.random() * 900 + 100);
            skuInput.value = `${categoryPrefix}-${subPrefix}-${randomNum}`;
        }
    }

    categorySelect.addEventListener('change', updateSKU);
    subcategoryInput.addEventListener('blur', updateSKU);

    let inventoryItems = [];
    const itemsPerPage = 5;
    let currentPage = 1;

    // Fetch Data
    function fetchInventoryItems() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: "http://localhost/fatima/admin/fetch_inventory.php",
                method: 'GET',
                dataType: 'json',
                success: resolve,
                error: (xhr, status, error) => {
                    console.error('Error fetching inventory items:', error);
                    reject(error);
                }
            });
        });
    }

    function formatStatus(status) {
        const map = {
            'in-stock': 'In Stock',
            'low-stock': 'Low Stock',
            'out-of-stock': 'Out of Stock'
        };
        return map[status] || status;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric', month: 'short', day: 'numeric'
        });
    }

    function renderInventory(items, page = 1) {
        const inventoryBody = document.getElementById('inventory-body');
        inventoryBody.innerHTML = '';

        const startIndex = (page - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, items.length);
        
        if (items.length === 0) {
            inventoryBody.innerHTML = `
            <tr><td colspan="10"><div class="no-results">
            <h2>No inventory items found</h2><p>Try adjusting your search or filter criteria</p>
            </div></td></tr>`;
            document.getElementById('pagination').style.display = 'none';
            return;
        }
        
        document.getElementById('pagination').style.display = 'flex';
        document.getElementById('totalItem').textContent = items.length;
        document.getElementById('instock').textContent = items.filter(item => item.status === 'in-stock').length;   
        document.getElementById('instock').textContent = items.filter(item => item.status === 'in-stock').length;   
        document.getElementById('lowstock').textContent = items.filter(item => item.status === 'low-stock').length;   
        document.getElementById('outstock').textContent = items.filter(item => item.status === 'out-of-stock').length;   
        
        for (let i = startIndex; i < endIndex; i++) {
            const item = items[i];
            const formattedPrice = new Intl.NumberFormat('en-PH', {
                style: 'currency', currency: 'PHP'
            }).format(item.price);
            
            const row = document.createElement('tr');
            row.innerHTML = `
            <td><img src="${item.image}" alt="${item.name}" class="thumbnail"></td>
            <td>${item.id}</td>
            <td>${item.name}</td>
            <td>${item.category}</td>
            <td>${item.sku}</td>
            <td>${item.quantity}</td>
            <td>${formattedPrice}</td>
            <td><span class="status ${item.status}">${formatStatus(item.status)}</span></td>
            <td>${formatDate(item.lastUpdated)}</td>
            <td>
            <div class="action-buttons">
            <button class="view-btn">View</button>
            <button class="edit-btn">Edit</button>
            <button class="delete-btn" >Delete</button>
            </div>
            </td>`;
            const instock = items.filter(item => item.status === 'in-stock').length
            inventoryBody.appendChild(row);
        }

        updatePagination(items.length, page);
    }

    function updatePagination(totalItems, currentPage) {
        const paginationElement = document.getElementById('pagination');
        paginationElement.innerHTML = '';
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        const prevButton = document.createElement('button');
        prevButton.innerText = '← Prev';
        prevButton.disabled = currentPage === 1;
        prevButton.addEventListener('click', () => goToPage(currentPage - 1));
        paginationElement.appendChild(prevButton);

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4 && startPage > 1) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageButton = document.createElement('button');
            pageButton.innerText = i;
            if (i === currentPage) pageButton.classList.add('active');
            pageButton.addEventListener('click', () => goToPage(i));
            paginationElement.appendChild(pageButton);
        }

        const nextButton = document.createElement('button');
        nextButton.innerText = 'Next →';
        nextButton.disabled = currentPage === totalPages;
        nextButton.addEventListener('click', () => goToPage(currentPage + 1));
        paginationElement.appendChild(nextButton);
    }

    function goToPage(page) {
        currentPage = page;
        filterInventory();
    }

    function filterInventory() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const categoryFilter = document.getElementById('category-filter').value;
        const statusFilter = document.getElementById('status-filter').value;
        const sortFilter = document.getElementById('sort-filter').value;

        let filteredItems = inventoryItems.filter(item => {
            const matchesSearch =
                item.id.toString().toLowerCase().includes(searchTerm) ||
                item.name.toLowerCase().includes(searchTerm) ||
                item.sku.toLowerCase().includes(searchTerm);

            const matchesCategory = !categoryFilter || item.category === categoryFilter;
            const matchesStatus = !statusFilter || item.status === statusFilter;

            return matchesSearch && matchesCategory && matchesStatus;
        });

        switch (sortFilter) {
            case 'name-asc': filteredItems.sort((a, b) => a.name.localeCompare(b.name)); break;
            case 'name-desc': filteredItems.sort((a, b) => b.name.localeCompare(a.name)); break;
            case 'quantity-low': filteredItems.sort((a, b) => a.quantity - b.quantity); break;
            case 'quantity-high': filteredItems.sort((a, b) => b.quantity - a.quantity); break;
            case 'price-low': filteredItems.sort((a, b) => a.price - b.price); break;
            case 'price-high': filteredItems.sort((a, b) => b.price - a.price); break;
        }

        renderInventory(filteredItems, currentPage);
    }

    // Attach filter and search event listeners
    ['search-input', 'category-filter', 'status-filter', 'sort-filter'].forEach(id => {
        document.getElementById(id).addEventListener('input', () => {
            currentPage = 1;
            filterInventory();
        });
        document.getElementById(id).addEventListener('change', () => {
            currentPage = 1;
            filterInventory();
        });
    });

    // Init fetch
    fetchInventoryItems()
        .then(data => {
            inventoryItems = data;
            filterInventory();
        })
        .catch(error => console.error('Error loading inventory:', error));
});

