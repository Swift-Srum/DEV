async function editBowser(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    
    try {
        // Get current bowser data from the table row
        const name = row.cells[0].textContent;
        const model = row.cells[1].textContent;
        const capacity = row.cells[2].textContent;
        const supplier = row.cells[3].textContent;
        const dateReceived = row.cells[4].textContent;
        const dateReturned = row.cells[5].textContent;
        const postcode = row.cells[6].textContent;
        const status = row.cells[7].textContent;

        // Show prompts with current values
        const newName = prompt('Enter new name:', name);
        if (!newName) return;

        const newModel = prompt('Enter new model:', model);
        if (!newModel) return;

        const newCapacity = prompt('Enter new capacity (L):', capacity);
        if (!newCapacity) return;

        const newSupplier = prompt('Enter new supplier:', supplier);
        if (!newSupplier) return;

        const newDateReceived = prompt('Enter date received (YYYY-MM-DD):', dateReceived);
        // Allow empty date received
        if (newDateReceived !== null && newDateReceived !== '' && !/^\d{4}-\d{2}-\d{2}$/.test(newDateReceived)) {
            alert('Invalid date format. Please use YYYY-MM-DD');
            return;
        }

        const newDateReturned = prompt('Enter date returned (YYYY-MM-DD):', dateReturned);
        // Allow empty date returned
        if (newDateReturned !== null && newDateReturned !== '' && !/^\d{4}-\d{2}-\d{2}$/.test(newDateReturned)) {
            alert('Invalid date format. Please use YYYY-MM-DD');
            return;
        }

        const newPostcode = prompt('Enter new postcode:', postcode);
        if (!newPostcode) return;

        const validStatuses = ['On Depot', 'Dispatched', 'In Transit', 
            'Maintenance Requested', 'Under Maintenance', 'Ready', 'Out of Service'];
        const newStatus = prompt(
            `Enter new status:\nValid options: ${validStatuses.join(', ')}`, 
            status
        );
        if (!newStatus || !validStatuses.includes(newStatus)) {
            alert('Invalid status selected');
            return;
        }

        // Send update request
        const response = await fetch('../admin/update-bowser.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                id: id,
                name: newName,
                model: newModel,
                capacity: newCapacity,
                supplier: newSupplier,
                date_received: newDateReceived,
                date_returned: newDateReturned,
                postcode: newPostcode,
                status: newStatus
            })
        });

        const result = await response.json();
        
        if (response.ok && result.success) {
            // Update the table row with new values
            row.cells[0].textContent = newName;
            row.cells[1].textContent = newModel;
            row.cells[2].textContent = newCapacity;
            row.cells[3].textContent = newSupplier;
            row.cells[4].textContent = newDateReceived;
            row.cells[5].textContent = newDateReturned;
            row.cells[6].textContent = newPostcode;
            row.cells[7].textContent = newStatus;
            
            alert('Bowser updated successfully');
        } else {
            throw new Error(result.error || 'Failed to update bowser');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'An error occurred while updating the bowser');
    }
}

async function deleteBowser(id) {
    if (!confirm('Are you sure you want to delete this bowser? This action cannot be undone.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('id', id);

        const response = await fetch('/admin/delete-bowser.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Remove the row from the table
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.remove();
            }
            alert('Bowser deleted successfully');
        } else {
            throw new Error(data.error || 'Failed to delete bowser');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'An error occurred while deleting the bowser');
    }
}